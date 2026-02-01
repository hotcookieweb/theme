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
    'lib/assets.php', // Scripts and stylesheets
    'lib/extras.php', // Custom functions
    'lib/setup.php', // Theme setup
    'lib/titles.php', // Page titles
    'lib/wrapper.php', // Theme wrapper class
    'lib/customizer.php', // Theme customizer
    'lib/ahotcookie.php',
    'lib/buildabox.php',
];

foreach ($sage_includes as $file) {
    if (!$filepath = locate_template($file)) {
        trigger_error(sprintf(__('Error locating %s for inclusion', 'sage'), $file), E_USER_ERROR);
    }

    require_once $filepath;
}
unset($file, $filepath);

// add_action('get_header', 'maintenance_mode');
function maintenance_mode()
{

    if (!current_user_can('edit_themes') || !is_user_logged_in()) {wp_die("Sorry, we're baking some updates. They'll be out of the oven soon!");}

}

// always start a woocommerce session when first page loads
add_action('woocommerce_init', 'create_wc_session');
function create_wc_session()
{
    if (is_user_logged_in() || is_admin()) {
        return;
    }

    if (isset(WC()->session)) {
        if (!WC()->session->has_session()) {
            WC()->session->set_customer_session_cookie(true);
            WC()->customer->set_shipping_country('US');
            WC()->customer->set_shipping_postcode('');
            WC()->customer->set_shipping_state('');
            WC()->customer->set_shipping_city('');
            WC()->customer->set_billing_country('');
            WC()->customer->set_billing_postcode('');
            WC()->customer->set_billing_state('');
            WC()->customer->set_billing_city('');
            WC()->session->set('current_zone','');
        }
    }
}

add_action('init', 'get_custom_coupon_code_to_session');
function get_custom_coupon_code_to_session()
{
    if (isset($_GET['coupon_code'])) {
        $coupon_code = esc_attr($_GET['coupon_code']);
        WC()->session->set('coupon_code', $coupon_code); // Set the coupon code in session
    }
}

