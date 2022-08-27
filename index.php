<div id="blog">
  <?php get_template_part('templates/components/blog-toolbar'); ?>

  <div class="container">
    <?php if (!have_posts()): ?>
      <div class="alert alert-warning">
        <?php _e('Sorry, no results were found.', 'sage'); ?>
      </div>
      <?php get_search_form(); ?>
    <?php endif; ?>

    <?php while (have_posts()):
      the_post(); ?>
        <?php get_template_part(
          'templates/content',
          get_post_type() != 'post' ? get_post_type() : get_post_format()
        ); ?>
    <?php
    endwhile; ?>
  </div>
</div>
