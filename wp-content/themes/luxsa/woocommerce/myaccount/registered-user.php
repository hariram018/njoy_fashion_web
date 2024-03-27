<?php
get_header();
do_action( 'woocommerce_account_navigation' ); 
// Get newly registered customers
$args = array(
    'role'      => 'customer',
    'orderby'   => 'registered',
    'order'     => 'DESC',
    'number'    => -1
);

$users = get_users( $args );
?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">

        <div class="container">
            <h1>New Registered Customers</h1>

            <?php if ( $users ) : ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Registration Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ( $users as $user ) : ?>
                                <tr>
                                    <td><?php echo esc_html( $user->display_name ); ?></td>
                                    <td><?php echo esc_html( $user->user_email ); ?></td>
                                    <td><?php echo esc_html( $user->billing_phone ); ?></td>
                                    <td><?php echo esc_html( $user->user_registered ); ?></td>
                                    <td>
                                        <a href="<?php echo esc_url( home_url( '/customer-details/?user_id=' . $user->ID ) ); ?>" class="btn btn-primary">View</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else : ?>
                <p>No new registered customers found.</p>
            <?php endif; ?>

        </div><!-- .container -->

    </main><!-- #main -->
</div><!-- #primary -->

<?php
get_footer();
