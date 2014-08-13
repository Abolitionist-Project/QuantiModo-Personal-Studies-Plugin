<?php

/********************************************************************************
 * Screen Functions
 *
 * Screen functions are the controllers of BuddyPress. They will execute when their
 * specific URL is caught. They will first save or manipulate data using business
 * functions, then pass on the user to a template file.
 */



/**
 * If your component uses a top-level directory, this function will catch the requests and load
 * the index page.
 *
 * @package BuddyPress_Template_Pack
 * @since 1.6
 */
function bp_study_directory_setup() {
	if ( bp_is_study_component() && !bp_current_action() && !bp_current_item() ) {
		// This wrapper function sets the $bp->is_directory flag to true, which help other
		// content to display content properly on your directory.
		bp_update_is_directory( true, BP_STUDY_SLUG );

		// Add an action so that plugins can add content or modify behavior
		do_action( 'bp_study_directory_setup' );

		bp_core_load_template( apply_filters( 'study_directory_template', 'study/index' ) );
	}
}
add_action( 'bp_screens', 'bp_study_directory_setup' );


function bp_study_my_results(){
	do_action( 'bp_study_screen_my_results' );
	bp_core_load_template( apply_filters( 'bp_study_template_my_studys', 'members/single/home' ) );
}

/**
 * bp_study_my_studys()
 *
 * Sets up and displays the screen output for the sub nav item "study/my_studys"
 */

function bp_study_my_studys() {

	do_action( 'bp_study_screen_my_studys' );

	bp_core_load_template( apply_filters( 'bp_study_template_my_studys', 'members/single/home' ) );
}

function bp_study_stats() {
	do_action( 'bp_study_screen_study_stats' );
	bp_core_load_template( apply_filters( 'bp_study_template_study_stats', 'members/single/home' ) );

}

/**
 * bp_study_instructor_studys()
 *
 * Sets up and displays the screen output for the sub nav item "study/instructor-studies"
 */

function bp_study_instructor_studys() {

	do_action( 'bp_study_instructing_studys' );

	bp_core_load_template( apply_filters( 'bp_study_instructor_studys', 'members/single/home' ) );
}
/**
 * The following screen functions are called when the Settings subpanel for this component is viewed
 */
function bp_study_screen_settings_menu() {
	global $bp, $current_user, $bp_settings_updated, $pass_error;

	if ( isset( $_POST['submit'] ) ) {
		/* Check the nonce */
		check_admin_referer('bp-study-admin');

		$bp_settings_updated = true;

		/**
		 * This is when the user has hit the save button on their settings.
		 * The best place to store these settings is in wp_usermeta.
		 */
		update_user_meta( $bp->loggedin_user->id, 'bp-study-option-one', attribute_escape( $_POST['bp-study-option-one'] ) );
	}

	add_action( 'bp_template_content_header', 'bp_study_screen_settings_menu_header' );
	add_action( 'bp_template_title', 'bp_study_screen_settings_menu_title' );
	add_action( 'bp_template_content', 'bp_study_screen_settings_menu_content' );

	bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/plugins' ) );
}

	function bp_study_screen_settings_menu_header() {
		_e( 'Study Settings Header', 'bp-study' );
	}

	function bp_study_screen_settings_menu_title() {
		_e( 'study Settings', 'bp-study' );
	}

	function bp_study_screen_settings_menu_content() {
		global $bp, $bp_settings_updated; ?>

		<?php if ( $bp_settings_updated ) { ?>
			<div id="message" class="updated fade">
				<p><?php _e( 'Changes Saved.', 'bp-study' ) ?></p>
			</div>
		<?php } ?>

		<form action="<?php echo $bp->loggedin_user->domain . 'settings/study-admin'; ?>" name="bp-study-admin-form" id="account-delete-form" class="bp-study-admin-form" method="post">

			<input type="checkbox" name="bp-study-option-one" id="bp-study-option-one" value="1"<?php if ( '1' == get_user_meta( $bp->loggedin_user->id, 'bp-study-option-one', true ) ) : ?> checked="checked"<?php endif; ?> /> <?php _e( 'Do you love clicking checkboxes?', 'bp-study' ); ?>
			<p class="submit">
				<input type="submit" value="<?php _e( 'Save Settings', 'bp-study' ) ?> &raquo;" id="submit" name="submit" />
			</p>

			<?php
			/* This is very important, don't leave it out. */
			wp_nonce_field( 'bp-study-admin' );
			?>

		</form>
	<?php
	}


