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
            WC()->session->set('delivery_zone','');
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

function hotcookie_admin_notices()
{
    if (!(wc_user_has_role(wp_get_current_user(), 'super_admin'))) { /* Only Admin sees admin notices */
        echo '<style>.notice { display: none !important;} </style>';
    }
}
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

function hotcookie_custom_menu_order($menu_ord)
{
    if (!$menu_ord) {
        return true;
    }

    return array(
        'order-manager',
        'woocommerce', // woocommerce order nanager
        'edit.php?post_type=shop_subscription', // subscriptioons
        'upload.php', // Media
        'edit.php?post_type=page', // pages
        'edit.php', // Posts
        'newsletter_main_index', // newsletter
        'users.php',
        'woocommerce-marketing',
        'edit-comments.php',
        'gf_edit_forms',
        'separator-woocommerce', // First separator
        'index.php',
    );
}
add_filter('custom_menu_order', 'hotcookie_custom_menu_order', 10, 1);
add_filter('menu_order', 'hotcookie_custom_menu_order', 10, 1);

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
    }
}
add_filter('admin_menu', 'hotcookie_admin_menu', 999);

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
                'id' => 'woocommerce-orders',
                'title' => 'Woocommerce orders',
                'parent' => 'site-name',
                'href' => admin_url('edit.php?post_type=shop_order'),
            )
        );
        $admin_bar->add_node(
            array(
                'id' => 'woocommerce-subscriptions',
                'title' => 'Woocommerce subscriptions',
                'parent' => 'site-name',
                'href' => admin_url('edit.php?post_type=shop_subscription'),
            )
        );
        if (wc_user_has_role(wp_get_current_user(), 'super_admin')) { /* Only Tony Roug  */
            $admin_bar->add_node(
                array(
                'id' => 'woocommerce-settings',
                'title' => 'Woocommerce settings',
                'parent' => 'site-name',
                'href' => admin_url('admin.php?page=wc-settings'),
                )
            );
        }
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
                'id' => 'newsletter_main_index',
                'title' => 'Newsletter',
                'parent' => 'site-name',
                'href' => admin_url('admin.php?page=newsletter_main_index'),
            )
        );
        $admin_bar->add_node(
            array(
                'id' => 'users',
                'title' => 'Users',
                'parent' => 'site-name',
                'href' => admin_url('users.php'),
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
                    'id' => 'wp-migrate',
                    'title' => 'WP Migrate',
                    'parent' => 'site-name',
                    'href' => admin_url('tools.php?page=wp-migrate-db-pro'),
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

add_filter('wp_logout', 'on_user_logout', 10, 1);