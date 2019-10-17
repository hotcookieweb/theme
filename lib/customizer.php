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

add_action( 'woocommerce_after_shop_loop_item_title', __NAMESPACE__ . '\\wc_add_long_description' );
/**
 * WooCommerce, Add Long Description to Products on Shop Page
 *
 * @link https://wpbeaches.com/woocommerce-add-short-or-long-description-to-products-on-shop-page
 */
function wc_add_long_description() {
	global $product;

	?>
        <!-- <div itemprop="description">
            <?php // echo apply_filters( 'the_content', $product->post->post_content ) ?>
        </div> -->
	<?php
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

add_theme_support( 'wc-product-gallery-lightbox' );

add_filter( 'woocommerce_product_tabs', __NAMESPACE__ . '\\exetera_custom_product_tabs', 98 );
function exetera_custom_product_tabs( $tabs ) {

    // Custom description callback.
    $tabs['description']['callback'] = function() {
      global $post, $product;

		  echo '<div class="left"><h2>Additional Information</h2>';
      // Display the heading and content of the Additional Information tab.
		  do_action( 'woocommerce_product_additional_information', $product );

      // Display the content of the Description tab of not empty
      if (! empty($post->post_content)) {
        echo '</div><div class="right"><h2>Description</h2>';
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





remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10 );
add_action( 'woocommerce_checkout_order_review', 'woocommerce_checkout_coupon_form', 15 );


add_filter( 'gettext', __NAMESPACE__ . '\\woocommerce_rename_coupon_field_on_cart', 10, 3 );
add_filter( 'gettext', __NAMESPACE__ . '\\woocommerce_rename_coupon_field_on_cart', 10, 3 );
add_filter('woocommerce_coupon_error', __NAMESPACE__ . '\\rename_coupon_label', 10, 3);
add_filter('woocommerce_coupon_message', __NAMESPACE__ . '\\rename_coupon_label', 10, 3);
add_filter('woocommerce_cart_totals_coupon_label', __NAMESPACE__ . '\\rename_coupon_label',10, 1);
add_filter( 'woocommerce_checkout_coupon_message', __NAMESPACE__ . '\\woocommerce_rename_coupon_message_on_checkout' );


function woocommerce_rename_coupon_field_on_cart( $translated_text, $text, $text_domain ) {
	// bail if not modifying frontend woocommerce text
	if ( is_admin() || 'woocommerce' !== $text_domain ) {
		return $translated_text;
	}
	if ( 'Coupon:' === $text ) {
		$translated_text = 'Discount Code:';
	}

	if ('Coupon has been removed.' === $text){
		$translated_text = 'Discount code has been removed.';
	}

	if ( 'Apply coupon' === $text ) {
		$translated_text = 'Apply Discount';
	}

	if ( 'Coupon code' === $text ) {
		$translated_text = 'Discount Code';

	}

	return $translated_text;
}


// rename the "Have a Coupon?" message on the checkout page
function woocommerce_rename_coupon_message_on_checkout() {
	return 'Have an Discount Code?' . ' ' . __( 'Click here to enter your code', 'woocommerce' ) . '';
}


function rename_coupon_label($err, $err_code=null, $something=null){

	$err = str_ireplace("Coupon","Discount Code ",$err);

	return $err;
}

// default to billing address is shipping address
add_filter( 'woocommerce_ship_to_different_address_checked', '__return_false' );
