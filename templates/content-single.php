<?php while (have_posts()):
  the_post(); ?>
  <article <?php post_class(); ?>>
    <header>
      <?php if (has_post_thumbnail()) {
        the_post_thumbnail();
      } ?>
    </header>
    <div class="entry-content container">
      <h1 class="entry-title"><?php the_title(); ?></h1>
      <?php get_template_part('templates/entry-meta'); ?>
      <?php the_content(); ?>
    </div>
    <footer class="container">
      <ul class="nav-posts">
        <li class="prev-link"><?php previous_post_link(
          '%link',
          '« Previous Post'
        ); ?></li>
        <li class="next-link"><?php next_post_link(
          '%link',
          'Next Post »'
        ); ?></li>
      </ul>
    </footer>
    <?php comments_template('/templates/comments.php'); ?>
  </article>
<?php
endwhile; ?>
