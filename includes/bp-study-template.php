<?php

/**
 * In this file you should define template tag functions that end users can add to their template
 * files.
 *
 * It's a general practice in WordPress that template tag functions have two versions, one that
 * returns the requested value, and one that echoes the value of the first function. The naming
 * convention is usually something like 'bp_study_get_item_name()' for the function that returns
 * the value, and 'bp_study_item_name()' for the function that echoes.
 */

/**
 * If you want to go a step further, you can create your own custom WordPress loop for your component.
 * By doing this you could output a number of items within a loop, just as you would output a number
 * of blog posts within a standard WordPress loop.
 *
 * The study template class below would allow you do the following in the template file:
 *
 * 	<?php if ( bp_get_study_has_items() ) : ?>
 *
 *		<?php while ( bp_get_study_items() ) : bp_get_study_the_item(); ?>
 *
 *			<p><?php bp_get_study_item_name() ?></p>
 *
 *		<?php endwhile; ?>
 *
 *	<?php else : ?>
 *
 *		<p class="error">No items!</p>
 *
 *	<?php endif; ?>
 *
 * Obviously, you'd want to be more specific than the word 'item'.
 *
 * In our study here, we've used a custom post type for storing and fetching our content. Though
 * the custom post type method is recommended, you can also create custom database tables for this
 * purpose. See bp-study-classes.php for more details.
 *
 */




function bp_study_has_items( $args = '' ) {
	global $bp, $items_template;

	// This keeps us from firing the query more than once
	if ( empty( $items_template ) ) {
		/***
		 * This function should accept arguments passes as a string, just the same
		 * way a 'query_posts()' call accepts parameters.
		 * At a minimum you should accept 'per_page' and 'max' parameters to determine
		 * the number of items to show per page, and the total number to return.
		 *
		 * e.g. bp_get_study_has_items( 'per_page=10&max=50' );
		 */

		/***
		 * Set the defaults for the parameters you are accepting via the "bp_get_study_has_items()"
		 * function call
		 */
		$defaults = array(
			'id' => 0,
			'date' 	=> date( 'Y-m-d H:i:s' ),
			'user' => 0,
			'slug' => '',
			'search_terms'    => '',
			'meta_query'      => '',
			'order'           => 'DESC',
			'orderby'         => '',
			'paged'            => 1,
			'per_page'        => 2,
		);

		$slug    = false;
		$type    = '';
		$user_id = 0;
		$order   = 'DESC';


		

		// Type
		// @todo What is $order? At some point it was removed incompletely?
		if ( bp_is_current_action( BP_STUDY_SLUG ) ) {
			if ( 'most-popular' == $order ) {
				$type = 'popular';
			} elseif ( 'alphabetically' == $order ) {
				$type = 'alphabetical';
			}
		} elseif ( isset( $bp->study->current_study->slug ) && $bp->study->current_study->slug ) {
			$type = 'single-study';
			$slug = $bp->study->current_study->slug;
		}

		/***
		 * This function will extract all the parameters passed in the string, and turn them into
		 * proper variables you can use in the code - $per_page, $max
		 */
		$r = wp_parse_args( $args, $defaults );
		extract( $r, EXTR_SKIP );

		if ( empty( $r['search_terms'] ) ) {
			if ( isset( $_REQUEST['study-filter-box'] ) && !empty( $_REQUEST['study-filter-box'] ) )
				$r['search_terms'] = $_REQUEST['study-filter-box'];
			elseif ( isset( $_REQUEST['s'] ) && !empty( $_REQUEST['s'] ))
				$r['search_terms'] = $_REQUEST['s'];
			else
				$r['search_terms'] = '';
		}

		
		if(isset( $_REQUEST['items_page'] ) && !empty( $_REQUEST['items_page'])){
			$r['paged'] = $_REQUEST['items_page'];
		}

		if(is_single()){
			$r['id']=get_the_ID();
		}


		// User filtering
		if ( bp_displayed_user_id() )
			$user_id = bp_displayed_user_id();

		$items_template = new BP_STUDY();
   		
		$items_template->get( $r );
	}

	return $items_template->have_posts();
}

function bp_study_the_item() {
	global $items_template;
	return $items_template->query->the_post();
}

function bp_study_item_name() {
	echo bp_study_get_item_name();
}
/* Always provide a "get" function for each template tag, that will return, not echo. */
function bp_study_get_item_name() {
	global $items_template;
	echo apply_filters( 'bp_study_get_item_name', $items_template->item->name ); // study: $items_template->item->name;
}

function bp_study_name(){
	echo bp_study_get_name();
}

function bp_study_get_name(){
	return get_the_title();
}


