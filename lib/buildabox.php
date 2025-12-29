<?php
add_action('wp_ajax_hc_get_modal_data', 'hc_get_modal_data');
add_action('wp_ajax_nopriv_hc_get_modal_data', 'hc_get_modal_data');
add_action('wp_enqueue_scripts', function() {
    if ( ! function_exists('WC') ) {
        return;
    }
    // Conditionally load on single product pages
    if ( is_product() ) {
        global $post;
        $box_size = get_field('size_of_box', $post->ID);

        if ( $box_size > 0 ) {
            hc_enqueue_box_scripts();
        }
    }

    // Always load on shop + category pages
    if (( is_page() && get_field('product_category') ) || is_shop() || is_product_category()){
        hc_enqueue_box_scripts();
    }

});

function hc_enqueue_box_scripts() {
    wp_enqueue_script(
        'wc-backbone-modal',
        plugins_url('woocommerce/assets/js/admin/backbone-modal.min.js'),
        ['jquery','wp-util','underscore','backbone','jquery-blockui'],
        WC()->version,
        true
    );

    wp_enqueue_script(
        'hc-box-modal',
        get_stylesheet_directory_uri() . '/assets/scripts/hc_box_modal.js',
        array('jquery','wp-util','underscore','backbone','jquery-blockui','wc-backbone-modal'),
        null,
        true
    );

    wp_localize_script('hc-box-modal','hc_box_modal_params',[
        'ajax_url'=>admin_url('admin-ajax.php'),
        'cart_url'=>wc_get_cart_url(),
    ]);

    wp_enqueue_style( 'dashicons' );
}

add_filter('woocommerce_loop_add_to_cart_args', function($args, $product) {
    $box_size = get_field('size_of_box', $product->get_id());

    if ($box_size > 0 && get_field('build_vs_customize', $product->get_id()) != 'customize') {
        $args['attributes']['data-box-size'] = $box_size;
        $args['attributes']['data-discount'] = get_field('percent_or_discount_amount', $product->get_id());
        $args['attributes']['data-box-mode'] = 'build';
    }

    return $args;
}, 10, 2);

/* from my version of simple.php template from woocommerce */
add_filter('hc_add_to_cart_button_attributes', function($attrs, $product) {
    $box_size = get_field('size_of_box', $product->get_id());

    if ($box_size > 0 && get_field('build_vs_customize', $product->get_id()) != 'customize') {
        $attrs['data-product_id'] = $product->get_id();
        $attrs['data-box-size']   = $box_size;
        $attrs['data-discount']   = get_field('percent_or_discount_amount', $product->get_id());
        $attrs['data-box-mode']   = 'build';
    }
    return $attrs;
}, 10, 2);

add_action('wp_print_footer_scripts', 'hc_get_box_builder_template');

add_filter('woocommerce_product_single_add_to_cart_text', function($text, $product) {
    $box_size = get_field('size_of_box', $product->get_id());
    if ($box_size > 0 && get_field('build_vs_customize', $product->get_id()) != 'customize') {
        return __(get_field('button_text', $product->get_id()), 'hotcookie');
    }
    return $text;
}, 10, 2);

add_filter('woocommerce_product_add_to_cart_text', function($text, $product) {
    $box_size = get_field('size_of_box', $product->get_id());
    if ($box_size > 0 && get_field('build_vs_customize', $product->get_id()) != 'customize') {
        return __(get_field('button_text', $product->get_id()), 'hotcookie');
    }
    return $text;
}, 10, 2);

add_filter('woocommerce_post_class', function($classes, $product) {
    $box_size = get_field('size_of_box', $product->get_id());
    if ($box_size > 0) {
        $classes[] = 'product_cat-build-a-box';
    }
    return $classes;
}, 10, 2);

add_action('woocommerce_after_add_to_cart_button', function() {
    global $product;

    $box_size = get_field('size_of_box', $product->get_id());
    $mode     = get_field('build_vs_customize', $product->get_id());
    $discount = get_field('percent_or_discount_amount', $product->get_id());

    if ($box_size > 0 && $mode === 'customize') {

        // Pull the button text from ACF
        $button_text = get_field('button_text', $product->get_id());

        // Fallback if empty
        if (!$button_text) {
            $button_text = 'Customize';
        }

        echo '<button style="margin-left: 12px;"
                class="single_add_to_cart_button button alt customize-button" 
                data-product_id="' . $product->get_id() . '" 
                data-box-size="' . $box_size . '"
                data-discount="' . $discount . '"
                data-box-mode="customize">
                ' . esc_html($button_text) . '
              </button>';
    }
});

