<?php
/**
 * Template Name: Text Template
 */
?>

<div class="container">
	<div class="content-ultrawide">
		<?php while (have_posts()) : the_post(); ?>
			<?php get_template_part('templates/page', 'header'); ?>
			<?php get_template_part('templates/content', 'page'); ?>
		<?php endwhile; ?>
	</div>
</div>

<?php get_template_part('templates/quick', 'nav'); ?>