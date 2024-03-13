<?php
// If this file is called directly, abort.

if ( ! defined( 'WPINC' ) ) {
    die;
}


function lastudio_elementor_recreate_editor_file( ){
	global $wp_filesystem;
	if (empty($wp_filesystem)) {
		require_once(ABSPATH . '/wp-admin/includes/file.php');
		WP_Filesystem();
		if(!defined('FS_METHOD')){
			define('FS_METHOD', 'direct');
		}
	}

	$wp_upload_dir = wp_upload_dir( null, false );
	$target_source_folder = $wp_upload_dir['basedir'] . '/elementor/';
	$target_source_file = $target_source_folder . '/editor.min.js';
	$remote_source_file = plugin_dir_path(LASTUDIO_PLUGIN_PATH) . 'elementor/assets/js/editor.min.js';

	if($wp_filesystem->exists($remote_source_file)){
		$file_content = $wp_filesystem->get_contents($remote_source_file);

		if ( version_compare( ELEMENTOR_VERSION, '3.2.0', '<' ) ) {
			$string_search = array(
				'["desktop","tablet","mobile"]',
				'$e.routes.saveState("library"),(0,u.default)((0,s.default)(_default.prototype),"activateTab",this).call(this,e)',
				'defaultTabs(){return{"templates/blocks":',
			);
			$string_replace = array(
				'["desktop","laptop","tablet","tabletportrait","mobile"]',
				'$e.routes.saveState("library"),(0,u.default)((0,s.default)(_default.prototype),"activateTab",this).call(this,e),jQuery(document).trigger("LaStudio/Elementor/TemplateLibraryActiveTab", [e])',
				'defaultTabs(){return{"templates/lastudio":{title:"Demos",filter:{source:"lastudio"}},"templates/blocks":',
			);
			$new_content = str_replace($string_search, $string_replace, $file_content);
			$new_content2 = preg_replace(
				'/stylesheet\.addDevice\((.*)\)},addStyleRules/',
				'stylesheet.addDevice("mobile",0).addDevice("tabletportrait",elementorFrontend.config.breakpoints.sm).addDevice("tablet",elementorFrontend.config.breakpoints.md).addDevice("laptop",elementorFrontend.config.breakpoints.lg).addDevice("desktop",elementorFrontend.config.breakpoints.xl)},addStyleRules',
				$new_content
			);
			if(empty($new_content2)){
				$tmp1 = explode('stylesheet.addDevice', $new_content);
				$tmp2 = explode('},addStyleRules', $new_content);
				$new_content = $tmp1[0] . 'stylesheet.addDevice("mobile",0).addDevice("tabletportrait",elementorFrontend.config.breakpoints.sm).addDevice("tablet",elementorFrontend.config.breakpoints.md).addDevice("laptop",elementorFrontend.config.breakpoints.lg).addDevice("desktop",elementorFrontend.config.breakpoints.xl)},addStyleRules' . $tmp2[1];
			}
			else{
				$new_content = $new_content2;
			}
		}
		else{
			$string_search = array(
				'["desktop","tablet","mobile"]',
				'$e.routes.saveState("library")',
				'defaultTabs(){return{"templates/blocks":',
				'(_tablet|_mobile)',
			);
			$string_replace = array(
				'["desktop","laptop","tablet","tabletportrait","mobile"]',
				'$e.routes.saveState("library"),jQuery(document).trigger("LaStudio/Elementor/TemplateLibraryActiveTab")',
				'defaultTabs(){return{"templates/lastudio":{title:"Demos",filter:{source:"lastudio"}},"templates/blocks":',
				'(_tablet|_mobile|_laptop|_tabletportrait)',
			);
			$new_content = str_replace($string_search, $string_replace, $file_content);
        }
		if(!$wp_filesystem->is_dir($target_source_folder)){
			if(! wp_mkdir_p( $target_source_folder )){
				return new WP_Error( 'lastudio_elementor.cannot_put_contents', __( 'Cannot put contents', 'lastudio' ) );
			}
		}
		if($wp_filesystem->put_contents($target_source_file, $new_content)){
			update_option('lastudio-has-override-elementor-editor-js', true);
			return true;
		}
		else{
			update_option('lastudio-has-override-elementor-editor-js', false);
			return new WP_Error( 'lastudio_elementor.cannot_put_contents', __( 'Cannot put contents', 'lastudio' ) );
		}
	}
	else{
		return new WP_Error( 'lastudio_elementor.resource_not_exists', __( 'Resource does not exist', 'lastudio' ) );
	}
}
add_action('lastudio_elementor_activate_hook', 'lastudio_elementor_recreate_editor_file');
add_action('elementor/core/files/clear_cache', function (){
	lastudio_elementor_recreate_editor_file();
});

function lastudio_elementor_recreate_editor_file_when_updating( $upgrader_object, $options ){
	$target_plugin = 'elementor/elementor.php';
	if( $options['action'] == 'update' && $options['type'] == 'plugin' && isset( $options['plugins'] ) ) {
		foreach( $options['plugins'] as $plugin ) {
			if( $plugin == $target_plugin ) {
				lastudio_elementor_recreate_editor_file();
			}
		}
	}
}
add_action( 'upgrader_process_complete', 'lastudio_elementor_recreate_editor_file_when_updating', 10, 2 );

