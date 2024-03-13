<?php
// If this file is called directly, abort.

if ( ! defined( 'WPINC' ) ) {
    die;
}

add_filter('elementor/editor/localize_settings', function ($configs){
    $key = ['elementor_site', 'docs_elementor_site', 'help_the_content_url', 'help_right_click_url', 'help_flexbox_bc_url', 'elementPromotionURL', 'dynamicPromotionURL'];
    $key2 = ['help_preview_error_url', 'help_preview_http_error_url', 'help_preview_http_error_500_url', 'goProURL'];
    $tmp = [];
    if(is_array($configs)){
        foreach ($configs as $k => $v){
            if(in_array($k, $key)){
                $old_val = $configs[$k];
                $tmp[] = $old_val;
                $configs[$k] = str_replace(['elementor.com/pro', 'go.elementor.com'], ['la-studioweb.com/go/elementor-pro', 'la-studioweb.com/go/elementor'], $old_val);
	            $configs[$k] = 'https://la-studioweb.com/go/elementor-pro';
            }
            if( ($k == 'preview' || $k == 'icons') && is_array($v) ){
                foreach ($v as $k1 => $v1){
                    if(in_array($k1, $key2)){
                        $old_val2 = $v[$k1];
                        $tmp[] = $old_val2;
                        $v[$k1] = str_replace(['elementor.com/pro', 'go.elementor.com'], ['la-studioweb.com/go/elementor-pro', 'la-studioweb.com/go/elementor'], $old_val2);
	                    $v[$k1] = 'https://la-studioweb.com/go/elementor-pro';
                    }
                }
                $configs[$k] = $v;
            }
        }
    }
    if(!empty($configs['initial_document']['widgets'])){
        foreach ($configs['initial_document']['widgets'] as $widget => &$setting ) {
            if(isset($setting['help_url'])){
                $setting['help_url'] = 'https://elementor.com/help/'.str_replace('_', '-', $widget).'-widget/?ref=14171&campaign=la-studiowebdotcom';
	            $setting['help_url'] = 'https://la-studioweb.com/go/elementor-pro';
            }
        }
    }
    return $configs;
});

add_action('elementor/app/init', function (){
    add_action('wp_print_footer_scripts', function (){
        ?>
        <script type="text/javascript">
            (function($) {
                'use strict';
                function dpv_change_elementor_ref_url(){
                    $('a[href*="elementor.com"]').each(function () {
                        var _old = $(this).attr('href');
                        if(_old.indexOf('elementor.com/pro') >= 0 ){
                            $(this).attr('href', 'https://la-studioweb.com/go/elementor-pro');
                        }
                        else{
                            if(_old.indexOf('elementor.com/popup-builder') >= 0 ){
                                $(this).attr('href', 'https://la-studioweb.com/go/elementor/popup-builder');
                            }
                            else{
                                $(this).attr('href', _old.replace('go.elementor.com', 'la-studioweb.com/go/elementor'));
                                $(this).attr('href', 'https://la-studioweb.com/go/elementor-pro');
                            }
                        }
                    })
                }
                $(function(){
                    dpv_change_elementor_ref_url();
                });

            })(jQuery);
        </script>
        <?php
    }, 999);
});

add_action('admin_footer', function (){
    ?>
    <script type="text/javascript">
        (function($) {
            'use strict';
            function dpv_change_elementor_ref_url(){
                $('a[href*="elementor.com"]').each(function () {
                    var _old = $(this).attr('href');
                    if(_old.indexOf('elementor.com/pro') >= 0 ){
                        $(this).attr('href', 'https://la-studioweb.com/go/elementor-pro');
                    }
                    else{
                        if(_old.indexOf('elementor.com/popup-builder') >= 0 ){
                            $(this).attr('href', 'https://la-studioweb.com/go/elementor/popup-builder');
                        }
                        else{
                            $(this).attr('href', _old.replace('go.elementor.com', 'la-studioweb.com/go/elementor'));
                            $(this).attr('href', 'https://la-studioweb.com/go/elementor-pro');
                        }
                    }
                })
            }
            $(function(){
                dpv_change_elementor_ref_url();
            });

        })(jQuery);
    </script>
    <?php
}, 999);

add_filter('wp_redirect', function ( $location ){
    if( strpos($location, 'https://elementor.com/pro') !== false ){
        $location = 'https://la-studioweb.com/go/elementor-pro';
    }
    if( $location == 'https://go.elementor.com/docs-admin-menu/' ){
	    $location = 'https://la-studioweb.com/go/elementor-pro';
    }
    return $location;
}, 20);

