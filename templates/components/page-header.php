<?php if( get_field('display_header') == 'show' ) { ?>
	<div class="container">
		<h1><?php the_field('header_title'); ?></h1>
		<p><?php the_field('header_content'); ?></p>
		<?php if ( is_front_page() ) { ?>
			<?php get_template_part('templates/components/delivery', 'form'); ?>
		<?php } ?>
	</div>
<?php } ?>