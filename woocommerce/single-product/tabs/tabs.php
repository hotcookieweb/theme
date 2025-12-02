<?php
/**
 * Single Product tabs (clean separation version)
 *
 * Copy to yourtheme/woocommerce/single-product/tabs/tabs.php
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

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
                    // Always print heading here, never in callbacks
                    echo '<h2>' . esc_html( $tab['title'] ) . '</h2>';
                    call_user_func( $tab['callback'], $key, $tab );
                    ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
<?php endif; ?>