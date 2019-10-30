<?php get_template_part('templates/page', 'header'); ?>

<div id="primary" class="content-area">
        <div id="content" class="site-content" role="main">
            <div class="page-wrapper">
                <center class="page-content">
                    <br>
                    <br>
                    <h2><?php _e( 'Oops, that cookie page does not exist', 'hotcookie' ); ?></h2>
                    <a href="<?php echo get_home_url(); ?>"> <?php _e( 'Try Hot Cookie home', 'hotcookie' ); ?></a>
                    <br>
                    <br>
                </center><!-- .page-content -->
            </div><!-- .page-wrapper -->
        </div><!-- #content -->
    </div><!-- #primary -->

<?php get_template_part('templates/page', 'footer'); ?>
