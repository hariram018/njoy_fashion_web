<?php
/*
Template Name: Custom Orders Page
*/

get_header();
// Check if user is logged in and is an admin
if (is_user_logged_in() && current_user_can('administrator')) {
    // Get latest orders
    $args = get_posts(array(
        'post_type'      => 'shop_order_placehold',
        'posts_per_page' => 10, // Number of orders to display
        'orderby'        => 'date',
        'order'          => 'DESC',
    ));

    $orders_query = new WP_Query($args);

    // Start page output
    get_header();
    ?>
    <div id="primary" class="content-area">
        <main id="main" class="site-main" role="main">

            <h1>New Orders</h1>

            <?php
            // Check if there are orders
            if ($orders_query->have_posts()) :
                echo '<ul>';
                while ($orders_query->have_posts()) : $orders_query->the_post();
                    $order = wc_get_order($orders_query->post->ID);
                    echo '<li>Order #' . $order->get_order_number() . '</li>';
                endwhile;
                echo '</ul>';
            else :
                echo 'No new orders found.';
            endif;
            ?>

        </main><!-- #main -->
    </div><!-- #primary -->

    <?php
    // Restore original post data
    wp_reset_postdata();
    get_footer();
} else {
    // Redirect non-admin users or show access denied message
    wp_redirect(home_url()); // Redirect to homepage
    exit;
}

get_footer();
