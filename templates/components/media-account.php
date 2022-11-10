
<?php
/** WordPress Administration File API */
require_once ABSPATH . 'wp-admin/includes/template.php';
/** WordPress Administration File API */
require_once ABSPATH . 'wp-admin/includes/file.php';

/** WordPress Image Administration API */
require_once ABSPATH . 'wp-admin/includes/image.php';

/** WordPress Media Administration API */
require_once ABSPATH . 'wp-admin/includes/media.php';

?>

<div class="wrap">
	<div id="primary" class="content-area">
		<div id="content" class="site-content" role="main">
			<div class="entry-content">
				<div class="media-upload">
					<?php gravity_form( 'A Hot Cookie', true, true ); ?>
				</div>
			</div>
			<div class="media-frame">
				<h2>Your A Hot Cookie Images</h2>
				<?php echo do_shortcode('[mla_gallery id="' . get_current_user_id() . '" orderby=" columns="4" limit="12" paginate="true"]'); ?>
				</div>
			</div>
		</div><!-- #content -->
	</div><!-- #primary -->
</div><!-- .wrap -->