add_action('woocommerce_cart_coupon', 'add_coupon_to_cart', 10, 0);
function add_coupon_to_cart()
{
    // Set coupon code
    $coupon_code = WC()->session->get('coupon_code');
    if (!empty($coupon_code)) {
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
add_filter('woocommerce_login_redirect', 'wc_login_redirect', 99, 2);
function wc_login_redirect($redirect, $user)
{
    if (is_wp_error($user)) {
        return ($redirect);
    }
    if (wc_user_has_role($user, 'order_manager')) {
        $redirect = admin_url('?page=order-manager');
    }
    return ($redirect);
}
add_filter('login_redirect', 'wp_login_redirect', 99, 3);
function wp_login_redirect($redirect_to, $requested_redirect_to, $user)
{
    if (is_wp_error($user)) {
        return ($redirect_to);
    }
    if (wc_user_has_role($user, 'order_manager')) {
        return (admin_url('?page=order-manager'));
    }
    return ($redirect_to);
}

/**
 * @snippet       Redirect to Referrer @ WooCommerce My Account Login
 * @how-to        Get CustomizeWoo.com FREE
 * @author        Rodolfo Melogli, BusinessBloomer.com
 * @testedwith    WooCommerce 5
 * @donate $9     https://businessbloomer.com/bloomer-armada/
 */
function bbloomer_actual_referrer()
{
    if (!wc_get_raw_referer()) {
        return;
    }

    if (is_checkout()) {
        return;
    }

    echo '<input type="hidden" name="redirect" value="' . wp_validate_redirect(wc_get_raw_referer(), wc_get_page_permalink('myaccount')) . '" />';
}
add_action('woocommerce_login_form_end', 'bbloomer_actual_referrer');

//For create account button:
add_action('woocommerce_register_form_end', 'bbloomer2_actual_referrer');
function bbloomer2_actual_referrer()
{
    if (!wc_get_raw_referer()) {
        return;
    }

    if (is_checkout()) {
        return;
    }

    echo '';
}

// add_filter( 'woocommerce_registration_redirect', 'iconic_register_redirect' );
add_filter('woocommerce_product_related_posts_relate_by_category', 'hc_related_by_category');
function hc_related_by_category($product_id)
{
    return (false);
}
add_filter('woocommerce_product_related_posts_relate_by_tag', 'hc_related_by_tag');
function hc_related_by_tag($product_id)
{
    return (true);
}

function login_errors()
{
    return 'Invalid login';
}
add_filter('login_errors', 'login_errors');

function hotcookie_admin_notices() {
    if ( ! wc_user_has_role( wp_get_current_user(), 'super_admin' ) ) {
        echo '<style>
            .notice:not(.hc-allow-notice) {
                display: none !important;
            }
        </style>';
    }
}
add_action( 'admin_enqueue_scripts', 'hotcookie_admin_notices' );
add_action('admin_enqueue_scripts', 'hotcookie_admin_notices');
add_action('login_enqueue_scripts', 'hotcookie_admin_notices');

function author_page_redirect()
{
    if (is_author()) {
        wp_redirect(site_url('/404'));
    }
}

add_action('template_redirect', 'author_page_redirect');

function remove_link_from_admin_bar($wp_admin_bar)
{
    $wp_admin_bar->remove_node('wp-logo');
    $wp_admin_bar->remove_node('updates');
    $wp_admin_bar->remove_node('comments');
    $wp_admin_bar->remove_node('new-content');
    $wp_admin_bar->remove_node('dashboard');
    $wp_admin_bar->remove_node('simple-history-view-history');
    $wp_admin_bar->remove_node('appearance');
    $wp_admin_bar->remove_node('customize');
    $wp_admin_bar->remove_node('search');
    $wp_admin_bar->remove_node('view-store');
    if (!(wc_user_has_role(wp_get_current_user(), 'super_admin'))) { /* Only Tony Roug  */
        $wp_admin_bar->remove_node('cache-purge');
    }
}

add_action('admin_bar_menu', 'remove_link_from_admin_bar', 999);

// add a parent item to the WordPress admin toolbar

// Enable custom ordering
add_filter('custom_menu_order', function () {
    return true;
}, 10);

add_filter('menu_order', function ($menu_order) {
    return array(
        'order-manager',
        'quickbooks-hc',
        'edit.php?post_type=product',           // Products
        'upload.php',                           // Media
        'edit.php?post_type=page',              // Pages
        'edit.php',                             // Posts
        'newsletter_main_index',                // Newsletter
        'users.php',                            // Users
        'edit.php?post_type=shop_coupon',       // Coupons
        'woocommerce',                          // WooCommerce (main menu)
    );
}, 10, 1);


/*
add_action('admin_init', function () {
  echo '<pre>' . print_r($GLOBALS['menu'], true) . '</pre>';
});
*/

function hotcookie_admin_menu() {
    if (!wc_user_has_role(wp_get_current_user(), 'super_admin')) { /* Only Tony Roug  */
        remove_menu_page('index.php');
        remove_menu_page('edit.php?post_type=cookielawinfo');
        remove_menu_page('wc-admin&path=/analytics/overview');
        remove_menu_page('separator2');
        remove_menu_page('themes.php');
        remove_menu_page('plugins.php');
        remove_menu_page('tools.php');
        remove_menu_page('options-general.php');
        remove_menu_page('edit.php?post_type=acf-field-group');
        remove_menu_page('tools.php');
        remove_menu_page('theme-general-settings');
        remove_menu_page('separator-last');
        remove_menu_page('separator-woocommerce');
        remove_menu_page('branding');
        remove_menu_page('googlesitekit-dashboard');
        remove_menu_page('postman');
        /* Don't use tags in my implementation */
        remove_submenu_page(
            'edit.php?post_type=product',
            'edit-tags.php?taxonomy=product_tag&post_type=product'
        );
        remove_meta_box('tagsdiv-product_tag', 'product', 'side');
    }
}
add_filter('admin_menu', 'hotcookie_admin_menu', 999);

/* don't use product tags */
add_action( 'admin_head', function() {
    if (!wc_user_has_role(wp_get_current_user(), 'super_admin')) { /* Only Tony Roug  */
        echo '<style>
            #tagsdiv-product_tag {
                display: none !important;
            }
        </style>';
    }
});

function hc_add_link_to_admin_bar($admin_bar) {
    $site_name = parse_url(get_home_url(), PHP_URL_HOST);
    $admin_bar->remove_node('site-name');  // remove current site-name
    if (is_admin()) { // admin view
        $admin_bar->add_node (
            array(
                'id' => 'site-name',
                'parent' => false,
                'title' => $site_name,
                'href' => home_url( '/' ),
                'group' => false,
                'meta' => array('class' => 'ab-item')
            )
        );
        
        $admin_bar->add_node(
            array(
                'parent' => 'site-name',
                'id' => 'view-site',
                'title' => __('Home'),
                'href' => home_url('/'),
            )
        );
        $admin_bar->add_node(
            array(
                'parent' => 'site-name',
                'id' => 'gift-cookie',
                'title' => __('Gift a Cookie'),
                'href' => home_url('/gift-boxes/'),
            )
        );
        $admin_bar->add_node(
            array(
                'parent' => 'site-name',
                'id' => 'make-special',
                'title' => __('Make it Special'),
                'href' => home_url('/special-orders/'),
            )
        );
        $admin_bar->add_node(
            array(
                'parent' => 'site-name',
                'id' => 'from-store',
                'title' => __('From our store'),
                'href' => home_url('/our-store/'),
            )
        );
        $admin_bar->add_node(
            array(
                'parent' => 'from-store',
                'id' => 'cookies',
                'title' => __('cookies'),
                'href' => home_url('/our-store/cookies'),
            )
        );
        $admin_bar->add_node(
            array(
                'parent' => 'from-store',
                'id' => 'treats',
                'title' => __('treats'),
                'href' => home_url('/our-store/treats'),
            )
        );
        $admin_bar->add_node(
            array(
                'parent' => 'from-store',
                'id' => 'famous',
                'title' => __('famous'),
                'href' => home_url('/our-store/famous'),
            )
        );
        $admin_bar->add_node(
            array(
                'parent' => 'from-store',
                'id' => 'icecream',
                'title' => __('ice cream'),
                'href' => home_url('/our-store/ice-cream'),
            )
        );
        $admin_bar->add_node(
            array(
                'parent' => 'from-store',
                'id' => 'gear',
                'title' => __('gear'),
                'href' => home_url('/our-store/gear'),
            )
        );
        $admin_bar->add_node(
            array(
                'parent' => 'from-store',
                'id' => 'drinks',
                'title' => __('drinks'),
                'href' => home_url('/our-store/drinks'),
            )
        );
        $admin_bar->add_node(
            array(
                'parent' => 'site-name',
                'id' => 'let-cater',
                'title' => __('Let us Cater'),
                'href' => home_url('/catering/'),
            )
        );
        $admin_bar->add_node(
            array(
                'parent' => 'site-name',
                'id' => 'our-blog',
                'title' => __('Our Blog'),
                'href' => home_url('/blog/'),
            )
        );
        $admin_bar->add_node(
            array(
                'parent' => 'site-name',
                'id' => 'my-account',
                'title' => __('My Account'),
                'href' => home_url('/account/'),
            ),
        );
    }
    else { // home view
        $admin_bar->add_node (
            array(
                'id' => 'site-name',
                'parent' => false,
                'title' => $site_name . ' admin',
                'href' => admin_url('admin.php?page=order-manager'),
                'group' => false,
                'meta' => array('class' => 'ab-item-order-manager'),
            )
        );
        $admin_bar->add_node (
            array(
                'id' => 'order-manager',
                'parent' => 'site-name',
                'title' => 'Order manager',
                'href' => admin_url('admin.php?page=order-manager'),
                'meta' => array('class' => 'ab-item-order-manager'),
            )
        );
        $admin_bar->add_node(
            array(
                'id' => 'products',
                'title' => 'Products',
                'parent' => 'site-name',
                'href' => admin_url('edit.php?post_type=product'),
            )
        );
        $admin_bar->add_node(
            array(
                'id' => 'media',
                'title' => 'Media',
                'parent' => 'site-name',
                'href' => admin_url('upload.php'),
            )
        );
        $admin_bar->add_node(
            array(
                'id' => 'posts',
                'title' => 'Posts',
                'parent' => 'site-name',
                'href' => admin_url('edit.php'),
            )
        );
        $admin_bar->add_node(
            array(
                'id' => 'pages',
                'title' => 'Pages',
                'parent' => 'site-name',
                'href' => admin_url('edit.php?post_type=page'),
            )
        );
        $admin_bar->add_node(
            array(
                'id' => 'newsletter_main_index',
                'title' => 'Newsletter',
                'parent' => 'site-name',
                'href' => admin_url('admin.php?page=newsletter_main_index'),
            )
        );
        $admin_bar->add_node(
            array(
                'id' => 'quickbooks-hc',
                'title' => 'Quickbooks',
                'parent' => 'site-name',
                'href' => admin_url('admin.php?page=quickbooks-hc'),
            )
        );

        if (wc_user_has_role(wp_get_current_user(), 'super_admin')) { /* Only Tony Roug  */
            $admin_bar->add_node(
                array(
                'id' => 'plugins',
                'title' => 'Plugins',
                'parent' => 'site-name',
                'href' => admin_url('plugins.php'),
                )
            );
            $admin_bar->add_node(
                array(
                    'id' => 'simple-history',
                    'title' => 'Simple history',
                    'parent' => 'site-name',
                    'href' => admin_url('index.php?page=simple_history_page'),
                )
            );
            $admin_bar->add_node(
                array(
                    'id' => 'wp-migrate',
                    'title' => 'WP Migrate',
                    'parent' => 'site-name',
                    'href' => admin_url('tools.php?page=wp-migrate-db-pro'),
                )
            );
            $admin_bar->add_node(
                array(
                'id' => 'wc-settings',
                'title' => 'Woocommerce settings',
                'parent' => 'site-name',
                'href' => admin_url('admin.php?page=wc-settings'),
                )
            );
        }
    }
}
add_action('admin_bar_menu', 'hc_add_link_to_admin_bar', 50);

add_filter('xmlrpc_methods', '__return_empty_array');

/**
 * Disable messages about the mobile apps in WooCommerce emails.
 * https://wordpress.org/support/topic/remove-process-your-orders-on-the-go-get-the-app/
 */
function mtp_disable_mobile_messaging( $mailer ) {
    remove_action( 'woocommerce_email_footer', array( $mailer->emails['WC_Email_New_Order'], 'mobile_messaging' ), 9 );
}
add_action( 'woocommerce_email', 'mtp_disable_mobile_messaging' );

/*
 * Creating a column (it is also possible to remove some default ones)
 */
add_filter( 'manage_users_columns', 'rudr_modify_user_table' );
function rudr_modify_user_table( $columns ) {
	
	// unset( $columns['posts'] ); // maybe you would like to remove default columns
	$columns[ 'registration_date' ] = 'Registration date'; // add new
	return $columns;

}

/*
 * Fill our new column with registration dates of the users
 */
add_filter( 'manage_users_custom_column', 'rudr_modify_user_table_row', 10, 3 );
function rudr_modify_user_table_row( $row_output, $column_id_attr, $user ) {
	
	$date_format = 'j M, Y H:i';

	switch( $column_id_attr ) {
		case 'registration_date' : {
			return date( $date_format, strtotime( get_the_author_meta( 'registered', $user ) ) );
			break;
		}
		default : {
			break;
		}
	}

	return $row_output;

}

/*
 * Make our "Registration date" column sortable
 */
add_filter( 'manage_users_sortable_columns', 'rudr_make_registered_column_sortable' );
function rudr_make_registered_column_sortable( $columns ) {
	
	return wp_parse_args( array( 'registration_date' => 'registered' ), $columns );
	
}
function on_user_logout($user_id)
{
    wp_redirect(home_url());
    exit();
}

add_action('wp_logout', 'on_user_logout', 10, 1);

add_action( 'woocommerce_product_bulk_edit_end', 'my_bulk_edit_fields' );
function my_bulk_edit_fields() {
    ?>
    <div class="inline-edit-group">
        <label>
            <?php esc_html_e( 'Sale Start Date', 'woocommerce' ); ?>
            <input type="date" name="bulk_sale_start_date" />
        </label>
        <label>
            <?php esc_html_e( 'Sale End Date', 'woocommerce' ); ?>
            <input type="date" name="bulk_sale_end_date" />
        </label>
    </div>
    <?php
}
add_action( 'woocommerce_product_bulk_edit_save', function( $product ) {
    $start_input = ! empty( $_REQUEST['bulk_sale_start_date'] ) ? wc_clean( $_REQUEST['bulk_sale_start_date'] ) : '';
    $end_input   = ! empty( $_REQUEST['bulk_sale_end_date'] ) ? wc_clean( $_REQUEST['bulk_sale_end_date'] ) : '';

    if ( ! $start_input && ! $end_input ) {
        return;
    }

    $tz = new DateTimeZone( wc_timezone_string() );

    $start_dt = $start_input ? new WC_DateTime( $start_input . ' 00:00:00', $tz ) : null;
    $end_dt   = $end_input   ? new WC_DateTime( $end_input   . ' 23:59:59', $tz ) : null;

    if ( $product->is_type( 'variable' ) ) {
        foreach ( $product->get_children() as $variation_id ) {
            $variation = wc_get_product( $variation_id );
            if ( $start_dt ) {
                $variation->set_date_on_sale_from( $start_dt );
            }
            if ( $end_dt ) {
                $variation->set_date_on_sale_to( $end_dt );
            }
            $variation->save();
        }
        // clear cached price ranges
        wc_delete_product_transients( $product->get_id() );
    } else if ( $product->is_type( array( 'variable-subscription', 'subscription' ) ) ) {
        $children = $product->is_type( 'variable-subscription' ) ? $product->get_children() : [ $product->get_id() ];
        foreach ( $children as $child_id ) {
            $child = wc_get_product( $child_id );
            if ( $start_dt ) {
                $child->set_date_on_sale_from( $start_dt );
            }
            if ( $end_dt ) {
                $child->set_date_on_sale_to( $end_dt );
            }
            $child->save();
        }
        wc_delete_product_transients( $product->get_id() );
    } else {
        if ( $start_dt ) {
            $product->set_date_on_sale_from( $start_dt );
        }
        if ( $end_dt ) {
            $product->set_date_on_sale_to( $end_dt );
        }
        $product->save();
    }
}, 99 );

// need to update cart details in header
add_action('wp_enqueue_scripts', function() {
    wp_enqueue_script('wc-cart-fragments');
});

/* add custom ACF location rule for WooCommerce product types */
add_filter('acf/location/rule_types', function($choices) {
    $choices['Product']['wc_product_type'] = 'Product Type';
    return $choices;
});

add_filter('acf/location/rule_values/wc_product_type', function($choices) {
    $choices['simple_subscription']   = 'Simple Subscription';
    $choices['variable_subscription'] = 'Variable Subscription';
    return $choices;
});

add_action('init', function() {

    // 1. Register rule type
    add_filter('acf/location/rule_types', function($choices) {
        $choices['Post']['wc_product_type'] = 'Product Type';
        return $choices;
    });

    // 2. Populate rule values
    add_filter('acf/location/rule_values/wc_product_type', function($choices) {

        // Replace ACF defaults
        $choices = [];

        if (function_exists('wc_get_product_types')) {
            foreach (wc_get_product_types() as $type => $label) {
                $choices[$type] = $label;
            }
        }

        return $choices;
    });

    // 3. Rule matching logic
    add_filter('acf/location/rule_match/wc_product_type', function($match, $rule, $options) {

        if (!isset($_GET['post'])) {
            return false;
        }

        $product = wc_get_product($_GET['post']);
        if (!$product) {
            return false;
        }

        $type = $product->get_type();

        if ($rule['operator'] === '==') {
            return $type === $rule['value'];
        }

        return $type !== $rule['value'];

    }, 10, 3);

});

/**
 * Replace product description editor with a plain textarea (no TinyMCE)
 */
add_action('init', function() {
    // Disable Gutenberg for products
    add_filter('use_block_editor_for_post_type', function($use_block_editor, $post_type) {
        if ($post_type === 'product') {
            return false;
        }
        return $use_block_editor;
    }, 10, 2);

    // Disable TinyMCE for products
    add_filter('user_can_richedit', function($can) {
        global $post;
        if ($post && $post->post_type === 'product') {
            return false; // force plain textarea
        }
        return $can;
    });
});

// Register new status
add_action( 'init', 'hc_reg_order_status' );
function hc_reg_order_status() {

    register_post_status( 'wc-printed', array(
        'label'                     => 'Printed',
        'public'                    => true, // must be true for customer visibility
        'exclude_from_search'       => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'label_count'               => _n_noop( 'Printed (%s)', 'Printed (%s)' ),
    ) );

    register_post_status( 'wc-picked-up', array(
        'label'                     => 'Picked-up',
        'public'                    => true,
        'exclude_from_search'       => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'label_count'               => _n_noop( 'Picked-up (%s)', 'Picked-up (%s)' ),
    ) );

    register_post_status( 'wc-delivery', array(
        'label'                     => 'Delivery',
        'public'                    => true,
        'exclude_from_search'       => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'label_count'               => _n_noop( 'Delivery (%s)', 'Delivery (%s)' ),
    ) );
}


// Add to list of WC Order statuses
add_filter( 'wc_order_statuses', 'hc_order_statuses' );
function hc_order_statuses( $order_statuses ) {

    $new_order_statuses = array();

    foreach ( $order_statuses as $key => $status ) {
        $new_order_statuses[ $key ] = $status;

        if ( 'wc-processing' === $key ) {
            $new_order_statuses['wc-printed']   = 'Printed';
            $new_order_statuses['wc-picked-up'] = 'Picked-up';
            $new_order_statuses['wc-delivery']  = 'Delivery';
        }
    }

    return $new_order_statuses;
}


// Make custom statuses visible in My Account
add_filter( 'woocommerce_my_account_my_orders_query', 'hc_show_custom_statuses_in_my_account' );
function hc_show_custom_statuses_in_my_account( $args ) {
    $args['status'][] = 'printed';
    $args['status'][] = 'picked-up';
    $args['status'][] = 'delivery';
    return $args;
}

add_filter('woocommerce_variation_is_active', function($active, $variation) {

    if (!isset($_POST['attributes'])) {
        return $active;
    }

    $selected = $_POST['attributes'];

    foreach ($selected as $attr_name => $selected_value) {

        // Only apply logic when user selected "none"
        if ($selected_value === 'none') {

            $variation_value = $variation->get_attribute($attr_name);

            // If variation has ANY real value, block it
            if ($variation_value && $variation_value !== 'none') {
                return false;
            }
        }
    }

    return $active;

}, 10, 2);