<?php if ( is_home() ) { ?>
    <div class="blog-toolbar">
    <div class="container">
      <h1>Blog<?php single_cat_title(__(' / ', 'textdomain')); ?></h1>
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
<?php } elseif ( is_category() ) { ?>
    <div class="blog-toolbar">
    <div class="container">
      <h1><a href="<?= esc_url(home_url('/blog')) ?>" title="Blog">Blog</a><?php single_cat_title(__(' / ', 'textdomain')); ?></h1>
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
<?php } else { ?>
    <div class="blog-toolbar">
        <div class="container">
        <h1><a href="<?= esc_url(home_url('/blog')) ?>" title="Blog">Blog</a> 
    <?php while (have_posts()): the_post(); ?>
        <?php
        $category = get_the_category();
        $firstCategory = $category[0]->cat_name;
        if ($category) { ?>
            / <a href="<?= esc_url(home_url('/category')) ?>/<?php echo $category[0]->slug ?>" title="<?php echo $firstCategory ?>"><?php echo $firstCategory ?></a>
        <? } ?>
    <?php endwhile; ?>

    / <?php wp_title(''); ?></h1>
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
<?php } ?>
