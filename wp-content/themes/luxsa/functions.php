<?php

/**
 * Theme functions and definitions.
 *
 * Sets up the theme and provides some helper functions
 *
 * When using a child theme (see https://codex.wordpress.org/Theme_Development
 * and https://codex.wordpress.org/Child_Themes), you can override certain
 * functions (those wrapped in a function_exists() call) by defining them first
 * in your child theme's functions.php file. The child theme's functions.php
 * file is included before the parent theme's file, so the child theme
 * functions would be used.
 *
 *
 * For more information on hooks, actions, and filters,
 * see https://codex.wordpress.org/Plugin_API
 *
 * @package Luxsa WordPress theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if(!defined('LUXSA_THEME_VERSION')){
	define('LUXSA_THEME_VERSION', '1.0.0');
}

if(!class_exists('Luxsa_Theme_Class')){

	final class Luxsa_Theme_Class {

		public static $template_dir_path = '';

		public static $template_dir_url = '';

		protected $extra_style = '';

		/**
		 * Main Theme Class Constructor
		 *
		 * @since   1.0.0
		 */
		public function __construct() {

			self::$template_dir_path   = get_template_directory();
			self::$template_dir_url    = get_template_directory_uri();

			// Define constants
			add_action( 'after_setup_theme', array( $this, 'constants' ), 0 );

			// Load all core theme function files
			add_action( 'after_setup_theme', array( $this, 'include_functions' ), 1 );

			// Load configuration classes
			add_action( 'after_setup_theme', array( $this, 'configs' ), 3 );

			// Load framework classes
			add_action( 'after_setup_theme', array( $this, 'classes' ), 4 );

			// Setup theme => add_theme_support: register_nav_menus, load_theme_textdomain, etc
			add_action( 'after_setup_theme', array( $this, 'theme_setup' ) );

			add_action( 'after_setup_theme', array( $this, 'theme_setup_default' ) );

			// register sidebar widget areas
			add_action( 'widgets_init', array( $this, 'register_sidebars' ) );

			/** Admin only actions **/
			if( is_admin() ) {
				// Load scripts in the WP admin
				add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
				add_action( 'elementor/editor/before_enqueue_scripts', array( $this, 'admin_scripts' ) );

				// Add favicon
				add_action( 'admin_head', array( $this, 'render_favicon') );
			}
			/** Non Admin actions **/
			else{

				// Load theme CSS
				add_action( 'wp_enqueue_scripts', array( $this, 'theme_css' ) );

				// Load theme js
				add_action( 'wp_enqueue_scripts', array( $this, 'theme_js' ), 99 );

				// Add a pingback url auto-discovery header for singularly identifiable articles
				add_action( 'wp_head', array( $this, 'pingback_header' ), 1 );

				// Add meta viewport tag to header
				add_action( 'wp_head', array( $this, 'meta_viewport' ), 1 );

				// Add meta apple web app capable tag to header
				add_action( 'wp_head', array( $this, 'apple_mobile_web_app_capable_header' ), 1 );

				// Add favicon
				add_action( 'wp_head', array( $this, 'render_favicon') );

				// Add an X-UA-Compatible header
				add_filter( 'wp_headers', array( $this, 'x_ua_compatible_headers' ) );

				// Add schema markup to the authors post link
				add_filter( 'the_author_posts_link', array( $this, 'the_author_posts_link' ) );

				// Add support for Elementor Pro locations
				add_action( 'elementor/theme/register_locations', array( $this, 'register_elementor_locations' ) );

			}
			// Load scripts in the WP Customizer
			add_action( 'customize_preview_init', array( $this, 'customize_preview_init' ) );

		}

		/**
		 * Define Constants
		 *
		 * @since   1.0.0
		 */
		public function constants() {}

		/**
		 * Load all core theme function files
		 *
		 * @since 1.0.0
		 */
		public function include_functions() {

			require_once get_theme_file_path('/framework/functions/helpers.php');
			require_once get_theme_file_path('/framework/functions/theme-hooks.php');
			require_once get_theme_file_path('/framework/functions/theme-functions.php');
			require_once get_theme_file_path('/framework/third/lastudio.php');
			require_once get_theme_file_path('/framework/third/lastudio-elementor.php');

		}

		/**
		 * Configs for 3rd party plugins.
		 *
		 * @since 1.0.0
		 */
		public function configs() {

			// WooCommerce
			if(function_exists('WC')){
				require_once get_theme_file_path('/framework/woocommerce/woocommerce-config.php');

				// Dokan
				if(function_exists('dokan')){
					require_once get_theme_file_path('/framework/third/dokan.php');
				}
			}
			// Hotel Booking
			if(class_exists('HotelBookingPlugin', false)){
				require_once get_theme_file_path('/framework/third/hotel-booking.php');
			}
		}

		/**
		 * Load theme classes
		 *
		 * @since   1.0.0
		 */
		public function classes() {
			// Admin only classes
			if ( is_admin() ) {
				// Recommend plugins
				require_once get_theme_file_path('/tgm/class-tgm-plugin-activation.php');
				require_once get_theme_file_path('/tgm/tgm-plugin-activation.php');
			}

			require_once get_theme_file_path('/framework/classes/class-admin.php');
			require_once get_theme_file_path('/framework/classes/class-megamenu-init.php');

			// Breadcrumbs class
			require_once get_theme_file_path('/framework/classes/breadcrumbs.php');
			require_once get_theme_file_path('/framework/classes/class-options.php');
			require_once get_theme_file_path('/framework/classes/class-megamenu-walker.php');

			new Luxsa_Admin();
		}

		/**
		 * Theme Setup
		 *
		 * @since   1.0.0
		 */
		public function theme_setup() {

			$ext = apply_filters('luxsa/use_minify_css_file', false) || ( defined('WP_DEBUG') && WP_DEBUG ) ? '' : '.min';

			// Load text domain
			load_theme_textdomain( 'luxsa', self::$template_dir_path .'/languages' );

			// Get globals
			global $content_width;
			// Set content width based on theme's default design
			if ( ! isset( $content_width ) ) {
				$content_width = 1200;
			}

			// Register navigation menus
			register_nav_menus( array(
				'main-nav'   => esc_attr_x( 'Main Navigation', 'admin-view', 'luxsa' )
			) );

			// Enable support for Post Formats
			add_theme_support( 'post-formats', array( 'video', 'gallery', 'audio', 'quote', 'link' ) );

			// Enable support for <title> tag
			add_theme_support( 'title-tag' );

			// Add default posts and comments RSS feed links to head
			add_theme_support( 'automatic-feed-links' );

			// Enable support for Post Thumbnails on posts and pages
			add_theme_support( 'post-thumbnails' );

			/**
			 * Enable support for header image
			 */
			add_theme_support( 'custom-header', apply_filters( 'luxsa/filter/custom_header_args', array(
				'width'              => 2000,
				'height'             => 1200,
				'flex-height'        => true,
				'video'              => true,
			) ) );

			add_theme_support( 'custom-background' );

			// Declare WooCommerce support.
			add_theme_support( 'woocommerce' );
			if(luxsa_get_option('woocommerce_gallery_zoom') == 'yes'){
				add_theme_support( 'wc-product-gallery-zoom');
			}
			if(luxsa_get_option('woocommerce_gallery_lightbox') == 'yes'){
				add_theme_support( 'wc-product-gallery-lightbox');
			}

			// Support WP Job Manager
			add_theme_support( 'job-manager-templates' );

			// Add editor style
			add_editor_style( 'assets/css/editor-style.css' );

			// Adding Gutenberg support
			add_theme_support( 'align-wide' );
			add_theme_support( 'wp-block-styles' );
			add_theme_support( 'responsive-embeds' );
			add_theme_support( 'editor-styles' );
			add_editor_style( 'assets/css/gutenberg-editor.css' );

			add_theme_support( 'editor-color-palette', array(

				array(
					'name' => esc_attr_x( 'pale pink', 'admin-view', 'luxsa' ),
					'slug' => 'pale-pink',
					'color' => '#f78da7',
				),

				array(
					'name' => esc_attr_x( 'theme primary', 'admin-view', 'luxsa' ),
					'slug' => 'luxsa-theme-primary',
					'color' => '#FF7979',
				),

				array(
					'name' => esc_attr_x( 'theme secondary', 'admin-view', 'luxsa' ),
					'slug' => 'luxsa-theme-secondary',
					'color' => '#212121',
				),

				array(
					'name' => esc_attr_x( 'strong magenta', 'admin-view', 'luxsa' ),
					'slug' => 'strong-magenta',
					'color' => '#a156b4',
				),
				array(
					'name' => esc_attr_x( 'light grayish magenta', 'admin-view', 'luxsa' ),
					'slug' => 'light-grayish-magenta',
					'color' => '#d0a5db',
				),
				array(
					'name' => esc_attr_x( 'very light gray', 'admin-view',  'luxsa' ),
					'slug' => 'very-light-gray',
					'color' => '#eee',
				),
				array(
					'name' => esc_attr_x( 'very dark gray', 'admin-view', 'luxsa' ),
					'slug' => 'very-dark-gray',
					'color' => '#444',
				),
			) );

			add_theme_support('lastudio', [
				'swatches'       => true,
				'header-builder' => [
					'menu' => true,
					'header-vertical' => true
				],
				'elementor'      => [
					'advanced-carousel' => true,
					'ajax-templates'    => true,
					'css-transform'     => true,
					'floating-effects'  => true,
					'wrapper-links'     => true,
				],
			]);
		}

		/**
		 * Theme Setup Default
		 *
		 * @since   1.0.0
		 */
		public function theme_setup_default(){
			$check_theme = get_option('luxsa_has_init', false);
			if(!$check_theme || !get_option('luxsa_options')){
				update_option(
					'luxsa_options',
					json_decode('{"layout":"col-1c","body_boxed":"no","body_max_width":"1230","main_full_width":"no","page_loading_animation":"off","page_loading_style":"1","enable_breadcrumb": "off","breadcrumb_home_item":"text","body_font_family":{"font-family":"Poppins","extra-styles":["normal","italic","600","600italic","700","700italic"],"font-size":{"mobile":"16"},"responsive":"yes","unit":"px"},"headings_font_family":{"font-family":"Poppins","extra-styles":["normal","italic","600","600italic","700","700italic"]},"page_title_bar_heading_tag":"h1","page_title_bar_layout":"1","page_title_bar_background":{"background-color":"transparent"},"page_title_bar_space":{"mobile":{"top":"50","bottom":"0","unit":"px"},"mobile_landscape":{"top":"50","bottom":"0","unit":"px"},"tablet":{"top":"70","bottom":"0","unit":"px"},"laptop":{"top":"70","bottom":"0","unit":"px"}},"layout_blog":"col-2cr","blog_small_layout":"off","blog_design":"list-1","blog_post_column":{"mobile":"1","mobile_landscape":"1","tablet":"1","laptop":"2","desktop":"3"},"blog_thumbnail_height_mode":"original","blog_excerpt_length":"37","layout_single_post":"col-2cr","single_small_layout":"off","header_transparency_single_post":"no","blog_post_page_title":"post-title","featured_images_single":"on","single_post_thumbnail_size":"full","blog_post_title":"below","blog_comments":"on","layout_archive_product":"col-1c","header_transparency_archive_product":"inherit","main_full_width_archive_product":"no","main_space_archive_product":{"mobile":{"top":"30","bottom":"60","unit":"px"},"mobile_landscape":{"top":"","bottom":"","unit":"px"},"tablet":{"top":"60","bottom":"60","unit":"px"},"laptop":{"top":"","bottom":"","unit":"px"},"desktop":{"top":"","bottom":"","unit":"px"}},"catalog_mode":"off","catalog_mode_price":"off","shop_catalog_display_type":"grid","shop_catalog_grid_style":"1","woocommerce_catalog_columns":{"mobile":"1","mobile_landscape":"2","tablet":"3","laptop":"4","desktop":"4"},"woocommerce_shop_page_columns":{"mobile":"1","mobile_landscape":"2","tablet":"3","laptop":"4","desktop":"4"},"product_per_page_allow":"12,15,30","product_per_page_default":"12","woocommerce_pagination_type":"pagination","woocommerce_load_more_text":"Load More Products","woocommerce_enable_crossfade_effect":"off","woocommerce_show_rating_on_catalog":"off","woocommerce_show_addcart_btn":"on","woocommerce_show_quickview_btn":"off","woocommerce_show_wishlist_btn":"off","woocommerce_show_compare_btn":"off","layout_single_product":"col-1c","header_transparency_single_product":"no","main_full_width_single_product":"inherit","main_space_single_product":{"mobile":{"top":"20","bottom":"50","unit":"px"},"mobile_landscape":{"top":"30","bottom":"50","unit":"px"},"tablet":{"top":"70","bottom":"50","unit":"px"}},"woocommerce_product_page_design":"1","single_ajax_add_cart":"yes","move_woo_tabs_to_bottom":"yes","woocommerce_gallery_zoom":"yes","woocommerce_gallery_lightbox":"yes","product_single_hide_breadcrumb":"no","product_single_hide_page_title":"yes","product_single_hide_product_title":"no","product_gallery_column":{"mobile":"3","mobile_landscape":"3","tablet":"3","laptop":"3","desktop":"3"},"product_sharing":"off","related_products":"on","related_product_title":"","related_product_subtitle":"","related_products_columns":{"mobile":"1","mobile_landscape":"2","tablet":"3","laptop":"4","desktop":"4"},"upsell_products":"on","upsell_product_title":"","upsell_product_subtitle":"","upsell_products_columns":{"mobile":"1","mobile_landscape":"2","tablet":"3","laptop":"3","desktop":"4"},"crosssell_products":"on","crosssell_product_title":"","crosssell_product_subtitle":"","crosssell_products_columns":{"mobile":"1","mobile_landscape":"2","tablet":"3","laptop":"4","desktop":"4"},"footer_copyright":"2021 Created by LaStudio"}',true)
				);
				update_option('lastudio_header_layout', 'default');
				update_option('lastudio_has_init_header_builder', false);
				update_option('luxsa_has_init', true);
				update_option('la_extension_available', array(
					'swatches' => true,
					'360' => false,
					'content_type' => true
				));
				update_option('lastudio_elementor_modules', array(
					'advanced-carousel' => true,
					'advanced-map' => true,
					'animated-box' => true,
					'animated-text' => true,
					'audio' => true,
					'banner' => true,
					'button' => true,
					'circle-progress' => true,
					'countdown-timer' => true,
					'dropbar'  => true,
					'headline' => true,
					'horizontal-timeline' => true,
					'image-comparison' => true,
					'images-layout' => true,
					'instagram-gallery' => true,
					'portfolio' => true,
					'posts' => true,
					'price-list' => true,
					'pricing-table' => true,
					'progress-bar' => true,
					'scroll-navigation' => true,
					'services' => true,
					'subscribe-form' => true,
					'table' => true,
					'tabs' => true,
					'team-member' => true,
					'testimonials' => true,
					'timeline' => true,
					'video' => true,
					'breadcrumbs' => true,
					'post-navigation' => true,
					'slides' => true
				));
				update_option( 'elementor_cpt_support', array( 'page', 'post', 'la_portfolio') );
				update_option( 'elementor_disable_color_schemes', 'yes' );
				update_option( 'elementor_disable_typography_schemes', 'yes' );
				update_option( 'elementor_stretched_section_container', '#outer-wrap > #wrap' );
				update_option( 'elementor_page_title_selector', '#section_page_header' );
				update_option( 'elementor_editor_break_lines', 1 );
				update_option( 'elementor_unfiltered_files_upload', 1 );
				update_option( 'elementor_edit_buttons', 'on' );
				update_option( 'elementor_global_image_lightbox', '' );
			}
		}

		/**
		 * Adds the meta tag to the site header
		 *
		 * @since 1.0.0
		 */
		public function pingback_header() {

			if ( is_singular() && pings_open() ) {
				printf( '<link rel="pingback" href="%s">' . "\n", esc_url( get_bloginfo( 'pingback_url' ) ) );
			}

		}

		/**
		 * Adds the meta tag to the site header
		 *
		 * @since 1.0.0
		 */
		public function apple_mobile_web_app_capable_header() {
			printf( '<meta name="apple-mobile-web-app-capable" content="yes">' );
		}

		/**
		 * Adds the meta tag to the site header
		 *
		 * @since 1.0.0
		 */
		public function meta_viewport() {

			// Meta viewport
			$viewport = '<meta name="viewport" content="user-scalable=no, width=device-width, initial-scale=1.0">';

			// Apply filters for child theme tweaking
			echo apply_filters( 'luxsa_meta_viewport', $viewport );

		}

		/**
		 * Load scripts in the WP admin
		 *
		 * @since 1.0.0
		 */
		public function admin_scripts() {
			// Load font icon style
			wp_enqueue_style( 'luxsa-font-lastudioicon', get_theme_file_uri( '/assets/css/lastudioicon.min.css' ), false, '1.0.0' );
			if(!class_exists('LASF', false)) {
				wp_enqueue_style( 'luxsa-fonts', $this->enqueue_google_fonts_url() , array(), null );
				wp_enqueue_style( 'luxsa-font-primanova', get_parent_theme_file_uri( '/assets/fonts/Proxima_Nova/stylesheet.css' ) , array(), null );
			}
		}


		/**
		 * Load front-end scripts
		 *
		 * @since   1.0.0
		 */
		public function theme_css() {

			$styleNeedRemove = array(
				'yith-woocompare-widget',
				'jquery-selectBox',
				'yith-wcwl-font-awesome',
				'woocomposer-front-slick',
				'jquery-colorbox'
			);

			foreach ($styleNeedRemove as $style) {
				if (wp_style_is($style, 'registered')) {
					wp_deregister_style($style);
				}
			}

			$theme_version = defined('WP_DEBUG') && WP_DEBUG ? time() : LUXSA_THEME_VERSION;

			$ext = apply_filters('luxsa/use_minify_css_file', false) || ( defined('WP_DEBUG') && WP_DEBUG ) ? '' : '.min';

			// Load font icon style
			wp_enqueue_style( 'luxsa-font-lastudioicon', get_theme_file_uri ('/assets/css/lastudioicon'.$ext.'.css' ), false, $theme_version );

			if(!class_exists('LASF', false)) {
				wp_enqueue_style( 'luxsa-fonts', $this->enqueue_google_fonts_url() , array(), null );
				wp_enqueue_style( 'luxsa-font-primanova', get_parent_theme_file_uri( '/assets/fonts/Proxima_Nova/stylesheet.css' ) , array(), null );
			}

			if(defined('ELEMENTOR_VERSION')){
				wp_enqueue_style( 'luxsa-extra-elementor', get_parent_theme_file_uri( '/assets/css/lastudio-elements'.$ext.'.css' ), false, $theme_version );
			}
			wp_enqueue_style( 'luxsa-theme', get_parent_theme_file_uri('/style'.$ext.'.css'), false, $theme_version );

			if(function_exists('luxsa_check_is_mphb') && luxsa_check_is_mphb()){
				wp_enqueue_style( 'luxsa-hotel-booking', get_theme_file_uri( '/assets/css/hotel-booking'.$ext.'.css' ), false, $theme_version );
			}

			if(luxsa_get_option('enable_header_mb_footer_bar', 'no') == 'yes'){
				wp_enqueue_style( 'luxsa-footer-bar', get_theme_file_uri( '/assets/css/footer-bar'.$ext.'.css' ), false, $theme_version );
			}

			$this->render_extra_style();

			if(function_exists('la_minify_css')){
				$additional_inline_stype = la_minify_css($this->extra_style);
			}
			else{
				$additional_inline_stype = $this->extra_style;
			}


			$inline_handler_name = 'luxsa-theme';

			if(function_exists('WC')){
				wp_enqueue_style( 'luxsa-woocommerce', get_theme_file_uri( '/assets/css/woocommerce'.$ext.'.css' ), false, $theme_version );
				$inline_handler_name = 'luxsa-woocommerce';
			}

			wp_add_inline_style($inline_handler_name, $additional_inline_stype);
		}

		/**
		 * Returns all js needed for the front-end
		 *
		 * @since 1.0.0
		 */
		public function theme_js() {


			$scriptNeedRemove = array(
				'woocomposer-slick',
				'jquery-slick',
			);

			foreach ($scriptNeedRemove as $script) {
				if (wp_script_is($script, 'registered')) {
					wp_dequeue_script($script);
				}
			}

			$theme_version = defined('WP_DEBUG') && WP_DEBUG ? time() : LUXSA_THEME_VERSION;

			$ext = !apply_filters('luxsa/use_minify_js_file', true) || ( defined('WP_DEBUG') && WP_DEBUG ) ? '' : '.min';

			// Get localized array
			$localize_array = $this->localize_array();

			wp_register_script( 'pace', get_theme_file_uri('/assets/js/lib/pace'.$ext.'.js'), null, $theme_version, true);

			wp_register_script( 'js-cookie', get_theme_file_uri('/assets/js/lib/js.cookie'.$ext.'.js'), array('jquery'), $theme_version, true);

			wp_register_script( 'jquery-slick', get_theme_file_uri('/assets/js/lib/slick'.$ext.'.js') , array('jquery'), $theme_version, true);

			wp_register_script( 'jquery-featherlight', get_theme_file_uri('/assets/js/lib/featherlight'.$ext.'.js') , array('jquery'), $theme_version, true);

			$dependencies = array( 'jquery', 'js-cookie', 'jquery-slick', 'jquery-featherlight' );

			if (luxsa_get_option('page_loading_animation', 'off') == 'on') {
				$dependencies[] = 'pace';
			}

			if(function_exists('WC')){
				$dependencies[] = 'luxsa-woo';
				$dependencies[] = 'luxsa-product-gallery';

				if(!empty($localize_array['la_extension_available']['swatches'])){
					$dependencies[] = 'luxsa-product-swatches';
				}
			}

			$dependencies = apply_filters('luxsa/filter/js_dependencies', $dependencies);

			wp_enqueue_script('luxsa-theme', get_theme_file_uri( '/assets/js/app'.$ext.'.js' ), $dependencies, $theme_version, true);

			wp_enqueue_script('luxsa-header-builder', get_theme_file_uri( '/assets/js/header-builder'.$ext.'.js' ), array('luxsa-theme'), $theme_version, true);

			if (is_singular() && comments_open() && get_option('thread_comments')) {
				wp_enqueue_script('comment-reply');
			}

			if(apply_filters('luxsa/filter/force_enqueue_js_external', true)){
				wp_localize_script('luxsa-theme', 'la_theme_config', $localize_array );
			}

			if(function_exists('la_get_polyfill_inline')){

				$polyfill_data = apply_filters('luxsa/filter/js_polyfill_data', [
					'luxsa-polyfill-object-assign' => [
						'condition' => '\'function\'==typeof Object.assign',
						'src'       => get_theme_file_uri( '/assets/js/lib/polyfill-object-assign'.$ext.'.js' ),
						'version'   => $theme_version,
					],
					'luxsa-polyfill-css-vars' => [
						'condition' => 'window.CSS && window.CSS.supports && window.CSS.supports(\'(--foo: red)\')',
						'src'       => get_theme_file_uri( '/assets/js/lib/polyfill-css-vars'.$ext.'.js' ),
						'version'   => $theme_version,
					],
					'luxsa-polyfill-promise' => [
						'condition' => '\'Promise\' in window',
						'src'       => get_theme_file_uri( '/assets/js/lib/polyfill-promise'.$ext.'.js' ),
						'version'   => $theme_version,
					],
					'luxsa-polyfill-fetch' => [
						'condition' => '\'fetch\' in window',
						'src'       => get_theme_file_uri( '/assets/js/lib/polyfill-fetch'.$ext.'.js' ),
						'version'   => $theme_version,
					],
					'luxsa-polyfill-object-fit' => [
						'condition' => '\'objectFit\' in document.documentElement.style',
						'src'       => get_theme_file_uri( '/assets/js/lib/polyfill-object-fit'.$ext.'.js' ),
						'version'   => $theme_version,
					]
				]);
				$polyfill_inline = la_get_polyfill_inline($polyfill_data);
				if(!empty($polyfill_inline)){
					wp_add_inline_script('luxsa-theme', $polyfill_inline, 'before');
				}
			}

		}

		/**
		 * Functions.js localize array
		 *
		 * @since 1.0.0
		 */
		public function localize_array() {

			$header_sticky_offset = luxsa_get_option('header_sticky_offset');

			$template_cache = luxsa_string_to_bool(luxsa_get_option('template_cache'));

			$array = array(
				'security' => array(
					'favorite_posts' => wp_create_nonce('favorite_posts'),
					'wishlist_nonce' => wp_create_nonce('wishlist_nonce'),
					'compare_nonce' => wp_create_nonce('compare_nonce')
				),
				'product_single_design' => esc_attr(luxsa_get_option('woocommerce_product_page_design', 1)),
				'product_gallery_column' => esc_attr(json_encode(luxsa_get_option('product_gallery_column', array(
					'xlg'	=> 3,
					'lg' 	=> 3,
					'md' 	=> 3,
					'sm' 	=> 5,
					'xs' 	=> 4,
					'mb' 	=> 3
				)))),
				'single_ajax_add_cart' => luxsa_string_to_bool(luxsa_get_option('single_ajax_add_cart', 'off')),
				'i18n' => array(
					'backtext' => esc_attr_x('Back', 'front-view', 'luxsa'),
					'compare' => array(
						'view' => esc_attr_x('View List Compare', 'front-view', 'luxsa'),
						'success' => esc_attr_x('has been added to comparison list.', 'front-view', 'luxsa'),
						'error' => esc_attr_x('An error occurred ,Please try again !', 'front-view', 'luxsa')
					),
					'wishlist' => array(
						'view' => esc_attr_x('View List Wishlist', 'front-view', 'luxsa'),
						'success' => esc_attr_x('has been added to your wishlist.', 'front-view', 'luxsa'),
						'error' => esc_attr_x('An error occurred, Please try again !', 'front-view', 'luxsa')
					),
					'addcart' => array(
						'view' => esc_attr_x('View Cart', 'front-view', 'luxsa'),
						'success' => esc_attr_x('has been added to your cart', 'front-view', 'luxsa'),
						'error' => esc_attr_x('An error occurred, Please try again !', 'front-view', 'luxsa')
					),
					'global' => array(
						'error' => esc_attr_x('An error occurred ,Please try again !', 'front-view', 'luxsa'),
						'comment_author' => esc_attr_x('Please enter Name !', 'front-view', 'luxsa'),
						'comment_email' => esc_attr_x('Please enter Email Address !', 'front-view', 'luxsa'),
						'comment_rating' => esc_attr_x('Please select a rating !', 'front-view', 'luxsa'),
						'comment_content' => esc_attr_x('Please enter Comment !', 'front-view', 'luxsa'),
						'continue_shopping' => esc_attr_x('Continue Shopping', 'front-view', 'luxsa'),
						'cookie_disabled' => esc_attr_x('We are sorry, but this feature is available only if cookies are enabled on your browser', 'front-view', 'luxsa'),
						'more_menu' => esc_attr_x('Show More +', 'front-view', 'luxsa'),
						'less_menu' => esc_attr_x('Show Less', 'front-view', 'luxsa'),
					)
				),
				'popup' => array(
					'max_width' => esc_attr(luxsa_get_option('popup_max_width', 790)),
					'max_height' => esc_attr(luxsa_get_option('popup_max_height', 430))
				),
				'js_path'       => esc_attr(apply_filters('luxsa/filter/js_path', self::$template_dir_url . '/assets/js/lib/')),
				'js_min'        => apply_filters('luxsa/use_minify_js_file', true),
				'theme_path'    => esc_attr(apply_filters('luxsa/filter/theme_path', self::$template_dir_url . '/')),
				'ajax_url'      => esc_attr(admin_url('admin-ajax.php')),
				'la_extension_available' => get_option('la_extension_available', array(
					'swatches' => true,
					'360' => true,
					'content_type' => true
				)),
				'mobile_bar' => esc_attr(luxsa_get_option('enable_header_mb_footer_bar_sticky', 'always')),
				'header_sticky_offset' => esc_attr(!empty($header_sticky_offset['height']) ? absint($header_sticky_offset['height']) : 0),
				'templateApiUrl'  => get_rest_url(null, 'lastudio-api/v1/elementor-template'),
				'menuItemsApiUrl'  => get_rest_url(null, 'lastudio-api/v1/get-menu-items'),
				'subscribeForm' => [
					'action' => 'lastudio_elementor_subscribe_form_ajax',
					'nonce' => wp_create_nonce('lastudio_elementor_subscribe_form_ajax'),
					'type' => 'POST',
					'data_type' => 'json',
					'is_public' => 'true',
				],
				'sys_messages' => [
					'invalid_mail'                => esc_html__( 'Please, provide valid mail', 'luxsa' ),
					'mailchimp'                   => esc_html__( 'Please, set up MailChimp API key and List ID', 'luxsa' ),
					'internal'                    => esc_html__( 'Internal error. Please, try again later', 'luxsa' ),
					'server_error'                => esc_html__( 'Server error. Please, try again later', 'luxsa' ),
					'subscribe_success'           => esc_html__( 'Success', 'luxsa' ),
				],
				'has_wc' => function_exists('WC' ) ? true : false,
				'cache_ttl' => apply_filters('luxsa/cache_time_to_life', !$template_cache ? 30 : (60 * 5)),
				'local_ttl' => apply_filters('luxsa/local_cache_time_to_life', !$template_cache ? 30 : (60 * 60 * 24)),
				'home_url' => esc_url(home_url('/')),
				'current_url' => esc_url( add_query_arg(null,null) ),
				'disable_cache' => $template_cache ? false : true,
				'is_dev' => defined('WP_DEBUG') && WP_DEBUG ? true : false
			);

			if(function_exists('la_get_wc_script_data') && function_exists('WC')){
				$variation_data = la_get_wc_script_data('wc-add-to-cart-variation');
				if(!empty($variation_data)){
					$array['i18n']['variation'] = $variation_data;
				}
				$array['wc_variation'] = [
					'base' => esc_url(WC()->plugin_url()) . '/assets/js/frontend/add-to-cart-variation.min.js',
					'wp_util' => esc_url(includes_url('js/wp-util.min.js')),
					'underscore' => esc_url(includes_url('js/underscore.min.js'))
				];
			}

			// Apply filters and return array
			return apply_filters( 'luxsa/filter/localize_array', $array );
		}

		/**
		 * Add headers for IE to override IE's Compatibility View Settings
		 *
		 * @since 1.0.0
		 */
		public function x_ua_compatible_headers( $headers ) {
			$headers['X-UA-Compatible'] = 'IE=edge';
			return $headers;
		}


		/**
		 * Add schema markup to the authors post link
		 *
		 * @since 1.0.0
		 */
		public function the_author_posts_link( $link ) {

			// Add schema markup
			$schema = luxsa_get_schema_markup( 'author_link' );
			if ( $schema ) {
				$link = str_replace( 'rel="author"', 'rel="author" '. $schema, $link );
			}
			// Return link
			return $link;

		}
		/**
		 * Add support for Elementor Pro locations
		 *
		 * @since 1.0.0
		 */
		public function register_elementor_locations( $elementor_theme_manager ) {
			$elementor_theme_manager->register_all_core_location();
		}

		/**
		 * Registers sidebars
		 *
		 * @since   1.0.0
		 */
		public function register_sidebars() {

			$heading = 'h4';
			$heading = apply_filters( 'luxsa/filter/sidebar_heading', $heading );

			// Default Sidebar
			register_sidebar( array(
				'name'			=> esc_html__( 'Default Sidebar', 'luxsa' ),
				'id'			=> 'sidebar',
				'description'	=> esc_html__( 'Widgets in this area will be displayed in the left or right sidebar area if you choose the Left or Right Sidebar layout.', 'luxsa' ),
				'before_widget'	=> '<div id="%1$s" class="widget sidebar-box %2$s">',
				'after_widget'	=> '</div>',
				'before_title'	=> '<'. $heading .' class="widget-title"><span>',
				'after_title'	=> '</span></'. $heading .'>',
			) );

		}

		public function customize_preview_init(){
			wp_enqueue_script('luxsa-customizer', get_theme_file_uri( '/assets/js/customizer.js' ), array( 'jquery', 'customize-preview' ), null, true);
		}

		public static function enqueue_google_fonts_url(){
			$fonts_url = '';
			$fonts     = array();

			if ( 'off' !== _x( 'on', 'Poppins font: on or off', 'luxsa' ) ) {
				$fonts[] = 'Poppins:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700';
			}

			if ( $fonts ) {
                $fonts_url = add_query_arg( array(
                    'family' => implode( '&family=', $fonts ),
                    'display' => 'swap',
                ), 'https://fonts.googleapis.com/css2' );
			}
			return esc_url_raw( $fonts_url );
		}

		public function render_extra_style(){
			$this->extra_style .= $this->dynamic_css();
			$this->extra_style .= $this->css_page_preload();
			$this->render_custom_fonts();
		}

		private function css_page_preload(){
			ob_start();
			include get_parent_theme_file_path('/framework/css/page-preload-css.php');
			$content = ob_get_clean();
			return $content;
		}

		private function dynamic_css(){
			ob_start();
			include get_parent_theme_file_path('/framework/css/dynamic-css.php');
			return ob_get_clean();
		}

		private function render_custom_fonts(){
			$need_enqueues = [];
			$raw_css = [];
			$custom_fonts = luxsa_get_option('custom_fonts', []);
			if(!empty($custom_fonts)){
				foreach ($custom_fonts as $custom_font){
					if(!empty($custom_font['name'])){
						$font_family = $custom_font['name'];
						$font_type = isset($custom_font['type']) ? $custom_font['type'] : 'upload';
						if($font_type == 'upload'){
							$font_variations = !empty($custom_font['variations']) ? $custom_font['variations'] : [];
							foreach ($font_variations as $variation){
								$src = [];
								foreach ( [ 'woff2', 'woff', 'ttf', 'svg' ] as $type ) {
									if ( !empty( $variation[ $type ] ) ) {
										$tmp_url = $variation[ $type ];
										if ( 'svg' === $type ) {
											$tmp_url .= '#' . str_replace( ' ', '', $font_family );
										}
										$tmp_src = 'url(\'' . esc_attr( $tmp_url  ) . '\') ';
										switch ( $type ) {
											case 'woff':
											case 'woff2':
											case 'svg':
												$tmp_src .= 'format(\'' . $type . '\')';
												break;
											case 'ttf':
												$tmp_src .= 'format(\'truetype\')';
												break;
										}
										$src[] = $tmp_src;
									}
								}
								if(!empty($src)){
									$font_face = '@font-face {' . PHP_EOL;
									$font_face .= "\tfont-family: '" . $font_family . "';" . PHP_EOL;
									if(!empty($variation['style'] )){
										$font_face .= "\tfont-style: " . $variation['style'] . ';' . PHP_EOL;
									}
									if(!empty($variation['weight'] )){
										$font_face .= "\tfont-weight: " . $variation['weight'] . ';' . PHP_EOL;
									}
									$font_face .= "\tfont-display: " . apply_filters( 'luxsa/custom_fonts/font_display', 'auto', $font_family, $variation ) . ';' . PHP_EOL;
									$font_face .= "\tsrc: " . implode( ',' . PHP_EOL . "\t\t", $src ) . ';' . PHP_EOL . '}';
									$raw_css[] = $font_face;
								}
							}
						}
						else{
							$need_enqueues[$font_family] = isset($custom_font['url']) ? $custom_font['url'] : '';
						}
					}
				}
			}
			if(!empty($raw_css)){
				$raw_css_output = implode('' . PHP_EOL , $raw_css);
				$this->extra_style .= $raw_css_output;
			}
			if(!empty($need_enqueues)){
				foreach ($need_enqueues as $font_family => $font_url){
					if(!empty($font_url)){
						wp_enqueue_style( 'luxsa-custom-font-' . sanitize_key($font_family), esc_url_raw($font_url) , array(), null );
					}
				}
			}
		}

		public function render_favicon(){
			if ( ! function_exists( 'has_site_icon' ) || ! has_site_icon() ) {
				if( $favicon = luxsa_get_option('favicon') ) {
					if(!empty($favicon['url'])){
						printf('<link rel="apple-touch-icon" sizes="16x16" href="%s"/>', esc_url($favicon['url']));
						printf('<link  rel="shortcut icon" type="image/png" sizes="16x16" href="%s"/>', esc_url($favicon['url']));
					}
				}
				if( $favicon = luxsa_get_option('favicon_iphone') ) {
					if(!empty($favicon['url'])){
						printf('<link rel="apple-touch-icon" sizes="72x72" href="%s"/>', esc_url($favicon['url']));
						printf('<link  rel="shortcut icon" type="image/png" sizes="72x72" href="%s"/>', esc_url($favicon['url']));
					}
				}
				if( $favicon = luxsa_get_option('favicon_ipad') ) {
					if(!empty($favicon['url'])){
						printf('<link rel="apple-touch-icon" sizes="120x120" href="%s"/>', esc_url($favicon['url']));
						printf('<link  rel="shortcut icon" type="image/png" sizes="120x120" href="%s"/>', esc_url($favicon['url']));
					}
				}
			}
		}
	}

}