function bp_study_type(){
	echo bp_study_get_type();
}

function bp_study_get_type(){
	global $post;
	$cats=get_the_terms( get_the_ID(), 'study-cat' );

	$cats_string='';
	if(isset($cats) && is_array($cats)){
		foreach($cats as $cat){
			$cats_string .='<a href="'.get_term_link( $cat->slug, 'study-cat' ).'">'.$cat->name.'</a>, ';
		}
	}
	return $cats_string;
}




function bp_study_description(){
	echo bp_study_desc();
}

/**
 * Echo "Viewing x of y pages"
 *
 * @package BuddyPress_Study_Component
 * @since 1.6
 */
function bp_study_pagination_count() {
	echo bp_study_get_pagination_count();
}
	/**
	 * Return "Viewing x of y pages"
	 *
	 * @package BuddyPress_Study_Component
	 * @since 1.6
	 */
	function bp_study_get_pagination_count() {
		global $items_template;

		$pagination_count = sprintf( __( 'Viewing page %1$s of %2$s', 'qm' ), $items_template->query->query_vars['paged'], $items_template->query->max_num_pages );

		return apply_filters( 'bp_study_get_pagination_count', $pagination_count );
	}

/**
 * Echo pagination links
 *
 * @package BuddyPress_Study_Component
 * @since 1.6
 */
function bp_study_item_pagination() {
	echo bp_study_get_item_pagination();
}
	/**
	 * return pagination links
	 *
	 * @package BuddyPress_Study_Component
	 * @since 1.6
	 */
function bp_study_get_item_pagination() {
	global $items_template;
	return apply_filters( 'bp_study_get_item_pagination', $items_template->pag_links );
}

/**
 *
 * @package BuddyPress_Study_Component
 * @since 1.6
 */
function bp_study_avatar( $args = array() ) {
	echo bp_study_get_avatar( $args );
}

	/**
	 *
	 * @package BuddyPress_Study_Component
	 * @since 1.6
	 *
	 * @param mixed $args Accepts WP style arguments - either a string of URL params, or an array
	 * @return str The HTML for a user avatar
	 */
	function bp_study_get_avatar( $args = array() ) {

		$defaults = array(
		'id' => get_the_ID(),
		'size'  => 'full'
		);

		$r = wp_parse_args( $args, $defaults );
		extract( $r, EXTR_SKIP );
		$thumb='<a href="'.get_permalink($id).'" title="'.the_title_attribute('echo=0').'">'.get_the_post_thumbnail($id,$size).'</a>';

		return $thumb;
	}


function bp_study_instructor_avatar( $args = array() ) {
	echo bp_study_get_instructor_avatar( $args );
}

/**
 *
 * @package BuddyPress_Skeleton_Component
 * @since 1.6
 *
 * @param mixed $args Accepts WP style arguments - either a string of URL params, or an array
 * @return str The HTML for a user avatar
 */
function bp_study_get_instructor_avatar( $args = array() ) {
	$defaults = array(
		'item_id' => get_the_author_meta( 'ID' ),
		'object'  => 'user'
	);

	$r = wp_parse_args( $args, $defaults );

	return apply_filters('quantipress_display_study_instructor_avatar',bp_core_fetch_avatar( $r ),get_the_ID());
}


function bp_study_instructor( $args = array() ) {
	echo bp_study_get_instructor( $args );
}

		

/**
 *
 * @package BuddyPress_Skeleton_Component
 * @since 1.6
 *
 * @param mixed $args Accepts WP style arguments - either a string of URL params, or an array
 * @return str The HTML for a user avatar
 */
function bp_study_get_instructor($args=NULL) {

	$defaults = array(
		'instructor_id' => get_the_author_meta( 'ID' ),
		'field' => 'Expertise'
	);

	$r = wp_parse_args( $args, $defaults );
	extract( $r, EXTR_SKIP );

	
	if(function_exists('qm_get_option'))
		$field = qm_get_option('instructor_field');
	

	$displayname = bp_core_get_user_displayname($instructor_id);

	$special = bp_get_profile_field_data('field='.$field.'&user_id='.$instructor_id);
	$instructor_title = '<h5 class="study_instructor"><a href="'.bp_core_get_user_domain($instructor_id) .'">'.$displayname.'<span>'.$special.'</span></a></h5>';
	return apply_filters('quantipress_display_study_instructor',$instructor_title,get_the_ID());
}
function bp_study_get_instructor_description($args=NULL) {
	$defaults = array(
		'instructor_id' => get_the_author_meta( 'ID' ),
		'field' => 'About'
	);

	$r = wp_parse_args( $args, $defaults );
	extract( $r, EXTR_SKIP );

	if(function_exists('qm_get_option'))
		$field = qm_get_option('instructor_about');

	$desc = bp_get_profile_field_data('field='.$field.'&user_id='.$instructor_id);

	return apply_filters('the_content',$desc);
}
/**
 *
 * @package BuddyPress_Study_Component
 * @since 1.6
 */

