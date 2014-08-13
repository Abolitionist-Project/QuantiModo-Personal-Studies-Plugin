<?php
/*
Plugin Name: Quantimodo Studies
Description: This plugin creates Studies as a post types and render's its components
Version: 1.0
Author: Quantimodo
Domain Path: /lang/
*/

if ( !defined( 'ABSPATH' ) ) exit;
/*  Copyright 2013 QuantiModo  (email: quantimodo@gmail.com)

    This is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This plugin is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Relevanssi.  If not, see <http://www.gnu.org/licenses/>.
*/

if( !defined('qm_PLUGIN_URL')){
    define('qm_PLUGIN_URL',plugins_url());
}

/*====== BEGIN VSLIDER======*/

include_once('custom-post-types.php');
include_once('errorhandle.php');
include_once('metaboxes/meta_box_studies.php');
include_once('metaboxes/library/qm-editor.php');
include_once('custom_meta_boxes_studies.php');
include_once('loader.php');

/*====== INSTALLATION HOOKs ======*/        

register_activation_hook(__FILE__,'register_qmenu');
register_activation_hook(__FILE__,'flush_rewrite_rules', 20);

if(!function_exists('animation_effects')){
    function animation_effects(){
        $animate=array(
                        ''=>'none',
                        'animate cssanim flash'=> 'Flash',
                        'animate zoom' => 'Zoom',
                        'animate scale' => 'Scale',
                        'animate slide' => 'Slide (Height)', 
                        'animate expand' => 'Expand (Width)',
                        'animate cssanim shake'=> 'Shake',
                        'animate cssanim bounce'=> 'Bounce',
                        'animate cssanim tada'=> 'Tada',
                        'animate cssanim swing'=> 'Swing',
                        'animate cssanim wobble'=> 'Flash',
                        'animate cssanim wiggle'=> 'Flash',
                        'animate cssanim pulse'=> 'Flash',
                        'animate cssanim flip'=> 'Flash',
                        'animate cssanim flipInX'=> 'Flip Left',
                        'animate cssanim flipInY'=> 'Flip Top',
                        'animate cssanim fadeIn'=> 'Fade',
                        'animate cssanim fadeInUp'=> 'Fade Up',
                        'animate cssanim fadeInDown'=> 'Fade Down',
                        'animate cssanim fadeInLeft'=> 'Fade Left',
                        'animate cssanim fadeInRight'=> 'Fade Right',
                        'animate cssanim fadeInUptBig'=> 'Fade Big Up',
                        'animate cssanim fadeInDownBig'=> 'Fade Big Down',
                        'animate cssanim fadeInLeftBig'=> 'Fade Big Left',
                        'animate cssanim fadeInRightBig'=> 'Fade Big Right',
                        'animate cssanim bounceInUp'=> 'Bounce Up',
                        'animate cssanim bounceInDown'=> 'Bounce Down',
                        'animate cssanim bounceInLeft'=> 'Bounce Left',
                        'animate cssanim bounceInRight'=> 'Bounce Right',
                        'animate cssanim rotateIn'=> 'Rotate',
                        'animate cssanim rotateInUpLeft'=> 'Rotate Up Left',
                        'animate cssanim rotateInUpRight'=> 'Rotate Up Right',
                        'animate cssanim rotateInDownLeft'=> 'Rotate Down Left',
                        'animate cssanim rotateInDownRight'=> 'Rotate Down Right',
                        'animate cssanim speedIn'=> 'Speed In',
                        'animate cssanim rollIn'=> 'Roll In',
                        'animate ltr'=> 'Left To Right',
                        'animate rtl' => 'Right to Left', 
                        'animate btt' => 'Bottom to Top',
                        'animate ttb'=>'Top to Bottom',
                        'animate smallspin'=> 'Small Spin',
                        'animate spin'=> 'Infinite Spin'
                        );
    return $animate;
    }
}

if ( file_exists( dirname( __FILE__ ) . '/languages/' . get_locale() . '.mo' ) ){
    load_textdomain( 'qm', dirname( __FILE__ ) . '/languages/' . get_locale() . '.mo' );
}





?>
