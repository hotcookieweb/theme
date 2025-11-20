<?php
/* $stores array is passed in from header.php */
/* $hc_zone is passed in from header.php */
global $hc_stores, $hc_zone;
$placeholder_text = "Select store, enter US ZIP or hit search for geo";
$customer = WC()->customer;
if ($customer && $customer instanceof WC_Customer) {
	$zipcode = $customer->get_shipping_postcode();
  $country = $customer->get_shipping_country();
  $state = $customer->get_shipping_state();
  $city = $customer->get_shipping_city();
}

if (!empty($zipcode) && !empty($city) && !empty ($state)) {
  $placeholder_text = "<strong>" . $zipcode . ": " . $city . ", " . $state . "</strong>"; 
}
else 	if (!empty($hc_zone) && ($hc_zone !== 'Rest of World')) {
    $placeholder_text = "<strong>" . hc_get_store_data('header_title',$hc_zone) . "</strong>";
}


?>
<form id="hot-cookie-delivery" class="delivery-form">
	<input type="hidden" name="hot_cookie_nonce" value="<?php echo wp_create_nonce('hot_cookie_delivery'); ?>">
	<input type="hidden" name="action" value="hc_save_delivery">

	<div class="hc-input-wrapper">
		<label for="hc-zip-input" class="hc-placeholder"><?= $placeholder_text ?></label>
		<input list="stores-locations" id="hc-zip-input" name="zipcode" class="frontpage-input" type="text">
		  <datalist id="stores-locations">
			<?php 
      foreach ($hc_stores as $key => $value) { /* $store and $zone set in header.php */
				$selected = ($key == $hc_zone) ? 'selected' : '';?>
				<option <?= $selected ?> class="store_local_option" data-zone="<?= $key ?>"><?= $value ?></option>
			<?php } ?>
		</datalist>
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

// Autosubmit on selection from datalist
hc_input.addEventListener("change", () => {
  const inputVal = hc_input.value.trim().toLowerCase();
	hc_form.dispatchEvent(new Event("submit"));
});

// Manual search click triggers submit even if input is empty
document.getElementById("hc-submit").addEventListener("click", e => {
  e.preventDefault();
  hc_form.dispatchEvent(new Event("submit"));
});

hc_input.addEventListener("keyup", function(e) {
	if (e.keyCode === 13) {
		e.preventDefault();
		hc_form.dispatchEvent(new Event("submit"));
	}
});

hc_form.addEventListener("submit", function(e) {
	e.preventDefault();

	const zipCodePattern = /^\d{5}$/;
	const inputVal = hc_input.value.trim();

	// Build zoneMap from datalist at time of submit
	const options = document.querySelectorAll("#stores-locations option");
	const zoneMap = {};

	options.forEach(opt => {
		const label = opt.value.trim(); // This is the string shown to the user
		const zoneKey = opt.dataset.zone?.trim(); // This is the canonical zone identifier
		if (label && zoneKey) {
			zoneMap[label] = zoneKey;
		}
	});

  // Helper: Submit form with optional lat/lng and zoneMap
  function submitForm(lat = null, lng = null) {
    const formData = new FormData(hc_form);

    // Determine resolution source
    let resolutionSource = "geo";
    if (zoneMap[inputVal]) {
      resolutionSource = "zone";
      formData.append("zone", zoneMap[inputVal]);
    } else if (zipCodePattern.test(inputVal)) {
      resolutionSource = "zip";
      formData.append("zipcode", inputVal);
    }

    formData.append("resolution_source", resolutionSource);
    if (lat && lng) {
      formData.append("lat", lat);
      formData.append("lng", lng);
    }

    fetch("<?= admin_url('admin-ajax.php'); ?>", {
      method: "POST",
      credentials: "same-origin",
      body: formData
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        placeholder.innerHTML = `<strong> ${data.data.zip}: ${data.data.city}, ${data.data.state}</strong>`;
        document.querySelectorAll('a.current_zone').forEach(link => {
          const currentHref = link.getAttribute('href');

          if (data.data.zone && currentHref) {
            const lastSlashIndex = currentHref.lastIndexOf('/');
            const baseHref = currentHref.substring(0, lastSlashIndex);
            const newHref = `${baseHref}/${data.data.zone}`;
            link.setAttribute('href', newHref);
          }
        });
        hcupdateStoreSwitcher(data.data.zone);
      } else {
        placeholder.textContent = (data.data?.message || "Unknown error") + " Select store, enter US ZIP or hit search for geo";
      }
      hc_input.value = "";
      placeholder.style.opacity = '1';
      placeholder.style.pointerEvents = 'auto';
    })
    .catch(err => {
      placeholder.textContent = "Error occurred: " + err;
      hc_input.value = "";
      placeholder.style.opacity = '1';
      placeholder.style.pointerEvents = 'auto';
    });
  }

  // Trigger geo fallback if input is empty
  if (inputVal.length === 0) {
    navigator.geolocation.getCurrentPosition(
      pos => submitForm(pos.coords.latitude, pos.coords.longitude),
      err => submitForm()
    );
    return;
  }

  // If input is invalid ZIP and not a known zone
  if (!zoneMap[inputVal] && !zipCodePattern.test(inputVal)) {
    placeholder.textContent = "Invalid ZIP: Select store, enter US ZIP or hit search for geo";
    hc_input.value = "";
    placeholder.style.opacity = '1';
    placeholder.style.pointerEvents = 'auto';
    return;
  }

  // Valid zone or ZIP — submit normally
  submitForm();
});
</script>