function bp_study_title($args=NULL) {
	echo bp_study_get_study_title($args);
}
	
	/* 
	 *
	 * We'll assemble the title out of the available information. This way, we can insert
	 * fancy stuff link links, and secondary avatars.
	 *
	 * @package BuddyPress_Study_Component
	 * @since 1.6
	 */

	function bp_study_get_study_title($args) {
		$defaults = array(
		'id' => get_the_ID()
		);
		$args= wp_parse_args( $args, $defaults );

		extract( $args, EXTR_SKIP );

		$title = '<a href="'. get_permalink($id) .'">';
		$title .= get_the_title($id);
		$title .= '</a>';
		return $title;
	}

function bp_study_meta() {
	echo bp_study_get_study_meta();
}
	
	/* 
	 *
	 * We'll assemble the title out of the available information. This way, we can insert
	 * fancy stuff link links, and secondary avatars.
	 *
	 * @package BuddyPress_Skeleton_Component
	 * @since 1.6
	 */

function bp_study_get_study_meta() {

	//bp_study_get_study_reviews();

	$reviews=get_post_meta(get_the_ID(),'average_rating',true);
	$count=get_post_meta(get_the_ID(),'rating_count',true);

	if(!isset($reviews) || $reviews == ''){
			$reviews_array=bp_study_get_study_reviews();	
			$reviews = $reviews_array['rating'];
			$count = $reviews_array['count'];
	}
	

	$meta ='';
	if(isset($reviews)){
		$meta = '<div class="star-rating"  itemprop="review" itemscope itemtype="http://data-vocabulary.org/Review-aggregate">
		<i class="hide" itemprop="rating">'.$reviews.'</i>';
		for($i=1;$i<=5;$i++){


			if($reviews >= 1){
				$meta .='<span class="fill"></span>';
			}elseif(($reviews < 1 ) && ($reviews >= 0.4 ) ){
				$meta .= '<span class="half"></span>';
			}else{
				$meta .='<span></span>';
			}
			$reviews--;
		}
		$meta .= '( <strong itemprop="count">'.$count.'</strong> '.__('REVIEWS','qm').' )</div>';
	}else{
		$meta = '<div class="star-rating">
					<span></span><span></span><span></span><span></span><span></span> ( 0 '.__('REVIEWS','qm').' )
				</div>';
	}
	
	$students = get_post_meta(get_the_ID(),'qm_students',true);
	
	if(!isset($students) && $students =''){$students=0;update_post_meta(get_the_ID(),'qm_students',0);} // If students not set

	$meta .='<div class="students" itemprop="size"><i class="icon-users"></i> '.$students.' '.__('STUDENTS','qm').'</div>';
	
	return apply_filters('quantipress_study_meta',$meta);
}




function bp_study_get_study_reviews($args=NULL){

	$defaults=array(
		'id' =>get_the_ID(),
		);
	$r = wp_parse_args( $args, $defaults );
		extract( $r, EXTR_SKIP );

	$args = array(
		'status' => 'approve',
		'post_id' => $id
		);
	$comments_query = new WP_Comment_Query;
	$comments = $comments_query->query( $args );

	// Comment Loop
	if ( $comments ) {
		$ratings =0;
		$count=0;
		$rating = array();
		foreach ( $comments as $comment ) {
			$rate = get_comment_meta( $comment->comment_ID, 'review_rating', true );
			if(isset($rate) && $rate !='')
				$rating[] = $rate;
		}

		

		$count = count($rating);

		if(!$count) $count=1;

		$ratings = round((array_sum($rating)/$count),1);
		
		update_post_meta(get_the_ID(),'average_rating',$ratings);
		update_post_meta(get_the_ID(),'rating_count',$count);

		$reviews = array('rating' => $rating,'count'=>$count);
		return $reviews;
	} else {
		return 0;
	}
}

function bp_study_desc() {
	echo bp_study_get_study_desc();
}
	
/* 
 */

function bp_study_get_study_desc() {
	
	$desc = get_the_excerpt();
	
	return apply_filters('the_content',$desc);
}	

function bp_study_action() {
	echo bp_study_get_study_action();
}
	
/* 
 */

function bp_study_get_study_action() {
	do_action('bp_study_get_study_action');
}


