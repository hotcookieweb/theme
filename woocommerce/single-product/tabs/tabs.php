<?php
/**
 * Single Product tabs
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/tabs/tabs.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.8.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Filter tabs and allow third parties to add their own.
 *
 * Each tab is an array containing title, callback and priority.
 *
 * @see woocommerce_default_product_tabs()
 */
$product_tabs = apply_filters( 'woocommerce_product_tabs', array() );

if ( ! empty( $product_tabs ) ) :
    if ( wp_is_mobile() ) : ?>
        <div class="woocommerce-tabs wc-tabs-wrapper">
            <ul class="tabs wc-tabs">
                <?php foreach ( $product_tabs as $key => $tab ) : ?>
                    <li class="<?php echo esc_attr( $key ); ?>_tab">
                        <a href="#tab-<?php echo esc_attr( $key ); ?>">
                            <?php echo apply_filters( 'woocommerce_product_' . $key . '_tab_title', esc_html( $tab['title'] ), $key ); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
            <?php foreach ( $product_tabs as $key => $tab ) : ?>
                <div id="tab-<?php echo esc_attr( $key ); ?>" class="woocommerce-Tabs-panel woocommerce-Tabs-panel--<?php echo esc_attr( $key ); ?>">
                    <?php call_user_func( $tab['callback'], $key, $tab ); ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else : ?>

        <div class="woocommerce-product-details__description" style="clear:both;display:block;">
            <?php foreach ( $product_tabs as $key => $tab ) : ?>
                <div class="signage-block signage-<?php echo esc_attr( $key ); ?>">
                    <?php
                    // Only output <h2> if the callback doesn't already include it
                    if ( ! in_array( $key, [ 'availability', 'additional_information' ] ) ) {
                        echo '<h2>' . esc_html( $tab['title'] ) . '</h2>';
                    }
                    call_user_func( $tab['callback'], $key, $tab );
                    ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
<?php endif; ?>