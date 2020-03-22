<?php while (have_posts()) : the_post(); ?>
	<div class="page-banner"
		<?php if ($image = get_the_post_thumbnail_url()) {?> style="background-image:url('<?php echo $image; ?>')" <?php } ?>>

		<?php get_template_part('templates/components/page', 'header'); ?>

	</div>
<?php endwhile; ?>
