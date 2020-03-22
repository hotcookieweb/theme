<?php while (have_posts()) : the_post(); ?>
	<div class="banner"
		<?php if ($image = get_the_post_thumbnail_url()) {?> style="background-image:url('<?php echo $image; ?>')" <?php } ?>>

			<div class="container">
					<h4><?php the_field('header_content_above');?></h4>
					<h1><?php the_field('header_title'); ?></h1>
					<?php get_template_part('templates/components/delivery', 'form'); ?>
					<h2><?php the_field('header_content_below'); ?></h2>
			</div>
	</div>
	<?php if( have_rows('services') ): ?>
	  <div class="frontpage-services">
	    <ul class="container">
	    <?php while ( have_rows('services') ) : the_row(); ?>

	      <li>
	        <a href="<?php the_sub_field('link'); ?>" title="<?php the_sub_field('title'); ?>">
	          <img src="<?php the_sub_field('image'); ?>" alt="<?php the_sub_field('title'); ?>" />
	          <h2><?php the_sub_field('title'); ?></h2>
	          <p><?php the_sub_field('content'); ?></p>
	        </a>
	      </li>

	    <?php endwhile; ?>
	    </ul>
	  </div>
	  <?php else : ?>
	<?php endif; ?>

	<?php if( have_rows('services') ): ?>
	    <ul class="mobile-slider">
	    <?php while ( have_rows('services') ) : the_row(); ?>
	      <li>
	        <a href="<?php the_sub_field('link'); ?>" title="<?php the_sub_field('title'); ?>">
	          <img src="<?php the_sub_field('image'); ?>" alt="<?php the_sub_field('title'); ?>" />
	          <h2><?php the_sub_field('title'); ?></h2>
	          <p><?php the_sub_field('content'); ?></p>
	          <p class="link">Check it out</p>
	        </a>
	      </li>
	    <?php endwhile; ?>
	    </ul>
	<?php else : ?>
	<?php endif; ?>

<?php endwhile; ?>
