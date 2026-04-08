<?php while (have_posts()) : the_post(); ?>
	<div class="banner"
		<?php
		$image = FALSE;
		if ( wp_is_mobile() ) {
			if ($image = get_field( 'featured_image_mobile' )) { ?>
				style="background-image:url('<?php echo $image; ?>')"
			<?php }
		}
		if ($image == FALSE) {
			if ($image = get_the_post_thumbnail_url()) { ?>
				style="background-image:url('<?php echo $image; ?>')"
		<?php }
		} ?>
		>
		<div class="container">
				<?php
				$_hca = get_field('header_content_above');
				// Add aria-hidden to empty H1 placeholders so screen readers skip them
				$_hca = preg_replace('/<h1([^>]*)>\s*<\/h1>/', '<h1$1 aria-hidden="true"></h1>', $_hca ?? '');
				echo $_hca;
				?>
				<?= get_field('header_title'); ?>
				<?php get_template_part('templates/components/delivery', 'form'); ?>
				<?= get_field('header_content_below'); ?>
		</div>
	</div>
	<?php if( have_rows('services') ): ?>
	  <div class="frontpage-services">
	    <ul class="container">
	    <?php while ( have_rows('services') ) : the_row(); ?>
	      <li>
			<?php if (get_sub_field('zone-to-link')) { ?>
				<a class="current_zone"; href="<?= get_sub_field('link') . '/' . WC()->session->get('current_zone') ?>" title="<?php the_sub_field('title'); ?>">
			<?php }
			else { ?>
				<a href="<?php the_sub_field('link'); ?>" title="<?php the_sub_field('title'); ?>">
			<?php } ?>
	          <img src="<?php the_sub_field('image'); ?>" alt="<?php the_sub_field('title'); ?>" />
	          <h2><?php the_sub_field('title'); ?></h2>
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
		<?php if (get_sub_field('zone-to-link')) { ?>
			<a class="current_zone"; href="<?= get_sub_field('link') . "/" . WC()->session->get('current_zone') ?>" title="<?php the_sub_field('title'); ?>">
		<?php }
		else { ?>
			<a href="<?php the_sub_field('link'); ?>" title="<?php the_sub_field('title'); ?>">
		<?php } ?>
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
				<a href="<?php the_permalink(); ?>" aria-label="<?php echo esc_attr(get_the_title()); ?>">
					<?php if (has_post_thumbnail()) {
						the_post_thumbnail('post-thumbnail',['style'=>'border:5px solid white','alt'=>get_the_title()]);
					} ?>
				</a>
				<article class="container">
					<h1>
							<?php the_title(); ?>
					</h1>
					<?php the_content(); ?>
					<a style="float:none" href="<?php echo get_permalink() . '#respond'; ?>" title="<?php echo strip_tags(get_the_title());?>" aria-label="<?php echo esc_attr('Read more: ' . strip_tags(get_the_title())); ?>" class="link">
					Hit us up with your thoughts</a>
				</article>
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
				<a href="<?php the_permalink(); ?>" aria-label="<?php echo esc_attr(get_the_title()); ?>">
					<?php if (has_post_thumbnail()) {
						the_post_thumbnail('post-thumbnail',['style'=>'border:5px solid white','alt'=>get_the_title()]);
					} ?>
				</a>
				<article class="entry-summary">
						<?php the_content(); ?>
						<a style="float:none" href="<?php echo get_permalink() . '#respond'; ?>" title="<?php echo strip_tags(get_the_title());?>" aria-label="<?php echo esc_attr('Read more: ' . strip_tags(get_the_title())); ?>" class="link">
							Hit us up with your thoughts</a>
				</article>
			</li>
			<?php if (($the_query->current_post + 1) != $the_query->post_count): ?>
						<hr>
			<?php endif ?>
		<?php endwhile; ?>
	</ul>
<?php endwhile; ?>
