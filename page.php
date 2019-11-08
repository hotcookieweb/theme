<?php get_template_part('templates/components/page', 'banner'); ?>

<div class="container text-page">
<?php while (have_posts()) : the_post(); ?>

		<?php if( get_field('display_sidebar') == 'show' ) { get_template_part('templates/components/sidebar', 'secondary'); } ?>

		<?php if( get_field('display_sidebar') == 'show' ) { ?><div class="content"><?php } ?>
			<?php if( get_field('display_address') == 'show' ) { ?>
				<div class="content-small">
			<?php } ?>
				<h1><?php the_title(); ?></h1>
				<?php the_content(); ?>
				<?php get_template_part('templates/components/products', 'list'); ?>
			<?php if( get_field('display_address') == 'show' ) { ?>
				</div>
				<?php get_template_part('templates/components/sidebar', 'contact'); ?>
			<?php } ?>
		<?php if( get_field('display_sidebar') == 'show' ) { ?></div><?php } ?>

<?php endwhile; ?>
</div>

<?php get_template_part('templates/components/quick', 'nav'); ?>