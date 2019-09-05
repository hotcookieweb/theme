<div class="container" id="cart">
	<div class="content-ultrawide">
		<?php while (have_posts()) : the_post(); ?>
			<?php get_template_part('templates/page', 'header'); ?>
			<?php get_template_part('templates/content', 'page'); ?>
		<?php endwhile; ?>
	</div>
</div>