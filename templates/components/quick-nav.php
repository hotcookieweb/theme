<div class="quick-nav">
<?php if( have_rows('logos', 'options') ): ?>
	<div class="logos">
		<div class="container">
			<h5>A selection of our awesome costumers</h5>
			<ul>
		 	<?php while ( have_rows('logos', 'options') ) : the_row(); ?>
				<li>
		        	<img src="<?php the_sub_field('image', 'options'); ?>" />
				<li>
		    <?php endwhile; ?>
			</ul>
		</div>
	</div>
<?php else : ?>

<?php endif; ?>

<?php if( have_rows('navigation', 'options') ): ?>
	<ul class="container">
	 	<?php while ( have_rows('navigation', 'options') ) : the_row(); ?>

			<li>
			  <a href="<?php the_sub_field('link', 'options'); ?>" title="<?php the_sub_field('title', 'options'); ?>">
			    <img src="<?php the_sub_field('image', 'options'); ?>" alt="<?php the_sub_field('title', 'options'); ?>" />
			    <h2><?php the_sub_field('title', 'options'); ?></h2>
			    <p><?php the_sub_field('content', 'options'); ?></p>
			  </a>
			</li>

	    <?php endwhile; ?>
	</ul>
<?php else : ?>

<?php endif; ?>

</div>