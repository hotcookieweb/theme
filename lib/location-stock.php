<?php
/**
 * HC Location Inventory — Variation Level
 *
 * "Per-location stock status" checkbox appears above the native WC
 * Stock Status field in each variation panel.
 *
 * Checked   → WC stock status row hidden, zone table shown.
 * Unchecked → WC stock status row visible, zone table hidden (WC works normally).
 *
 * Meta keys (on variation post):
 *   _hc_location_stock_enabled  1|0
 *   _hc_location_stock          array( 'Zone Name' => 'instock'|'outofstock' )
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// ─────────────────────────────────────────────
// 1. Zone names
// ─────────────────────────────────────────────
function hc_inv_get_zone_names(): array {
    static $zones = null;
    if ( $zones !== null ) return $zones;
    $zones = [];
    $data_store = WC_Data_Store::load( 'shipping-zone' );
    foreach ( $data_store->get_zones() as $z ) {
        $zone = new WC_Shipping_Zone( $z );
        $name = $zone->get_zone_name();
        if ( $name !== 'Rest of World' ) $zones[] = $name;
    }
    return $zones;
}

// ─────────────────────────────────────────────
// 2. Zone status for current session (null = not enabled / no zone)
// ─────────────────────────────────────────────
function hc_inv_get_zone_status( int $variation_id ): ?string {
    if ( ! get_post_meta( $variation_id, '_hc_location_stock_enabled', true ) ) return null;
    $zone = WC()->session ? WC()->session->get( 'current_zone' ) : '';
    if ( empty( $zone ) ) return null;
    $stock = get_post_meta( $variation_id, '_hc_location_stock', true );
    if ( ! is_array( $stock ) || ! isset( $stock[ $zone ] ) ) return null;
    return $stock[ $zone ];
}

// ─────────────────────────────────────────────
// 3. Front-end filters
// ─────────────────────────────────────────────
add_filter( 'woocommerce_variation_is_purchasable', function( bool $purchasable, WC_Product_Variation $v ): bool {
    if ( ! $purchasable ) return false;
    $s = hc_inv_get_zone_status( $v->get_id() );
    return $s === null ? $purchasable : $s === 'instock';
}, 10, 2 );

add_filter( 'woocommerce_variation_is_active', function( bool $active, WC_Product_Variation $v ): bool {
    if ( ! $active ) return false;
    $s = hc_inv_get_zone_status( $v->get_id() );
    return $s === null ? $active : $s === 'instock';
}, 10, 2 );

// ─────────────────────────────────────────────
// 4. Variation admin UI — hidden placeholder that JS moves into position
// ─────────────────────────────────────────────
add_action( 'woocommerce_product_after_variable_attributes', function( int $loop, array $variation_data, WP_Post $variation ): void {
    $zones   = hc_inv_get_zone_names();
    $enabled = (bool) get_post_meta( $variation->ID, '_hc_location_stock_enabled', true );
    $saved   = get_post_meta( $variation->ID, '_hc_location_stock', true );
    $saved   = is_array( $saved ) ? $saved : [];
    ?>

    <?php /* This wrapper is moved by JS to sit above the WC stock status row */ ?>
    <div class="hc-location-stock-wrap" data-loop="<?= $loop ?>" style="display:none;">

        <div class="form-row form-row-full hc-loc-toggle-row">
            <label class="hc-loc-enable-label">
                <input
                    type="checkbox"
                    class="hc-loc-enable-cb"
                    name="hc_location_stock_enabled[<?= $loop ?>]"
                    value="1"
                    <?= checked( $enabled, true, false ) ?>
                >
                <?php esc_html_e( 'Per-location stock status', 'hotcookie' ); ?>
            </label>
        </div>

        <table class="hc-loc-table widefat" style="<?= $enabled ? '' : 'display:none;' ?>margin-top:8px;width:auto;">
            <thead>
                <tr>
                    <th><?php esc_html_e( 'Location', 'hotcookie' ); ?></th>
                    <th><?php esc_html_e( 'Stock Status', 'hotcookie' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $zones as $zone ) :
                    $status = $saved[ $zone ] ?? 'instock';
                ?>
                <tr>
                    <td><?= esc_html( $zone ) ?></td>
                    <td>
                        <select name="hc_location_stock[<?= $loop ?>][<?= esc_attr( $zone ) ?>]">
                            <option value="instock"    <?= selected( $status, 'instock',    false ) ?>>In stock</option>
                            <option value="outofstock" <?= selected( $status, 'outofstock', false ) ?>>Out of stock</option>
                        </select>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    </div>

    <?php
}, 10, 3 );

