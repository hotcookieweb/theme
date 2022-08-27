<article <?php post_class(); ?>>
  <?php if (has_post_thumbnail()) {
    the_post_thumbnail('post-thumbnail',['style'=>'border:5px solid white']);
  } ?>
  <header>
    <h2 class="entry-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
    <?php get_template_part('templates/entry-meta'); ?>
  </header>
  <div class="entry-summary">
    <?php the_excerpt(); ?>
    <a href="<?php the_permalink(); ?>" class="btn">Read more</a>
  </div>
</article>
<hr class="line-break">
