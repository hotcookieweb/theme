<?php
/*
* If zipcode was passed as an argument,
* save in customer session data
*/
$zipcode = sanitize_text_field( $_GET["zipcode"] );

if ($zipcode) {

	$wc_customer = WC()->customer;
	$wc_customer->set_shipping_postcode ( $zipcode );
	$wc_customer->save_data();
}
?>