new Luxsa_Theme_Class();

// Add meta box to page editor
function add_custom_meta_box() {
	add_meta_box(
	    'banner_image_meta_box',
	    'Banner Image',
	    'render_banner_image_meta_box',
	    'page',
	    'normal',
	    'high'
	);
      }
      add_action('add_meta_boxes', 'add_custom_meta_box');
      
      // Render meta box content
      function render_banner_image_meta_box($post) {
	// Retrieve the current value of the banner image
	$banner_image_url = get_post_meta($post->ID, 'banner_image', true);
	?>
	<p>
	    <label for="banner_image">Upload Banner Image:</label><br>
	    <input type="text" id="banner_image" name="banner_image" value="<?php echo esc_attr($banner_image_url); ?>" style="width: 100%;">
	    <input type="button" id="upload_banner_image_button" class="button" value="Upload Image">
	</p>

	<div id="banner_image_preview" style="margin-top: 10px;">
		<?php if (!empty($banner_image_url)) : ?>
			<img src="<?php echo esc_url($banner_image_url); ?>" style="max-width: 100px; height: auto;">
		<?php endif; ?>
    	</div>

	<script>
	    jQuery(document).ready(function($){
	        $('#upload_banner_image_button').click(function() {
		  var image = wp.media({
		      title: 'Upload Image',
		      multiple: false
		  }).open()
		  .on('select', function(e){
		      var uploaded_image = image.state().get('selection').first();
		      var image_url = uploaded_image.toJSON().url;
		      $('#banner_image').val(image_url);
		      $('#banner_image_preview').html('<img src="' + image_url + '" style="max-width:100px; height:auto;">');
		  });
	        });
	    });
	</script>
	<?php
      }
      
      // Save meta box data
      function save_banner_image_meta_data($post_id) {
	if (isset($_POST['banner_image'])) {
	    update_post_meta($post_id, 'banner_image', sanitize_text_field($_POST['banner_image']));
	}
      }
      add_action('save_post', 'save_banner_image_meta_data');

      /**
 * Custom currency and currency symbol
 */
