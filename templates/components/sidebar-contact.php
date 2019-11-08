<div class="sidebar-contact">
	<h3>Address</h3>
	<p><?php the_field('address', 'option'); ?><br />E-mail: &#119;&#101;&#098;&#064;&#104;&#111;&#116;&#099;&#111;&#111;&#107;&#105;&#101;&#046;&#099;&#111;&#109;</p>
	<?php if( have_rows('socialmedia', 'option') ): ?>
		<ul class="socialmedia-red">
		  <?php while ( have_rows('socialmedia', 'option') ) : the_row(); ?>

		      <li class="<?php the_sub_field('platform', 'option'); ?>">
		        <a href="<?php the_sub_field('url', 'option'); ?>">
		          <?php the_sub_field('platform', 'option'); ?>
		        </a>
		      </li>

		  <?php endwhile; ?>
		</ul>
	<?php else : ?>

	<?php endif; ?>
</div>