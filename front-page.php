<?php while (have_posts()) : the_post(); ?>
	<?php get_template_part('components/frontpage-banner'); ?>
	<?php get_template_part('components/frontpage-content'); ?>
<?php endwhile; ?>