// Replace price HTML with ACF discount field
add_filter('woocommerce_get_price_html', function($price_html, $product) {
    $discount = hc_get_discount( $product->get_id() );
    $price_string = $discount['price_string'];
    if ( empty( $price_string ) ) {
        return $price_html;
    }
    $price_html = '<span class="woocommerce-Price-amount amount"><bdi>' . $price_string . '</bdi></span>';
    return $price_html;
}, 10, 2);

/**
 * Format the Build‑a‑Box discount into a price string
 *
 * Handles:
 * 1. Percentage values (e.g. "10%")
 * 2. Numeric values (e.g. "10")
 * 3. Text labels (e.g. "Price varies", "Varies", "Custom")

 */
function hc_get_discount( $product_id ) {

    // Default return structure (safe for all callers)
    $result = [
        'price_string' => '',
        'discount'     => 0,
        'is_percent'   => false
    ];

    // Build‑a‑Box detection via ACF
    $box_size = get_field( 'size_of_box', $product_id );
    if ( empty( $box_size ) || intval( $box_size ) <= 0 ) {
        return $result;
    }

    // Raw discount field
    $discount_raw = get_field('percent_or_discount_amount', $product_id);
    $raw = trim((string) $discount_raw);

    // 1. Percentage (strict: digits + optional dot + %)
    if (preg_match('/^[0-9]*\.?[0-9]+%$/', $raw)) {

        $numeric = floatval(rtrim($raw, '%')) / 100;

        if ($numeric < 0 || $numeric > 1) {
            error_log(sprintf(
                "%s line %s: Build-a-Box error: invalid discount percentage %s",
                __FILE__, __LINE__, $raw
            ));
            return $result;
        }

        $result['is_percent']   = true;
        $result['discount']     = $numeric;
        $result['price_string'] = esc_html($raw) . ' off';

        return $result;
    }

    // 2. Pure number (numeric discount)
    if (is_numeric($raw)) {

        $numeric = floatval($raw);

        if ($numeric < 0) {
            error_log(sprintf(
                "%s line %s: Build-a-Box error: invalid discount amount %s",
                __FILE__, __LINE__, $raw
            ));
            return $result;
        }

        $result['discount']     = $numeric;
        $result['price_string'] = '$' . esc_html($raw) . ' off';

        return $result;
    }

    // 3. Text label (anything else)
    $result['price_string'] = esc_html($raw);
    $result['discount']     = 0;
    $result['is_percent']   = false;

    return $result;
}

