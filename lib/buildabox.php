<?php
add_action('wp_ajax_hc_get_box_products', 'hc_get_box_products');
add_action('wp_ajax_nopriv_hc_get_box_products', 'hc_get_box_products');
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

    if ($box_size > 0) {
        $args['attributes']['data-box-size'] = $box_size;
    }

    return $args;
}, 10, 2);

/* from my version of simple.php template from woocommerce */
add_filter('hc_add_to_cart_button_attributes', function($attrs, $product) {
    $box_size = get_field('size_of_box', $product->get_id());

    if ($box_size > 0) {
        $attrs['data-box-size']   = $box_size;
        $attrs['data-product_id'] = $product->get_id();
    }
    return $attrs;
}, 10, 2);

add_action('wp_print_footer_scripts', 'hc_get_box_builder_template');

add_filter('woocommerce_product_single_add_to_cart_text', function($text, $product) {
    $box_size = get_field('size_of_box', $product->get_id());
    if ($box_size > 0) {
        return __('Build a Box', 'hotcookie');
    }
    return $text;
}, 10, 2);

add_filter('woocommerce_product_add_to_cart_text', function($text, $product) {
    $box_size = get_field('size_of_box', $product->get_id());
    if ($box_size > 0) {
        return __('Build a Box', 'hotcookie');
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

function hc_get_box_products() {
    ob_start();

    $assigned = wp_get_post_terms($_POST['product_id'], 'product_cat' );
    error_log('assigned: ' . json_encode($assigned));
    $parent = get_term_by( 'slug', 'build-a-box', 'product_cat' );
    $parent_id = $parent ? $parent->term_id : 0;

    $box_product_cats = [];

    foreach ( $assigned as $cat ) {

        // Must be a child or descendant of Build-a-Box
        if ( $cat->parent != $parent_id && ! term_is_ancestor_of( $parent_id, $cat->term_id, 'product_cat' ) ) {
            error_log('skipping ' . $cat->name);
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
            'exclude' => [ $product_id ],
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

    if ($products) {
        echo '<ul class="hc-product-list">';
        echo '<table class="box-products-table">';
        /* echo '<thead><tr><th></th><th>' . __('Product','hotcookie') . '</th><th>' . __('Quantity','hotcookie') . '</th></tr></thead>'; */
        echo '<tbody>';

        foreach ($products as $product) {
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

            // name cell
            echo '<td class="product-name">' . esc_html( $product->get_name() ) . '</td>';

            // quantity cell
            echo '<td class="product-qty">';
                echo '<input type="number"
                        class="input-text qty text"
                        name="quantity[' . esc_attr($product->get_id()) . ']"
                        value="0"
                        min="0"
                        step="1"
                        inputmode="numeric"
                        autocomplete="off"
                        aria-label="' . esc_attr__('Product quantity','hotcookie') . '"
                        data-product-id="' . esc_attr($product->get_id()) . '">';
            echo '</td>';

            echo '</tr>';
        }
        echo '</tbody></table>';
        echo '</ul>';
    }
    wp_send_json_success(['modal_html' => ob_get_clean()]);
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

    // Discount parsing
    $discount_raw = get_field('percent_or_discount_amount', $product_id);
    $discount_raw = trim((string) $discount_raw);;
    $discount     = 0;
    $is_percent   = false;

    if ($discount_raw !== '') {
        if (substr($discount_raw, -1) === '%') {
            $is_percent = true;
            $discount   = floatval(rtrim($discount_raw, '%')) / 100;
            if ($discount < 0 || $discount > 1) {
                error_log(sprintf("%s line: %s: Build-a-Box error: invalid discount percentage %s", __FILE__, __LINE__, $discount_raw));
                $discount = 0;
            }
        } else {
            $discount   = floatval($discount_raw);
            if ($discount < 0 || $discount > $total_price) {
                error_log(sprintf("%s line: %s: Build-a-Box error: invalid discount amount %s", __FILE__, __LINE__, $discount_raw));
                $discount = 0;
            }
        }
    }

    $discounted_total = $is_percent
        ? $total_price * (1 - $discount)
        : max(0, $total_price - $discount);

    // Attach metadata
    $cart_item_data['box_contents']         = $selections;
    $cart_item_data['box_total']            = $total_price;
    $cart_item_data['box_discount']         = $discount_raw;
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
                    <span class="modal-title">Build a Box</span>
                    <button class="modal-close modal-close-link dashicons dashicons-no-alt">
                        <span class="screen-reader-text">Close modal panel</span>
                    </button>
                    </header>

                    <article class="wc-backbone-modal-article hc-product-scroll">
                        {{{ data.modal_html }}}
                    </article>

                    <footer class="wc-backbone-modal-footer">
                        <div class="hc-progress-wrapper">
                            <div class="hc-progress-bar">
                                <div class="hc-progress-fill" style="width:0%"></div>
                                <span class="hc-progress-text">0 / 3</span>
                            </div>
                        </div>

                        <button id="finish-box" class="button button-primary modal-close" style="display:none;">
                            Add to Cart
                        </button>
                    </footer>
                </section>
			</div>
        </div>
        <div class="wc-backbone-modal-backdrop modal-close"></div>
    </script>
<?php
}
