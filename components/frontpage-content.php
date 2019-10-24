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