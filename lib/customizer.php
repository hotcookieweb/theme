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

if (function_exists('acf_add_options_page')) {

  acf_add_options_page(array(
    'page_title' => 'Theme General Settings',
    'menu_title' => 'Theme Settings',
    'menu_slug' => 'theme-general-settings',
    'capability' => 'edit_posts',
    'redirect' => false,
  ));

}

remove_action('woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30);
remove_action('woocommerce_before_shop_loop', 'woocommerce_result_count', 20);

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

function mytheme_add_woocommerce_support() {
  add_theme_support('woocommerce');
}

add_action('after_setup_theme', __NAMESPACE__ . '\\mytheme_add_woocommerce_support');

add_filter('woocommerce_product_tabs', __NAMESPACE__ . '\\exetera_custom_product_tabs', 98);
function exetera_custom_product_tabs($tabs) {
  // Custom description callback.
  if (isset($tabs['additional_information'])) {
    $tabs['additional_information']['callback'] = function () {
      global $post, $product;
      echo '<div class="left"><h2>Additional Information</h2>';
      // Display the heading and content of the Additional Information tab.
      do_action('woocommerce_product_additional_information', $product);
      echo '</div>';
    };
  }
  if (isset($tabs['description'])) {
    $tabs['description']['callback'] = function () {
      global $post, $product;
      // Display the content of the Description tab if not empty
      if (!empty($post->post_content)) {
        echo '<div class="right"><h2>Description</h2>';

        the_content();

        echo '</div>';
      }

      echo '<div class="left"><h2>Additional Information</h2>';
      /* subscription details */
      if ( have_rows('months') ) {
        $months = get_field('months');
        ?>
        <table cellspacing="0" cellpadding="0" border="5">
          <?php 
          foreach ($months as $index => $month) {
            if ($index % 3 == 0) { ?>
              <tr>
            <?php } ?>
              <td style="width:33%; vertical-align: top; padding: 5px;">
                <strong><?=$month['month_name']?>:</strong> <br>
                <ol>
                <?php if (is_array ($month['cookies'])) {
                  foreach ($month['cookies'] as $cookie) { ?>
                  <li><?= preg_replace('/ Cookie$/','',get_the_title($cookie['cookie_name'])) ?> 
                      <?= empty($cookie['details']) ? '' : ' ' . $cookie['details']?>
                  </li>
                <?php } ?>
                </ol>
                <?php } ?>
              </td>
            <?php if (($index % 3) == (2 % 3)) { ?>
              </tr>
            <?php } ?>
          <?php } ?>
        </table>
        <br>
      <?php }
      else {
        do_action('woocommerce_product_additional_information', $product);
      }
      echo '</div>';
    };

    unset($tabs['additional_information']);
  }

  return $tabs;
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
 * @how-to        Get CustomizeWoo.com FREE
 * @author        Rodolfo Melogli
 * @compatible    WooCommerce 5
 * @donate $9     https://businessbloomer.com/bloomer-armada/
 */

add_filter('manage_edit-product_columns', __NAMESPACE__ . '\\hc_add_stock_column', 9999);
function hc_add_stock_column($columns) {
  $new_columns = array();
  // add new order status after processing
  foreach ($columns as $key => $data) {
    if ('product_tag' === $key) {
      $new_columns['stock'] = 'Stock';
    } else {
      $new_columns[$key] = $data;
    }
  }
  return $new_columns;
}

add_action('manage_product_posts_custom_column', __NAMESPACE__ . '\\hc_add_stock_column_content', 10, 2);
function hc_add_stock_column_content($column, $product_id) {
  if ($column == 'stock') {
    $product = wc_get_product($product_id);
    echo ($product->is_in_stock() ? "In" : "Out");
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

