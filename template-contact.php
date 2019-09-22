<?php
/**
 * Template Name: Contact Template
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
	<div class="content-small">
		<?php while (have_posts()) : the_post(); ?>
		  <?php get_template_part('templates/page', 'header'); ?>
		  <?php get_template_part('templates/content', 'page'); ?>
		<?php endwhile; ?>
	</div>
	<div class="sidebar-contact">
		<h3>Address</h3>
		<p><?php the_field('address', 'option'); ?><br />E-mail: &#119;&#101;&#098;&#064;&#104;&#111;&#116;&#099;&#111;&#111;&#107;&#105;&#101;&#046;&#099;&#111;&#109;</p>
		<?php if( have_rows('socialmedia', 'option') ): ?>
			<ul class="socialmedia-red">
			  <?php while ( have_rows('socialmedia', 'option') ) : the_row(); ?>

			      <li class="<?php the_sub_field('platform', 'option'); ?>">
			        <a href="<?php the_sub_field('url', 'option'); ?>">
			          <?php the_sub_field('platform', 'option'); ?>
			        </a>
			      </li>

			  <?php endwhile; ?>
			</ul>
		<?php else : ?>

		<?php endif; ?>
	</div>
</div>

<?php get_template_part('templates/quick', 'nav'); ?>