add_action('elementor/editor/footer', function (){
    ?>
    <script type="text/javascript">
        var LaStudioScriptTemplateIds = ['#tmpl-elementor-panel-categories', '#tmpl-elementor-panel-global', '#tmpl-elementor-template-library-get-pro-button', '#elementor-preview-responsive-wrapper #elementor-notice-bar'];
        LaStudioScriptTemplateIds.forEach(function (id){
            var temp = document.querySelector(id);
            temp.innerHTML = temp.innerHTML.replace(/href="(.*?)"/gi, 'href="https://la-studioweb.com/go/elementor-pro"');
        });
    </script>
    <?php
}, 100);

function lastudio_grab_shutdown_handler_if_has_elementor_error() {
    $last_error = error_get_last();
    if (isset($last_error['type']) && $last_error['type'] == E_ERROR) {
        if(strpos($last_error['file'], 'wp-content/plugins/elementor/') !== false || strpos($last_error['file'], 'wp-content/plugins/lastudio/') !== false){
            update_option('lastudio_has_elementor_error', 'has_error');
        }
    }
}
register_shutdown_function ('lastudio_grab_shutdown_handler_if_has_elementor_error');

function lastudio_elementor_remove_error_status(){
    delete_option('lastudio_has_elementor_error');
}
add_action('lastudio_elementor_activate_hook', 'lastudio_elementor_remove_error_status');

function lastudio_admin_notices_if_has_elementor_error(){
    $has_error = get_option('lastudio_has_elementor_error', false);
    if(!empty($has_error)){
        $msg = sprintf('The latest version of %1$s is incompatible with %2$s plugin,<br/> Please downgrade %1$s plugin to the previous version, then reactivate %2$s plugin and wait for updated version of the theme', '<strong>Elementor</strong>', '<strong>LA-Studio Core</strong>');
        echo sprintf('<div class="%1$s"><p>%2$s</p></div>', 'notice notice-error la-has-elementor-error', $msg);
    }
}
add_action('admin_notices', 'lastudio_admin_notices_if_has_elementor_error');



if(!function_exists('lastudio_elements_fix_elementor_override_core_file')){
    function lastudio_elements_fix_elementor_override_core_file(){
        $msg = [];
        $elementor_path = WP_PLUGIN_DIR . '/elementor/';
        $target_path = LASTUDIO_PLUGIN_PATH . 'includes/extensions/elementor/override/';
	    if ( version_compare( ELEMENTOR_VERSION, '3.2.0', '<' ) ) {
		    $override = [
			    'core-files-css-base' => [
				    'source' => $elementor_path . 'core/files/css/base.php',
				    'target' => $target_path . 'core/files/css/base.php',
				    'find' => [
					    '->add_device( \'tablet\', $breakpoints[\'md\'] )',
					    '->add_device( \'desktop\', $breakpoints[\'lg\'] )'
				    ],
				    'replace' => [
					    '->add_device( \'tabletportrait\', $breakpoints[\'sm\'] )->add_device( \'tablet\', $breakpoints[\'md\'] )',
					    '->add_device( \'laptop\', $breakpoints[\'lg\'] )->add_device( \'desktop\', $breakpoints[\'xl\'] )'
				    ]
			    ],
			    'includes-base-controlsstack' => [
				    'source' => $elementor_path . 'includes/base/controls-stack.php',
				    'target' => $target_path . 'includes/base/controls-stack.php',
				    'find' => [
					    'const RESPONSIVE_MOBILE = \'mobile\';',
					    'self::RESPONSIVE_MOBILE,',
					    'unset( $control_args[\'mobile_default\'] );'
				    ],
				    'replace' => [
					    'const RESPONSIVE_MOBILE = \'mobile\';const RESPONSIVE_LAPTOP = \'laptop\';const RESPONSIVE_TABLET_PORTRAIT = \'tabletportrait\';',
					    'self::RESPONSIVE_MOBILE,self::RESPONSIVE_LAPTOP,self::RESPONSIVE_TABLET_PORTRAIT,',
					    'unset( $control_args[\'mobile_default\'] ); if(isset($control_args[\'laptop_default\'])){unset( $control_args[\'laptop_default\'] );}if(isset($control_args[\'tabletportrait_default\'])){unset( $control_args[\'tabletportrait_default\'] );}'
				    ]
			    ]
		    ];
	    }
	    else{
		    $override = [
			    'core-breakpoints-manager' => [
				    'source' => $elementor_path . 'core/breakpoints/manager.php',
				    'target' => $target_path . 'core/breakpoints/manager.php',
				    'find' => [
					    'const BREAKPOINT_KEY_WIDESCREEN = \'widescreen\';',
					    'self::BREAKPOINT_KEY_TABLET => [',
                        '767',
                        '1024',
                        '1620',
                        'self::BREAKPOINT_KEY_TABLET === $breakpoint_name'
				    ],
				    'replace' => [
					    'const BREAKPOINT_KEY_WIDESCREEN = \'widescreen\';const BREAKPOINT_KEY_TABLET_PORTRAIT = \'tabletportrait\';',
					    'self::BREAKPOINT_KEY_TABLET_PORTRAIT => [ \'label\' => __( \'Tablet Portrait\', \'elementor\' ), \'default_value\' => 991, \'direction\' => \'max\', ],self::BREAKPOINT_KEY_TABLET => [',
                        '575',
                        '1279',
                        '1699',
                        'self::BREAKPOINT_KEY_TABLET === $breakpoint_name || self::BREAKPOINT_KEY_LAPTOP === $breakpoint_name || self::BREAKPOINT_KEY_TABLET_PORTRAIT === $breakpoint_name'
				    ]
			    ],
                'core-files-css-base' => [
				    'source' => $elementor_path . 'core/files/css/base.php',
				    'target' => $target_path . 'core/files/css/base.php',
				    'find' => [
					    '\'_tablet\', \'_mobile\'',
				    ],
				    'replace' => [
					    '\'_tablet\', \'_mobile\', \'_laptop\', \'_tabletportrait\'',
				    ]
			    ],
			    'includes-base-controlsstack' => [
				    'source' => $elementor_path . 'includes/base/controls-stack.php',
				    'target' => $target_path . 'includes/base/controls-stack.php',
				    'find' => [
					    'const RESPONSIVE_MOBILE = \'mobile\';',
					    'self::RESPONSIVE_MOBILE,',
					    'unset( $control_args[\'mobile_default\'] );'
				    ],
				    'replace' => [
					    'const RESPONSIVE_MOBILE = \'mobile\';const RESPONSIVE_LAPTOP = \'laptop\';const RESPONSIVE_TABLET_PORTRAIT = \'tabletportrait\';',
					    'self::RESPONSIVE_MOBILE,self::RESPONSIVE_LAPTOP,self::RESPONSIVE_TABLET_PORTRAIT,',
					    'unset( $control_args[\'mobile_default\'] ); if(isset($control_args[\'laptop_default\'])){unset( $control_args[\'laptop_default\'] );}if(isset($control_args[\'tabletportrait_default\'])){unset( $control_args[\'tabletportrait_default\'] );}'
				    ]
			    ]
		    ];
        }

        global $wp_filesystem;
        if (empty($wp_filesystem)) {
            require_once(ABSPATH . '/wp-admin/includes/file.php');
            WP_Filesystem();
            if(!defined('FS_METHOD')){
                define('FS_METHOD', 'direct');
            }
        }
        foreach ($override as $k => $file){
            $status = false;
            if($wp_filesystem->exists($file['source'])){
                $file_content = $wp_filesystem->get_contents($file['source']);
                $new_content = str_replace( $file['find'], $file['replace'], $file_content );
                if($wp_filesystem->put_contents($file['target'], $new_content)){
                    $status = true;
                }
            }
            $msg[$k] = $status;
        }

        $result = array_filter( $msg, function ( $val ) {
            return !$val;
        } );

        if(count($result) > 0){
            return false;
        }
        else{
            return true;
        }
    }
}

