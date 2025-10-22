/*
Template Name: Template Our Stores
*/
<?php
get_template_part('templates/components/page', 'banner');
?>
<div class="stores container">
<!-- <span style="visibility: hidden">hack</span> */ -->
<?php if (have_rows('categories')): ?>
  <ul class="shop-categories">
    <?php while (have_rows('categories')): the_row(); ?>
      <li>
        <a href="<?php the_sub_field('link'); ?>" title="<?php the_sub_field('title'); ?>">
          <img src="<?php the_sub_field('image'); ?>" alt="<?php the_sub_field('title'); ?>"/>
          <h2><?php the_sub_field('title'); ?></h2>
        </a>
      </li>
    <?php endwhile; ?>
  </ul>
<?php endif; ?>
</div>
