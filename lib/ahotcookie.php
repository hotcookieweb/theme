<?php
class A_HotCookie_Account_Endpoint {

	/**
	 * Custom endpoint name.
	 *
	 * @var string
	 */
	public static $endpoint = 'a-hotcookie';

	/**
	 * Plugin actions.
	 */
	public function __construct() {
		// Actions used to insert a new endpoint in the WordPress.
		add_action( 'init', array( $this, 'add_endpoints' ) );
		add_filter( 'woocommerce_get_query_vars', array( $this, 'get_query_vars' ), 0 );

		// Change the My Accout page title.
		add_filter( 'the_title', array( $this, 'endpoint_title' ) );

		// Insering your new tab/page into the My Account page.
		add_filter( 'woocommerce_account_menu_items', array( $this, 'new_menu_items' ) );
		add_action( 'woocommerce_account_' . self::$endpoint .  '_endpoint', array( $this, 'endpoint_content' ) );
	}

	/**
	 * Register new endpoint to use inside My Account page.
	 *
	 * @see https://developer.wordpress.org/reference/functions/add_rewrite_endpoint/
	 */
	public function add_endpoints() {
		add_rewrite_endpoint( self::$endpoint, EP_ROOT | EP_PAGES );
	}

	/**
	 * Add new query var.
	 *
	 * @param array $vars
	 * @return array
	 */
	public function get_query_vars( $vars ) {
		$vars[ self::$endpoint ] = self::$endpoint;

		return $vars;
	}

	/**
	 * Set endpoint title.
	 *
	 * @param string $title
	 * @return string
	 */
	public function endpoint_title( $title ) {
		global $wp_query;

		$is_endpoint = isset( $wp_query->query_vars[ self::$endpoint ] );

		if ( $is_endpoint && ! is_admin() && is_main_query() && in_the_loop() && is_account_page() ) {
			// New page title.
			$title = __( 'Pictures and Videos', 'woocommerce' );

			remove_filter( 'the_title', array( $this, 'endpoint_title' ) );
		}

		return $title;
	}

	/**
	 * Insert the new endpoint into the My Account menu.
	 *
	 * @param array $items
	 * @return array
	 */
	public function new_menu_items( $items ) {
		// Remove the logout menu item.
		$logout = $items['customer-logout'];
		unset( $items['customer-logout'] );

		// Insert your custom endpoint.
		$items[ self::$endpoint ] = __( 'Pictures and Videos', 'woocommerce' );

		// Insert back the logout item.
		$items['customer-logout'] = $logout;

		return $items;
	}

	/**
	 * Endpoint HTML content.
	 */
	public function endpoint_content() {
 		get_template_part('templates/components/media', 'account');;
	}

	/**
	 * Plugin install action.
	 * Flush rewrite rules to make our custom endpoint available.
	 */
	public static function install() {
			flush_rewrite_rules();
	}
}

new A_HotCookie_Account_Endpoint();
//register_activation_hook( __FILE__, array( 'FUE_Account_Endpoint', 'install' ) );


add_filter( 'acf/upload_prefilter/name=a_hotcookie_files', 'a_hotcookie_upload_prefilter' );
add_filter( 'acf/prepare_field/name=a_hotcookie_files', 'a_hotcookie_files_field_display' );

function a_hotcookie_upload_prefilter( $errors ) {

  add_filter( 'upload_dir', 'a_hotcookie_upload_directory' );

  return $errors;

}

add_filter("gform_upload_path", "change_upload_path", 10, 2);
function change_upload_path($path_info, $form_id){
 if ($form_id = '5') { //a hotcookie
	 $user_id = get_current_user_id();
	 $folder = 'a-hothookie-files/';
   $path_info['path'] = WP_CONTENT_DIR . '/uploads/' . $folder . $user_id;
   $path_info['url'] = WP_CONTENT_URL . '/uploads/' . $folder . $user_id;
	 return $path_info;
 }
}
