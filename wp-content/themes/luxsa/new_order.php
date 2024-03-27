<?php
/*
Template Name: Custom Orders Page
*/

get_header();
// Check if user is logged in and is an admin
if (is_user_logged_in() && current_user_can('administrator')) {
    // Get latest orders
    $args = get_posts(array(
        'numberposts' => -1,
        'post_type'   => 'shop_order',
        'post_status' => array('wc-completed', 'wc-processing', 'wc-on-hold'), // Adjust status as needed
    ));

    $orders_query = new WP_Query($args);

    // Start page output
    get_header();
    ?>
    <div id="primary" class="content-area">
        <main id="main" class="site-main" role="main">

            <h1>New Orders</h1>
<?php echo do_shortcode('[display_new_order_details]');?>
        </main><!-- #main -->
    </div><!-- #primary -->

    <?php

    get_footer();
} else {
    // Redirect non-admin users or show access denied message
    wp_redirect(home_url()); // Redirect to homepage
    exit;
}

get_footer();
