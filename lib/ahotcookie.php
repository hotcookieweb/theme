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
 		get_template_part('templates/components/media', 'account');
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

function a_hotcookie_user_dir($arr) {
    $subdir  = get_current_user_id();
    $folder  = '/a-hothookie-files';
    $basedir = WP_CONTENT_DIR . '/uploads' . $folder . '/';
    $baseurl = WP_CONTENT_URL . '/uploads' . $folder . '/';
    return [
        'path'   => $basedir . $subdir,
        'url'    => $baseurl . $subdir,
        'subdir' => $folder . '/' . $subdir,
    ];
}

add_filter('body_class', function ($classes) {
if (is_page('customers-as-hot-as-our-cookies')) {
	$classes[] = 'woocommerce';
}
return $classes;
});

add_shortcode('a_hotcookie_uploader', 'render_a_hotcookie_uploader');
function render_a_hotcookie_uploader() {
	$current_user = wp_get_current_user();
	$is_logged_in = is_user_logged_in();
	$user_email   = $is_logged_in ? esc_attr($current_user->user_email) : '';

	ob_start();
	?>
		<h3>A Hot Cookie</h3>
		<form id="hot-cookie-upload" class="woocommerce-EditAccountForm edit-account" enctype="multipart/form-data">
		<?php if (! $is_logged_in): ?>
			<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
			<label for="user_email">Email <span class="required" aria-hidden="true">*</span></label>
			<input type="email" class="woocommerce-Input woocommerce-Input--text input-text" name="user_email" id="user_email" required placeholder="Your email">
			<div id="email-status"></div>
			</p>
		<?php else: ?>
			<input type="hidden" name="user_email" value="<?php echo $user_email; ?>">
		<?php endif; ?>

		<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
			<label for="image_caption">Caption <span class="required" aria-hidden="true">*</span></label>
			<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="image_caption" id="image_caption" maxlength="20" value="A Hot Cookie" required>
			<span class="char-counter" data-counter-for="image_caption" aria-live="polite"></span>
		</p>

		<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
			<label for="upload_image">Upload Image <span class="required" aria-hidden="true">*</span></label>
			<input type="file" class="woocommerce-Input input-text" name="upload_image" style="border: none;" id="upload_image" accept=".jpg,.gif,.png,.webp" required>
			<span>Accepted file types: jpg, gif, png, webp. Max. file size: 20 MB.</span>
		</p>

		<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
			<label for="consent_checkbox">
			<input type="checkbox" class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" name="consent_checkbox" id="consent_checkbox" required>
			I agree to the <a href="/policies/a-hotcookie-policy/" target="_blank">Privacy Policy</a>
			<span class="required" aria-hidden="true">*</span>
			</label>
		</p>

		<input type="hidden" name="hot_cookie_nonce" value="<?php echo wp_create_nonce('hot_cookie_upload'); ?>">
		<input type="hidden" name="action" value="hot_cookie_upload">
		<input type="submit" class="btn" id="submit_button" value="Upload" style="display:none;">
		<?php
			if (isset($_GET['upload'])) { ?>
    			<div class="woocommerce-message" role="alert"><?= $_GET['upload'] ?></div>
		<?php } ?>
		<br>
		</form>

		<script>
		function validateFormFields() {
			const caption   = document.getElementById('image_caption');
			const file      = document.getElementById('upload_image');
			const consent   = document.getElementById('consent_checkbox');
			const submitBtn = document.getElementById('submit_button');

			let emailValid = true;
			const emailField = document.getElementById('user_email');
			if (emailField) {
				emailValid = emailField.checkValidity();
			}

			const isValid =
				emailValid &&
				caption.checkValidity() &&
				file.files.length > 0 &&
				consent.checked;

			submitBtn.style.display = isValid ? 'inline-block' : 'none';
		}

		['input', 'change'].forEach(evt => {
			document.getElementById('hot-cookie-upload').addEventListener(evt, validateFormFields);
		});

		document.getElementById('hot-cookie-upload').addEventListener('submit', function(e) {
			e.preventDefault();
			const form = e.target;
			const formData = new FormData(form);
			fetch('<?= admin_url("admin-ajax.php"); ?>', {
				method: 'POST',
				body: formData
			})
			.then(res => res.json())
			.then(data => {
			if (data.success) {
				const url = new URL(window.location.href);
				url.searchParams.set('upload', data.data.message); // ✅ correct path
				window.location.href = url.toString();
			} else {
				alert('Upload error: ' + data.data.message);
			}
			});
		});

		document.addEventListener("DOMContentLoaded", function () {
			const counters = document.querySelectorAll(".char-counter");

			counters.forEach(counter => {
				const inputId = counter.getAttribute("data-counter-for");
				const input = document.getElementById(inputId);

				if (!input) return;

				const update = () => {
				const length = input.value.length;
				const max = input.maxLength || 100;
				counter.textContent = `${length} / ${max} characters`;
				};

				input.addEventListener("input", update);
				update(); // initialize
			});
		});

		</script>
	<?php
	return ob_get_clean();
}
  
