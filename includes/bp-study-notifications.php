<?php



/********************************************************************************
 * Activity & Notification Functions
 *
 * These functions handle the recording, deleting and formatting of activity and
 * notifications for the user and for this specific component.
 */


/**
 * bp_study_screen_notification_settings()
 *
 * Adds notification settings for the component, so that a user can turn off email
 * notifications set on specific component actions.
 */
function bp_study_screen_notification_settings() {
	global $current_user;

	/**
	 * Under Settings > Notifications within a users profile page they will see
	 * settings to turn off notifications for each component.
	 *
	 * You can plug your custom notification settings into this page, so that when your
	 * component is active, the user will see options to turn off notifications that are
	 * specific to your component.
	 */

	 /**
	  * Each option is stored in a posted array notifications[SETTING_NAME]
	  * When saved, the SETTING_NAME is stored as usermeta for that user.
	  *
	  * For study, notifications[notification_friends_friendship_accepted] could be
	  * used like this:
	  *
	  * if ( 'no' == get_user_meta( $bp->displayed_user->id, 'notification_friends_friendship_accepted', true ) )
	  *		// don't send the email notification
	  *	else
	  *		// send the email notification.
      */

	?>
	<table class="notification-settings" id="bp-study-notification-settings">

		<thead>
		<tr>
			<th class="icon"></th>
			<th class="title"><?php _e( 'study', 'bp-study' ) ?></th>
			<th class="yes"><?php _e( 'Yes', 'bp-study' ) ?></th>
			<th class="no"><?php _e( 'No', 'bp-study' )?></th>
		</tr>
		</thead>

		<tbody>
		<tr>
			<td></td>
			<td><?php _e( 'Action One', 'bp-study' ) ?></td>
			<td class="yes"><input type="radio" name="notifications[notification_study_action_one]" value="yes" <?php if ( !get_user_meta( $current_user->id, 'notification_study_action_one', true ) || 'yes' == get_user_meta( $current_user->id, 'notification_study_action_one', true ) ) { ?>checked="checked" <?php } ?>/></td>
			<td class="no"><input type="radio" name="notifications[notification_study_action_one]" value="no" <?php if ( get_user_meta( $current_user->id, 'notification_study_action_one') == 'no' ) { ?>checked="checked" <?php } ?>/></td>
		</tr>
		<tr>
			<td></td>
			<td><?php _e( 'Action Two', 'bp-study' ) ?></td>
			<td class="yes"><input type="radio" name="notifications[notification_study_action_two]" value="yes" <?php if ( !get_user_meta( $current_user->id, 'notification_study_action_two', true ) || 'yes' == get_user_meta( $current_user->id, 'notification_study_action_two', true ) ) { ?>checked="checked" <?php } ?>/></td>
			<td class="no"><input type="radio" name="notifications[notification_study_action_two]" value="no" <?php if ( 'no' == get_user_meta( $current_user->id, 'notification_study_action_two', true ) ) { ?>checked="checked" <?php } ?>/></td>
		</tr>

		<?php do_action( 'bp_study_notification_settings' ); ?>

		</tbody>
	</table>
<?php
}
add_action( 'bp_notification_settings', 'bp_study_screen_notification_settings' );


/**
 * bp_study_remove_screen_notifications()
 *
 * Remove a screen notification for a user.
 */
function bp_study_remove_quiz_results() {
	global $bp;

	/**
	 * When clicking on a screen notification, we need to remove it from the menu.
	 * The following command will do so.bp_notifications_delete_notifications_by_type
 	 */
	bp_notifications_delete_notifications_by_type( $bp->loggedin_user->id, $bp->study->slug, 'quiz_results' );
}
add_action( 'bp_study_quiz_results', 'bp_study_remove_quiz_results' );
add_action( 'xprofile_screen_display_profile', 'bp_study_remove_quiz_results' );


/**
 * bp_study_format_notifications()
 *
 * The format notification function will take DB entries for notifications and format them
 * so that they can be displayed and read on the screen.
 *
 * Notifications are "screen" notifications, that is, they appear on the notifications menu
 * in the site wide navigation bar. They are not for email notifications.
 *
 *
 * The recording is done by using bp_core_add_notification() which you can search for in this file for
 * studies of usage.
 */
function bp_study_format_notifications( $action, $item_id, $secondary_item_id, $total_items ) {
	global $bp;

	switch ( $action ) {
		case 'quiz_results':
			/* In this case, $item_id is the user ID of the user who sent the high five. */

			/***
			 * We don't want a whole list of similar notifications in a users list, so we group them.
			 * If the user has more than one action from the same component, they are counted and the
			 * notification is rendered differently.
			 */
			if ( (int)$total_items > 1 ) {
				return apply_filters( 'bp_study_multiple_new_high_five_notification', '<a href="' . $bp->loggedin_user->domain . $bp->study->slug . '/screen-one/" title="' . __( 'Multiple high-fives', 'bp-study' ) . '">' . sprintf( __( '%d new high-fives, multi-five!', 'bp-study' ), (int)$total_items ) . '</a>', $total_items );
			} else {
				$user_fullname = bp_core_get_user_displayname( $item_id, false );
				$user_url = bp_core_get_user_domain( $item_id );
				return apply_filters( 'bp_study_single_new_high_five_notification', '<a href="' . $user_url . '?new" title="' . $user_fullname .'\'s profile">' . sprintf( __( '%s sent you a high-five!', 'bp-study' ), $user_fullname ) . '</a>', $user_fullname );
			}
		break;
	}

	do_action( 'bp_study_format_notifications', $action, $item_id, $secondary_item_id, $total_items );

	return false;
}

/**
 * Notification functions are used to send email notifications to users on specific events
 * They will check to see the users notification settings first, if the user has the notifications
 * turned on, they will be sent a formatted email notification.
 *
 * You should use your own custom actions to determine when an email notification should be sent.
 */

function bp_study_send_quiz_notification( $to_user_id, $from_user_id,$quiz,$marks ) {
	global $bp;

	/* Let's grab both user's names to use in the email. */
	$sender_name = bp_core_get_user_displayname( $from_user_id, false );
	$reciever_name = bp_core_get_user_displayname( $to_user_id, false );


	/* Get the userdata for the reciever and sender, this will include usernames and emails that we need. */
	$reciever_ud = get_userdata( $to_user_id );
	$sender_ud = get_userdata( $from_user_id );

	/* Now we need to construct the URL's that we are going to use in the email */
	$sender_profile_link = site_url( BP_MEMBERS_SLUG . '/' . $sender_ud->user_login . '/' . $bp->profile->slug );
	$quiz_results_link = site_url( BP_MEMBERS_SLUG . '/' . $reciever_ud->user_login . '/' . $bp->study->slug . '/study-results' );
	$reciever_settings_link = site_url( BP_MEMBERS_SLUG . '/' . $reciever_ud->user_login . '/settings/notifications' );

	/* Set up and send the message */
	$to = $reciever_ud->user_email;
	$subject = '[' . get_blog_option( 1, 'blogname' ) . '] ' . sprintf( __( '%s high-fived you!', 'bp-study' ), stripslashes($sender_name) );

	$message = sprintf( __(
'Results for %s are out. You\'ve recieved %s marks.

see quiz results %s ,evaluated by %s [%s]


---------------------
', 'bp-study' ), $quiz,$marks,$quiz_results_link ,$sender_name, $sender_profile_link);


bp_notifications_add_notification("user_id=$to_user_id&component_name=quiz&component_action=quiz_results");

echo 'notification';
}
add_action( 'bp_study_send_quiz_notification', 'bp_study_send_quiz_notification', 10, 2 );

?>
