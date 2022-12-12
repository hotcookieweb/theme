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
  'lib/customizer.php', // Theme customizer
  'lib/ahotcookie.php'
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

// always start a woocommerce session when first page loads
add_action( 'woocommerce_init', 'create_wc_session');
function create_wc_session() {
  if (is_user_logged_in() || is_admin())
    return;
  if (isset(WC()->session)) {
    if (!WC()->session->has_session()) {
      WC()->session->set_customer_session_cookie(true);
      WC()->customer->set_shipping_country('');
      WC()->customer->set_shipping_postcode ( '' );
      WC()->customer->set_shipping_state( '');
      WC()->customer->set_shipping_city('');
      WC()->customer->set_billing_country('');
      WC()->customer->set_billing_postcode ( '' );
      WC()->customer->set_billing_state( '');
      WC()->customer->set_billing_city('');
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

/**
 * Redirect to page login was request
 *
 * @param $redirect
 * @param $user
 *
 * @return false|string
 */
 add_filter('woocommerce_login_redirect', 'wc_login_redirect', 99 );

 function wc_login_redirect () {
     echo "<script type='text/javascript'>
     history.go(-2);
     </script>";
 }


/**
 * Redirect after registration.
 *
 * @param $redirect
 *
 * @return string
 */
function iconic_register_redirect( $redirect ) {

    return wc_get_page_permalink( 'home' );
}

// add_filter( 'woocommerce_registration_redirect', 'iconic_register_redirect' );
add_filter('woocommerce_product_related_posts_relate_by_category', 'hc_related_by_category');
function hc_related_by_category($product_id) {
  return(false);
}
add_filter('woocommerce_product_related_posts_relate_by_tag','hc_related_by_tag');
function hc_related_by_tag($product_id) {
  return(true);
}
function login_errors(){
  return 'Invalid login';
}
add_filter( 'login_errors', 'login_errors' );


function hotcookie_admin_notices() {
  if (!(get_current_user_id() == 2)) {  /* Only Tony Roug sees admin notices */
       echo '<style>.notice { display: none !important;} </style>';
  }
}
add_action('admin_enqueue_scripts', 'hotcookie_admin_notices');
add_action('login_enqueue_scripts', 'hotcookie_admin_notices');

function author_page_redirect() {
  if ( is_author() ) {
      wp_die('fuck you');
  }
}

add_action( 'template_redirect', 'author_page_redirect' );
