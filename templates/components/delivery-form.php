
	<input autocomplete="off" class="frontpage-input" maxlength="5" id="hc-address-input" placeholder="Enter destination ZIP (e.g 94114)" type="text" pattern="[0-9]{5}" value="">
	<button class="icon-search" href="#" id="hc-submit" title="HotCookie Search">Search</button>

	<script>

	var hc_submit = document.getElementById("hc-submit");

	var hc_input = document.getElementById("hc-address-input");

	serialize = function(obj) {
		var str = [];
		for (var p in obj)
		if (obj.hasOwnProperty(p)) {
			str.push(encodeURIComponent(p) + "/" + encodeURIComponent(obj[p]));
		}
		return str.join("&");
	}

	hc_input.addEventListener("keyup", function(e) {
		if (e.keyCode == 13) {
			hc_submit.click();
		}
	});

	hc_submit.addEventListener("click", function() {
		var zipCodePattern = /^\d{5}$/;
		var zipcode = document.getElementById("hc-address-input").value;
		var url = "home";
		if (zipCodePattern.test(zipcode)) {
			var castro_zips = ["94122","94116","94132","94117","94114","94131","94127","94112","94134","94110","94124"];
			var polk_zips = ["94129","94123","94115","94109","94133","94111","94104","94105","94108","94102","94103","94107","94121","94118"];

			if (castro_zips.includes(zipcode)) {
				url = "delvery/castro-sf";
			} else if (polk_zips.includes(zipcode)) {
				url = "delivery/polk-sf";
			} else {
				url = "delivery/national";
			}
			url += "?zipcode=" + zipcode;
		}
		window.location.href = url;
	});
	</script>
