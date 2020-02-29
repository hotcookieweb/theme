
	<input autocomplete="off" class="frontpage-input" maxlength="5" id="wls-address-input" placeholder="Enter your 5 Digit ZIP Code (e.g 94114)" type="text" pattern="[0-9]{5}" value="">
	<button class="icon-search" href="#" id="wls-submit" title="HotCookie Search">Search</button>

	<script>
	    var wls_zips = ["94102", "94103", "94104", "94107", "94108", "94109", "94110", "94112", "94114", "94115",
	               "94116", "94117", "94118", "94122", "94123", "94127", "94131", "94134", "94143", "94158",
	               "94005", "94014", "94105", "94111", "94121", "94124", "94129", "94132", "94133", "94188",
	               "94005", "94014", "94105", "94111", "94121", "94124", "94129", "94132", "94133", "94188"];

	    var wls_submit = document.getElementById("wls-submit");

	    var wls_input = document.getElementById("wls-address-input");

	    serialize = function(obj) {
	      var str = [];
	      for (var p in obj)
	        if (obj.hasOwnProperty(p)) {
	          str.push(encodeURIComponent(p) + "/" + encodeURIComponent(obj[p]));
	        }
	      return str.join("&");
	    }

	    wls_input.addEventListener("keyup", function(e) {
	      if (e.keyCode == 13) {
	        wls_submit.click();
	      }
	    });

	    wls_submit.addEventListener("click", function() {
	      var zip = document.getElementById("wls-address-input").value;
	      var query = [];
	      query["delivery"] = [];

	      if (wls_zips.includes(zip)) {
	        query["delivery"].push("local");
	      } else {
	        query["delivery"].push("national");
	      }

	      query = "" + serialize(query);

	      window.location.href = query;
	    });
	</script>
