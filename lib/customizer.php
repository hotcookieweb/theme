<?php

namespace Roots\Sage\Customizer;

use Roots\Sage\Assets;

require_once "alias-product-attributes.php";
require_once "delivery-save.php";


remove_action('woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30);
remove_action('woocommerce_before_shop_loop', 'woocommerce_result_count', 20);

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

add_action('init', function () {
    if (function_exists('acf_add_options_page')) {
        acf_add_options_page([
            'page_title' => __('Theme Settings', 'your-text-domain'),
            'menu_title' => __('Settings', 'your-text-domain'),
            'menu_slug'  => 'theme-settings',
            'capability' => 'edit_posts',
            'redirect'   => false
        ]);
    }
});

add_action('after_setup_theme', __NAMESPACE__ . '\\mytheme_add_woocommerce_support');
function mytheme_add_woocommerce_support() {
  add_theme_support('woocommerce');
}

/**
 * Show cart contents / total Ajax
 */
add_filter('woocommerce_add_to_cart_fragments', __NAMESPACE__ . '\\woocommerce_header_add_to_cart_fragment');
function woocommerce_header_add_to_cart_fragment($fragments) {
  global $woocommerce;

  ob_start();

  ?>
	<a class="webshop-cart pulse" href="<?php echo get_permalink(wc_get_page_id('cart')); ?>" title="<?php _e('View your shopping cart');?>">$ <?php echo WC()->cart->total ?> <span><?php echo sprintf(_n('%d item', '%d items', WC()->cart->get_cart_contents_count()), WC()->cart->get_cart_contents_count()); ?></span></a>
	<?php
  $fragments['a.webshop-cart'] = ob_get_clean();
  return $fragments;
}


function hc_shop_content() {
  if (get_field('display_content_on_shop_page') == 'show') {
    echo get_the_content(null, false, null);
  }

}
add_action('woocommerce_after_shop_loop_item_title', __NAMESPACE__ . '\\hc_shop_content', 6);

/**
 * Changes the redirect URL for the Return To Shop button in the cart.
 *
 * @return string
 */
function wc_empty_cart_redirect_url() {
  return get_home_url();
}
add_filter('woocommerce_return_to_shop_redirect', __NAMESPACE__ . '\\wc_empty_cart_redirect_url');

/**
 * WooCommerce: Hide 'Coupon form' on checkout page if a coupon was already applied in the cart
 */
function woocommerce_coupons_enabled_checkout($coupons_enabled) {
  global $woocommerce;
  if (!empty($woocommerce->cart->applied_coupons)) {
    return false;
  }
  return $coupons_enabled;
}
add_filter('woocommerce_coupons_enabled', __NAMESPACE__ . '\\woocommerce_coupons_enabled_checkout');

add_filter('woocommerce_after_single_product', __NAMESPACE__ . '\\heart_option_hack', 10);
function heart_option_hack() {
  global $product;

  if ($product->get_slug() == 'heart-cookie-cake') {?>
    <script>
      xxElement = document.getElementsByClassName("wc-pao-addons-container")[0];
      xxElement.input = document.getElementById("addon-49833-enter-xxyy-text-e-g-jbar-0");
      selectElement = document.getElementById("pa_heart-cake-writing");
      if (selectElement.value != 'xx-yy') {
        xxElement.style.display = "none";
      }
      selectElement.onchange = function () {
        if (selectElement.value == 'xx-yy') {
          xxElement.style.display = "";
        }
        else {
          xxElement.style.display = "none";
          xxElement.value = "";
        }
      }
    </script>
  <?php }
}

add_filter('woocommerce_add_cart_item_data', __NAMESPACE__ . '\\product_description', 20, 2);
function product_description($cart_item_data, $product_id) {
  if (get_field('description_packing', $product_id) == 'show') {
    $cart_item_data['description_packing'] = wc_get_product($product_id)->get_description();
  }
  return ($cart_item_data);
}

add_filter('woocommerce_get_item_data', __NAMESPACE__ . '\\product_description_get', 20, 2);
function product_description_get($item_data, $cart_item_data) {
  if (isset($cart_item_data['description_packing'])) {
    $item_data[] = array(
      'key' => 'contains',
      'value' => wc_clean($cart_item_data['description_packing']),
    );
  }
  return $item_data;
}

/**
 * Add custom meta to order
 */
add_filter('woocommerce_checkout_create_order_line_item', __NAMESPACE__ . '\\product_description_meta', 20, 4);
function product_description_meta($item, $cart_item_key, $values, $order) {
  if (isset($values['description_packing'])) {
    $item->add_meta_data(
      'contains',
      $values['description_packing'],
      true
    );
  }
}

/**
 * @snippet       New Products Table Column @ WooCommerce Admin
 */

add_filter('manage_edit-product_columns', __NAMESPACE__ . '\\hc_edit_column', 9999);
function hc_edit_column($columns) {
    unset($columns['date']);
    unset($columns['taxonomy-product_brand']);
    unset($columns['shipping_category']);
    unset($columns['product_cat']);
    unset($columns['product_tag']);

    $new_columns = array();
  // add new order status after processing
    foreach ($columns as $key => $data) {
        if ($key === 'price') {
            $new_columns[$key] = $data;
            $new_columns['adate'] = '<span>1st avail<br>last avail</span>';
            $new_columns['hc_shipping'] = '<span>Location<br>Lead Time<br>Method</span>';
            $new_columns['hc_stock'] = 'Stock';
            $new_columns['product_cat'] = 'Categories';
        } else {
            $new_columns[$key] = $data;
        }
    }
    return $new_columns;
}

add_action('manage_product_posts_custom_column', __NAMESPACE__ . '\\hc_column_content', 10, 2);
function hc_column_content($column, $product_id) {
  if ($column == 'hc_stock') {
    $product = wc_get_product($product_id);
    echo ($product->is_in_stock() ? "In" : "Out");
  }
  if ($column == 'adate') {
    echo get_field('first_date', $product_id) . '<br>' .  get_field('last_date', $product_id);
  }
  if ($column == 'hc_shipping') {
    $locations = get_post_meta($product_id, '_custom_location', true);
    $lead_time = get_post_meta($product_id, '_custom_lead_time', true);
    $delivery_method = get_post_meta($product_id, '_custom_delivery_method', true);
    echo (is_array($locations) ? implode(', ', $locations) : $locations) . '<br>' . $lead_time . '<br>' . $delivery_method;
  }
}

add_action('admin_enqueue_scripts', __NAMESPACE__ . '\\wc_product_list_css_overrides');
function wc_product_list_css_overrides() {
  wp_add_inline_style('woocommerce_admin_styles',
    "table.wp-list-table .column-product_cat{ width: 25% !important; } table.wp-list-table .column-shipping_weight{ width: 7%; } table.wp-list-table .column-stock{ width: 6%; }");
}

function hc_maybe_redirect($url, $product) {
  $redirect = get_field('redirect_to_link');
  if (get_field('redirect_to_link')) {
    return $redirect;
  }
  return $url;
}
add_filter('woocommerce_product_add_to_cart_url', __NAMESPACE__ . '\hc_maybe_redirect', 10, 2);

function hc_maybe_redirect_button($text, $product) {
  $redirect = get_field('redirect_to_link');
  if (get_field('redirect_to_link')) {
    return 'Click to see';
  }
  return $text;
}
add_filter('woocommerce_product_add_to_cart_text', __NAMESPACE__ . '\hc_maybe_redirect_button', 10, 2);

/* filter for woocomerce order export to get meta version of shipping zone */
add_filter('woe_fetch_order_row', function ($row, $order_id) {
  $order = wc_get_order( $order_id );
  $shipping_zone = $order->get_meta( 'shipping_zone' );
  if (array_key_exists('shipping_zone', $row) && !empty($shipping_zone) ) {
    $row['shipping_zone'] = $shipping_zone;
  }
  return $row;
}, 10, 2);

/* hack for news article */
add_action('template_redirect', function () {
  if ($_SERVER['REQUEST_URI'] === '/product-category/delivery/palm-springs/') {
    wp_redirect(home_url('/our-stores/palm-springs/'), 301);
    exit;
  }
  if ($_SERVER['REQUEST_URI'] === '/delivery/palm-springs/') {
    wp_redirect(home_url('/our-stores/palm-springs/'), 301);
    exit;
  }
  if ($_SERVER['REQUEST_URI'] === '/delivery/castro-sf/') {
    wp_redirect(home_url('/our-stores/castro-sf/'), 301);
    exit;
  }
  if ($_SERVER['REQUEST_URI'] === '/delivery/polk-sf/') {
    wp_redirect(home_url('/our-stores/polk-sf/'), 301);
    exit;
  }
  if ($_SERVER['REQUEST_URI'] === '/delivery/national/') {
    wp_redirect(home_url('/our-stores/national/'), 301);
    exit;
  }
});

// 1 Disable State
//add_filter( 'woocommerce_shipping_calculator_enable_state', '__return_false' );

// 2 Disable City
//add_filter( 'woocommerce_shipping_calculator_enable_city', '__return_false' )

add_filter('woocommerce_product_tabs', __NAMESPACE__ . '\\hc_custom_product_tabs', 98, 1);

function hc_custom_product_tabs($tabs) {
    $product = wc_get_product(get_the_ID());

    // 🔸 Availability tab
    $tabs['availability'] = [
        'title'    => __('Availability Information', 'woocommerce'),
        'priority' => 50,
        'callback' => function ($key, $tab) use ($product) {
            echo '<table class="woocommerce-product-attributes shop_attributes">';

            // Availability dates
            $available_from = get_field('first_date', $product->get_id());
            $available_to   = get_field('last_date', $product->get_id());
            if ($available_from || $available_to) {
                echo '<tr class="woocommerce-product-attributes-item">';
                echo '<th class="woocommerce-product-attributes-item__label" scope="row">Availability</th>';
                echo '<td class="woocommerce-product-attributes-item__value">' . esc_html($available_from . " to " . $available_to) . '</td>';
                echo '</tr>';
            }

            // Delivery type
            $delivery_type = get_post_meta($product->get_id(), '_custom_delivery_method', true);
            if (!empty($delivery_type)) {
                $term = get_term_by('slug', $delivery_type, 'pa_delivery-type');
                if ($term && !is_wp_error($term) && !empty($term->description)) {
                    echo '<tr class="woocommerce-product-attributes-item">';
                    echo '<th class="woocommerce-product-attributes-item__label" scope="row">Delivery</th>';
                    echo '<td class="woocommerce-product-attributes-item__value">' . esc_html($term->description) . '</td>';
                    echo '</tr>';
                }
            }

            // Lead time
            $lead_time = get_post_meta($product->get_id(), '_custom_lead_time', true);
            if (!empty($lead_time)) {
                $term = get_term_by('slug', $lead_time, 'pa_lead-time');
                if ($term && !is_wp_error($term) && !empty($term->description)) {
                    echo '<tr class="woocommerce-product-attributes-item">';
                    echo '<th class="woocommerce-product-attributes-item__label" scope="row">Lead Time</th>';
                    echo '<td class="woocommerce-product-attributes-item__value">' . esc_html($term->description) . '</td>';
                    echo '</tr>';
                }
            }

            // Stores
            $locations = get_post_meta($product->get_id(), '_custom_location', true);
            global $hc_stores;
            if (!empty($locations) && is_array($locations)) {
                $store_labels = [];
                foreach ($locations as $location) {
                    $store_labels[] = ($location === 'any-zone') ? 'All Stores' : ($hc_stores[$location] ?? $location);
                }

                if (!empty($store_labels)) {
                    foreach ($store_labels as $index => $store_label) {
                        echo '<tr class="woocommerce-product-attributes-item">';
                        if ($index === 0) {
                            echo '<th class="woocommerce-product-attributes-item__label" scope="row">Stores</th>';
                        } else {
                            echo '<th> </th>';
                        }
                        echo '<td class="woocommerce-product-attributes-item__value">' . esc_html($store_label) . '</td>';
                        echo '</tr>';
                    }
                }
            }

            echo '</table>';
        }
    ];

    // 🔸 Description tab override
    if (isset($tabs['description'])) {
        $tabs['description']['callback'] = function ($key, $tab) use ($product) {
            if (have_rows('months')) {
                $months = get_field('months');
                echo '<table cellspacing="0" cellpadding="0" border="5"><tbody>';
                foreach ($months as $index => $month) {
                    if ($index % 3 === 0) echo '<tr>';
                    echo '<td style="width:33%; vertical-align: top; padding: 5px;">';
                    echo '<strong>' . esc_html($month['month_name']) . ':</strong><br><ol>';
                    if (!empty($month['cookies']) && is_array($month['cookies'])) {
                        foreach ($month['cookies'] as $cookie) {
                            $title = get_the_title($cookie['cookie_name']);
                            $title = preg_replace('/ Cookie$/', '', $title);
                            $details = !empty($cookie['details']) ? ' ' . $cookie['details'] : '';
                            echo '<li>' . esc_html($title . $details) . '</li>';
                        }
                    }
                    echo '</ol></td>';
                    if (($index + 1) % 3 === 0) echo '</tr>';
                }
                echo '</tbody></table><br>';
            } else {
                global $post;
                if (!empty($post->post_content)) {
                    the_content();
                }
            }
            // Fallback to WooCommerce default
            do_action('woocommerce_product_description', $product);
        };
    }

    // 🔸 Additional Information tab override
    if (isset($tabs['additional_information'])) {
        $tabs['additional_information']['callback'] = function ($key, $tab) use ($product) {
            // Output attributes table only, no heading
            wc_display_product_attributes($product, []); 
        };
    }

    return $tabs;
}

remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20 );