add_action('admin_menu', function (){
    add_submenu_page(
        'tools.php',
        esc_html__('LaStudio Elements', 'lastudio'),
        esc_html__('LaStudio Elements', 'lastudio'),
        'manage_options',
        'fix-lastudio-elements',
        'lastudio_elements_admin_menu_fix_cb'
    );
});

if(!function_exists('lastudio_elements_admin_menu_fix_cb')){
    function lastudio_elements_admin_menu_fix_cb(){
        ?>
        <div class="wrap">
            <br/>
            <h1><?php esc_html_e('Fix LaStudio Elements', 'lastudio') ?></h1>
            <br/>
            <?php
            if(isset($_POST['lasf_fix_elem'])){
                $nonce = isset($_POST['_wpnonce']) ? $_POST['_wpnonce'] : '';
                if(wp_verify_nonce($nonce, 'lastudio_elements_fix')){
                    $result = lastudio_elements_fix_elementor_override_core_file();
                    if($result){
                        do_action('lastudio_elementor_recreate_editor_file');
                        echo sprintf('<p>%s</p>', __('All done!, please re-active `LaStudio Core` plugin', 'lastudio'));
                    }
                    else{
                        $msg = __('An error has occurred please contact support %s', 'lastudio');
                        echo '<p>'. sprintf($msg, '<a href="https://support.la-studioweb.com/" target="_blank">https://support.la-studioweb.com/</a>') .'</p>';
                    }
                }
                else{
                    echo sprintf('<p>%s</p>', __('Invalid Nonce', 'lastudio'));
                }
            }
            else{
                ?>
                <form method="post">
                    <button name="lasf_fix_elem" value="yes" id="lasf_fix_elem" class="button button-primary" type="submit"><?php esc_html_e('Fix now!', 'lastudio') ?></button>
                    <?php wp_nonce_field('lastudio_elements_fix'); ?>
                </form>
                <?php
            }
            ?>
        </div>
        <?php
    }
}