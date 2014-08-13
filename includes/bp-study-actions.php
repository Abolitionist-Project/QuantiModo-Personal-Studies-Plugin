<?php

/**
 * Check to see if a high five is being given, and if so, save it.
 *
 * Hooked to bp_actions, this function will fire before the screen function. We use our function
 * bp_is_study_component(), along with the bp_is_current_action() and bp_is_action_variable()
 * functions, to detect (based on the requested URL) whether the user has clicked on "send high
 * five". If so, we do a bit of simple logic to see what should happen next.
 *
 * @package BuddyPress_Study_Component
 * @since 1.6
 */


add_action('bp_activity_register_activity_actions','bp_study_register_actions');
function bp_study_register_actions(){
	global $bp;
	$bp_study_action_desc=array(
		'remove_from_study' => __( 'Removed a student from Study', 'qm' ),
		'submit_study' => __( 'Student submitted a Study', 'qm' ),
		'start_study' => __( 'Student started a Study', 'qm' ),
		'submit_quiz' => __( 'Student submitted a Quiz', 'qm' ),
		'start_quiz' => __( 'Student started a Study', 'qm' ),
		'unit_complete' => __( 'Student submitted a Study', 'qm' ),
		'reset_study' => __( 'Study reset for Student', 'qm' ),
		'bulk_action' => __( 'Bulk action by instructor', 'qm' ),
		'study_evaluated' => __( 'Study Evaluated for student', 'qm' ),
		'student_badge'=> __( 'Student got a Badge', 'qm' ),
		'student_certificate' => __( 'Student got a certificate', 'qm' ),
		'quiz_evaluated' => __( 'Quiz Evaluated for student', 'qm' ),
		'subscribe_study' => __( 'Student subscribed for study', 'qm' ),
		);
	foreach($bp_study_action_desc as $key => $value){
		bp_activity_set_action($bp->activity->id,$key,$value);	
	}
}

add_filter( 'woocommerce_get_price_html', 'study_subscription_filter',100,2 );
function study_subscription_filter($price,$product){

	$subscription=get_post_meta($product->id,'qm_subscription',true);

		if(qm_validate($subscription)){
			$x=get_post_meta($product->id,'qm_duration',true);

			$t=$x*86400;

			if($x == 1){
				$price = $price .'<span class="subs"> '.__('per','qm').' '.tofriendlytime($t).'</span>';
			}else{
				$price = $price .'<span class="subs"> '.__('per','qm').' '.tofriendlytime($t).'</span>';
			}
		}
		return apply_filters( 'woocommerce_get_price', $price );
}




add_action('woocommerce_after_add_to_cart_button','bp_study_subscription_product');
function bp_study_subscription_product(){
	global $product;
	$check_susbscription=get_post_meta($product->id,'qm_subscription',true);
	if(qm_validate($check_susbscription)){
		$duration=get_post_meta($product->id,'qm_duration',true);	
		$t=tofriendlytime($duration*86400);
		echo '<div id="duration"><strong>'.__('SUBSCRIPTION FOR','qm').' '.$t.'</strong></div>';
	}
}
//woocommerce_order_status_completed
add_action('woocommerce_order_status_completed','bp_study_enable_access');

function bp_study_enable_access($order_id){
	$order = new WC_Order( $order_id );

	$items = $order->get_items();
	$user_id=$order->user_id;
	foreach($items as $item){
		$product_id = $item['product_id'];

		$subscribed=get_post_meta($product_id,'qm_subscription',true);

		$studies=qm_sanitize(get_post_meta($product_id,'qm_studys',false));


		if(qm_validate($subscribed) ){

			$duration=get_post_meta($product_id,'qm_duration',true);
			$t=time()+$duration*86400;

			foreach($studies as $study){
				update_post_meta($study,$user_id,0);
				update_user_meta($user_id,$study,$t);
				$group_id=get_post_meta($study,'qm_group',true);
				if(isset($group_id) && $group_id !='')
				groups_join_group($group_id, $user_id );  

				bp_study_record_activity(array(
				      'action' => __('Student subscribed for study ','qm').get_the_title($study),
				      'content' => __('Student ','qm').bp_core_get_userlink( $user_id ).__(' subscribed for study ','qm').get_the_title($study).__(' for ','qm').$duration.__(' days','qm'),
				      'type' => 'subscribe_study',
				      'item_id' => $study,
				      'primary_link'=>get_permalink($study),
				      'secondary_item_id'=>$user_id
		        ));      
			}
			//wp_update_user(array('ID'=>$user_id,'role'=>'student') );
		}else{	
			if(isset($studies) && is_array($studies)){
			foreach($studies as $study){
				$duration=get_post_meta($study,'qm_duration',true);
				$t=time()+$duration*86400;
				update_post_meta($study,$user_id,0);
				update_user_meta($user_id,$study,$t);
				$group_id=get_post_meta($study,'qm_group',true);
				if(isset($group_id) && $group_id !='')
				groups_join_group($group_id, $user_id );

				bp_study_record_activity(array(
				      'action' => __('Student subscribed for study ','qm').get_the_title($study),
				      'content' => __('Student ','qm').bp_core_get_userlink( $user_id ).__(' subscribed for study ','qm').get_the_title($study).__(' for ','qm').$duration.__(' days','qm'),
				      'type' => 'subscribe_study',
				      'item_id' => $study,
				      'primary_link'=>get_permalink($study),
				      'secondary_item_id'=>$user_id
		        )); 
				}
				//wp_update_user(array('ID'=>$user_id,'role'=>'student') );
			}
		}
		
	}
	 
}


add_action('pre_get_posts', 'hdb_add_custom_type_to_query_study');

function hdb_add_custom_type_to_query_study( $notused ){ //Authors Page
     if (! is_admin() ){
        global $wp_query;
        if ( is_author()){
            $wp_query->set( 'post_type',  array( BP_STUDY_SLUG ) );
        }
     }
}

add_action('bp_members_directory_member_types','bp_study_instructor_member_types');

function bp_study_instructor_member_types(){
	?>
		<li id="members-instructors"><a href="#"><?php printf( __( 'All Instructors <span>%s</span>', 'qm' ), bp_get_total_instructor_count() ); ?></a></li>
	<?php
}
?>