<?php 
if (get_field('product_category')) {

    $category      = get_field('product_category');
    $sort_by       = get_field('sort_by');
    $columns       = get_field('columns');
    $limit_value   = get_field('limit') ?: -1; 
    $current_store = WC()->session->get('current_zone');
    if (empty($current_store)) {
        $current_store = 'any-zone';
    }

    if (get_field('display_products') && !empty($category->slug)) {

        echo do_shortcode(
            '[products columns="' . esc_attr($columns) . 
            '" category="' . esc_attr($category->slug) . 
            '" orderby="' . esc_attr($sort_by) . 
            '" cat_operator="AND" limit="' . esc_attr($limit_value) . 
            '" paginate="true" current_store="' . esc_attr($current_store) . '"]'
        );
    }
}
?>
