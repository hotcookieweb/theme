<?php
/**
 * Template Name: Team Template
 */
?>

<?php while (have_posts()) : the_post(); ?>
	<?php if (has_post_thumbnail( $post->ID ) ): ?>
  	<?php $image = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'single-post-thumbnail' ); ?>
	  <div class="page-banner" style="background-image:url('<?php echo $image[0]; ?>')">
	<?php else : ?>
	  <div class="page-banner">
	<?php endif; ?>
<?php endwhile; ?>
	<div class="container">
		<?php while (have_posts()) : the_post(); ?>
			<h1><?php the_field('header_title'); ?></h1>
			<p><?php the_field('header_content'); ?></p>
		<?php endwhile; ?>
	</div>
</div>

<div class="container" id="aboutus">
	<div class="sidebar-page">
		<?php dynamic_sidebar('sidebar-secondary'); ?>
	</div>
	<div class="content">
		<?php while (have_posts()) : the_post(); ?>
			<?php get_template_part('templates/page', 'header'); ?>
			<?php get_template_part('templates/content', 'page'); ?>
			<p><?php the_field('text'); ?></p>
			<?php if( have_rows('team') ): ?>
				<hr />
				<ul class="team">
			    <?php while ( have_rows('team') ) : the_row(); ?>
					<li>
						<img src="<?php the_sub_field('image'); ?>" title="<?php the_sub_field('name'); ?>" />
						<h3><?php the_sub_field('name'); ?></h3>
						<small><?php the_sub_field('function'); ?></small>
						<p><?php the_sub_field('description'); ?></p>
					</li>
		    	<?php endwhile; ?>
				</ul>
			<?php else : ?>

			    // no rows found

			<?php endif; ?>

		<?php endwhile; ?>
	</div>
</div>

<?php get_template_part('templates/quick', 'nav'); ?>