function bp_study_credits($args=NULL) {
	echo bp_study_get_study_credits();
}
	
/* 
 *
 * We'll assemble the title out of the available information. This way, we can insert
 * fancy stuff link links, and secondary avatars.
 *
 * @package BuddyPress_Skeleton_Component
 * @since 1.6
 */

function bp_study_get_study_credits($args=NULL) {
	
	$defaults=array(
		'id' =>get_the_ID(),
		'currency'=>'CREDITS'
		);
	$r = wp_parse_args( $args, $defaults );
		extract( $r, EXTR_SKIP );

	$user_id=get_current_user_id();

	$credits='<strong itemprop="price">';
		
	$free_study = get_post_meta($id,'qm_study_free',true);

	if(qm_validate($free_study)){
		$credits .= apply_filters('quantipress_free_study_price','FREE');
	}else if ( in_array( 'paid-memberships-pro/paid-memberships-pro.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

		$membership_ids = qm_sanitize(get_post_meta($id,'qm_pmpro_membership',false));
		
		if(isset($membership_ids) && is_Array($membership_ids) && count($membership_ids) && function_exists('pmpro_getAllLevels')){

		$membership_id = min($membership_ids);

		$levels=pmpro_getAllLevels();
			foreach($levels as $level){
				if($level->id == $membership_id)
					break;
			}
		
		$credits .= $level->name.'<span class="subs">'.__('MEMBERSHIP','qm').'</span>';
	    }else{
	    	$credits .= '<span class="subs">'.__('No Level Selected','qm').'</span>';
	    } 
	}else{
		
		$product_id = get_post_meta($id,'qm_product',true);
		if(isset($product_id) && $product_id !='' && function_exists('get_product')){
		
			$product = get_product( $product_id );
			$credits = $product->get_price_html();
			//$credits = apply_filters('quantipress_study_credits',$credits,$id);

		}else{
			$credits =get_post_meta($id,'qm_study_credits',true);
			if(isset($credits) && $credits !='' ){
				$credits .= '<span class="subs">'.$credits.'</span>';
			}else{
				$credits .= apply_filters('quantipress_private_study_label',__('PRIVATE','qm'));
			}
		}
	
	}
	$credits .='</strong>';
	return apply_filters('quantipress_study_credits',$credits,$id);
}

/**
 * Is this page part of the study component?
 *
 * Having a special function just for this purpose makes our code more readable elsewhere, and also
 * allows us to place filter 'bp_is_study_component' for other components to interact with.
 *
 * @package BuddyPress_Study_Component
 * @since 1.6
 *
 * @uses bp_is_current_component()
 * @uses apply_filters() to allow this value to be filtered
 * @return bool True if it's the study component, false otherwise
 */
function bp_is_study_component() {
	
		$is_study_component = bp_is_current_component(BP_STUDY_SLUG);

	return apply_filters( 'bp_is_study_component', $is_study_component );
}



function bp_is_single_study(){
	global $bp;
	global $post;
	return is_single();
}
/**
 * Echo the component's slug
 *
 * @package BuddyPress_Study_Component
 * @since 1.6
 */
function bp_study_slug() {
	echo bp_get_study_slug();
}

function bp_get_study_slug(){

	// Avoid PHP warnings, in case the value is not set for some reason
	$study_slug = isset( $bp->study->slug ) ? $bp->study->slug : '';

	return apply_filters( 'bp_get_study_slug', $study_slug );
}

/**
 * Echo the component's root slug
 *
 * @package BuddyPress_Study_Component
 * @since 1.6
 */
function bp_study_root_slug() {
	echo bp_get_study_root_slug();
}
/**
 * Return the component's root slug
 *
 * Having a template function for this purpose is not absolutely necessary, but it helps to
 * avoid too-frequent direct calls to the $bp global.
 *
 * @package BuddyPress_Skeleton_Component
 * @since 1.6
 *
 * @uses apply_filters() Filter 'bp_get_study_root_slug' to change the output
 * @return str $study_root_slug The slug from $bp->study->root_slug, if it exists
 */
function bp_get_study_root_slug() {
	global $bp;

	// Avoid PHP warnings, in case the value is not set for some reason
	$study_root_slug = isset( $bp->study->root_slug ) ? $bp->study->root_slug : '';

	return apply_filters( 'bp_get_study_root_slug', $study_root_slug );
}


function bp_study_get_students_undertaking($study_id=NULL, $page=0){
	global $wpdb,$post;
	if(!isset($study_id))
		$study_id=get_the_ID();

	$study_members = array();

	$loop_number=qm_get_option('loop_number');
	if(!isset($loop_number)) $loop_number = 5;

	$prepage = (isset($_GET['items_page'])?$loop_number*($_GET['items_page']-1):0);
	$nextpage = (isset($_GET['items_page'])?$loop_number*$_GET['items_page']:$loop_number);
	$study_meta = $wpdb->get_results( $wpdb->prepare("select meta_key from {$wpdb->postmeta} where post_id = %d AND meta_value >= %d AND meta_key REGEXP '^-?[0-9]+$' ORDER BY meta_value ASC LIMIT %d, %d",$study_id,0,$prepage,$nextpage), ARRAY_A);
	
	foreach($study_meta as $meta){
		if(is_numeric($meta['meta_key']))  // META KEY is NUMERIC ONLY FOR USERIDS
			$study_members[] = $meta['meta_key'];
	}

	return $study_members;
}

function bp_study_paginate_students_undertaking($study_id=NULL){
	global $wpdb,$post;
	if(!isset($study_id))
		$study_id=get_the_ID();

	$loop_number=qm_get_option('loop_number');
	if(!isset($loop_number)) $loop_number = 5;

	$study_number = $wpdb->get_row( $wpdb->prepare("select count(meta_key) as number from {$wpdb->postmeta} where post_id = %d AND meta_value <= %d AND meta_key REGEXP '^-?[0-9]+$'",$study_id,2), ARRAY_A);
	$max_page = ceil($study_number['number']/$loop_number);


	$return  =	'<div class="pagination"><div class="pag-count" id="study-member-count">Viewing page '.((isset($_GET['items_page']) && $_GET['items_page'])?$_GET['items_page']:1 ).' of '.$max_page.'</div>
					<div class="pagination-links" id="study-member-page-links">';
						$f=$g=1;
						for($i=1;$i<=$max_page;$i++ ){

								if(isset($_GET['items_page']) && is_numeric($_GET['items_page'])){
									if($_GET['items_page'] == $i){
										$return .= '<span class="page-numbers current">'.$i.'</span>';
									}else{
										if($i == 1 || $i == $max_page || ($_GET['items_page'] < 5 && $i < 5) || (($i <= ($_GET['items_page'] + 2)) && ($i >= ($_GET['items_page'] -2))))
										 	$return  .= '<a class="page-numbers" href="?action='.$_GET['action'].'&items_page='.$i.'">'.$i.'</a>';
										 else{
										 	if($f && ($i > ($_GET['items_page'] + 2))){
												$return  .= '<a class="page-numbers">...</a>'; 
												$f=0;
											}
											if($g && ($i <($_GET['items_page'] - 2))){
												$return  .= '<a class="page-numbers">...</a>'; 
												$g=0;
											}
										 }
									}
								}else{
									
									if($i==1)
										$return .= '<span class="page-numbers current">1</span>';
									else{
										if($i < 5 || $i > ($max_page-2))
											$return  .= '<a class="page-numbers" href="?action='.$_GET['action'].'&items_page='.$i.'">'.$i.'</a>';
										else{
											if($f){
												$return  .= '<a class="page-numbers">...</a>'; 
												$f=0;
											}
										}
									}
								}	
						}
						$return  .= '
					</div>
				</div>';

	return $return;
}

/**
 * Echo the total of all high-fives given to a particular user
 *
 * @package BuddyPress_Study_Component
 * @since 1.6
 */
function bp_study_total_study_count_for_user( $user_id = false ) {
	echo bp_study_get_total_study_count_for_user( $user_id = false );
}
/**
 * Return the total of all high-fives given to a particular user
 *
 * The most straightforward way to get a post count is to run a WP_Query. In your own plugin
 * you might consider storing data like this with update_option(), incrementing each time
 * a new item is published.
 *
 * @package BuddyPress_Skeleton_Component
 * @since 1.6
 *
 * @return int
 */
function bp_study_get_total_study_count_for_user( $user_id = false ) {
	// If no explicit user id is passed, fall back on the loggedin user
	if ( !$user_id ) {
		$user_id = bp_loggedin_user_id();
	}

	if ( !$user_id ) {
		return 0;
	}

	$user_studys=get_posts('post_type=study&numberposts=999&meta_key='.$user_id);
	
	$c=count($user_studys);
	if(!isset($c) || !$c) $c = 0;

	//return $c;

	return apply_filters( 'quantipress_get_total_study_count', $c, $user_id );
	
}

function bp_study_get_curriculum_units($study_id=NULL){
	$units=array();
	if(!isset($study_id) || !$study_id)
		return $units;

	$study_curriculum=qm_sanitize(get_post_meta($study_id,'qm_study_curriculum',false));
        
        if(isset($study_curriculum) && is_array($study_curriculum)){
        	foreach($study_curriculum as $key=>$curriculum){
            if(is_numeric($curriculum)){
                $units[]=$curriculum;
            }
          }
        }
    return $units;    
}

function bp_study_check_unit_complete($unit_id=NULL,$user_id=NULL){
	if(!isset($unit_id) || !$unit_id)
		return false;
	if(!isset($user_id) || !$user_id)
		$user_id = get_current_user_id();

	$unit_check=0;
	$unit_check=get_user_meta($user_id,$unit_id,true);
	if(isset($unit_check) && $unit_check)
		return true;
	else
		return false;
}

function bp_study_total_study_count() {
	echo bp_study_get_total_study_count();
}
/**
 * Return the total of all high-fives given to a particular user
 *
 * The most straightforward way to get a post count is to run a WP_Query. In your own plugin
 * you might consider storing data like this with update_option(), incrementing each time
 * a new item is published.
 *
 * @package BuddyPress_Skeleton_Component
 * @since 1.6
 *
 * @return int
 */
function bp_study_get_total_study_count( ) {
	// If no explicit user id is passed, fall back on the loggedin user
	
	$count_study = wp_count_posts(BP_STUDY_SLUG);

	if(!isset($count_study)) $count_study =0;

	return $count_study->publish;
	
}


function bp_is_study() {
	global $bp;

	if ( bp_is_study_component())
		return true;

	return false;
}

/**
 * Is the current page a single study's home page?
 *
 * URL will vary depending on which study tab is set to be the "home". By
 * default, it's the study's recent activity.
 *
 * @return bool True if the current page is a single study's home page.
 */
function bp_is_study_home() {

	if ( bp_is_study_single_item() && bp_is_study_component() && ( !bp_current_action() || bp_is_current_action( 'home' ) ) )
		return true;

	global $post;
	if(is_single())
		return true;

	return false;
}

function bp_is_study_single_item(){ // Global BP Fails Here **** ComeBack when BuddyPress Fixes This
	global $post,$bp;
	if(is_single()){
		return true;
		$bp->is_single_item=true;
	}
	else
		return false;
}
/**
 * Is the current page part of the study creation process?
 *
 * @return bool True if the current page is part of the study creation process.
 */
function bp_is_study_create() {
	if ( bp_is_study_component() && bp_is_current_action( 'create' ) )
		return true;

	return false;
}

/**
 * Is the current page part of a single study's admin screens?
 *
 * Eg http://example.com/studies/mystudy/admin/settings/.
 *
 * @return bool True if the current page is part of a single study's admin.
 */
function bp_is_study_admin_page() {
	if ( bp_is_study_single_item() && bp_is_study_component() && bp_is_current_action( 'admin' ) )
		return true;

	return false;
}

/**
 * Is the current page a study's activity page?
 *
 * @return True if the current page is a study's activity page.
 */
function bp_is_study_activity() {
	if ( bp_is_study_single_item() && bp_is_study_component() && bp_is_current_action( 'activity' ) )
		return true;

	return false;
}


function get_current_study_slug(){
	global $post;
	return $post->post_name;
}



/**
 * Is the current page a study's Members page?
 *
 * Eg http://example.com/studies/mystudy/members/.
 *
 * @return bool True if the current page is part of a study's Members page.
 */
function bp_is_study_members() {
	if ( bp_is_study_single_item() && bp_is_study_component() && bp_is_current_action( 'members' ) )
		return true;

	return false;
}

function bp_is_user_study() {
	if ( bp_is_user() && bp_is_study_component() )
		return true;

	return true;
}


function bp_study_creation_form_action() {
	echo bp_get_study_creation_form_action();
}

function bp_get_study_creation_form_action(){
	global $bp;

	if ( !bp_action_variable( 1 ) ) {
			$keys = array_keys( $bp->studies->study_creation_steps );
		if ( !$user_id ) {
			$bp->action_variables[1] = array_shift( $keys );
		}
	}

	return apply_filters( 'bp_get_study_creation_form_action', trailingslashit( bp_get_root_domain() . '/' . bp_get_studys_root_slug() . '/create/step/' . bp_action_variable( 1 ) ) );
}

function bp_is_study_creation_step( $step_slug ) {
	global $bp;

	/* Make sure we are in the studies component */
	if ( !bp_is_studys_component() || !bp_is_current_action( 'create' ) )
		return false;

	/* If this the first step, we can just accept and return true */
	$keys = array_keys( $bp->studies->study_creation_steps );
	if ( !bp_action_variable( 1 ) && array_shift( $keys ) == $step_slug )
		return true;

	/* Before allowing a user to see a study creation step we must make sure previous steps are completed */
	if ( !bp_is_first_study_creation_step() ) {
		if ( !bp_are_previous_study_creation_steps_complete( $step_slug ) )
			return false;
	}

	/* Check the current step against the step parameter */
	if ( bp_is_action_variable( $step_slug ) )
		return true;

	return false;
}

function bp_is_study_creation_step_complete( $step_slugs ) {
	global $bp;

	if ( !isset( $bp->studies->completed_create_steps ) )
		return false;

	if ( is_array( $step_slugs ) ) {
		$found = true;

		foreach ( (array) $step_slugs as $step_slug ) {
			if ( !in_array( $step_slug, $bp->studies->completed_create_steps ) )
				$found = false;
		}

		return $found;
	} else {
		return in_array( $step_slugs, $bp->studies->completed_create_steps );
	}

	return true;
}

function bp_are_previous_study_creation_steps_complete( $step_slug ) {
	global $bp;

	/* If this is the first study creation step, return true */
	$keys = array_keys( $bp->studies->study_creation_steps );
	if ( array_shift( $keys ) == $step_slug )
		return true;

	reset( $bp->studies->study_creation_steps );
	unset( $previous_steps );

	/* Get previous steps */
	foreach ( (array) $bp->studies->study_creation_steps as $slug => $name ) {
		if ( $slug == $step_slug )
			break;

		$previous_steps[] = $slug;
	}

	return bp_is_study_creation_step_complete( $previous_steps );
}


function is_user_instructor_study(){
	
	if(!is_user_logged_in())
		return false;

	
	if(current_user_can('edit_posts'))
		return true;
	else
		return false;
}

function bp_study_get_instructor_study_count_for_user($id=NULL){
	if(!isset($id))
		$id=bp_loggedin_user_id();

	if(function_exists('count_user_posts_by_type')){
		return count_user_posts_by_type($id,BP_STUDY_SLUG);
	}else
		return 0;
}

function bp_is_my_profile_intructor_study(){
	
	if(current_user_can('edit_posts'))
		return true;
	else
		return false;
}

function is_instructor_study($id=NULL){
	
	if(!is_user_logged_in())
		return false;

	global $post;
	if(!isset($id)){
		$id= $post->ID;
	}

	if(bp_loggedin_user_id() == $post->post_author)
		return true;
	else
		return false;
}

function bp_study_permalink( $study = false ) {
	echo bp_get_study_permalink( $study );
}
function bp_get_study_permalink( $study = false ) {
	global $post;

	if(isset($study) && $study){
		$id = $study;
	}else{
		$id=$post->ID;
	}
	
	return apply_filters( 'bp_get_study_permalink', get_permalink($id));
}

function bp_study_admin_permalink( $study = false ) {
	echo bp_get_study_admin_permalink( $study );
}

function bp_get_study_admin_permalink( $study = false ) {
	global $post;

	if(isset($study) && $study){
	$id = $study;
	}else{
		$id=$post->ID;
	}

	return apply_filters( 'bp_get_study_admin_permalink', ( get_the_permalink($id). 'admin' ) );
}

function bp_study_check_study_complete($args=NULL){
	echo bp_get_study_check_study_complete($args);
}


function bp_get_study_check_study_complete($args=NULL){
	global $post;
	$defaults = array(
		'id'=>$post->ID,
		'user_id'=>get_current_user_id()
		);

	$r = wp_parse_args( $args, $defaults );
		extract( $r, EXTR_SKIP );

	$return ='<div class="study_finish">';

	$study_curriculum=qm_sanitize(get_post_meta($id,'qm_study_curriculum',false));
	if(isset($study_curriculum) && count($study_curriculum)){
		$flag =0;
		foreach($study_curriculum as $unit_id){
			if(is_numeric($unit_id)){
				$unittaken=get_user_meta($user_id,$unit_id,true);
				if(!isset($unittaken) || !$unittaken){
					$flag=$unit_id;
					break;
				}
			}
		}
		if(!$flag){


			$auto_eval = get_post_meta($id,'qm_study_auto_eval',true);

			if(qm_validate($auto_eval)){

				// AUTO EVALUATION
				$curriculum=qm_sanitize(get_post_meta($id,'qm_study_curriculum',false));
				$total_marks=$student_marks=0;

				foreach($curriculum as $c){
					if(is_numeric($c)){
						if(get_post_type($c) == 'quiz'){

		          			$k=get_post_meta($c,$user_id,true);
							$student_marks +=$k;

							$questions = qm_sanitize(get_post_meta($c,'qm_quiz_questions',false));
				      		$total_marks += array_sum($questions['marks']);
						}
					}
				}
				// Apply Filters on Auto Evaluation
				$student_marks=apply_filters('quantipress_study_student_marks',$student_marks,$id,$user_id);
				$total_marks=apply_filters('quantipress_study_maximum_marks',$total_marks,$id,$user_id);

				if(!$total_marks)$total_marks=1; // Avoid the Division by Zero Error

				$marks = round(($student_marks*100)/$total_marks);

				$return .='<div class="message" class="updated"><p>'.__('STUDY EVALUATED ','qm').'</p></div>';

				$badge_per = get_post_meta($id,'qm_study_badge_percentage',true);
    			$passing_per = get_post_meta($id,'qm_study_passing_percentage',true);


    			if(isset($badge_per) && $badge_per && $marks > $badge_per){
			        $badges = array();
			        $badges= qm_sanitize(get_user_meta($user_id,'badges',false));
			        if(isset($badges) && is_array($badges))
			        	$badges[]=$id;
			        else
			        	$badges=array($id);

			        update_user_meta($user_id,'badges',$badges);

			        $return .='<div class="congrats_badge">'.__('Congratulations ! You\'ve earned the ','qm').' <strong>'.get_post_meta($id,'qm_study_badge_title',true).'</strong> '.__('Badge','qm').'</div>';
			        bp_study_record_activity(array(
			          'action' => __('Student got a Badge in the study ','qm'),
			          'content' => __('Student ','qm').bp_core_get_userlink($user_id).__(' got a badge in the study ','qm').get_the_title($id),
			          'type' => 'student_badge',
			          'item_id' => $id,
			          'primary_link'=>get_permalink($id),
			          'secondary_item_id'=>$user_id
			        )); 

			    }

			    if(isset($passing_per) && $passing_per && $marks > $passing_per){
			        $pass = array();
			        $pass=qm_sanitize(get_user_meta($user_id,'certificates',false));
			        
			        if(isset($pass) && is_array($pass))
			        	$pass[]=$id;
			        else
			        	$pass=array($id);

			        update_user_meta($user_id,'certificates',$pass);

			        $return .='<div class="congrats_certificate">'.__('Congratulations ! You\'ve successfully passed the study and earned the Study Completion Certificate !','qm').'</div>';

			        bp_study_record_activity(array(
			          'action' => __('Student got a Certificate in the study ','qm'),
			          'content' => __('Student ','qm').bp_core_get_userlink($user_id).__(' got a caertificate in the study ','qm').get_the_title($id),
			          'type' => 'student_certificate',
			          'item_id' => $id,
			          'primary_link'=>get_permalink($id),
			          'secondary_item_id'=>$user_id
			        )); 
			    }

			    if(update_post_meta( $id,$user_id,$marks)){
			      $message = __('You\'ve obtained ','qm').$marks.__(' out of 100 in Study :','qm').' <a href="'.get_permalink($id).'">'.get_the_title($id).'</a>';
			      messages_new_message( array('sender_id' => get_current_user_id(), 'subject' => __('Study results available','qm'), 'content' => $message,   'recipients' => $user_id ) );
			      
			      $return .='<div class="congrats_message">'.$message.'</div>';

			    }else{
			      $return .='<div id="message" class="error">'. __('FAILED TO MARK STUDY, CONTACT ADMIN','qm').'</div>';
			    }

			    bp_study_record_activity(array(
			      'action' => __('Auto evaluated Study for Student','qm'),
			      'content' => __('Student ','qm').bp_core_get_userlink( $user_id ).__(' got ','qm').$marks.__(' out of 100 in study ','qm').get_the_title($id),
			      'primary_link' => get_permalink($id),
			      'type' => 'study_evaluated',
			      'item_id' => $id,
			      'secondary_item_id' => $user_id
			      ));


			}else{
				$return .='<div class="message" class="updated"><p>'.__('STUDY SUBMITTED FOR EVALUATION','qm').'</p></div>';
				update_post_meta($id,$user_id,2); // 2 determines Study is Complete
			}
			
			// Show the Generic Study Submission
			$content=get_post_meta($id,'qm_study_message',true);
			$return .=apply_filters('the_content',$content);
			$return = apply_filters('quantipress_study_finished',$return);
		}else{
			$return .='<div class="message"><p>'.__('PLEASE COMPLETE THE ','qm').get_post_type($flag).' : '.get_the_title($flag).'</p></div>';
		}
	}else{
		$retun .=__('STUDY CURRICULUM NOT SET','qm');
	}	
	$return .='</div>';
	return $return;
}

?>
