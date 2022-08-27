<?php while (have_posts()) : the_post(); ?>
	<div class="banner"
		<?php if ($image = get_the_post_thumbnail_url()) {?> style="background-image:url('<?php echo $image; ?>')" <?php } ?>>

			<div class="container">
					<?php the_field('header_content_above');?>
					<?php the_field('header_title'); ?>
					<?php get_template_part('templates/components/delivery', 'form'); ?>
					<?php the_field('header_content_below'); ?>
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
        </a>
      </li>
			<hr>
    <?php endwhile; ?>
    </ul>
	<?php endif; ?>

	<?php
	$args = array( 'posts_per_page' => 3,
									'post_status' => 'publish', // Show only the published posts
									'category_name' => 'Announcements'
								);
	$the_query = new WP_Query( $args );
	while ( $the_query->have_posts() ) : $the_query->the_post();?>
	<div class="frontpage-blog">
		<ul class="container">
			<li>
				<a href="<?php the_field('featured_image_url'); ?>">
					<?php if (has_post_thumbnail()) {
						the_post_thumbnail('post-thumbnail',['style'=>'border:5px solid white']);
					} ?>
				</a>
				<article class="container">
					<h1>
							<?php the_title(); ?>
					</h1>
					<?php the_content(); ?>
					<a href="<?php the_permalink();?>" title="<?php echo strip_tags(get_the_title());?>" class="link">
						Hit us up with your thoughts
					</a>
				</article>
			</li>
		</ul>
	</div>
	<?php endwhile; ?>

	<?php
	$args = array( 'posts_per_page' => 3,
									'post_status' => 'publish', // Show only the published posts
									'category_name' => 'Announcements'
								);
	$the_query = new WP_Query( $args );
	?>
	<ul class="mobile-slider">
		<?php while ( $the_query->have_posts() ) : $the_query->the_post(); ?>
			<li>
				<h1><?php the_title(); ?></h1>
				<a href="<?php the_field('featured_image_url'); ?>">
					<?php if (has_post_thumbnail()) {
						the_post_thumbnail('post-thumbnail',['style'=>'border:5px solid white']);
					} ?>
				</a>
				<article class="entry-summary">
						<?php the_content(); ?>
						<a style="float:none" href="<?php the_permalink();?>" title="<?php echo strip_tags(get_the_title());?>" class="link">
							Hit us up with your thoughts</a>
				</article>
			</li>
			<?php if (($the_query->current_post + 1) != $the_query->post_count): ?>
						<hr>
			<?php endif ?>
		<?php endwhile; ?>
	</ul>
<?php endwhile; ?>
