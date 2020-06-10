<div class="blog-toolbar">
  <div class="container">
    <?php if ( is_home() ) { ?>
      <h1>Blog<?php single_cat_title(__(' / ', 'textdomain')); ?></h1>
    <?php } elseif ( is_category() ) { ?>
      <h1><a href="<?= esc_url(home_url('/blog')) ?>" title="Blog">Blog</a><?php single_cat_title(__(' / ', 'textdomain')); ?></h1>
    <?php } else {
      $addlinks = true;
      ?>
      <h1><a href="<?= esc_url(home_url('/blog')) ?>" title="Blog">Blog</a>
        <?php while (have_posts()): the_post(); ?>
          <?php
          $category = get_the_category();
          $firstCategory = $category[0]->cat_name;
          ?>
          / <a href="<?= esc_url(home_url('/blog/category')) ?>/<?php echo $category[0]->slug ?>" title="<?php echo $firstCategory ?>"><?php echo $firstCategory ?></a>
        <?php endwhile; ?>
        / <?php wp_title(''); ?></h1>
    <?php } ?>
    <div class="dropdown">
      <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        Category
      </button>
      <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
        <?php wp_list_categories([
          'orderby' => 'name',
          'title_li' => '',
          'style' => '',
        ]); ?>
      </div>
    </div>
  </div>
</div>
<?php if ($addlinks) { ?>
  <div class=nav-toolbar>
    <div class=container>
      <h1 class="prev-link" style="float: left;"><?php previous_post_link(
        '%link',
        '« Previous Post'
      ); ?></h1>
      <h1 class="next-link" style="float: right;"><?php next_post_link(
        '%link',
        'Next Post »'
      ); ?></h1>
    </div>
  </div>
<?php } ?>