/*=== SINGLE STUDY SCREENS ====*/	


function bp_screen_study_home() {

	if ( ! bp_is_single_item() ) {
		return false;
	}

	do_action( 'bp_screen_study_home' );

	bp_core_load_template( apply_filters( 'bp_template_study_home', 'studies/single/home' ) );
}

function bp_screen_study_structure(){
	
}

add_action('quantipress_study_admin_bulk_actions','bp_study_admin_bulk_actions',10);

function bp_study_admin_bulk_actions(){
	echo '<ul>'.apply_filters('quantipress_study_admin_bulk_actions_list',
			'<li><a href="#" class="expand_message tip" title="'.__('Send Bulk Message','qm').'"><i class="icon-letter-mail-1"></i></a></li>
		    <li><a href="#" class="expand_add_students tip" title="'.__('Add Students to Study','qm').'"><i class="icon-users"></i></a></li>
		    <li><a href="#" class="expand_assign_students tip" title="'.__('Assign Badges/Certificates to Students','qm').'"><i class="icon-key-fill"></i></a></li>').
		'</ul>';
}

add_action('quantipress_study_admin_bulk_actions','bp_study_admin_bulk_send_message',10);
function bp_study_admin_bulk_send_message(){
	$user_id = get_current_user_id();
	echo'
		<div class="bulk_message">
			<input type="text" id="bulk_subject" class="form_field" placeholder="'.__('Type Message Subject','qm').'">
			<textarea id="bulk_message" placeholder="'.__('Type Message','qm').'"></textarea>
	 		<a href="#" id="send_study_message" data-study="'.get_the_ID().'" class="button full">'.__('Send Message','qm').'</a>
	 		<input type="hidden" id="sender" value="'.$user_id.'" />';
	 	echo '</div>';
		
}

add_action('quantipress_study_admin_bulk_actions','bp_study_admin_add_students',20);
function bp_study_admin_add_students(){
	$instructor_add_students = qm_get_option('instructor_add_students');
	if((isset($instructor_add_students) && $instructor_add_students) || current_user_can('publish_posts')){
		$user_id = get_current_user_id();
		echo'
			<div class="bulk_add_students">
				<textarea id="student_usernames" placeholder="'.__('Enter Student Usernames, separated by comma','qm').'"></textarea>
		 		<a href="#" id="add_student_to_study" data-study="'.get_the_ID().'" class="button full">'.__('Add Students','qm').'</a>';
	 	echo '</div>';
	}
}

add_action('quantipress_study_admin_bulk_actions','bp_study_admin_assign_students',20);
function bp_study_admin_assign_students(){
	$instructor_assign_students = qm_get_option('instructor_assign_students');
	if((isset($instructor_assign_students) && $instructor_assign_students) || current_user_can('publish_posts')){
		$user_id = get_current_user_id();
		echo'
		<div class="bulk_assign_students">
			<br />
			<select id="assign_action" name="assign_action">
				<option value="add_badge">'.__('ASSIGN STUDY BADGE','qm').'</option>
				<option value="add_certificate">'.__('ASSIGN STUDY CERTIFICATE','qm').'</option>
				<option value="remove_badge">'.__('REMOVE STUDY BADGE','qm').'</option>
				<option value="remove_certificate">'.__('REMOVE STUDY CERTIFICATE','qm').'</option>
			</select>
			<a href="#" id="assign_study_badge_certificate" data-study="'.get_the_ID().'" class="button full">'.__('Assign Action','qm').'</a>';
	 	echo '</div>';
	}
}
?>