add_shortcode('a_hotcookie_images', 'render_a_hotcookie_images');
function render_a_hotcookie_images() {
	$current_user = wp_get_current_user();
	$is_logged_in = is_user_logged_in();
	$user_email   = $is_logged_in ? esc_attr($current_user->user_email) : '';

	ob_start();
	?>

	<h3>Your Uploaded Hot Cookie Images</h3>
	<?php
		if (isset($_GET['ahcdelete'])) {
			$image_id = intval($_GET['ahcdelete']);
			$current_user = wp_get_current_user();

			$attachment = get_post($image_id);
			if ($attachment->post_author !== get_current_user_id()) {
				echo '<div class="woocommerce-error" role="alert">Unauthorized deletion attempt.</div>';
				return;
			}

			// Check if image is still tagged with 'a-hotcookie'
			$terms = wp_get_object_terms($image_id, 'attachment_category', ['fields' => 'slugs']);
			if (in_array('a-hotcookie', $terms)) {
				// Remove original category
				wp_remove_object_terms($image_id, 'a-hotcookie', 'attachment_category');

				// Assign deleted category
				wp_set_object_terms($image_id, 'a-hotcookie-deleted', 'attachment_category', true);

				// Audit log
				$message = "user login: {$current_user->user_login}" . PHP_EOL .
						"image: " . wp_get_attachment_image_url($image_id);
				wp_mail('web@hotcookie.com', 'User deleted image', $message);

				echo '<div class="woocommerce-message" role="alert">Image deleted</div>';
			}
		}
	?>
	<?php if (is_user_logged_in()) { ?>
		<div class="media-frame">
			<?php
			$args = [
				'author'        => get_current_user_id(),
				'post_type'     => 'attachment',
				'post_status'   => 'inherit',
				'orderby'       => 'post_date',
				'posts_per_page'=> -1,
				'tax_query'     => [
					[
						'taxonomy' => 'attachment_category',
						'field'    => 'term_id',
						'terms'    => 300,
					],
				],
			];
			$the_query = new WP_Query($args);
			?>
			<div class='gallery galleryid-9 gallery-columns-4 gallery-size-thumbnail'>
			<?php if ($the_query->have_posts()) : ?>
				<?php while ($the_query->have_posts()) : $the_query->the_post(); ?>
					<?php
					$id = get_the_ID();
					if (wp_get_post_parent_id($id) == 0) continue;

					$image_thumbnail = wp_get_attachment_image_src($id, 'thumbnail');
					$image_large     = wp_get_attachment_image_src($id, '1536x1536');

					if (!$image_thumbnail || !$image_large) continue;
					?>
					<div class='gallery-item' style='width:200px; height:200px;'>
						<figure class='gallery-image'>
							<a class="gallery-link" href="<?= esc_url($image_large[0]); ?>">
								<img class="gallery-thumbnail" src="<?= esc_url($image_thumbnail[0]); ?>" alt="">
							</a>
						</figure>
						<figcaption class="gallery-caption" style="display: flex; align-items: center; gap: 10px;">
							<a href="<?= esc_url('?ahcdelete=' . $id); ?>" style="display: inline-block; padding-right: 10px;">
							<img src="https://upload.wikimedia.org/wikipedia/commons/7/7d/Trash_font_awesome.svg"
								width="20" height="20" style="display: block;" alt="Delete" title='Delete image'>
							</a>
							<?php $excerpt = get_post_field('post_excerpt', $id); ?>
							<h4 style="margin: 0;">
								<?= !empty($excerpt) ? esc_html($excerpt) : '&nbsp;'; ?>
							</h4>
						</figcaption>
					</div>
				<?php endwhile; ?>
				<?php wp_reset_postdata(); ?>
			<?php else : ?>
				<p>No hot cookie images found.</p>
			<?php endif; ?>
			</div>
		</div>
	<?php } else {?>
		<p>Log in to view your uploaded images.</p>
	<?php }
	return ob_get_clean();
}

