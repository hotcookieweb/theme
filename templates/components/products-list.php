<?php if( get_field('product_category') ):
	$category = get_field('product_category');
	$sort_by = get_field('sort_by');
	$columns = get_field('columns');
	$limit = get_field_object('limit');
	$limit_value = $limit['value'];


	if( get_field('display_products') == 'show' ) { echo do_shortcode('[products columns="'. $columns .'" category="'. $category->slug .'" orderby="'. $sort_by .'" cat_operator="AND" limit="'. $limit_value .'" paginate="true"]'); }

endif; ?>