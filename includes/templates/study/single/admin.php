<?php
$user_id=get_current_user_id();
?>
<div class="item-list-tabs no-ajax " id="subnav" role="navigation">
	<ul>
		<li class="study_sub_action <?php if(!isset($_GET['submissions']) && !isset($_GET['stats'])) echo 'current'; ?>">
			<a id="study" href="?action=admin">Members</a>
		</li>	
		<li class="study_sub_action <?php if(isset($_GET['submissions'])) echo 'current'; ?>">
			<a id="study" href="?action=admin&submissions">Submissions</a>
		</li>
		<li class="study_sub_action <?php if(isset($_GET['stats'])) echo 'current'; ?>">
			<a id="study" href="?action=admin&stats">Stats</a>
		</li>
	</ul>
</div>
<div id="message" class="info vnotice">
</div>
<?php

if(isset($_GET['submissions'])){

$study_id=get_the_ID();
global $wpdb;

echo '<div class="submissions"><h4 class="minmax">';
_e('QUIZ SUBMISSIONS');
echo '<i class="icon-plus-1"></i></h4>';
$curriculum=qm_sanitize(get_post_meta(get_the_ID(),'qm_study_curriculum',false));
foreach($curriculum as $c){
	if(is_numeric($c)){
		if(get_post_type($c) == 'quiz'){
			// RUN META QUERY : GET ALL POST META WITH VALUE 0 FOR UNCHECKED QUIZ, THE KEY IS THE USERID
			$members_unchecked_quiz = $wpdb->get_results( $wpdb->prepare("select meta_key from {$wpdb->postmeta} where meta_value = '0' && post_id = %d",$c), ARRAY_A);

			if(count($members_unchecked_quiz)){
				echo '<ul class="quiz_students">';
				foreach($members_unchecked_quiz as $unchecked_quiz ){
					$member_id=$unchecked_quiz['meta_key'];
					$bp_name = bp_core_get_userlink( $member_id );
			    	$bp_location = bp_get_profile_field_data('field=Location&user_id='.$member_id);
					echo '<li id="s'.$member_id.'">';
			    	echo get_avatar($member_id);
			    	echo '<h6>'. $bp_name . '</h6>';
				    if ($bp_location) {
				    	echo '<span>'. $bp_location . '</span>';
				    }
				    // PENDING AJAX SUBMISSIONS
				    echo '<ul> 
				    		<li><a class="tip reset_quiz_user" data-quiz="'.$c.'" data-user="'.$member_id.'" title="'.__('Reset Quiz for User','qm').'"><i class="icon-reload"></i></a></li>
				    		<li><a class="tip evaluate_quiz_user" data-quiz="'.$c.'" data-user="'.$member_id.'" title="'.__('Evaluate Quiz for User','qm').'"><i class="icon-check-clipboard-1"></i></a></li>
				    	  </ul>';
				    echo '</li>';
				}
				echo '</ul>';
				
			}
		}
	}
}
wp_nonce_field('qm_quiz','qsecurity');
echo '</div>';

echo '<div class="submissions"><h4 class="minmax">';
_e('STUDY SUBMISSIONS');
echo '<i class="icon-plus-1"></i></h4>';
// ALL MEMBERS who SUBMITTED STUDY
$members_submit_study = $wpdb->get_results( "select meta_key from $wpdb->postmeta where meta_value = '2' && post_id = $study_id", ARRAY_A);
if(count($members_submit_study)){
	echo '<ul class="study_students">';
	foreach($members_submit_study as $submit_study ){

		$member_id=$submit_study['meta_key'];

		$bp_name = bp_core_get_userlink( $member_id );
    	$bp_location = bp_get_profile_field_data('field=Location&user_id='.$member_id);

		echo '<li id="s'.$member_id.'">';
    	echo get_avatar($member_id);
    	echo '<h6>'. $bp_name . '</h6>';
	    if ($bp_location) {
	    	echo '<span>'. $bp_location . '</span>';
	    }
	    // PENDING AJAX SUBMISSIONS
	    echo '<ul> 
	    		<li><a class="tip evaluate_study_user" data-study="'.$study_id.'" data-user="'.$member_id.'" title="'.__('Evaluate Study for User','qm').'"><i class="icon-check-clipboard-1"></i></a></li>
	    	  </ul>';
	    echo '</li>';
	}
	echo '</ul>';
	wp_nonce_field($study_id,'security');
}
echo '</div>';
}else{
	if(isset($_GET['stats'])){


		$study_id=get_the_ID();
		$students=get_post_meta($study_id,'qm_students',true);

		$avg=get_post_meta($study_id,'average',true);
		$pass=get_post_meta($study_id,'pass',true);
		$badge=get_post_meta($study_id,'badge',true);


		echo '<div class="study_grade">
				<ul>
					<li>'.__('Total Number of Students who took this study','qm').' <strong>'.$students.'</strong></li>
					<li>'.__('Average Percentage obtained by Students','qm').' <strong>'.$avg.' <span>out of 100</span></strong></li>
					<li>'.__('Number of Students who got a Badge','qm').' <strong>'.$badge.'</strong></li>
					<li>'.__('Number of Passed Students','qm').' <strong>'.$pass.'</strong></li>
					<li>'.__('Number of Students who did not pass ','qm').' <strong>'.($students-$pass).'</strong></li>
				</ul>
			</div>';
		echo '<div id="average">'.__('Average Marks obtained by Students','qm').'<input type="text" class="dial" data-max="100" value="'.$avg.'"></div>';
		echo '<div id="pass">'.__('Number of Passed Students','qm').' <input type="text" class="dial" data-max="'.$students.'" value="'.$pass.'"></div>';	
		echo '<div id="badge">'.__('Number of Students who got a Badge','qm').'<input type="text" class="dial" data-max="'.$students.'" value="'.$badge.'"></div>';

		
		
		
		$curriculum=qm_sanitize(get_post_meta(get_the_ID(),'qm_study_curriculum',false));
		foreach($curriculum as $c){
			if(is_numeric($c)){
				if(get_post_type($c) == 'quiz'){
					$qavg=get_post_meta($c,'average',true);

					$ques = qm_sanitize(get_post_meta($c,'qm_quiz_questions',false));
					$qmax= array_sum($ques['marks']);

					echo '<div class="study_quiz">
							<h5>'.__('Average Marks in Quiz ','qm').' '.get_the_title($c).'</h5>
							<input type="text" class="dial" data-max="'.$qmax.'" value="'.$qavg.'">
						</div>';			
				}
			}
		}
		

		echo '<div class="calculate_panel"><strong>'.__('Calculate :','qm').'</strong>';
			echo '<a href="#" id="calculate_avg_study" data-studyid="'.get_the_ID().'" class="tip" title="'.__('Calculate Statistics for Study','qm').'"> <i class="icon-calculator"></i> </a>';
			wp_nonce_field('qm_security','security'); // Just random text to verify
		echo '</div>';

	}else{

	global $post;
	$students=get_post_meta(get_the_ID(),'qm_students',true);
?>	
	<h4 class="total_students"><?php _e('Total number of Students in study','qm'); ?><span><?php echo $students; ?></span></h4>
	<h3><?php _e('Students Currently taking this study','qm'); ?></h3>
	<?php

	$students_undertaking=bp_study_get_students_undertaking();
	//qm_sanitize(get_post_meta(get_the_ID(),'qm_students_undertaking',false));
	if(count($students_undertaking)>0){
		echo '<ul class="study_students">';
		foreach($students_undertaking as $student){

			if (function_exists('bp_get_profile_field_data')) {
			    $bp_name = bp_core_get_userlink( $student );
			    $bp_location = bp_get_profile_field_data('field=Location&user_id='.$student);
			    
			    if ($bp_name) {
			    	echo '<li id="s'.$student.'"><input type="checkbox" class="member" value="'.$student.'"/>';
			    	echo get_avatar($student);
			    	echo '<h6>'. $bp_name . '</h6>';
				    if ($bp_location) {
				    	echo '<span>'. $bp_location . '</span>';
				    }
				    // PENDING AJAX SUBMISSIONS
				    echo '<ul> 
				    		<li><a class="tip reset_study_user" data-study="'.get_the_ID().'" data-user="'.$student.'" title="'.__('Reset Study for User','qm').'"><i class="icon-reload"></i></a></li>
				    		<li><a class="tip study_stats_user" data-study="'.get_the_ID().'" data-user="'.$student.'" title="'.__('See Study stats for User','qm').'"><i class="icon-bars"></i></a></li>
				    		<li><a class="tip remove_user_study" data-study="'.get_the_ID().'" data-user="'.$student.'" title="'.__('Remove User from this Study','qm').'"><i class="icon-x"></i></a></li>
				    	  </ul>';
				    echo '</li>';
			    }
			}
		}
		echo '</ul>';
		wp_nonce_field('qm_security','security'); // Just random text to verify

		echo '<div class="study_bulk_actions">
				<strong>BULK ACTIONS</strong> 
				 <a href="#" class="send_study_message"><i class="icon-letter-mail-1"></i> Message</a>
					<div class="study_message">
						<input type="text" id="bulk_subject" class="form_field" placeholder="Type Message Subject">
						<textarea id="bulk_message" placeholder="Type Message"></textarea>
				 		<a href="#" id="send_study_message" class="button full">Send Message</a>
				 		<input type="hidden" id="sender" value="'.$user_id.'" />
				 	</div>
			</div>';
			wp_nonce_field('security','bulk_message');
	}
  }
}

?>