add_action('wp_ajax_nopriv_hot_cookie_upload', 'hot_cookie_upload_handler');
add_action('wp_ajax_hot_cookie_upload', 'hot_cookie_upload_handler');

function hot_cookie_upload_handler()
{
    check_ajax_referer('hot_cookie_upload', 'hot_cookie_nonce');

    $email   = sanitize_email($_POST['user_email']);
    $caption = sanitize_text_field($_POST['image_caption']);
    $consent = isset($_POST['consent_checkbox']);

    if (! $consent) {
        wp_send_json_error(['message' => 'Consent is required.']);
    }

	if (! is_email($email)) {
		wp_send_json_error(['message' => 'Invalid email address.']);
	}

    // ✅ Create user silently if needed
    $user_id = email_exists($email);
    if (! $user_id) {
        $random_password = wp_generate_password();
        $user_id         = wp_create_user($email, $random_password, $email);
        if (is_wp_error($user_id)) {
            wp_send_json_error(['message' => 'User registration failed.']);
        }
    }
	else {
		$pending = get_posts([
		'post_type'   => 'attachment',
		'post_status' => 'inherit',
		'author'      => $user_id,
		'numberposts' => 1,
		'tax_query'   => [[
			'taxonomy' => 'attachment_category',
			'field'    => 'slug',
			'terms'    => 'a-hotcookie-pending',
		]],
		]);

		if (!empty($pending)) {
		wp_send_json_error(['message' => 'You already have an upload pending review.']);
		}
	}

    // ✅ Prepare upload directory
    $custom_upload_dir = function ($dirs) use ($user_id) {
        $folder         = '/a-hothookie-files';
        $dirs['path']   = WP_CONTENT_DIR . '/uploads' . $folder . '/' . $user_id;
        $dirs['url']    = WP_CONTENT_URL . '/uploads' . $folder . '/' . $user_id;
        $dirs['subdir'] = $folder . '/' . $user_id;
        return $dirs;
    };
    add_filter('upload_dir', $custom_upload_dir);

    require_once ABSPATH . 'wp-admin/includes/file.php';
    $upload = wp_handle_upload($_FILES['upload_image'], ['test_form' => false]);

    remove_filter('upload_dir', $custom_upload_dir);

    if (isset($upload['error'])) {
        wp_send_json_error(['message' => $upload['error']]);
    }

    $file_url  = $upload['url'];
    $file_path = $upload['file'];
    $file_type = wp_check_filetype($file_path);

    // ✅ Create attachment post
    $attachment = [
        'guid'           => $file_url,
        'post_mime_type' => $file_type['type'],
        'post_title'     => sanitize_file_name(pathinfo($file_path, PATHINFO_FILENAME)),
        'post_excerpt'   => $caption,
        'post_status'    => 'inherit',
        'post_author'    => $user_id,
    ];

    $attach_id = wp_insert_attachment($attachment, $file_path);
    require_once ABSPATH . 'wp-admin/includes/image.php';
    $attach_data = wp_generate_attachment_metadata($attach_id, $file_path);
    wp_update_attachment_metadata($attach_id, $attach_data);

    // ✅ Assign pending category
    if (!term_exists('a-hotcookie-pending', 'attachment_category')) {
        wp_insert_term('a-hotcookie-pending', 'attachment_category');
    }
    wp_set_object_terms($attach_id, 'a-hotcookie-pending', 'attachment_category');

    // ✅ Populate ACF field
    update_field('caption_field', $caption, $attach_id);

    // ✅ Send email to admin
    $image_url = wp_get_attachment_url($attach_id);
    $headers   = ['Content-Type: text/plain; charset=UTF-8'];
    $subject   = "Hot Cookie Upload from: " . $email;
    $message   = "Image titled \"" . $caption . "\" has been uploaded:\n" . $image_url . "\n\nUser ID: {$user_id}";
    wp_mail("info@hotcookie.com", $subject, $message, $headers);

    wp_send_json_success(['message' => 'Upload complete']);
}

add_action('template_redirect', function () {
	if (isset($_GET['upload'])) {
		wp_safe_redirect(remove_query_arg('upload'));
		exit;
	}
});

