<?php

namespace Roots\Sage\Customizer;

use Roots\Sage\Assets;

/**
 * Add postMessage support
 */
function customize_register($wp_customize) {
  $wp_customize->get_setting('blogname')->transport = 'postMessage';
}
add_action('customize_register', __NAMESPACE__ . '\\customize_register');

/**
 * Customizer JS
 */
function customize_preview_js() {
  wp_enqueue_script('sage/customizer', Assets\asset_path('scripts/customizer.js'), ['customize-preview'], null, true);
}
add_action('customize_preview_init', __NAMESPACE__ . '\\customize_preview_js');

if( function_exists('acf_add_options_page') ) {

	acf_add_options_page(array(
		'page_title' 	=> 'Theme General Settings',
		'menu_title'	=> 'Theme Settings',
		'menu_slug' 	=> 'theme-general-settings',
		'capability'	=> 'edit_posts',
		'redirect'		=> false
	));

}

remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30 );
remove_action( 'woocommerce_before_shop_loop', 'woocommerce_result_count', 20 );


/**
 * Show cart contents / total Ajax
 */
add_filter( 'woocommerce_add_to_cart_fragments', __NAMESPACE__ . '\\woocommerce_header_add_to_cart_fragment' );

function woocommerce_header_add_to_cart_fragment( $fragments ) {
	global $woocommerce;

	ob_start();

	?>
	<a class="webshop-cart pulse" href="<?php echo get_permalink( wc_get_page_id( 'cart' ) ); ?>" title="<?php _e( 'View your shopping cart' ); ?>">$ <?php echo WC()->cart->total ?> <span><?php echo sprintf ( _n( '%d item', '%d items', WC()->cart->get_cart_contents_count() ), WC()->cart->get_cart_contents_count() ); ?></span></a>
	<?php
	$fragments['a.webshop-cart'] = ob_get_clean();
	return $fragments;
}

function mytheme_add_woocommerce_support() {
    add_theme_support( 'woocommerce' );
}

add_action( 'after_setup_theme', __NAMESPACE__ . '\\mytheme_add_woocommerce_support' );

add_filter( 'woocommerce_product_tabs', __NAMESPACE__ . '\\exetera_custom_product_tabs', 98 );
function exetera_custom_product_tabs( $tabs ) {
    // Custom description callback.
    $tabs['description']['callback'] = function() {
      global $post, $product;

		  echo '<div class="left"><h2>Additional Information</h2>';
      // Display the heading and content of the Additional Information tab.
		  do_action( 'woocommerce_product_additional_information', $product );
      echo '</div>';

      // Display the content of the Description tab of not empty
      if (! empty($post->post_content)) {
        echo '<div class="right"><h2>Description</h2>';

        the_content();

        echo '</div>';
      };
    };

    // Remove the additional information tab.
    unset( $tabs['additional_information'] );

    return $tabs;
}

/**
 * Display custom field on the front end
 * @since 1.0.0
 */
function cfwc_display_custom_field() {
 global $post;
 // Check for the custom field value
 $product = wc_get_product( $post->ID );
 $title = $product->get_meta( 'custom_text_field_title' );
 if( $title ) {
 // Only display our field if we've got a value for the field title
 printf(
 'test',
 esc_html( $title )
 );
 }
}
add_action( 'woocommerce_before_add_to_cart_button', __NAMESPACE__ . '\\cfwc_display_custom_field' );

//remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10 );
//add_action( 'woocommerce_checkout_order_review', 'woocommerce_checkout_coupon_form', 15 );

function hc_shop_content() {
	  if( get_field('display_content_on_shop_page') == 'show' )
      the_content();
}
add_action( 'woocommerce_after_shop_loop_item_title', __NAMESPACE__ . '\\hc_shop_content', 6 );

/**
 * Changes the redirect URL for the Return To Shop button in the cart.
 *
 * @return string
 */
function wc_empty_cart_redirect_url() {
	return get_home_url();
}
add_filter( 'woocommerce_return_to_shop_redirect', __NAMESPACE__ . '\\wc_empty_cart_redirect_url' );

/**
* WooCommerce: Hide 'Coupon form' on checkout page if a coupon was already applied in the cart
*/
function woocommerce_coupons_enabled_checkout( $coupons_enabled ) {
    global $woocommerce;
    if ( ! empty( $woocommerce->cart->applied_coupons ) ) {
        return false;
    }
    return $coupons_enabled;
}
add_filter( 'woocommerce_coupons_enabled', __NAMESPACE__ . '\\woocommerce_coupons_enabled_checkout' );

function woocommerce_change_text($translated, $text, $domain) {
  switch ( $translated ) {
    case 'Invoice':
      $translated= __( 'Receipt', 'woocommerce' );
      break;
    case 'PDF Invoice data':
        $translated= __( 'PDF Receipt data', 'woocommerce' );
        break;
    case 'Invoice number:':
        $translated= __( 'Invoice Number', 'woocommerce' );
        break;
      case 'Invoice date:':
        $translated= __( 'Invoice Date', 'woocommerce' );
        break;
  }
  return $translated;
}
add_filter( 'gettext', __NAMESPACE__ . '\\woocommerce_change_text', 20, 3 );
