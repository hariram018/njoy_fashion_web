<?php
/**
 * Single Product stock.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/stock.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woo.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$stock_status = get_post_meta( get_the_ID(), '_stock_status', true );
?>

<p class="stock <?php echo esc_attr( $class ); ?>">
<?php if ($stock_status=="instock")
{
	echo "In Stock";
}
else{
	echo "Out of Stock";
}
?>
</p>