add_filter( 'woocommerce_currencies', 'add_my_currency' );

function add_my_currency( $currencies ) {
     $currencies['Lanka'] = __( 'LKR', 'woocommerce' );
     return $currencies;
}

add_filter('woocommerce_currency_symbol', 'add_my_currency_symbol', 10, 2);

function add_my_currency_symbol( $currency_symbol, $currency ) {
     switch( $currency ) {
          case 'Lanka': $currency_symbol = 'LKR '; break;
     }
     return $currency_symbol;
}
      

function custom_get_new_order_details($order_id) {
	// Get the order object
	$order = wc_get_order($order_id);
      
	// Get order details
	$order_number = $order->get_order_number();
	$order_status = $order->get_status();
	$order_total = $order->get_total();
      
	// Get customer details
	$customer_id = $order->get_customer_id();
	$customer = $order->get_user();
	$customer_name = $customer ? $customer->display_name : 'Guest';
      
	// Get billing details
	$billing_address = $order->get_formatted_billing_address();
	$billing_email = $order->get_billing_email();
	$billing_phone = $order->get_billing_phone();
      
	// Get shipping details
	$shipping_address = $order->get_formatted_shipping_address();
	$shipping_method = $order->get_shipping_method();
	
	// Additional order details if needed
	$payment_method_title = $order->get_payment_method_title();
	$currency = $order->get_currency();
      
	// Format the order details
	$order_details = "<strong>New Order Received:</strong><br>";
	$order_details .= "Order Number: $order_number<br>";
	$order_details .= "Status: $order_status<br>";
	$order_details .= "Total: $order_total<br>";
	$order_details .= "Customer ID: $customer_id<br>";
	$order_details .= "Customer Name: $customer_name<br>";
	$order_details .= "Billing Address: $billing_address<br>";
	$order_details .= "Billing Email: $billing_email<br>";
	$order_details .= "Billing Phone: $billing_phone<br>";
	$order_details .= "Shipping Address: $shipping_address<br>";
	$order_details .= "Shipping Method: $shipping_method<br>";
	$order_details .= "Payment Method: $payment_method_title<br>";
	$order_details .= "Currency: $currency<br>";
      
	// Return the formatted order details
	return $order_details;
      }
      


