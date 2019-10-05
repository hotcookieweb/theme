<script>
  jQuery(window).scroll(function() {
      var scroll = jQuery(window).scrollTop();
       //console.log(scroll);
      if (scroll >= 100) {
          //console.log('a');
          jQuery(".header").addClass("change");
      } else {
          //console.log('a');
          jQuery(".header").removeClass("change");
      }
  });
</script>

<div class="header">
  <div class="container">
    <h2 class="brand"><a href="<?= esc_url(home_url('/')); ?>"><span><?php bloginfo('name'); ?></span></a></h2>
    <nav class="nav-primary">
      <?php
      if (has_nav_menu('primary_navigation')) :
        wp_nav_menu(['theme_location' => 'primary_navigation', 'menu_class' => 'nav']);
      endif;
      ?>
    </nav>
    <div class="webshop-nav">
      <a class="webshop-cart" href="<?php echo wc_get_cart_url(); ?>" title="<?php _e( 'View your shopping cart' ); ?>">$ <?php echo WC()->cart->total ?> <span><?php echo sprintf ( _n( '%d item', '%d items', WC()->cart->get_cart_contents_count() ), WC()->cart->get_cart_contents_count() ); ?></span></a>
        <?php if ( is_user_logged_in() ) { ?>
          <a href="<?php echo get_permalink( get_option('woocommerce_myaccount_page_id') ); ?>" title="My Account"class="webshop-login">My Account</a>
         <?php } else { ?>
          <a href="<?php echo get_permalink( get_option('woocommerce_myaccount_page_id') ); ?>" title="Login" class="webshop-login">Login</a>
         <?php } ?>
    </div>
  </div>
</div>

<div class="header-mobile">
  <div class="topbar">
    <div class="hamburger">
      <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
      <path fill-rule="evenodd" clip-rule="evenodd" d="M2.66663 5.3335H29.3333V8.00016H2.66663V5.3335ZM2.66663 14.6668H29.3333V17.3335H2.66663V14.6668ZM29.3333 24.0002H2.66663V26.6668H29.3333V24.0002Z" fill="#9B0D12"/>
      </svg>
    </div>

    <a class="brand" href="<?= esc_url(home_url('/')); ?>">
      <?php bloginfo('name'); ?>12
    </a>

    <div class="cart">
      <a href="<?php echo wc_get_cart_url(); ?>" title="<?php _e( 'View your shopping cart' ); ?>">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path fill-rule="evenodd" clip-rule="evenodd" d="M12 1.9502C14.0344 1.9502 15.7039 3.61967 15.7039 5.65404L15.6539 6.33481L19.3119 6.38148L19.3576 7.06713L20.0884 20.2208L20.0842 21.0502L3.86597 20.9973L3.91163 20.221L4.64243 7.06658L4.738 6.33481L8.29617 6.38481V5.65404C8.29617 3.61967 9.96564 1.9502 12 1.9502ZM12 3.51173C10.8173 3.51173 9.85771 4.47132 9.85771 5.65404L9.80771 6.33481L14.1423 6.38481V5.65404C14.1423 4.47132 13.1827 3.51173 12 3.51173ZM6.15531 7.89635L5.46877 19.4887L18.4813 19.5414L17.8918 7.89635L15.7039 7.84635L15.6539 10.0887L14.1423 10.0387L14.1923 7.89635L9.85771 7.84635L9.80771 10.0887L8.29617 10.0387L8.34617 7.89635H6.15531Z" fill="#9B0D12"/>
        </svg>
        <span>
          <?php echo sprintf ( _n( '%d', '%d', WC()->cart->get_cart_contents_count() ), WC()->cart->get_cart_contents_count() ); ?>
        </span>
      </a>
    </div>
  </div>

  <script>
    jQuery(document).ready(function(){
      jQuery('.header-mobile .hamburger').click(function(event) {
        jQuery('.header-mobile .navigation').toggleClass('active');
      });
    });
  </script>

    <div class="navigation">
      <div class="quick-nav">
        <h2>Shop for</h2>
        <?php $the_query = new WP_Query( 'page_id=2' ); ?>

        <?php while ($the_query -> have_posts()) : $the_query -> the_post();  ?>

          <?php if( have_rows('services') ): ?>
              <ul>
              <?php while ( have_rows('services') ) : the_row(); ?>
                <li>
                  <a href="<?php the_sub_field('link'); ?>" title="<?php the_sub_field('title'); ?>">
                    <?php the_sub_field('title'); ?>
                  </a>
                </li>
              <?php endwhile; ?>
              </ul>
          <?php else : ?>
          <?php endif; ?>

        <?php endwhile;?>
      </div>
      <?php if (has_nav_menu('mobile_navigation')) :
        wp_nav_menu(['theme_location' => 'mobile_navigation', 'menu_class' => 'nav']);
      endif; ?>
      <ul>
        <?php if ( is_user_logged_in() ) { ?>
          <li><a href="<?php echo get_permalink( get_option('woocommerce_myaccount_page_id') ); ?>" title="My Account"class="webshop-login">My Account</a></li>
        <?php } else { ?>
          <li><a href="<?php echo get_permalink( get_option('woocommerce_myaccount_page_id') ); ?>" title="Login" class="webshop-login">Login</a></li>
        <?php } ?>
      </ul>
    </div>
  </div>