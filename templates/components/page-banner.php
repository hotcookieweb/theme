<?php while (have_posts()) : the_post(); ?>
	<?php if( get_field('display_featuredimage') == 'show' ) { ?>
		<div class="<?php if ( is_front_page() ) { ?>banner<?php } else { ?>page-banner<?php } ?>" style="background-image:url('<?php echo get_the_post_thumbnail_url(); ?>')">

			<?php get_template_part('templates/components/page', 'header'); ?>

		</div>
	<?php } ?>
<?php endwhile; ?>