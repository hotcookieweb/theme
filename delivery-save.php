<?php
add_action('wp_ajax_hc_save_delivery', 'hc_save_delivery');
add_action('wp_ajax_nopriv_hc_save_delivery', 'hc_save_delivery');
add_action('wp_ajax_hc_set_store', 'hc_set_store');
add_action('wp_ajax_nopriv_hc_set_store', 'hc_set_store');

function hc_set_store() {
  if (!empty($_POST['zone'])) {
    WC()->session->set('current_zone', sanitize_text_field($_POST['zone']));
	wp_send_json_success(['zone' => sanitize_text_field($_POST['zone'])]);
  }
  else {
	error_log("hc_set_store: no zone set in POST");
	wp_send_json_error(['message' => 'No zone set in Post.']);
  }
}


function hc_save_delivery() {
	check_ajax_referer('hot_cookie_delivery', 'hot_cookie_nonce');

	$resolutionSource = $_POST['resolution_source'] ?? 'unknown';
	$zone = '';

    switch ($resolutionSource) {
	case 'zip':
		$zipcode = sanitize_text_field($_POST['zipcode']);
		if (!empty($zipcode)) {
			if (!preg_match('/^\d{5}$/', $zipcode)) {
				wp_send_json_error(['message' => 'Invalid ZIP.']);
			}
			$location = lookupLocationFromZip($zipcode);
			if (empty($location['zip'])) { // Try our local file
				error_log("ZIP {$zipcode} not found via Nominatim, trying local CSV");
				$location = hc_ziplocal($zipcode);
				if (empty($location)) {
					wp_send_json_error(['message' => 'Invalid ZIP.']);
				}
			}
		}
		break;

	case 'zone':
		$zone = sanitize_text_field($_POST['zone']);
		$locarray = explode(',',hc_get_store_data('store_address', $zone));
		$stateziparray = explode(' ',trim($locarray[2]) ?? '');
		if ((count($locarray) != 3) || (count($stateziparray) != 2)) {
			wp_send_json_error(['message' => 'Store address error.']);
		}
		$location = [
			'zip' => substr(trim($stateziparray[1]), 0, 5),
			'state' => trim($stateziparray[0] ?? ''),
			'city' => trim($locarray[1] ?? ''),
			'address' => '',
			'country' => 'US'
		];
		break;

	case 'geo':
	default:
		if (!empty($_POST['lat']) && !empty($_POST['lng'])) {
			$lat = floatval($_POST['lat']);
			$lng = floatval($_POST['lng']);
			$location = lookupLocationFromLatLng($lat, $lng);
			if (empty($location['zip'])) { // Try our local file
				$location = lookupLocationFromIP();
				if (empty($location) || empty($location['zip'])) {
					wp_send_json_error(['message' => 'Could not get location.']);
				}
			}
		} else {
			$location = lookupLocationFromIP();
			if (empty($location) || empty($location['zip'])) {
				wp_send_json_error(['message' => 'Could not get location.']);
			}
		}
		break;
	}

	if (empty($location['state']))
		$location['state'] = '';
	else {
		$states = WC()->countries->get_states('US');
		$abbrs = array_keys($states);
		$input = strtoupper(trim($location['state']));

		if (in_array($input, $abbrs, true)) {
			$location['state'] = $input;
		} else {
			$state_abbr = array_flip($states);
			$normalized_name = ucwords(strtolower($location['state']));
			$location['state'] = $state_abbr[$normalized_name] ?? $location['state'];
		}
	}
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

	$matched_zone = hc_set_current_zone($location, $zone);
	
	wp_send_json_success([
		'zip' => $location['zip'],
		'state' => $location['state'],
		'city' => $location['city'],
		'country' => $location['country'],
		'address' => '',
		'zone' => $matched_zone,
		'title' => hc_get_store_data('header_title',$matched_zone)
	]);
}

function hc_set_current_zone($location, $zone = '') {
	$package = array(
		'destination' => array(
			'country'  => $location['country'],
			'state'    => $location['state'],
			'postcode' => $location['zip'],
			'city'     => $location['city'],
			'address'  => '', // optional
		),
		'contents'        => array(),
		'contents_cost'   => 0,
		'applied_coupons' => array(),
	);

	if (empty($zone)) {
		$zone = WC_Shipping_Zones::get_zone_matching_package($package);
		$matched_zone = $zone ? $zone->get_zone_name() : 'national';
	}
	else {
		$matched_zone = $zone;
	}
	WC()->session->set('current_zone', $matched_zone);
	return $matched_zone;
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
            'zip' => $data['address']['postcode'] ?? null,
			'address' => ''
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
            'zip' => $data[0]['address']['postcode'] ?? $zip,
			'address' => ''
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
	if ($ip === '::1' || $ip === '127.0.0.1') {
		$ip = '8.8.8.8'; // fallback for local dev
	}

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

function hc_get_store_data($field, $zone) {
	$post = get_page_by_path('/our-stores/' . $zone);
	if (!$post || !isset($post->ID)) {
		error_log("hc_get_store_data: could not find 'our-stores/" . $zone);
		return 'hc_get_store_data error';
	}
	return get_field($field, $post->ID);
}
?>