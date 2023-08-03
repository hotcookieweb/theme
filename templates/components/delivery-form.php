
	<?php
		$data_store = WC_Data_Store::load('shipping-zone');
		$raw_zones = $data_store->get_zones();
		foreach ( $raw_zones as $raw_zone ) {
			$zone = new WC_Shipping_Zone($raw_zone);
			$locations[$zone->get_zone_name()] = $zone->get_formatted_location(1000);
		}
		?>
	<input autocomplete="off" class="frontpage-input" maxlength="5" id="hc-zip-input" placeholder="Enter US destination ZIP (e.g 94114)" type="text" pattern="[0-9]{5}" value="">

	<button class="icon-search" href="#" id="hc-submit" title="HotCookie Search">Search</button>

	<script>

	var hc_submit = document.getElementById("hc-submit");

	var hc_input = document.getElementById("hc-zip-input");

	hc_input.addEventListener("keyup", function(e) {
		if (e.keyCode == 13) {
			hc_submit.click();
		}
	});

	hc_submit.addEventListener("click", function() {
		var zipCodePattern = /^\d{5}$/;
		var zipcode = hc_input.value;
		var url = "home/";
		var zonelast = '';
		if (zipCodePattern.test(zipcode)) {
			var locations = <?= json_encode($locations) ?>;
      Object.entries(locations).forEach(([zone, location], index, arr) => {
        console.log(zone, location);
				if (location.includes(zipcode)) {
					url = "delivery/" + zone;
				}
				zonelast = zone;
			});
			if (url == "home/") {
				url = "delivery/" + zonelast; /* should  be national */
			}
			url += "?zipcode=" + zipcode;
			window.location.href = url;
		}
		else {
			hc_input.value = "";
		}
	});

</script>
