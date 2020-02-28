<?php
/**
 * Template Name: Template Store
 */
?>
<?php get_template_part('templates/components/page', 'banner'); ?>

<div class="container">
	<?php if( get_field('display_sidebar') == 'show' ) { get_template_part('templates/components/sidebar', 'primary'); } ?>

	<?php if( get_field('display_sidebar') == 'show' ) { ?><div class="content"><?php } ?>
		<?php while (have_posts()) : the_post(); ?>
			<?php the_content(); ?>
			<?php if( get_field('display_categories') == 'show' ) { get_template_part('templates/components/store', 'categories'); } ?>

			<?php get_template_part('templates/components/products', 'list'); ?>

		<?php endwhile; ?>
	<?php if( get_field('display_sidebar') == 'show' ) { ?></div><?php } ?>
</div>

<?php get_template_part('templates/components/quick', 'nav'); ?>
