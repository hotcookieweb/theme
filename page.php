<?php if (has_post_thumbnail( $post->ID ) ): ?>
  <?php $image = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'single-post-thumbnail' ); ?>
  	<div class="page-banner" style="background-image:url('<?php echo $image[0]; ?>')"></div>
<?php else : ?>

<?php endif; ?>

<div class="container">
	<div class="content-ultrawide">
		<?php while (have_posts()) : the_post(); ?>
			<?php get_template_part('templates/page', 'header'); ?>
			<?php get_template_part('templates/content', 'page'); ?>
		<?php endwhile; ?>
	</div>
</div>

<?php get_template_part('templates/quick', 'nav'); ?>