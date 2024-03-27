<?php
/**
 * The template for displaying the footer.
 * @package Luxsa WordPress theme
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
?>

        </main><!-- #main -->

        <?php do_action('luxsa/action/after_main'); ?>

        <?php
            do_action('luxsa/action/before_footer');

            if ( ! function_exists( 'elementor_theme_do_location' ) || ! elementor_theme_do_location( 'footer' ) ) {
                do_action('luxsa/action/footer');
            }

            do_action('luxsa/action/after_footer');
        ?>
    </div><!-- #wrap -->

    <?php do_action('luxsa/action/after_wrap'); ?>

</div><!-- #outer-wrap-->

<?php do_action('luxsa/action/after_outer_wrap'); ?>

<div class="la-overlay-global"></div>
<script>
    document.getElementById("newsletter-subscription-form").addEventListener("submit", function(event) {
        var mobileField = document.getElementById("mobile");
        var mobileError = document.getElementById("mobile-error");

        if (!mobileField.checkValidity()) {
            mobileError.style.display = "inline";
            event.preventDefault(); // Prevent form submission
        } else {
            mobileError.style.display = "none";
        }
    });
</script>
<?php wp_footer(); ?>
</body>
</html>