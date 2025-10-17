<?php
$placeholder_text = "Enter US destination ZIP or hit search for geo";
$customer = WC()->customer;
if ($customer && $customer instanceof WC_Customer) {
	$zipcode = $customer->get_shipping_postcode();
	
	$zone    = WC()->session->get('delivery_zone');
	$store_title = WC()->session->get('store_title');
	if (empty($zone) || empty($store_title)) {
		$store_title = hc_set_delivery_zone($location);
	}

	if (!empty($zipcode) && !empty($store_title)) {
		$placeholder_text = "<strong>" . $zipcode . ": " . $store_title . "</strong>"; 
	}
	else {
		$placeholder_text = "Enter US destination ZIP or hit search for geo";
	}
}
?>
<form id="hot-cookie-delivery" class="delivery-form">
	<input type="hidden" name="hot_cookie_nonce" value="<?php echo wp_create_nonce('hot_cookie_delivery'); ?>">
	<input type="hidden" name="action" value="hc_save_delivery">

	<div class="hc-input-wrapper">
		<label for="hc-zip-input" class="hc-placeholder"><?= $placeholder_text ?></label>
		<input id="hc-zip-input" name="zipcode" class="frontpage-input" type="text">
	</div>

	<button class="icon-search" type="submit" id="hc-submit" value="Search" title="HotCookie Search">Search</button>
</form>

<script>
const hc_form = document.querySelector(".delivery-form");
const hc_input = document.getElementById("hc-zip-input");
const placeholder = document.querySelector('.hc-placeholder');

function updateLabelVisibility() {
  const hasText = hc_input.value.trim().length > 0;
  const isFocused = document.activeElement === hc_input;

  // Hide label if input has text or is focused
  placeholder.style.opacity = hasText || isFocused ? '0' : '1';
  placeholder.style.pointerEvents = hasText || isFocused ? 'none' : 'auto';
}

hc_input.addEventListener('input', updateLabelVisibility);
hc_input.addEventListener('focus', updateLabelVisibility);
hc_input.addEventListener('blur', updateLabelVisibility);

// Initial state
updateLabelVisibility();

hc_input.addEventListener("keyup", function(e) {
	if (e.keyCode === 13) {
		e.preventDefault();
		hc_form.dispatchEvent(new Event("submit"));
	}
});

hc_form.addEventListener("submit", function(e) {
	e.preventDefault();

	const zipCodePattern = /^\d{5}$/;
	const zipcode = hc_input.value;

	// Helper: Submit form with optional lat/lng
	function submitForm(lat = null, lng = null) {
		const formData = new FormData(hc_form);
		if (lat && lng) {
			formData.append("lat", lat);
			formData.append("lng", lng);
		}

		console.log([...formData.entries()]);

		fetch("<?= admin_url('admin-ajax.php'); ?>", {
			method: "POST",
			credentials: "same-origin",
			body: formData
		})
		.then(res => res.json())
		.then(data => {
			if (data.success) {
				placeholder.innerHTML = `<strong> ${data.data.zip}: ${data.data.title} </strong>`;
				hc_input.value = "";
				placeholder.style.opacity = '1';
				placeholder.style.pointerEvents = 'auto';
			} else {
				placeholder.textContent  = (data.data?.message || "Unknown error") + " Enter US destination ZIP or hit search for geo";
				hc_input.value = "";
				placeholder.style.opacity = '1';
				placeholder.style.pointerEvents = 'auto';
			}
		})
		.catch(err => {
			placeholder.textContent = "Error occurred: " + err;
			hc_input.value = "";
			placeholder.style.opacity = '1';
			placeholder.style.pointerEvents = 'auto';
		});
	}

	// If ZIP is empty, try geolocation
	if (zipcode.length === 0) {
		navigator.geolocation.getCurrentPosition(
			function successCallback(position) {
				const lat = position.coords.latitude;
				const lng = position.coords.longitude;
				submitForm(lat, lng);
			},
			function errorCallback(error) {
				submitForm(); // Fallback to IP lookup
			}
		);
		return;
	}

	// If ZIP is invalid
	if (!zipCodePattern.test(zipcode)) {
		placeholder.textContent = "Invalid ZIP. Enter US destination ZIP or search for geo";
		hc_input.value = "";
		placeholder.style.opacity = '1';
		placeholder.style.pointerEvents = 'auto';
		return;
	}

	// ZIP is valid, submit normally
	submitForm();
});
</script>
