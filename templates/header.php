<?php // stores array and zone used in delivery-form.php and product pages also
$data_store = WC_Data_Store::load('shipping-zone');
$shipping_zones = $data_store->get_zones();
foreach ($shipping_zones as $shipping_zone) {
  $zone_data = new WC_Shipping_Zone($shipping_zone);
  $zone_name = $zone_data->get_zone_name();
  if ($zone_name !== 'Rest of World') {
    $stores[$zone_name] = hc_get_store_data('header_title', $zone_name);
  }
}
$zone = WC()->session->get('delivery_zone');
set_query_var('stores', $stores);
set_query_var('zone', $zone);
?>
<div class="header">
  <div class="container">
    <nav class="nav-primary">
      <div class="hamburger">
        <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path fill-rule="evenodd" clip-rule="evenodd" d="M2.66663 5.3335H29.3333V8.00016H2.66663V5.3335ZM2.66663 14.6668H29.3333V17.3335H2.66663V14.6668ZM29.3333 24.0002H2.66663V26.6668H29.3333V24.0002Z" fill="#ffffff" />
        </svg>
      </div>
      <div class="store-switcher">
        <div class="current-store">
          <span class="store-icon"></span>
          <span class="store-name"><?= (empty($zone) ? "Choose Store" : $stores[$zone]); ?></span>
          <ul class="store-dropdown">
          <?php foreach ($stores as $key => $value) { ?>
            <li>
              <?php if ($key != $zone) { ?>
                <a href="#" data-zone="<?= $key ?>" ><?= $value ?></a>
              <?php } ?>
            </li>
          <?php } ?>
          </ul>
        </div>
      </div>
    </nav>
    <h2 class="brand"><a href="<?= esc_url(home_url('/')) ?>"><span><?php bloginfo('name'); ?></span></a></h2>
    <div class="webshop-nav">
      <a class="webshop-cart" href="<?php echo wc_get_cart_url(); ?>" title="<?php _e('View your shopping cart'); ?>">$ <?php echo WC()->cart->total; ?>
        <span><?php echo sprintf(_n('%d item', '%d items', WC()->cart->get_cart_contents_count()), WC()->cart->get_cart_contents_count()); ?></span>
      </a>
      <?php if (is_user_logged_in()) { ?>
        <a href="<?php echo get_permalink(get_option('woocommerce_myaccount_page_id')); ?>" title="My Account" class="webshop-login">My Account</a>
      <?php } else { ?>
        <a href="<?php echo get_permalink(get_option('woocommerce_myaccount_page_id')); ?>" title="Login" class="webshop-login">Login</a>
      <?php } ?>
    </div>
  </div>

  <div class="navigation">
    <?php if (has_nav_menu('mobile_navigation')):
      wp_nav_menu([
        'theme_location' => 'mobile_navigation',
        'menu_class' => 'nav',
      ]);
    endif; ?>
    <ul>
      <?php if (is_user_logged_in()) { ?>
        <li><a href="<?php echo get_permalink(
                        get_option('woocommerce_myaccount_page_id')
                      ); ?>" title="My Account" class="webshop-login">My Account</a></li>
      <?php } else { ?>
        <li><a href="<?php echo get_permalink(
                        get_option('woocommerce_myaccount_page_id')
                      ); ?>" title="Login" class="webshop-login">Login</a></li>
      <?php } ?>
    </ul>
  </div>
</div>

