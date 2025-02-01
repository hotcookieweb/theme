<?php if( get_field('display_header') == 'show' ) { ?>
	<div class="container">
		<h1><?= get_field('header_title'); ?></h1>
		<p><?= get_field('header_content'); ?></p>
	</div>
<?php } ?>
