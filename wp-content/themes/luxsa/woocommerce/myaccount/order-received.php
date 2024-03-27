<?php
get_header();
do_action( 'woocommerce_account_navigation' ); 

if ( is_user_logged_in() && current_user_can( 'administrator' ) ) {
    $new_order_ids = get_user_meta( get_current_user_id(), 'new_order_ids', true );
    ?>

    <div class="container">
        <h2>Newly Received Orders</h2>

        <?php if ( $new_order_ids ) : ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer Name</th>
                            <th>Customer Email</th>
                            <th>Order Date</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $new_order_ids as $order_id ) : ?>
                            <?php $order = wc_get_order( $order_id ); ?>
                            <?php if ( $order ) : ?>
                                <tr>
                                    <td><?php echo $order_id; ?></td>
                                    <td><?php echo $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(); ?></td>
                                    <td><?php echo $order->get_billing_email(); ?></td>
                                    <td><?php echo $order->get_date_created()->format( 'F j, Y' ); ?></td>
                                    <td><?php echo wc_price( $order->get_total() ); ?></td>
                                    <td><?php echo wc_get_order_status_name( $order->get_status() ); ?></td>
                                    <td>
                                        <a href="<?php echo esc_url( $order->get_view_order_url() ); ?>" class="btn btn-primary">View</a>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else : ?>
            <p>No newly received orders.</p>
        <?php endif; ?>
    </div>

<?php
} else {
    // Redirect non-admin users or non-logged-in users
    wp_redirect( home_url() );
    exit;
}
get_footer();