// Register shortcode to display order details
add_shortcode('display_new_order_details', 'custom_display_new_order_details');

function custom_display_new_order_details() {
    // Call the function to retrieve order details
    $order_details = custom_get_new_order_details();

    // Output the order details
    echo '<div class="new-order-details">' . $order_details . '</div>';
}

// Custom login form
function custom_login_form() {
	?>
	<form id="login-form" action="<?php echo esc_url( wp_login_url() ); ?>" method="post">
	    <div class="form-group">
	        <label for="username">Username or Email</label>
	        <input type="text" class="form-control" id="username" name="log" required>
	    </div>
	    <div class="form-group">
	        <label for="password">Password</label>
	        <input type="password" class="form-control" id="password" name="pwd" required>
	    </div>
	    <button type="submit" class="btn btn-primary">Login</button>
	    <input type="hidden" name="redirect_to" value="<?php echo esc_url( home_url() ); ?>">
	</form>
	<?php
      }
      
      // Custom registration form
      function custom_registration_form() {
	?>
	<form id="registration-form" action="<?php echo esc_url( wp_registration_url() ); ?>" method="post">
	    <div class="form-group">
	        <label for="username">Username</label>
	        <input type="text" class="form-control" id="username" name="username" required>
	    </div>
	    <div class="form-group">
	        <label for="email">Email</label>
	        <input type="email" class="form-control" id="email" name="email" required>
	    </div>
	    <div class="form-group">
	        <label for="password">Password</label>
	        <input type="password" class="form-control" id="password" name="password" required>
	    </div>
	    <button type="submit" class="btn btn-primary">Register</button>
	</form>
	<?php
      }
      
      // Custom registration logic
      function custom_user_registration() {
	if ( isset( $_POST['username'] ) && isset( $_POST['email'] ) && isset( $_POST['password'] ) ) {
	    $username = sanitize_user( $_POST['username'] );
	    $email = sanitize_email( $_POST['email'] );
	    $password = $_POST['password'];
      
	    $user_id = wp_create_user( $username, $password, $email );
      
	    if ( ! is_wp_error( $user_id ) ) {
	        // Registration successful, you can perform further actions here
	        // For example, you can log the user in automatically
	        $credentials = array(
		  'user_login'    => $username,
		  'user_password' => $password,
		  'remember'      => true,
	        );
      
	        $user = wp_signon( $credentials, false );
      
	        if ( ! is_wp_error( $user ) ) {
		  // Redirect the user after successful login
		  wp_redirect( home_url() );
		  exit;
	        }
	    }
	}
      }
      
      // Hook custom registration logic to user_register action
      add_action( 'user_register', 'custom_user_registration' );
      
      // Customize authentication to allow login via email or username
      function custom_allow_email_login( $user, $username, $password ) {
	if ( ! $user && is_email( $username ) ) {
	    $user = get_user_by( 'email', $username );
	    if ( $user ) {
	        $username = $user->user_login;
	    }
	}
	return wp_authenticate_username_password( null, $username, $password );
      }
      
      add_filter( 'authenticate', 'custom_allow_email_login', 20, 3 );