<div class="header-mobile">
  <div class="topbar">
    <div class="hamburger">
      <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path fill-rule="evenodd" clip-rule="evenodd" d="M2.66663 5.3335H29.3333V8.00016H2.66663V5.3335ZM2.66663 14.6668H29.3333V17.3335H2.66663V14.6668ZM29.3333 24.0002H2.66663V26.6668H29.3333V24.0002Z" fill="#ffffff" />
      </svg>
    </div>
    <div class="store-switcher">
      <div class="current-store">
        <span class="store-icon"></span>
        <ul class="store-dropdown">
          <?php foreach ($stores as $key => $value) { ?>
            <li>
              <a href="#" data-zone="<?= $key ?>" class="<?= ($key === $zone) ? 'current' : '' ?>">
                <?= $value ?>
              </a>
            </li>
          <?php } ?>
        </ul>
      </div>
    </div>
    <a class="brand" href="<?= esc_url(home_url('/')) ?>">
      <?php bloginfo('name'); ?>12
    </a>

    <div class="cart">
      <a href="<?php echo wc_get_cart_url(); ?>" title="<?php _e('View your shopping cart'); ?>" class="webshop-cart">
        <span>
          <?php echo sprintf(
            _n('%d', '%d', WC()->cart->get_cart_contents_count()),
            WC()->cart->get_cart_contents_count()
          ); ?>
        </span>
      </a>
    </div>
    <div class="login">
      <?php if (is_user_logged_in()) { ?>
      <a href="<?php echo get_permalink(
                      get_option('woocommerce_myaccount_page_id')
                    ); ?>" title="My Account" class="webshop-login"></a>
      <?php } else { ?>
        <a href="<?php echo get_permalink(
                        get_option('woocommerce_myaccount_page_id')
                      ); ?>" title="Login" class="webshop-login"></a>
      <?php } ?>
    </div>
  </div>
  <div class="navigation">
    <?php if (has_nav_menu('mobile_navigation')):
      wp_nav_menu([
        'theme_location' => 'mobile_navigation',
        'menu_class' => 'nav',
      ]);
    endif; ?>
    <ul>
      <?php if (is_user_logged_in()) { ?>
        <li><a href="<?php echo get_permalink(
                        get_option('woocommerce_myaccount_page_id')
                      ); ?>" title="My Account" class="webshop-login">My Account</a></li>
      <?php } else { ?>
        <li><a href="<?php echo get_permalink(
                        get_option('woocommerce_myaccount_page_id')
                      ); ?>" title="Login" class="webshop-login">Login</a></li>
      <?php } ?>
    </ul>
  </div>
</div>

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

jQuery(document).ready(function() {
  // Desktop hamburger
  jQuery('.header .hamburger').click(function(event) {
    event.stopPropagation();
    jQuery('.header .navigation').toggleClass('active');
    jQuery('.store-switcher').removeClass('active');
  });

  // Mobile hamburger
  jQuery('.header-mobile .hamburger').click(function(event) {
    event.stopPropagation();
    jQuery('.header-mobile .navigation').toggleClass('active');
    jQuery('.store-switcher').removeClass('active');
  });

  // Store icon or current-store click closes both navs
  jQuery('.store-icon, .current-store').click(function(event) {
    event.stopPropagation();
    jQuery('.header .navigation, .header-mobile .navigation').removeClass('active');
  });

  // ✅ Close hamburger menu when clicking outside
  jQuery(document).click(function(event) {
    if (
      !jQuery(event.target).closest('.header .navigation, .header .hamburger').length &&
      !jQuery(event.target).closest('.header-mobile .navigation, .header-mobile .hamburger').length
    ) {
      jQuery('.header .navigation, .header-mobile .navigation').removeClass('active');
    }
  });
});

document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.store-switcher').forEach(switcher => {
    const currentStore = switcher.querySelector('.current-store');
    const storeIcon = switcher.querySelector('.store-icon');
    const dropdown = switcher.querySelector('.store-dropdown');

    if (currentStore && storeIcon && dropdown) {
      console.log('store switcher found');

      currentStore.addEventListener('click', function (e) {
        console.log('current-store clicked');
        e.stopPropagation();
        switcher.classList.toggle('active');
      });

      storeIcon.addEventListener('click', function (e) {
        console.log('store icon clicked');
        e.stopPropagation();
        switcher.classList.toggle('active');
      });

      document.addEventListener('click', function (e) {
        if (!switcher.contains(e.target)) {
          console.log('store switcher closed');
          switcher.classList.remove('active');
        }
      });

      dropdown.querySelectorAll('a[data-zone]').forEach(link => {
        link.addEventListener('click', function (e) {
          e.preventDefault();
          const zone = this.dataset.zone;

          fetch('/wp-admin/admin-ajax.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
              action: 'hc_set_store',
              zone: zone
            })
          })
          .then(res => res.json())
          .then(response => {
            if (!response.success) {
              console.error('AJAX error:', response.data?.message || 'Unknown error');
              return;
            }

            const zoneSlug = response.data.zone;
            const currentUrl = window.location.href;

            if (currentUrl.includes('our-stores')) {
              const newUrl = currentUrl.replace(/our-stores\/?.*$/, `our-stores/${zoneSlug}`);
              window.location.href = newUrl;
            } else {
              location.reload();
            }
          })
          .catch(err => {
            console.error('AJAX exception:', err);
          });
        });
      });
    }
  });
});
</script>