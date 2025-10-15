<?php
add_action('wp_ajax_hc_save_delivery', 'hc_save_delivery');
add_action('wp_ajax_nopriv_hc_save_delivery', 'hc_save_delivery');

function hc_save_delivery() {
	check_ajax_referer('hot_cookie_delivery', 'hot_cookie_nonce');

	$zipcode = sanitize_text_field($_POST['zipcode']);
	if (!empty($zipcode)) {
		if (!preg_match('/^\d{5}$/', $zipcode)) {
			wp_send_json_error(['message' => 'Invalid ZIP, must be 5 digits']);
		}
		$location = lookupLocationFromZip($zipcode);
		if (empty($location['zip']) || empty($location['city'] || empty($location['state']))) { // Try our local file
			error_log("ZIP {$zipcode} not found via Nominatim, trying local CSV");
			$location = hc_ziplocal($zipcode);
			if (empty($location)) {
				wp_send_json_error(['message' => 'Could not determine location from ZIP']);
			}
		}
	} else if (!empty($_POST['lat']) && !empty($_POST['lng'])) {
		$lat = floatval($_POST['lat']);
		$lng = floatval($_POST['lng']);
		$location = lookupLocationFromLatLng($lat, $lng);
		if (empty($location['zip'])) { // Try our local file
			$location = lookupLocationFromIP();
			if (empty($location) || empty($location['zip'])) {
				wp_send_json_error(['message' => 'Could not determine ZIP from location']);
			}
		}
	} else {
		wp_send_json_error(['message' => 'No ZIP or location provided']);
	}

	if (empty($location['state']))
		$location['state'] = '';
	if (empty($location['country']))
		$location['country'] = 'US';
	if (empty($location['city'])) {
		$location['city'] = '';
	}
	WC()->customer->set_shipping_postcode($location['zip']);
	WC()->customer->set_shipping_state($location['state']);
	WC()->customer->set_shipping_country($location['country']);
	WC()->customer->set_shipping_city($location['city']);
	WC()->customer->set_shipping_address_1('');
	WC()->customer->set_shipping_address_2('');
	WC()->customer->set_shipping_first_name('');
	WC()->customer->set_shipping_last_name('');
	WC()->customer->set_shipping_company('');
	WC()->customer->set_shipping_phone('');

	$data_store = WC_Data_Store::load('shipping-zone');
	$raw_zones = $data_store->get_zones();
	$matched_zone = null;

	foreach ($raw_zones as $raw_zone) {
		$zone = new WC_Shipping_Zone($raw_zone);
		$zone_locations = $zone->get_zone_locations();

		foreach ($zone_locations as $loc) {
			if ($loc->type === 'postcode' && wc_format_postcode($loc->code, $location['country']) === wc_format_postcode($location['zip'], $location['country'])) {
				$matched_zone = $zone->get_zone_name();
				break 2;
			}
			if ($loc->type === 'state' && $loc->code === $location['country'] . ':' . $location['state']) {
				$matched_zone = $zone->get_zone_name();
				break 2;
			}
			if ($loc->type === 'country' && $loc->code === $location['country']) {
				$matched_zone = $zone->get_zone_name();
				break 2;
			}
		}
	}

	if (!$matched_zone) {
		$matched_zone = 'National';
	}

	WC()->session->set('delivery_zone', $matched_zone);
	
	wp_send_json_success([
		'zone' => $matched_zone,
		'zipcode' => $location['zip'],
		'state' => $location['state'],
		'city' => $location['city']
	]);
}
function lookupLocationFromLatLng($lat, $lng) {
    $url = "https://nominatim.openstreetmap.org/reverse?format=json&lat={$lat}&lon={$lng}&addressdetails=1";
    $opts = ['http' => ['header' => "User-Agent: hotcookie.com"]];
    $context = stream_context_create($opts);
    $response = file_get_contents($url, false, $context);
    $data = json_decode($response, true);

    if (!empty($data['address'])) {
        return [
            'city' => $data['address']['city'] ?? $data['address']['town'] ?? $data['address']['village'] ?? null,
            'state' => $data['address']['state'] ?? null,
            'zip' => $data['address']['postcode'] ?? null
        ];
    }

    return null;
}
function lookupLocationFromZip($zip) {
    $url = "https://nominatim.openstreetmap.org/search?format=json&q={$zip}&countrycodes=us&addressdetails=1&limit=1";
    $opts = ['http' => ['header' => "User-Agent: hotcookie.com"]];
    $context = stream_context_create($opts);
    $response = file_get_contents($url, false, $context);
    $data = json_decode($response, true);

    if (!empty($data[0]['address'])) {
        return [
            'city' => $data[0]['address']['city'] ?? $data[0]['address']['town'] ?? $data[0]['address']['village'] ?? null,
            'state' => $data[0]['address']['state'] ?? null,
            'zip' => $data[0]['address']['postcode'] ?? $zip
        ];
    }

    return null;
}

function hc_ziplocal($zip) {
	$filepath = get_template_directory() . '/assets/zipcodes.csv';
	if (!file_exists($filepath)) {
		throw new Exception("CSV file not found: $filepath");
	}

	$handle = fopen($filepath, 'r');
	if (!$handle) {
		throw new Exception("Unable to open CSV file: $filepath");
	}

	$headers = fgetcsv($handle); // read header row
	if (!in_array('zipcode', $headers)) {
		fclose($handle);
		error_log('CSV Headers: ' . implode(', ', $headers));
		throw new Exception("CSV missing 'zipcode' column");
	}
	while (($row = fgetcsv($handle)) !== false) {
		$location = array_combine($headers, $row);
		if ($location['zipcode'] === $zip) {
			fclose($handle);
			$headers = null; // to indicate we found the ZIP
			break;
		}
	}
	return $headers ? null : $location; // if $headers is still set, we didn't find the ZIP
}

function lookupLocationFromIP() {
	$ip = $_SERVER['REMOTE_ADDR'];
	$apiKey = 'hot_cookie'; // e.g. ipapi.co, ipinfo.io, etc.
	$url = "https://ipapi.co/{$ip}/json/";

	$response = file_get_contents($url);
	$data = json_decode($response, true);

	if (isset($data['error']) || strpos($http_response_header[0], '429') !== false) {
		if (class_exists('SimpleLogger')) {
			SimpleLogger()->info('IP geolocation quota exceeded', [
				'ip' => $ip,
				'provider' => 'ipapi.co',
				'response' => $data
			]);
		}
		return null;
	}

	if (!empty($data['postal']) && !empty($data['region']) && !empty($data['city'])) {
		return [
			'zip' => $data['postal'],
			'state' => $data['region'],
			'city' => $data['city'],
			'country' => $data['country'] ?? 'US'
		];
	}
	return null;
}
?>