function add_custom_admin_tab( $items ) {
		if ( current_user_can( 'administrator' ) ) {
			$dashboard_key = array_search( 'dashboard', array_keys( $items ) );
			$new_items = array_slice( $items, 0, $dashboard_key + 1, true ) +
						 array( 'registered-user' => __( 'Users', 'text-domain' ) ) +
						 array_slice( $items, $dashboard_key + 1, null, true );
			return $new_items;
		}
		return $items;
	}
	add_filter( 'woocommerce_account_menu_items', 'add_custom_admin_tab' );


function custom_account_endpoint() {
    add_rewrite_endpoint( 'registered-user', EP_PAGES );
}
add_action( 'woocommerce_init', 'custom_account_endpoint' );


function custom_endpoint_redirect() {
    global $wp;

    if ( isset( $wp->query_vars['registered-user'] ) ) {
        $template = locate_template( 'woocommerce/myaccount/registered-user.php' );

        if ( $template ) {
            include( $template );
            exit;
        }
		else {
            wp_redirect( home_url() );
            exit;
        }
    }
}
add_action( 'template_redirect', 'custom_endpoint_redirect' );

function add_custom_admin_tab_order( $items ) {
    if ( current_user_can( 'administrator' ) ) {

		$dashboard_key = array_search( 'dashboard', array_keys( $items ) );
		$new_order_count = count( get_user_meta( get_current_user_id(), 'new_order_ids', true ) );
			$new_items = array_slice( $items, 0, $dashboard_key + 3, true ) +
						 array( 'new_orders' =>sprintf( __( 'New Orders (%d)', 'text-domain' ), $new_order_count ?? 0 )) +
						 array_slice( $items, $dashboard_key + 3, null, true );
			return $new_items;
    }
    return $items;
}
add_filter( 'woocommerce_account_menu_items', 'add_custom_admin_tab_order', 10 );

