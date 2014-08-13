<?php
/**
 * FILE: custom-post-types.php 
 */

if ( !defined( 'ABSPATH' ) ) exit;

add_action( 'admin_menu', 'register_qmenu_menu_page' );

function register_qmenu_menu_page(){
    add_menu_page( 'QuantiPress', 'QuantiModo', 'edit_posts', 'qmenu', 'qm_qmenu_dashboard',plugins_url( 'qm-study-module/images/qm-icon.png' ),15 );
    //admin.php?page=qmenu
   // add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function )
}

/*== PORTFOLIO == */
if(!function_exists('register_qmenu')){
function register_qmenu() {
register_post_type( 'study',
		array(
			'labels' => array(
				'name' => 'Studies',
				'menu_name' => 'Studies',
				'singular_name' => 'Study',
				'add_new_item' => 'Add New Study',
				'all_items' => 'All Studies'
			),
			'public' => true,
			'publicly_queryable' => true,
			'show_ui' => true,
			'capapbility_type' => 'post',
            'has_archive' => true,
			'show_in_menu' => 'qmenu',
			'show_in_admin_bar' => true,
			'show_in_nav_menus' => true,
			'taxonomies' => array( 'study-cat'),
			'supports' => array( 'title','editor','thumbnail','author','comments','excerpt','revisions','custom-fields'),
			'hierarchical' => true,
            'show_in_nav_menus' => true,
			'rewrite' => array( 'slug' => 'study', 'hierarchical' => true, 'with_front' => false )
		)
	);

   register_taxonomy( 'study-cat', array( 'study'),
		array(
			'labels' => array(
				'name' => 'Category',
				'menu_name' => 'Category',
				'singular_name' => 'Category',
				'add_new_item' => 'Add New Category',
				'all_items' => 'All Categories'
			),
			'public' => true,
			'hierarchical' => true,
			'show_ui' => true,
			'show_in_menu' => 'qmenu',
			'show_admin_column' => true,
            'query_var' => 'study-cat',           
			'show_in_nav_menus' => true,
			'rewrite' => array( 'slug' => 'study-cat', 'hierarchical' => true, 'with_front' => false ),
		)
	);
}

}
add_action( 'init', 'register_qmenu',5 );
?>