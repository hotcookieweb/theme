<?php
/**
 * Template Name: Template Team
 */
?>

<?php get_template_part('templates/components/page', 'banner'); ?>

<div class="container">
	<?php get_template_part('templates/components/sidebar', 'primary'); ?>
	<div class="content">
		<?php while (have_posts()) : the_post(); ?>
			<p><?php the_field('text'); ?></p>
			<?php get_template_part('templates/components/team', 'list'); ?>
		<?php endwhile; ?>
	</div>
</div>

<?php get_template_part('templates/components/quick', 'nav'); ?>
