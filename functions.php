<?php
/**
 * Sage includes
 *
 * The $sage_includes array determines the code library included in your theme.
 * Add or remove files to the array as needed. Supports child theme overrides.
 *
 * Please note that missing files will produce a fatal error.
 *
 * @link https://github.com/roots/sage/pull/1042
 */
$sage_includes = [
  'lib/assets.php',    // Scripts and stylesheets
  'lib/extras.php',    // Custom functions
  'lib/setup.php',     // Theme setup
  'lib/titles.php',    // Page titles
  'lib/wrapper.php',   // Theme wrapper class
  'lib/customizer.php' // Theme customizer
];

foreach ($sage_includes as $file) {
  if (!$filepath = locate_template($file)) {
    trigger_error(sprintf(__('Error locating %s for inclusion', 'sage'), $file), E_USER_ERROR);
  }

  require_once $filepath;
}
unset($file, $filepath);

// add_action('get_header', 'maintenance_mode');
function maintenance_mode() {

      if ( !current_user_can( 'edit_themes' ) || !is_user_logged_in() ) {wp_die("Sorry, we're baking some updates. They'll be out of the oven soon!");}

}

/* assume billing address is shipping address unless customer checks different */
add_filter( 'woocommerce_ship_to_different_address_checked', '__return_true');

// always start a woocommerce session when first page loads
add_action( 'woocommerce_init', 'create_wc_session');
function create_wc_session() {
  if (is_user_logged_in() || is_admin())
    return;
  if (isset(WC()->session)) {
    if (!WC()->session->has_session()) {
      WC()->session->set_customer_session_cookie(true);
      WC()->customer->set_shipping_postcode ( "94114" );
    }
  }
}

add_action('init', 'get_custom_coupon_code_to_session');
function get_custom_coupon_code_to_session(){
    if( isset($_GET['coupon_code']) ){
        $coupon_code = esc_attr( $_GET['coupon_code'] );
        WC()->session->set( 'coupon_code', $coupon_code ); // Set the coupon code in session
    }
}

add_action( 'woocommerce_cart_coupon', 'add_coupon_to_cart', 10, 0 );
function add_coupon_to_cart( ) {
    // Set coupon code
    $coupon_code = WC()->session->get('coupon_code');
    if ( ! empty( $coupon_code )) {
      echo '<script>  coupon_code.value="' . $coupon_code . '"</script>';
   }
}
