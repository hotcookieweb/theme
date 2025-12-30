<?php if (get_field('product_category')) {
    $category = get_field('product_category');
    $sort_by = get_field('sort_by');
    $columns = get_field('columns');
    $limit = get_field_object('limit');
    $limit_value = isset($limit['value']) ? $limit['value'] : 12;
    $current_store = WC()->session->get('current_zone');
    error_log('Current store in products list: ' . $current_store);
    if (get_field('display_products') && !empty($category->slug)) {
        echo do_shortcode('[products columns="' . esc_attr($columns) . '" category="' . esc_attr($category->slug) . '" orderby="' . esc_attr($sort_by) . '" cat_operator="AND" limit="' . esc_attr($limit_value) . '" paginate="true" current_store="' . esc_attr($current_store) . '"]');
    }
};