function custom_account_endpoint_order() {
    add_rewrite_endpoint( 'new_orders', EP_PAGES );
}
add_action( 'woocommerce_init', 'custom_account_endpoint_order' );

function custom_endpoint_order_redirect() {
    global $wp;
    if ( isset( $wp->query_vars['new_orders'] ) ) {
        $template = locate_template( 'woocommerce/myaccount/order-received.php' );

        if ( $template ) {
            include( $template );
            exit;
        }
		else {
            wp_redirect( home_url() );
            exit;
        }
    }
}
add_action( 'template_redirect', 'custom_endpoint_order_redirect' );


function update_new_order_ids( $order_id ) {
    $admin_id = get_users( array( 'role' => 'administrator' ) )[0]->ID; // Assuming there's only one admin
    $new_order_ids = get_user_meta( $admin_id, 'new_order_ids', true ) ?: array();
    $new_order_ids[] = $order_id;
    update_user_meta( $admin_id, 'new_order_ids', $new_order_ids );
}
add_action( 'woocommerce_checkout_order_processed', 'update_new_order_ids' );

function enqueue_sweetalert() {
    
    wp_enqueue_style( 'sweetalert', 'https://cdn.jsdelivr.net/npm/sweetalert2@10/dist/sweetalert2.min.css' );
    
    
    wp_enqueue_script( 'sweetalert', 'https://cdn.jsdelivr.net/npm/sweetalert2@10/dist/sweetalert2.min.js', array(), '', true );
}
add_action( 'wp_enqueue_scripts', 'enqueue_sweetalert' );


