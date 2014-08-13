<?php

/**
 * NOTE: You should always use the wp_enqueue_script() and wp_enqueue_style() functions to include
 * javascript and css files.
 */


function bp_study_add_js() {
	global $bp;

	//if ( $bp->current_component == $bp->study->slug ){ // Globals All Messed Up, falling back to WordPress
		wp_enqueue_style( 'bp-study-css', plugins_url( '/qm-study-module/includes/css/study_template.css' ) );
		wp_enqueue_style( 'bp-study-graph', plugins_url( '/qm-study-module/includes/css/graph.css' ) );
		wp_enqueue_script( 'bp-confirm-js', plugins_url( '/qm-study-module/includes/js/jquery.confirm.min.js' ) );
		wp_enqueue_script( 'bp-html2canvas-js', plugins_url( '/qm-study-module/includes/js/html2canvas.js' ) );

		wp_enqueue_script( 'bp-study-js', plugins_url( '/qm-study-module/includes/js/study.js' ),array('jquery','jquery-ui-core','jquery-ui-sortable') );
		$color=bp_quantipress_get_theme_color();
		$translation_array = array( 
			'too_fast_answer' => __( 'Too Fast or Answer not marked.','qm' ), 
			'answer_saved' => __( 'Answer Saved.','qm' ), 
			'processing' => __( 'Processing...','qm' ), 
			'saving_answer' => __( 'Saving Answer...please wait','qm' ), 
			'remove_user_text' => __( 'This step is irreversible. Are you sure you want to remove the User from the study ?','qm' ), 
			'remove_user_button' => __( 'Confirm, Remove User from Study','qm' ), 
			'cancel' => __( 'Cancel','qm' ), 
			'reset_user_text' => __( 'This step is irreversible. All Units, Quiz results would be reset for this user. Are you sure you want to Reset the Study for this User?','qm' ), 
			'reset_user_button' => __( 'Confirm, Reset Study for this User','qm' ), 
			'quiz_reset' => __( 'This step is irreversible. All Questions answers would be reset for this user. Are you sure you want to Reset the Quiz for this User? ','qm' ), 
			'quiz_reset_button' => __( 'Confirm, Reset Quiz for this User','qm' ), 
			'marks_saved' => __( 'Marks Saved','qm' ), 
			'quiz_marks_saved' => __( 'Quiz Marks Saved','qm' ), 
			'submit_quiz' => __( 'Submit Quiz','qm' ), 
			'sending_messages' => __( 'Sending Messages ...','qm' ), 
			'adding_students' => __( 'Adding Students to Study ...','qm' ), 
			'successfuly_added_students' => __( 'Students successfully added to Study','qm' ),
			'unable_add_students' => __( 'Unable to Add students to Study','qm' ),
			'theme_color' => $color
			);
    	wp_localize_script( 'bp-study-js', 'qm_study_module_strings', $translation_array );
    	
	//}
}
add_action( 'wp_footer', 'bp_study_add_js');


add_action('admin_enqueue_scripts','bp_study_admin_scripts');
function bp_study_admin_scripts(){
	wp_enqueue_script( 'bp-graph-js', plugins_url( '/qm-study-module/includes/js/jquery.flot.min.js' ) );
}
?>