function hc_get_modal_data() {
    ob_start();

    $assigned = wp_get_post_terms($_POST['product_id'], 'product_cat' );

    $parent = get_term_by( 'slug', 'build-a-box', 'product_cat' );
    $parent_id = $parent ? $parent->term_id : 0;

    $box_product_cats = [];

    foreach ( $assigned as $cat ) {

        // Must be a child or descendant of Build-a-Box
        if ( $cat->parent != $parent_id && ! term_is_ancestor_of( $parent_id, $cat->term_id, 'product_cat' ) ) {
            error_log( 'Skipping category ' . $cat->name . ' parent ' . $cat->parent . ' parent_id ' . $parent_id . ' term_id ' . $cat->term_id );
            continue;
        }

        // Check if this category has children
        $children = get_terms([
            'taxonomy'   => 'product_cat',
            'parent'     => $cat->term_id,
            'hide_empty' => false,
            'fields'     => 'ids'
        ]);

        // If no children → it's a leaf
        if ( empty( $children ) ) {
            $box_product_cats[] = $cat;
        }
    }

    $products = [];

    if ( ! empty( $box_product_cats ) ) {

        $box_product_ids = wp_list_pluck( $box_product_cats, 'term_id' );
   
        $products = wc_get_products([
            'limit'   => -1,
            'exclude' => [ $_POST['product_id'] ],
            'tax_query' => [
                [
                    'taxonomy' => 'product_cat',
                    'field'    => 'term_id',
                    'terms'    => $box_product_ids,
                    'operator' => 'IN',
                ]
            ]
        ]);
    }

    $discount_details = hc_get_discount($_POST['product_id']);
    $discount = $discount_details['discount'];
    $is_percent = $discount_details['is_percent'];
    $box_size = get_field('size_of_box', $_POST['product_id']);
    $product = wc_get_product( $_POST['product_id'] );
    $product_defaults = hc_format_content($product->get_description(), 'customize');
    // Build a fast lookup: name → quantity
    $defaults = [];
    /** @var array $product_defaults */
    foreach ($product_defaults as $default) {
        /* product names may have (gluten-free/vegan) or similar suffixes, strip them for matching */
        $defaults[ trim(preg_replace('/\([^)]*\)/', '', strtolower($default['name'])))] = intval($default['quantity']);
    }
    if ($products) {
        echo '<ul class="hc-product-list">';
        echo '<table class="box-products-table">';
        echo '<tbody>';

        foreach ($products as $product) {
            $product_id = $product->get_id();

            // Skip Build-a-Box products
            if (( intval(get_field( 'size_of_box', $product_id )) > 0) ||
                ( $product->get_type() !== 'simple' ))  {
                    error_log( 'Skipping product ID ' . $product_id . ' type ' . $product->get_type() );
                continue;
            }

            echo '<tr class="product-item">';

            // thumbnail cell
            echo '<td class="product-thumb">' . wp_get_attachment_image(
                $product->get_image_id(),
                'woocommerce_thumbnail',
                false,
                array(
                    'class' => 'cart-thumb',
                    'width' => null,
                    'height' => null
                )
            ) . '</td>';

            if($discount != 0) {
                if ($is_percent) {
                    $price = $product->get_price() * (1 - $discount);
                } else {
                    $price = max(0, $product->get_price() - round($discount/$box_size, 2));
                }
            } else {
                $price = $product->get_price();
            }
            $price = number_format((float)$price, 2, '.', '');

            // name cell
            echo '<td class="product-name">' . esc_html( $product->get_name() ) . ' ($' . $price .')</td>';

            // quantity cell
            echo '<td class="product-qty">';
                echo '<input type="number"
                        class="input-text qty text"
                        name="quantity[' . esc_attr($product->get_id()) . ']"
                        value="' . ($defaults[ strtolower($product->get_name()) ] ?? 0) . '"
                        min="0"
                        step="1"
                        inputmode="numeric"
                        autocomplete="off"
                        aria-label="' . esc_attr__('Product quantity','hotcookie') . '"
                        data-product-id="' . esc_attr($product->get_id()) . '"
                        data-price="' . esc_attr($price) . '">';
            echo '</td>';

            echo '</tr>';
        }
        echo '</tbody></table>';
        echo '</ul>';
    }

    wp_send_json_success([
        'product_html' => ob_get_clean(),
        'title'        => get_field('button_text', $_POST['product_id']) ?: '',
        'price'        => $discount_details['price_string']
    ]);
    exit();
}

add_filter('woocommerce_add_cart_item_data', function($cart_item_data, $product_id, $variation_id) {
    // Only run if selections were posted
    if (!isset($_POST['selections'])) {
        return $cart_item_data; // normal product flow
    }

    $selections_raw = wp_unslash($_POST['selections']);
    $selections     = json_decode($selections_raw, true);

    // Validate selections
    if (empty($selections) || !is_array($selections)) {
        error_log(sprintf("%s line: %s: Build-a-Box error: selections payload invalid", __FILE__, __LINE__));
        return $cart_item_data; // skip attaching metadata
    }

    $total_price = 0;
    foreach ($selections as $pid => $qty) {
        $pid = intval($pid);
        $qty = intval($qty);

        if ($qty <= 0) {
            error_log(sprintf("%s line: %s: Build-a-Box error: invalid quantity for product ID %d", __FILE__, __LINE__, $pid));
            continue; // skip this product
        }

        $product = wc_get_product($pid);
        if (!$product) {
            error_log(sprintf("%s line: %s: Build-a-Box error: invalid product ID %d", __FILE__, __LINE__, $pid));
            continue; // skip this product
        }

        $total_price += $product->get_price() * $qty;
    }

    $discount_details = hc_get_discount($product_id);
    $discount = $discount_details['discount'];
    if ($discount < 0 || $discount > $total_price) {
        error_log(sprintf(
            "%s line %s: Build-a-Box error: invalid discount amount %s",
            __FILE__, __LINE__, $discount_raw
        ));
        $discount = 0;
    }
    $is_percent = $discount_details['is_percent'];
    $discounted_total = $is_percent ? $total_price * (1 - $discount) : max(0, $total_price - $discount);

    // Attach metadata
    $cart_item_data['box_contents']         = $selections;
    $cart_item_data['box_total']            = $total_price;
    $cart_item_data['box_discounted_total'] = $discounted_total;

    return $cart_item_data;
}, 10, 3);

