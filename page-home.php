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
	<?php endif; ?>

	<?php
	$args = array( 'posts_per_page' => 1,
									'post_status' => 'publish', // Show only the published posts
									'category_name' => 'Announcements'
								);
	$the_query = new WP_Query( $args );
	while ( $the_query->have_posts() ) : $the_query->the_post(); ?>
	<div class="frontpage-blog">
		<ul class="container">
      <li>
				<a href="<?php the_permalink(); ?>" title="<?php the_title();?>">
					<h1><?php the_title(); ?></h1>
					<?php if (has_post_thumbnail()) {
		      	the_post_thumbnail();
		    	} ?>
					<p>
						<?php the_content(); ?>
					</p>
				</a>
			</li>
    <?php endwhile; ?>
		</ul>
	</div>

	<?php
	$args = array( 'posts_per_page' => 1,
									'post_status' => 'publish', // Show only the published posts
									'category_name' => 'Announcements'
								);
	$the_query = new WP_Query( $args );
	while ( $the_query->have_posts() ) : $the_query->the_post(); ?>
	<ul class="mobile-slider">
		<li>
			<a href="<?php the_permalink();?>" title="<?php the_title();?>">
				<h2 class="entry-title"><?php the_title(); ?></h2>
				<?php if (has_post_thumbnail()) {
					the_post_thumbnail();
				} ?>
				<div class="entry-summary">
					<?php the_excerpt(); ?>
				</div>
			</a>
		</li>
    <?php endwhile; ?>
	</ul>
<?php endwhile; ?>
