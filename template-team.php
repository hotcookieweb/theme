<?php
/**
 * Template Name: Template Team
 */
?>

<?php get_template_part('templates/components/page', 'banner'); ?>
	<?php if( get_field('display') == 'show' ) { get_template_part('templates/components/products', 'header'); } ?>
</div> <!-- Closing the banner -->

<div class="container">
	<?php if( get_field('display_sidebar') == 'show' ) { get_template_part('templates/components/sidebar', 'secondary'); } ?>
	<?php if( get_field('display_sidebar') == 'show' ) { ?><div class="content"><?php } ?>
		<?php while (have_posts()) : the_post(); ?>
			<h1><?php the_title(); ?></h1>
			<p><?php the_field('text'); ?></p>
			<?php get_template_part('templates/components/team', 'list'); ?>
		<?php endwhile; ?>
	<?php if( get_field('display_sidebar') == 'show' ) { ?></div><?php } ?>
</div>

<?php get_template_part('templates/components/quick', 'nav'); ?>