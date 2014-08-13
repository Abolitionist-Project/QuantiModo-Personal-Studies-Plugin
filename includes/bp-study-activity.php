<?php

/**
 * bp_study_record_activity()
 *
 * If the activity stream component is installed, this function will record activity items for your
 * component.
 *
 * You must pass the function an associated array of arguments:
 *
 *     $args = array(
 *	 	 REQUIRED PARAMS
 *		 'action' => For study: "Andy high-fived John", "Andy posted a new update".
 *       'type' => The type of action being carried out, for study 'new_friendship', 'joined_group'. This should be unique within your component.
 *
 *		 OPTIONAL PARAMS
 *		 'id' => The ID of an existing activity item that you want to update.
 * 		 'content' => The content of your activity, if it has any, for study a photo, update content or blog post excerpt.
 *       'component' => The slug of the component.
 *		 'primary_link' => The link for the title of the item when appearing in RSS feeds (defaults to the activity permalink)
 *       'item_id' => The ID of the main piece of data being recorded, for study a group_id, user_id, forum_post_id - useful for filtering and deleting later on.
 *		 'user_id' => The ID of the user that this activity is being recorded for. Pass false if it's not for a user.
 *		 'recorded_time' => (optional) The time you want to set as when the activity was carried out (defaults to now)
 *		 'hide_sitewide' => Should this activity item appear on the site wide stream?
 *		 'secondary_item_id' => (optional) If the activity is more complex you may need a second ID. For study a group forum post may need the group_id AND the forum_post_id.
 *     )
 *
 * study usage would be:
 *
 *   bp_study_record_activity( array( 'type' => 'new_highfive', 'action' => 'Andy high-fived John', 'user_id' => $bp->loggedin_user->id, 'item_id' => $bp->displayed_user->id ) );
 *
 */
function bp_study_record_activity( $args = '' ) {
	global $bp;

	if ( !function_exists( 'bp_activity_add' ) )
		return false;

	$defaults = array(
		'id' => false,
		'user_id' => $bp->loggedin_user->id,
		'action' => 'study',
		'content' => '',
		'primary_link' => '',
		'component' => 'study',
		'type' => false,
		'item_id' => false,
		'secondary_item_id' => false,
		'recorded_time' => gmdate( "Y-m-d H:i:s" ),
		'hide_sitewide' => false
	);

	$r = wp_parse_args( $args, $defaults );
	extract( $r );

	return bp_activity_add( array( 'id' => $id, 'user_id' => $user_id, 'action' => $action, 'content' => $content, 'primary_link' => $primary_link, 'component' => $component, 'type' => $type, 'item_id' => $item_id, 'secondary_item_id' => $secondary_item_id, 'recorded_time' => $recorded_time, 'hide_sitewide' => $hide_sitewide ) );
}

function bp_study_record_activity_meta($args=''){
	if ( !function_exists( 'bp_activity_update_meta' ) )
		return false;

	$defaults = array(
		'id' => false,
		'meta_key' => '',
		'meta_value' => ''
	);

	$r = wp_parse_args( $args, $defaults );
	extract( $r );

	return bp_activity_update_meta($id,$meta_key,$meta_value);
}

// Add custom post type to activity record
add_filter ( 'bp_blogs_record_post_post_types', 'activity_publish_custom_post_types_study',1,1 );
// Add custom post type comments to activity record
add_filter ( 'bp_blogs_record_comment_post_types', 'activity_publish_custom_post_types_study',1,1 );
 
function activity_publish_custom_post_types_study( $post_types ) {
// add any custom post-type here
$post_types[] = 'study';
$post_types[] = 'question';
return $post_types;
}