add_shortcode( 'newsletter_subscription_form', 'render_newsletter_subscription_form' );

function render_newsletter_subscription_form() {
    ob_start();
    ?>
    <div class="newsletter-subscription-form">
        <form id="newsletter-subscription-form" method="post" action="#newsletter-subscription-form">
            <div class="form-group">
                <input type="tel" class="form-control" id="mobile" name="mobile" placeholder="Your Mobile Number" required>
                <p class="error-message" style="display: none;"></p>
            </div>
            <?php wp_nonce_field( 'subscribe_nonce', 'subscribe_nonce' ); ?>
            <button type="submit" class="btn btn-primary">Subscribe</button>
        </form>
    </div>

    <script>
    jQuery(function($) {
        $('#newsletter-subscription-form').submit(function(event) {
            event.preventDefault(); 

            var mobile = $('#mobile').val();

            
            if (mobile.length !== 10 || !/^\d+$/.test(mobile)) {
                // $('.error-message').text('Please enter a valid 10-digit mobile number.').show();
				Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'Please enter a valid 10-digit mobile number.'
                        });
                return;
            }

            
            $.ajax({
                type: 'POST',
                url: '<?php echo esc_js(admin_url('admin-ajax.php')); ?>',
                data: {
                    action: 'process_newsletter_subscription',
                    mobile: mobile,
                    subscribe_nonce: '<?php echo esc_js(wp_create_nonce('subscribe_nonce')); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: 'You have subscribed successfully!'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                
                                location.reload();
                            }
                        });
                    } else {

                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: response.data.message
                        });
                    }
                }
            });
        });
    });
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode( 'newsletter_subscription_form', 'render_newsletter_subscription_form' );


