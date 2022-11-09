
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
				<div class="media-display">
					<h2>Your A Hot Cookie Images</h2>
					<?php $the_query = new WP_Query( array( 'author' => get_current_user_id(), 'post_type' => 'attachment', 'post_status' => 'public' )); ?>
					<?php if ( $the_query->have_posts() ) { ?>
						<?php while ( $the_query->have_posts() ) { ?>
							<?php $the_query->the_post(); $id = get_the_ID() ?>
								<?php $image_thumbnail = wp_get_attachment_image_src( $id, 'woocommerce_gallery_thumbnail' );?>
								<?php $image_large = wp_get_attachment_image_src( $id, '1536x1536' );?>
								<?php echo '<a class="attachment" href="' . $image_large[0] . '">'; ?>
								<?php echo '<img width=' . $image_thumbnail[1] . ' height=' . $image_thumbnail[2] . ' class="attachment-woocommerce-gallery-thumbnail" src="' . $image_thumbnail[0] . '" alt=""</a>'; ?>
								<h3><?php echo get_post_field('post_content', $id); ?></h3>
						<?php } ?>
					<?php } ?>
				</div>
			</div><!-- #content -->
		</div><!-- #primary -->
	</div><!-- .wrap -->
