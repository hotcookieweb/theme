<?php
/**
 * Template Name: Template Store
 */
?>

<?php

$page = $wp->request;
$parent = explode ("/", $page)[0];

if ($parent == 'charity') {
	$postid = url_to_postid($page); // info of charity page
  WC()->session->set( 'charity_pageid', $postid ); // Save the charity_pageid in session
}

$zipcode = sanitize_text_field( $_GET["zipcode"] );

get_template_part('templates/components/page', 'banner');

if ($zipcode) {
	get_template_part('templates/components/delivery', 'save', ['zipcode'=>$zipcode]);
}

?>

<div class="container">
	<?php if( get_field('display_sidebar') == 'show' ) { get_template_part('templates/components/sidebar', 'primary'); } ?>

	<?php if( get_field('display_sidebar') == 'show' ) { ?><div class="content"><?php } ?>
		<?php while (have_posts()) : the_post(); ?>
			<?php the_content(); ?>
			<?php if( get_field('display_categories') == 'show' ) { get_template_part('templates/components/store', 'categories'); } ?>

			<?php get_template_part('templates/components/products', 'list'); ?>

		<?php endwhile; ?>
	<?php if( get_field('display_sidebar') == 'show' ) { ?></div><?php } ?>
</div>

<?php get_template_part('templates/components/quick', 'nav'); ?>