add_filter('woocommerce_cart_item_price', function($price, $cart_item, $cart_item_key) {
    if (!empty($cart_item['box_contents'])) {
        $standard_total   = floatval($cart_item['box_total']);
        $discounted_total = floatval($cart_item['box_discounted_total']);

        // If no discount, show only the price
        if ($standard_total == $discounted_total) {
            $price = wc_price($discounted_total);
        } else {
            // Show regular + discounted
            $price = '<del>' . wc_price($standard_total) . '</del> ' .
                     '<ins>' . wc_price($discounted_total) . '</ins>';
        }
    }

    return $price;
}, 10, 3);

/**
 * Show Build-a-Box contents in cart/checkout.
 */
add_filter('woocommerce_get_item_data', function($item_data, $cart_item) {
    // Reformat your box contents as a clean table
    if (!empty($cart_item['box_contents'])) {
        foreach ($cart_item['box_contents'] as $pid => $qty) {
            $product = wc_get_product($pid);
            if ($product && $qty > 0) {
                $item_data[] = [
                    'key' => $qty,
                    'value'   => $product->get_name(),
                ];
            }
        }
    }

    return $item_data;
}, 10, 2);


// When the item is added to the cart
add_filter('woocommerce_add_cart_item', function($cart_item) {
    if (isset($cart_item['box_discounted_total'])) {
        $cart_item['data']->set_price($cart_item['box_discounted_total']);
    }
    return $cart_item;
}, 20);

// When WooCommerce rebuilds the cart from session
add_filter('woocommerce_get_cart_item_from_session', function($cart_item, $values) {
    if (isset($values['box_discounted_total'])) {
        $cart_item['box_discounted_total'] = $values['box_discounted_total'];
        $cart_item['data']->set_price($values['box_discounted_total']);
    }
    return $cart_item;
}, 20, 2);

add_action('woocommerce_checkout_create_order_line_item', function($item, $cart_item_key, $values, $order) {
    if (!empty($values['box_contents'])) {
        $lines = [];
        foreach ($values['box_contents'] as $pid => $qty) {
            $product = wc_get_product($pid);
            if ($product && $qty > 0) {
                $lines[] = $product->get_name() . ' × ' . $qty;
            }
        }
        // Save as a string so WooCommerce will always display it
        $item->add_meta_data('Box Contents', implode(', ', $lines));
    }
}, 10, 4);

function hc_get_box_builder_template() {
    // sage runs wp_footer multiple times, prevent duplicate templates
    static $printed = false;
    if ($printed) {
        return;
    }
    $printed = true;?>
    <script type="text/template" id="tmpl-hc-modal-add-box-products">
        <div class="wc-backbone-modal hc-buildabox-modal">
            <div class="wc-backbone-modal-content">
                <section class="wc-backbone-modal-main" role="main">
                    <header class="wc-backbone-modal-header">
                    <span class="modal-title">{{{ data.title }}} ({{{data.price}}})</span>
                    <button class="modal-close modal-close-link dashicons dashicons-no-alt">
                        <span class="screen-reader-text">Close modal panel</span>
                    </button>
                    </header>

                    <article class="wc-backbone-modal-article hc-product-scroll">
                        {{{ data.product_html }}}
                    </article>

                    <footer class="wc-backbone-modal-footer">
                        <div class="hc-progress-wrapper">
                            <div class="hc-progress-bar">
                                <div class="hc-progress-fill" style="width:0%"></div>
                                <span class="hc-progress-text">0 / 3</span>
                            </div>
                        </div>
                        <button id="finish-box"
                            class="button product_type_simple add_to_cart_button modal-close"
                            style="display:none;">
                            <span class="hc-btn-text">Add to Cart</span>
                        </button>
                    </footer>
                </section>
			</div>
        </div>
        <div class="wc-backbone-modal-backdrop modal-close"></div>
    </script>
<?php
}
