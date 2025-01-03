<?php get_template_part('templates/components/page', 'banner'); ?>

<div class="container text-page">
<?php while (have_posts()) : the_post(); ?>

		<?php if( get_field('display_sidebar') == 'show' ) { get_template_part('templates/components/sidebar', 'secondary'); } ?>

		<?php if( get_field('display_sidebar') == 'show' ) { ?><div class="content"><?php } ?>
				<?php the_content(); ?>
				<?php get_template_part('templates/components/products', 'list'); ?>
		<?php if( get_field('display_sidebar') == 'show' ) { ?></div><?php } ?>

<?php endwhile; ?>
</div>

<?php get_template_part('templates/components/quick', 'nav'); ?>
