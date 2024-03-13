<?php
// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
    exit( 'Direct script access denied.' );
}

function la_luxsa_preset_main_07()
{
    return [
        [
            'key'               => 'header_layout',
            'value'             => 'luxsa-header-09'
        ],
        [
            'key'               => 'footer_layout',
            'value'             => 'luxsa-footer-03'
        ],
        [
            'key'               => 'header_transparency',
            'value'             => 'no'
        ],
        [
            'filter_name'       => 'luxsa/filter/get_option',
            'filter_func'       => function( $value, $key ) {
                if( $key == 'la_custom_css'){
                    $value .= '

';
                }
                return $value;
            },
            'filter_priority'   => 10,
            'filter_args'       => 2
        ],
    ];
}