//Modify activity records for custom post-type
add_filter('bp_blogs_activity_new_post_action', 'record_cpt_activity_action_study', 1, 3);
function record_cpt_activity_action_study( $activity_action,  $post, $post_permalink ) {
global $bp;
if( $post->post_type != 'post' ) {
	if ( is_multisite() )
	$activity_action  = sprintf( __( '%1$s wrote a new %2$s, %3$s, on the site %4$s', 'qm' ), bp_core_get_userlink( (int) $post->post_author ), $post->post_type, '<a href="' . $post_permalink . '">' . $post->post_title . '</a>', get_blog_option( $blog_id, 'blogname' ));
	else
	$activity_action  = sprintf( __( '%1$s wrote a new %2$s, %3$s', 'qm' ), bp_core_get_userlink( (int) $post->post_author ),$post->post_type, '<a href="' . $post_permalink . '">' . $post->post_title . '</a>' );
} 
return $activity_action;
}
 
//Modify activity comment records for custom post-type
add_filter('bp_blogs_activity_new_comment_action', 'record_cpt_comment_activity_action_study', 10, 3);
function record_cpt_comment_activity_action_study( $activity_action,  &$recorded_comment, $comment_link ) {
global $bp;

//$recorded_comment = get_comment( $comment_id ); 
if( $recorded_comment->post->post_type == 'study' ) {
		if ( is_multisite() )
			$activity_action = sprintf( __( '%1$s reviewed the %2$s, %3$s, on the site %4$s', 'qm' ), bp_core_get_userlink( $user_id ), $recorded_comment->post->post_type, '<a href="' . $post_permalink . '">' . apply_filters( 'the_title', $recorded_comment->post->post_title ) . '</a>', '<a href="' . get_blog_option( $blog_id, 'home' ) . '">' . get_blog_option( $blog_id, 'blogname' ) . '</a>' );
		else
			$activity_action = sprintf( __( '%1$s reviewed the %2$s, %3$s', 'qm' ), bp_core_get_userlink( $user_id ),$recorded_comment->post->post_type, '<a href="' . $post_permalink . '">' . apply_filters( 'the_title', $recorded_comment->post->post_title ) . '</a>' );
} 
if( $recorded_comment->post->post_type == 'question' ) {
		if ( is_multisite() )
			$activity_action = sprintf( __( '%1$s answered the %2$s, %3$s, on the site %4$s', 'qm' ), bp_core_get_userlink( $user_id ), $recorded_comment->post->post_type, '<a href="' . $post_permalink . '">' . apply_filters( 'the_title', $recorded_comment->post->post_title ) . '</a>', '<a href="' . get_blog_option( $blog_id, 'home' ) . '">' . get_blog_option( $blog_id, 'blogname' ) . '</a>' );
		else
			$activity_action = sprintf( __( '%1$s answered the %2$s, %3$s', 'qm' ), bp_core_get_userlink( $user_id ),$recorded_comment->post->post_type, '<a href="' . $post_permalink . '">' . apply_filters( 'the_title', $recorded_comment->post->post_title ) . '</a>' );
} 
return $activity_action;
}

/* =========== */

function bp_study_activity_filter( $has_activities,$activities) {
    global $bp;
   
    $is_super_admin = is_super_admin();
    $bp_displayed_user_id = bp_displayed_user_id();
    $bp_loggedin_user_id = bp_loggedin_user_id();
    
    foreach ( $activities->activities as $key => $activity ) {

        if( $bp_loggedin_user_id == $activity->user_id  ) 
            continue;


        $remove_from_stream = false;

    	if( !current_user_can('edit_posts') && ($activity->user_id != $bp_loggedin_user_id) && (in_array($activity->component,array('study','quiz','unit'))))
    		$remove_from_stream = true;
       

        if ( $remove_from_stream && isset( $activities->activity_count ) ) {
            $activities->activity_count = $activities->activity_count - 1;
            unset( $activities->activities[$key] );
        }
    }
    
    $activities_new = array_values( $activities->activities );
    $activities->activities = $activities_new;

    return $has_activities;
}

//add_action( 'bp_has_activities', 'bp_study_activity_filter', 10, 9 );


?>