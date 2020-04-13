<div class="blog-footer container">
  <!-- Go to www.addthis.com/dashboard to customize your tools -->
<script type="text/javascript" src="//s7.addthis.com/js/300/addthis_widget.js#pubid=ra-5e94cf2067a2e262"></script>

<!-- Go to www.addthis.com/dashboard to customize your tools -->
<small class="addthis_inline_share_toolbox"></small>

  <p>
    <?php printf(
      _nx(
        'One response',
        '%1$s responses',
        get_comments_number(),
        'comments title',
        'sage'
      ),
      number_format_i18n(get_comments_number()),
      '<span>' . get_the_title() . '</span>'
    ); ?>
  </p>
</div>

<?php if (post_password_required()) {
  return;
} ?>

<section id="comments" class="comments container">
  <?php if (have_comments()): ?>

    <ol class="comment-list">
      <?php wp_list_comments(['style' => 'ol', 'short_ping' => true]); ?>
    </ol>

    <?php if (get_comment_pages_count() > 1 && get_option('page_comments')): ?>
      <nav>
        <ul class="pager">
          <?php if (get_previous_comments_link()): ?>
            <li class="previous"><?php previous_comments_link(
              __('&larr; Older comments', 'sage')
            ); ?></li>
          <?php endif; ?>
          <?php if (get_next_comments_link()): ?>
            <li class="next"><?php next_comments_link(
              __('Newer comments &rarr;', 'sage')
            ); ?></li>
          <?php endif; ?>
        </ul>
      </nav>
    <?php endif; ?>
  <?php endif;
// have_comments()
?>

  <?php if (
    !comments_open() &&
    get_comments_number() != '0' &&
    post_type_supports(get_post_type(), 'comments')
  ): ?>
    <div class="alert alert-warning">
      <?php _e('Comments are closed.', 'sage'); ?>
    </div>
  <?php endif; ?>

  <div class="comment-form">
    <?php comment_form(); ?>
  </div>
</section>
