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

<?php if (has_post_thumbnail( $post->ID ) ): ?>
  <?php $image = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'single-post-thumbnail' ); ?>
  <div class="mobile-about" style="background-image:url('<?php echo $image[0]; ?>')">
<?php else : ?>
  <div class="mobile-about">
<?php endif; ?>
  <div class="effect">
    <h2><?php the_field('title'); ?></h2>
    <p><?php the_field('intro'); ?></p>
    <a href="#" title="About us" class="button">About us</a>
  </div>
</div>