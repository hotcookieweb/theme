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
function change_upload_path($path_info, $form_id) {
	if ($form_id != '6') { //a hotcookie
	 return $path_info;
	}
	$user_id = get_current_user_id();
	$folder = 'a-hothookie-files';
	$path_info['path'] = WP_CONTENT_DIR . '/uploads/' . $folder . '/' . $user_id . '/';
	$path_info['url'] = WP_CONTENT_URL . '/uploads/' . $folder . '/' . $user_id . '/';
	return $path_info;
}

add_filter( 'gform_after_submission', 'set_post_content', 10, 2 );
function set_post_content( $entry, $form ) {
  if ($entry['form_id'] != '6') { //a hotcookie
		return;
  }

	$url = $entry[6]; // gravity forms URL
	$title = $entry[4]; // gravity forms caption

	$parsed_url = parse_url( $url );
	if (empty($parsed_url['path'])) {
			return 'path';
	}
	$file = ABSPATH . ltrim( $parsed_url['path'], '/');
	$file_name        = basename( $file );
	$file_type        = wp_check_filetype( $file_name, null );
	$attachment_title = sanitize_file_name( pathinfo( $file_name, PATHINFO_FILENAME ) );

	$post_info = array(
		'guid'           => $url,
		'post_mime_type' => $file_type['type'],
		'post_title'     => $attachment_title,
		'post_excerpt'   => $title,
		'post_status'    => 'pending',
		'post_category' => get_term_by( 'slug', 'a-hotcookie', 'category' ), //a-hotcookie-category
	);

	add_filter('upload_dir', 'a_hotcookie_user_dir');
	// Create the attachment.
	$attach_id = wp_insert_attachment( $post_info, $file, get_current_user_id() );

	// Include image.php.
	require_once ABSPATH . 'wp-admin/includes/image.php';

	// Generate the attachment metadata.
	$attach_data = wp_generate_attachment_metadata( $attach_id, $file );
	// Assign metadata to attachment.
	wp_update_attachment_metadata( $attach_id, $attach_data );

//	wp_set_post_categories( int $post_ID, int[]|int $post_categories = array(), bool $append = false ): array|false|WP_Error

	remove_filter( 'upload_dir', 'a_hotcookie_user_dir' );

	return $attach_id;
}

function a_hotcookie_user_dir( $arr ) {
	$subdir = get_current_user_id();
	$folder = '/a-hothookie-files';
	$basedir = WP_CONTENT_DIR . '/uploads' . $folder . '/';
	$baseurl = WP_CONTENT_URL . '/uploads' . $folder . '/';
	return array(
		'path'    => $basedir . $subdir,
		'url'     => $baseurl . $subdir,
		'subdir'  => $folder . '/' . $subdir,
	);
}
