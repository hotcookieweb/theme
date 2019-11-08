<?php if( have_rows('team') ): ?>
	<hr />
	<ul class="team">
    <?php while ( have_rows('team') ) : the_row(); ?>
		<li>
			<img src="<?php the_sub_field('image'); ?>" title="<?php the_sub_field('name'); ?>" />
			<h3><?php the_sub_field('name'); ?></h3>
			<small><?php the_sub_field('function'); ?></small>
			<p><?php the_sub_field('description'); ?></p>
		</li>
	<?php endwhile; ?>
	</ul>
<?php else : ?>

    // no rows found

<?php endif; ?>