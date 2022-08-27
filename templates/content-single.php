<?php while (have_posts()):
  the_post(); ?>
  <article <?php post_class(); ?>>
    <div class="entry-content container">
      <?php if (has_post_thumbnail()) {
        the_post_thumbnail('post-thumbnail',['style'=>'border:5px solid white']);
      } ?>
      <br>
      <h1 class="entry-title"><?php the_title(); ?></h1>
      <?php get_template_part('templates/entry-meta'); ?>
      <?php the_content(); ?>
    </div>
    <footer class="container">
    </footer>
    <?php comments_template('/templates/comments.php'); ?>
  </article>
<?php
endwhile; ?>
