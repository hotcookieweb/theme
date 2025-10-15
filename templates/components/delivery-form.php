
<form id="hot-cookie-delivery" class="delivery-form">
	<input type="hidden" name="hot_cookie_nonce" value="<?php echo wp_create_nonce('hot_cookie_delivery'); ?>">
	<input type="hidden" name="action" value="hc_save_delivery">
	<input autocomplete="off" class="frontpage-input" maxlength="5" id="hc-zip-input" name="zipcode" placeholder="US destination ZIP or search for geo" type="text" pattern="[0-9]{5}" value="">
	<button class="icon-search" type="submit" id="hc-submit" value="Search" title="HotCookie Search">Search</button>
</form>

<style>
.zip-error {
	border: 2px solid red !important;
	color: red;
}
</style>

<script>
const hc_form = document.querySelector(".delivery-form");
const hc_input = document.getElementById("hc-zip-input");

hc_input.addEventListener("keyup", function(e) {
	if (e.keyCode === 13) {
		e.preventDefault();
		hc_form.dispatchEvent(new Event("submit"));
	}
});

hc_input.addEventListener("input", function () {
	hc_input.classList.remove("zip-error");
	hc_input.placeholder = "Enter US destination ZIP or hit search for geo";
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
				hc_input.placeholder = `Store set to ${data.data.city}, ${data.data.state}`;
				hc_input.classList.remove("zip-error");
				hc_input.value = "";
			} else {
				hc_input.placeholder = (data.data?.message || "Unknown error") + "Enter US destination ZIP or hit search for geo";
				hc_input.classList.add("zip-error");
				hc_input.value = "";
			}
		})
		.catch(err => {
			hc_input.placeholder = "Error occurred: " + err;
			hc_input.classList.add("zip-error");
			hc_input.value = "";
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
				console.error("Geolocation error:", error);
				hc_input.placeholder = "Get Geocode failed. Enter US destination ZIP";
				hc_input.classList.add("zip-error");
				hc_input.value = "";
			}
		);
		return;
	}

	// If ZIP is invalid
	if (!zipCodePattern.test(zipcode)) {
		hc_input.placeholder = "Invalid ZIP. Enter US destination ZIP or search for geo";
		hc_input.classList.add("zip-error");
		hc_input.value = "";
		return;
	}

	// ZIP is valid, submit normally
	submitForm();
});
</script>
