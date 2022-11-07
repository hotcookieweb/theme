<?php if( have_rows('ahc_media') ): ?>
	<hr />
	<ul class="media">
    <?php while ( have_rows('ahc_media') ) : the_row(); ?>
		<li>
			<img src="<?php the_sub_field('image'); ?>" title="<?php the_sub_field('name'); ?>" />
			<h3><?php the_sub_field('name'); ?></h3>
			<p><?php the_sub_field('description'); ?></p>
		</li>
	<?php endwhile; ?>
	</ul>
<?php else : ?>

    // no rows found

<?php endif; ?>
