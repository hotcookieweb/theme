<?php
/**
 * Template Name: Template Legal
 */
?>

<div class="container text-page">
<?php while (have_posts()) : the_post(); ?>

		<?php get_template_part('templates/components/sidebar', 'primary'); ?>

		<div class="content">
				<h1><?php the_title(); ?></h1>
				<?php the_content(); ?>
		</div>

<?php endwhile; ?>
</div>

<?php get_template_part('templates/components/quick', 'nav'); ?>
