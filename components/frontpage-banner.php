<?php if (has_post_thumbnail( $post->ID ) ): ?>
  <?php $image = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'single-post-thumbnail' ); ?>
  <div class="banner">
<?php else : ?>
  <div class="banner">
<?php endif; ?>
  <div class="container">
    <h1><?php the_field('title'); ?></h1>
    <p><?php the_field('intro'); ?></p>

    <?php echo do_shortcode('[wls-search]'); ?>

    <?php if( have_rows('buttons') ): ?>
      <ul>
        <?php while ( have_rows('buttons') ) : the_row(); ?>

          <li>
            <a href="<?php the_sub_field('url'); ?>" title="<?php the_sub_field('name'); ?>"><?php the_sub_field('name'); ?></a>
          </li>

        <?php endwhile; ?>

        <?php else : ?>
      </ul>
    <?php endif; ?>
  </div>
</div>
