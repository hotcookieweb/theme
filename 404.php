<?php get_template_part('templates/page', 'header'); ?>

<div id="primary" class="content-area">
        <div id="content" class="site-content" role="main">
            <div class="page-wrapper">
                <center class="page-content">
                    <br>
                    <br>
                    <h2><?php _e( 'Oops, lost our cookies', 'hotcookie' ); ?></h2>
                    <h4><?php _e( 'What could have caused this?', 'hotcookie' ); ?></h4>
                    <p><?php _e( 'We removed a page as part of our website redesign', 'hotcookie' ); ?></p>
                    <p><?php _e( 'The link you clicked might be old and does not work anymore', 'hotcookie' ); ?></p>
                    <p><?php _e( 'You might have accidentally typed an incorrectly URL', 'hotcookie' ); ?></p>
                    <h4><?php _e( 'What can you do?', 'hotcookie' ); ?></h4>
                    <p><a href="<?php echo get_home_url(); ?>"> <?php _e( 'Go to Hot Cookie home', 'hotcookie' ); ?></a></p>
                    <p><a href="<?php echo get_home_url(); ?>/gift-boxes"> <?php _e( 'Send yourself or a friend a gift', 'hotcookie' ); ?></a></p>
                    <br>
                    <br>
                </center><!-- .page-content -->
            </div><!-- .page-wrapper -->
        </div><!-- #content -->
    </div><!-- #primary -->

<?php get_template_part('templates/page', 'footer'); ?>
