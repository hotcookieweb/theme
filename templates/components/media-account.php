
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
			</div><!-- #content -->
		</div><!-- #primary -->
	</div><!-- .wrap -->