add_filter('woocommerce_shortcode_products_query', __NAMESPACE__ . '\\hc_filter_products_by_store_meta', 10, 2);
function hc_filter_products_by_store_meta($query_args, $atts) {
	$store = isset($atts['current_store']) ? $atts['current_store'] : 'any-zone';
	if ($store === 'any-zone') {
		return $query_args; // no store set, skip filtering
	}
	$query_args['meta_query'] = [
		'relation' => 'OR',
		[
			'key'     => '_custom_location',
			'value'   => '"' . $store . '"',
			'compare' => 'LIKE'
		],
	];

	return $query_args;
}

// trigger shipping updates for users not logged in

add_action('woocommerce_after_checkout_form', function(){
    if (!is_user_logged_in()) : ?>
        <script>
        jQuery(function($){
            // Always recalc when shipping postcode changes
            $('#shipping_postcode').on('change input', function(){
                $(document.body).trigger('update_checkout');
            });

            // Only recalc on billing postcode if shipping postcode is empty
            $('#billing_postcode').on('change input', function(){
                if ($('#shipping_postcode').length === 0 || $('#shipping_postcode').val() === '') {
                    $(document.body).trigger('update_checkout');
                }
            });
        });
        </script>
    <?php endif;
});

add_action( 'woocommerce_before_calculate_totals', function( $cart ) {
    if ( is_admin() && ! defined( 'DOING_AJAX' ) ) return;

    foreach ( $cart->get_cart() as $cart_item ) {
        $product = $cart_item['data'];

        // Handle variable and variable-subscription: use first variation
        if ( $product->is_type( [ 'variable', 'variable-subscription' ] ) ) {
            $variations = $product->get_children();
            foreach ( $variations as $variation_id ) {
                $variation = wc_get_product( $variation_id );
                if ( $variation && $variation->is_on_sale() ) {
                    $sale_price = $variation->get_sale_price();
                    if ( $sale_price > 0 ) {
                        $product->set_price( $sale_price );
                        break;
                    }
                }
            }
        }

        // Handle simple, subscription, subscription_variation
        elseif ( $product->is_type( [ 'simple', 'subscription', 'subscription_variation' ] ) ) {
            if ( $product->is_on_sale() ) {
                $sale_price = $product->get_sale_price();
                if ( $sale_price > 0 ) {
                    $product->set_price( $sale_price );
                }
            }
        }
    }
}, 10 );