add_action('wp_ajax_process_newsletter_subscription', 'process_newsletter_subscription');
add_action('wp_ajax_nopriv_process_newsletter_subscription', 'process_newsletter_subscription');

function process_newsletter_subscription() {
    $response = array('success' => false, 'data' => array());

    
    if ( ! isset($_POST['subscribe_nonce']) || ! wp_verify_nonce($_POST['subscribe_nonce'], 'subscribe_nonce') ) {
        $response['data']['message'] = 'Nonce verification failed.';
        wp_send_json($response);
    }

    
    $mobile = isset($_POST['mobile']) ? sanitize_text_field($_POST['mobile']) : '';
    if (strlen($mobile) !== 10 || !ctype_digit($mobile)) {
        $response['data']['message'] = 'Please enter a valid 10-digit mobile number.';
        wp_send_json($response);
    }

   
    global $wpdb;
    $table_name = $wpdb->prefix . 'newsletter_subscriptions';
    $existing_mobile = $wpdb->get_var($wpdb->prepare("SELECT mobile FROM $table_name WHERE mobile = %s", $mobile));
    if ($existing_mobile) {
        $response['data']['message'] = 'This mobile number is already subscribed.';
        wp_send_json($response);
    }

   
    $wpdb->insert($table_name, array('mobile' => $mobile));

    
    $response['success'] = true;
    wp_send_json($response);
}

add_filter('woocommerce_process_registration_errors','custom_validate_billing_phone',10, 4 );
add_action('woocommerce_after_save_address_validation','custom_validate_billing_phone',1,2);
add_action('woocommerce_checkout_process', 'custom_validate_billing_phone');

function custom_validate_billing_phone() {
    $is_correct = preg_match('/^[0-9]{10}$/', $_POST['billing_phone']);
    
    if ( $_POST['billing_phone'] && !$is_correct ) {
        wc_add_notice( __( 'The Phone field should be <strong>start 05 and 10 digits</strong>.' ), 'error' );
    }
}


// Function to send SMS notification (replace placeholders with actual credentials and API call)
function send_sms( $mobile, $message ) {
    $api_username = 'your_api_username';
    $api_password = 'your_api_password';
    $api_endpoint = 'https://api.smsprovider.com/send_sms';

    $params = array(
        'username' => $api_username,
        'password' => $api_password,
        'mobile' => $mobile,
        'message' => $message
    );

    // Send SMS using cURL or another method
    // Example:
    $response = wp_remote_post( $api_endpoint, array(
        'body' => $params
    ) );

    // Handle response
    if ( ! is_wp_error( $response ) && $response['response']['code'] == 200 ) {
        // SMS sent successfully
    } else {
        // Error sending SMS
    }
}

