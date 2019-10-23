<?php
/**
 * Template Name: Specials Template
 */
?>
<?php use Roots\Sage\Titles; ?>

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

<div class="container" id="specials">
	<div class="content-ultrawide">
		<?php while (have_posts()) : the_post(); ?>
			<h1><?= Titles\title(); ?></h1>
			<?php get_template_part('templates/content', 'page'); ?>
			<div class="product-ultrawide">
				<?php echo do_shortcode('[products columns="2" category="special-orders" cat_operator="AND" limit="99994" paginate="true"]'); ?>
			</div>
		<?php endwhile; ?>
	</div>
</div>

<?php get_template_part('templates/quick', 'nav'); ?>
