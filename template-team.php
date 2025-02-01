<?php
/**
 * Template Name: Template Team
 */
?>

<?php get_template_part('templates/components/page', 'banner'); ?>

<div class="container">
	<?php get_template_part('templates/components/sidebar', 'secondary'); ?>
	<div class="content">
		<?php while (have_posts()) : the_post(); ?>
			<p><?= get_field('text'); ?></p>
			<?php get_template_part('templates/components/team', 'list'); ?>
		<?php endwhile; ?>
	</div>
</div>

<?php get_template_part('templates/components/quick', 'nav'); ?>
