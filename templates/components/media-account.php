
<?php
/** WordPress Administration File API */
require_once ABSPATH . 'wp-admin/includes/template.php';
/** WordPress Administration File API */
require_once ABSPATH . 'wp-admin/includes/file.php';

/** WordPress Image Administration API */
require_once ABSPATH . 'wp-admin/includes/image.php';

/** WordPress Media Administration API */
require_once ABSPATH . 'wp-admin/includes/media.php';

if (isset($_GET['ahcdelete'])) {
	$image_id=($_GET['ahcdelete']);

	global $current_user;
	wp_mail('web@hotcookie.com', 'user deleted image', 'user login: ' . $current_user->user_login . '/nimage: ' . wp_get_attachment_image_url($image_id));
	wp_update_post([ 'ID' => $image_id, 'post_parent' => '0']);
}
?>

<div class="wrap">
	<div id="primary" class="content-area">
		<div id="content" class="site-content" role="main">
			<div class="entry-content">
				<div class="media-upload">
					<style>
						.gform_heading { padding-bottom:20px !important; }
					</style>
					<?php gravity_form( "You're A Hot Cookie!", true, true ); ?>
				</div>
			</div>
			<div class="media-frame">
				<h3>Your Uploaded Hot Cookie Images</h3>
				<?php $the_query = new WP_Query( array( 'author' => get_current_user_id(), 'post_type' => 'attachment', 'post_status' => 'public', 'orderby' => 'post_date' )); ?>
				<div class='gallery galleryid-9 gallery-columns-4 gallery-size-thumbnail'>
				<?php if ( $the_query->have_posts() ) { ?>
					<?php while ( $the_query->have_posts() ) { ?>
						<?php $the_query->the_post(); $id = get_the_ID() ?>
						<?php if (wp_get_post_parent_id() == 0) continue; ?>
						<?php $image_thumbnail = wp_get_attachment_image_src( $id, 'thumbnail' );?>
						<?php $image_large = wp_get_attachment_image_src( $id, '1536x1536' );?>
						<div class='gallery-item' width='200' height='200'>
							<figure class='gallery-image'>
									<?php echo '<a class="gallery-link" href="' . $image_large[0] . '">'; ?>
									<?php echo '<img class="gallery-thumbnail" src="' . $image_thumbnail[0] . '"></a>'; ?>
									<a href=<?php echo '"?ahcdelete=' . $id . '"';?>>
										<img class='gallery-icon' width='30' height='30' src='https://upload.wikimedia.org/wikipedia/commons/7/7d/Trash_font_awesome.svg'>
									</a>
							</figure>
							<figurecap class="gallery-caption">
								<?php $excerpt = get_post_field('post_excerpt', $id); ?>
								<h4><?php echo (!empty($excerpt) ? $excerpt : '&nbsp');?></h4>
							</figcaption>
						</div>
					<?php } ?>
				<?php } ?>
				</div>
			</div>
		</div><!-- #content -->
	</div><!-- #primary -->
</div><!-- .wrap -->
