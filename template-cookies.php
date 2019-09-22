<?php
/**
 * Template Name: Products 2 Columns
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
		<?php dynamic_sidebar('sidebar-primary'); ?>
	</div>
	<div class="content">
		<?php while (have_posts()) : the_post(); ?>
			<h1><?php the_field('title'); ?></h1>
			<p><?php the_field('intro'); ?></p>
			<?php $term = get_field('select_product_category'); if( $term ): ?>
				<?php echo do_shortcode('[products columns="2" category="' . $term->slug . '" cat_operator="AND" limit="99999" paginate="true"]'); ?>
			<?php endif; ?>
		<?php endwhile; ?>
	</div>
</div>

<?php get_template_part('templates/quick', 'nav'); ?>