function lastudio_elementor_override_editor_before_enqueue_scripts( $src, $handler ){
	if($handler == 'elementor-editor'){
		$wp_upload_dir = wp_upload_dir( null, false );
		return $wp_upload_dir['baseurl'] . '/elementor/editor.min.js';
	}
	return $src;
}
add_action('script_loader_src', 'lastudio_elementor_override_editor_before_enqueue_scripts', 10, 2);

add_action('elementor/core/files/clear_cache', function (){
	$key = 'lastudio-gmap-style-' . LASTUDIO_VERSION;
	delete_transient($key);
});

function lastudio_elementor_override_editor_wp_head(){
    ?>
    <script type="text/template" id="tmpl-elementor-control-responsive-switchers">
        <div class="elementor-control-responsive-switchers">
            <#
            var devices = responsive.devices || [ 'desktop', 'laptop', 'tablet', 'tabletportrait', 'mobile' ];
            _.each( devices, function( device ) { #>
            <a class="elementor-responsive-switcher elementor-responsive-switcher-{{ device }}" data-device="{{ device }}">
                <i class="eicon-device-{{ device }}"></i>
            </a>
            <# } );
            #>
        </div>
    </script>
    <?php
}
add_action('elementor/editor/wp_head', 'lastudio_elementor_override_editor_wp_head', 0);


function lastudio_elementor_get_widgets_black_list( $black_list ){
    $new_black_list = array(
        'WP_Widget_Calendar',
        'WP_Widget_Pages',
        'WP_Widget_Archives',
        'WP_Widget_Media_Audio',
        'WP_Widget_Media_Image',
        'WP_Widget_Media_Gallery',
        'WP_Widget_Media_Video',
        'WP_Widget_Meta',
        'WP_Widget_Text',
        'WP_Widget_RSS',
        'WP_Widget_Custom_HTML',
        'RevSliderWidget',
        'LaStudio_Widget_Recent_Posts',
        //        'LaStudio_Widget_Product_Sort_By',
        //        'LaStudio_Widget_Price_Filter_List',
        //        'LaStudio_Widget_Product_Tag',
        //        'WP_Widget_Recent_Posts',
        //        'WP_Widget_Recent_Comments',
        //        'WC_Widget_Cart',
        //        'WC_Widget_Layered_Nav_Filters',
        //        'WC_Widget_Layered_Nav',
        //        'WC_Widget_Price_Filter',
        //        'WC_Widget_Product_Search',
        //        'WC_Widget_Product_Tag_Cloud',
        //        'WC_Widget_Products',
        //        'WC_Widget_Recently_Viewed',
        //        'WC_Widget_Top_Rated_Products',
        //        'WC_Widget_Recent_Reviews',
        //        'WC_Widget_Rating_Filter'
    );

    $new_black_list = array_merge($black_list, $new_black_list);
    return $new_black_list;
}
add_filter('elementor/widgets/black_list', 'lastudio_elementor_get_widgets_black_list', 20);

function lastudio_elementor_backend_enqueue_scripts(){
    wp_enqueue_script(
        'lastudio-elementor-backend',
        LASTUDIO_PLUGIN_URL . 'public/element/js/editor-backend.js' ,
        ['jquery'],
        LASTUDIO_VERSION,
        true
    );
    $breakpoints = [
        'laptop' => [
            'name' => __( 'Laptop', 'lastudio' ),
            'text' => __( 'Preview for 1366px', 'lastudio' )
        ],
        'tablet' => [
            'name' => __( 'Tablet Landscape', 'lastudio' ),
            'text' => __( 'Preview for 1024px', 'lastudio' )
        ],
        'tabletportrait' => [
            'name' => __( 'Tablet Portrait', 'lastudio' ),
            'text' => __( 'Preview for 768px', 'lastudio' )
        ]
    ];
    if(la_is_local()){
        $breakpoints = [
            'laptop1' => [
                'name' => __( 'Laptop 1', 'lastudio' ),
                'text' => __( 'Preview for 1680px', 'lastudio' )
            ],
            'laptop2' => [
                'name' => __( 'Laptop 2', 'lastudio' ),
                'text' => __( 'Preview for 1440px', 'lastudio' )
            ],
            'laptop' => [
                'name' => __( 'Laptop', 'lastudio' ),
                'text' => __( 'Preview for 1366px', 'lastudio' )
            ],
            'tablet' => [
                'name' => __( 'Tablet Landscape', 'lastudio' ),
                'text' => __( 'Preview for 1024px', 'lastudio' )
            ],
            'tabletportrait' => [
                'name' => __( 'Tablet Portrait', 'lastudio' ),
                'text' => __( 'Preview for 768px', 'lastudio' )
            ]
        ];
    }
    wp_localize_script('lastudio-elementor-backend', 'LaCustomBPFE', $breakpoints);
}
add_action( 'elementor/editor/before_enqueue_scripts', 'lastudio_elementor_backend_enqueue_scripts');