// ─────────────────────────────────────────────
// 5. Styles + JS
// ─────────────────────────────────────────────
add_action( 'admin_footer', function() {
    static $done = false;
    if ( $done ) return;
    $done = true;
    ?>
    <style>
        .hc-loc-toggle-row { margin: 0; padding: 6px 0; }
        .hc-loc-enable-label { display: flex; align-items: center; gap: 6px; font-weight: 600; cursor: pointer; }
        .hc-loc-table th, .hc-loc-table td { padding: 5px 12px 5px 0; vertical-align: middle; }
        .hc-loc-table th { font-weight: 600; color: #444; }
        .hc-loc-table select { min-width: 130px; }
    </style>
    <script>
    (function($) {
        function hcInitVariation(variation) {
            var $v       = $(variation);
            var loop     = $v.find('.hc-location-stock-wrap').data('loop');
            var $wrap    = $v.find('.hc-location-stock-wrap[data-loop="' + loop + '"]');
            var $cb      = $wrap.find('.hc-loc-enable-cb');
            var $table   = $wrap.find('.hc-loc-table');

            // Find the WC stock status row by its unique per-loop class
            var $stockRow = $v.find('.variable_stock_status' + loop + '_field');

            if ( ! $stockRow.length ) return;

            // Move our wrapper to sit directly below the WC stock status row
            $wrap.insertAfter( $stockRow ).show();

            // Wire toggle — hide WC stock status when per-location is enabled
            function applyToggle( enabled ) {
                if ( enabled ) {
                    $stockRow.hide();
                    $table.show();
                } else {
                    $stockRow.show();
                    $table.hide();
                }
            }

            // Init state
            applyToggle( $cb.is(':checked') );

            // On change
            $cb.on('change', function() {
                applyToggle( this.checked );
            });
        }

        // Run on initial page load (existing variations already in DOM)
        function hcInitAll() {
            $('.woocommerce_variation').each(function() {
                if ( ! $(this).data('hc-inv-init') ) {
                    hcInitVariation(this);
                    $(this).data('hc-inv-init', true);
                }
            });
        }

        $(document).ready( hcInitAll );
        $(window).on( 'load', hcInitAll );
        setTimeout( hcInitAll, 500 );
        setTimeout( hcInitAll, 1500 );

        // Fires after WC loads variations via AJAX
        $(document).on( 'woocommerce_variations_loaded woocommerce_variations_added', hcInitAll );

    }(jQuery));
    </script>
    <?php
} );

// ─────────────────────────────────────────────
// 6. Save
// ─────────────────────────────────────────────
add_action( 'woocommerce_save_product_variation', function( int $variation_id, int $loop ): void {
    $zones   = hc_inv_get_zone_names();
    $enabled = ! empty( $_POST['hc_location_stock_enabled'][ $loop ] );

    update_post_meta( $variation_id, '_hc_location_stock_enabled', $enabled ? 1 : 0 );

    if ( $enabled ) {
        $posted = isset( $_POST['hc_location_stock'][ $loop ] )
            ? (array) $_POST['hc_location_stock'][ $loop ]
            : [];
        $stock = [];
        foreach ( $zones as $zone ) {
            $raw           = $posted[ $zone ] ?? 'instock';
            $stock[ $zone ] = in_array( $raw, [ 'instock', 'outofstock' ], true ) ? $raw : 'instock';
        }
        update_post_meta( $variation_id, '_hc_location_stock', $stock );
    } else {
        delete_post_meta( $variation_id, '_hc_location_stock' );
    }
}, 10, 2 );




