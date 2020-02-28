
<?php get_template_part('templates/components/page', 'banner'); ?>

<div class="container text-page">
<?php while (have_posts()) : the_post(); ?>

		<?php get_template_part('templates/components/sidebar', 'secondary'); ?>

		<div class="content">
			<div class="content-small">
				<?php the_content(); ?>
				<?php get_template_part('templates/components/products', 'list'); ?>
			</div>
			<?php get_template_part('templates/components/sidebar', 'contact'); ?>
		</div>
<?php endwhile; ?>
</div>

<?php get_template_part('templates/components/quick', 'nav'); ?>
