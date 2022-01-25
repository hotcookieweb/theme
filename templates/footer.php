<div class="footer">
  <div class="container">
    <h2><span>Hot Cookie</span></h2>
    <div class="footer-nav">
      <div class="main">
        <h3>About Us</h3>
        <?php wp_nav_menu( array( 'theme_location' => 'footer_navigation_main' ) ); ?>
      </div>
      <div class="second">
        <h3>Products</h3>
        <?php wp_nav_menu( array( 'theme_location' => 'footer_navigation_second' ) ); ?>
      </div>
      <div class="third">
        <h3>Legal</h3>
        <?php wp_nav_menu( array( 'theme_location' => 'footer_navigation_third' ) ); ?>
      </div>
    </div>
    <div class="contact">
      <p>Follow us & Spread the word!</p>
      <?php if( have_rows('socialmedia', 'option') ): ?>
        <ul class="socialmedia">
          <?php while ( have_rows('socialmedia', 'option') ) : the_row(); ?>

              <li class="<?php the_sub_field('platform', 'option'); ?>">
                <a href="<?php the_sub_field('url', 'option'); ?>">
                  <?php the_sub_field('platform', 'option'); ?>
                </a>
              </li>

          <?php endwhile; ?>
        </ul>
      <?php else : ?>

      <?php endif; ?>
    </div>
  </div>
  <div class="copyright">
    <div class="container">
      <p>© 2022 Hot Cookie. All right reserved.</p>
    </div>
  </div>
</div>
