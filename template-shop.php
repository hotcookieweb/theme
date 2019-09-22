<?php
/**
 * Template Name: Template Store
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
			<?php get_template_part('templates/content', 'page'); ?>
			<ul class="shop-categories">
				<li>
					<a href="<?= esc_url(home_url('/the-bakery/cookies/')); ?>" title="#">
						<img src="<?php echo get_bloginfo('template_directory'); ?>/assets/images/Cookies-Home.png" alt="Cookies" />
						<h2>Cookies</h2>
					</a>
				</li>
				<li>
					<a href="<?= esc_url(home_url('/the-bakery/famous')); ?>" title="#">
						<img src="<?php echo get_bloginfo('template_directory'); ?>/assets/images/Famous-Home.png" alt="Famous" />
						<h2>Famous</h2>
					</a>
				</li>
				<li>
					<a href="<?= esc_url(home_url('/the-bakery/treats')); ?>" title="#">
						<img src="<?php echo get_bloginfo('template_directory'); ?>/assets/images/Treat-Home.png" alt="Treats" />
						<h2>Treats</h2>
					</a>
				</li>
				<li>
					<a href="<?= esc_url(home_url('/the-bakery/drinks')); ?>" title="#">
						<img src="<?php echo get_bloginfo('template_directory'); ?>/assets/images/milk-glass.png" alt="Drinks" />
						<h2>Drinks</h2>
					</a>
				</li>
				<li>
					<a href="<?= esc_url(home_url('/the-bakery/gear')); ?>" title="#">
						<img src="<?php echo get_bloginfo('template_directory'); ?>/assets/images/product-4-300x275.png" alt="Gear" />
						<h2>Gear</h2>
						<p>Complete your Hot Cookie experience with our famous Hot Cookie underwear and t-shirt. Buy our gear (underwear, t-shirt or both), post a picture on snapchat, instagram or facebook and we’ll send you a gift certificate for a free cookie or treat.</p>
					</a>
				</li>
			</ul>
		<?php endwhile; ?>
	</div>
</div>

<?php get_template_part('templates/quick', 'nav'); ?>
