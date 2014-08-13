<?php

/***
 * You can hook in ajax functions in WordPress/BuddyPress by using the 'wp_ajax' action.
 * 
 * When you post your ajax call from javascript using jQuery, you can define the action
 * which will determin which function to run in your PHP component code.
 *
 * Here's an study:
 *
 * In Javascript we can post an action with some parameters via jQuery:
 * 
 * 			jQuery.post( ajaxurl, {
 *				action: 'my_study_action',
 *				'cookie': encodeURIComponent(document.cookie),
 *				'parameter_1': 'some_value'
 *			}, function(response) { ... } );
 *
 * Notice the action 'my_study_action', this is the part that will hook into the wp_ajax action.
 * 
 * You will need to add an add_action( 'wp_ajax_my_study_action', 'the_function_to_run' ); so that
 * your function will run when this action is fired.
 * 
 * You'll be able to access any of the parameters passed using the $_POST variable.
 *
 * Below is an study of the addremove_friend AJAX action in the friends component.
 */



add_action('wp_ajax_complete_unit_study', 'complete_unit_study');

function complete_unit_study(){
  $unit_id= $_POST['id'];
  $study_id = $_POST['study_id'];
  if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'security') ){
     _e('Security check Failed. Contact Administrator.','qm');
     die();
  }

  // Check if user has taken the study
  $user_id = get_current_user_id();
  $studytaken=get_user_meta($user_id,$study_id,true);
  if(isset($studytaken) && $studytaken){
    $nextunit_access = qm_get_option('nextunit_access');

    if(isset($nextunit_access) && $nextunit_access){ // Enable Next unit access
      if(add_user_meta($user_id,$unit_id,time())){
         $curriculum=bp_study_get_curriculum_units($study_id);
         $key = array_search($unit_id,$curriculum);
         if($key <=(count($curriculum)-1) ){  // Check if not the last unit
          $key++;
          echo $curriculum[$key];
         }
      }
    }else{
      add_user_meta($user_id,$unit_id,time());
    }
    
    $activity_id=bp_study_record_activity(array(
      'action' => __('Student finished unit ','qm').get_the_title($unit_id),
      'content' => __('Student finished the unit ','qm').get_the_title($unit_id).__(' in study ','qm').get_the_title($study_id),
      'type' => 'unit_complete',
      'primary_link' => get_permalink($unit_id),
      'item_id' => $unit_id,
      'secondary_item_id' => $user_id
    ));
    bp_study_record_activity_meta(array(
      'id' => $activity_id,
      'meta_key' => 'instructor',
      'meta_value' => get_post_field( 'post_author', $unit_id )
      ));
  }

die();
}

add_action('wp_ajax_reset_question_answer_study', 'reset_question_answer_study');
function reset_question_answer_study(){
  global $wpdb;
  $ques_id = $_POST['ques_id'];
  if(isset($ques_id) && $_POST['security'] && wp_verify_nonce($_POST['security'],'security'.$ques_id)){
    $user_id = get_current_user_id();
    $wpdb->query($wpdb->prepare("UPDATE $wpdb->comments SET comment_approved='trash' WHERE comment_post_ID=%d AND user_id=%d",$ques_id,$user_id));
    echo __('Answer Reset','qm');
  }else
    echo __('Unable to Reset','qm');

  die();
}


add_action( 'wp_ajax_calculate_stats_study', 'calculate_stats_study' ); // RESETS QUIZ FOR USER
function calculate_stats_study(){
	$study_id=$_POST['id'];
	$flag=0;
	if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'qm_security') ){
        echo '<p>'.__('Security check failed !','qm').'</p>';
        die();
    }

    if ( !isset($study_id) || !$study_id){
    	echo '<p>'.__('Incorrect Study selected.','qm').'</p>';
        die();
    }
    $badge=$pass=$total_qmarks=$gross_qmarks=0;
    $users=array();
	global $wpdb;

	$badge_val=get_post_meta($study_id,'qm_study_badge_percentage',true);
	$pass_val=get_post_meta($study_id,'qm_study_passing_percentage',true);

	$members_study_grade = $wpdb->get_results( $wpdb->prepare("select meta_value,meta_key from {$wpdb->postmeta} where post_id = %d",$study_id), ARRAY_A);


	if(count($members_study_grade)){
		foreach($members_study_grade as $meta){
			if(is_numeric($meta['meta_key']) && $meta['meta_value'] > 2){

       
						if($meta['meta_value'] > $pass_val)
							$badge++;

						if($meta['meta_value'] > $badge_val)
							$pass++;

						$users[]=$meta['meta_key'];
					}
			}  // META KEY is NUMERIC ONLY FOR USERIDS
	}

	if($pass)
		update_post_meta($study_id,'pass',$pass);


	if($badge)
		update_post_meta($study_id,'badge',$badge);

	

if($flag !=1){
	$curriculum=qm_sanitize(get_post_meta($study_id,'qm_study_curriculum',false));
		foreach($curriculum as $c){
			if(is_numeric($c)){

				if(get_post_type($c) == 'quiz'){
          $i=$qmarks=0;

					foreach($users as $user){
						$k=get_post_meta($c,$user,true);
						$qmarks +=$k;
            $i++;
						$gross_qmarks +=$k;
					}
          if($i==0)$i=1;
					
          $qavg=$qmarks/$i;

					if($qavg)
						update_post_meta($c,'average',$qavg);
					else{
						$flag=1;
						break;
					}
				}
			}
	}
	
	$cmarks=$i=0;
foreach($users as $user){
    $k=get_post_meta($study_id,$user,true);
    if($k > 2 && $k<101){
      $cmarks += $k;
      $i++;
    }
}
if($i==0)$i=1;
	$avg = round(($cmarks/$i));

  

	if($avg && $flag !=1){
		update_post_meta($study_id,'average',$avg);
	}else{
		$flag=1;
	}
}

	if(!$flag){
		echo '<p>'.__('Statistics successfully calculated. Reloading...','qm').'</p>';
	}else{
		echo '<p>'.__('Unable to calculate Average.','qm').'</p>';
	}

	die();
}

add_action( 'wp_ajax_study_stats_user', 'study_stats_user' ); // RESETS QUIZ FOR USER
function study_stats_user(){
	$study_id = $_POST['id'];
    $user_id = $_POST['user'];

    echo '<a class="show_side link right" data-side=".study_stats_user">'.__('SHOW STATS','qm').'</a><div class="study_stats_user"><a class="hide_parent link right">'.__('HIDE','qm').'</a>';

    if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'qm_security') ){
        echo '<div id="message" class="info notice"><p>'.__('Security check failed !','qm').'</p></div>';
        die();
    }

    if ( !isset($user_id) || !$user_id){
    	echo '<div id="message" class="info notice"><p>'.__('Incorrect User selected.','qm').'</p></div>';
        die();
    }


    $start=get_user_meta($user_id,$study_id,true);
	
	$being=get_post_meta($study_id,$user_id,true);

	if(isset($being) && $being !=''){
		if(!$being){
			echo '<p>'.__('This User has not started the study.','qm').'</p>';
		}else if($being > 2 && $being < 100){
			echo '<p>'.__('This User has completed the study.','qm').'</p>';
			echo '<h4>'.__('Student Score for Study ','qm').' : <strong>'.$being.__(' out of 100','qm').'</strong></h4>';

      $study_curriculum=qm_sanitize(get_post_meta($study_id,'qm_study_curriculum',false));
      $complete=$total=count($study_curriculum);

		}else{
			$total=0;
			$complete=0;

			echo '<h6>';
			_e('Study Started : ');
			echo '<span>'.tofriendlytime((time()-$start)).__(' ago','qm').'</span></h6>';

			$study_curriculum=qm_sanitize(get_post_meta($study_id,'qm_study_curriculum',false));

			$curriculum = '<div class="curriculum_check"><h6>'.__('Curriculum :','qm').'</h6><ul>';
			$quiz ='<h5>'.__('Quizes','qm').'</h5>';
			foreach($study_curriculum as $c){
				if(is_numeric($c)){
					$total++;
					$check=get_user_meta($user_id,$c,true);
					if(isset($check) && $check !=''){
						$complete++;
						if(get_post_type($c) == 'quiz'){
							$marks = get_post_meta($c,$user_id,true);

							$curriculum .= '<li><span class="done"></span> '.get_the_title($c).' <strong>'.(($marks)?__('Marks Obtained : ','qm').$marks:__('Under Evaluation','qm')).'</strong></li>';
						}else
							$curriculum .= '<li><span class="done"></span> '.get_the_title($c).'</li>';

					}else{
						$curriculum .= '<li><span></span> '.get_the_title($c).'</li>';
					}
				}else{
					$curriculum .= '<li><h5>'.$c.'</h5></li>';
				}
			}
			$curriculum .= '</ul></div>';
		}
	}

	echo '<strong>'.__('Units Completed ').$complete.__(' out of ','qm').$total.'</strong>';
	echo '<div class="complete_study"><input type="text" class="dial" data-max="'.$total.'" value="'.$complete.'"></div>';
	echo $curriculum;
    echo '</div>';
	die();
}


add_action( 'wp_ajax_remove_user_study', 'remove_user_study' ); // RESETS QUIZ FOR USER
function remove_user_study(){
	$study_id = $_POST['id'];
    $user_id = $_POST['user'];

    if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'qm_security') ){
        echo '<p>'.__('Security check failed !','qm').'</p>';
        die();
    }

    if ( !isset($user_id) || !$user_id){
        echo '<p>'.__(' Incorrect User selected.','qm').'</p>';
        die();
    }

    if(delete_user_meta($user_id,$study_id)){
			delete_post_meta($study_id,$user_id);
			echo '<p>'.__('User removed from the Study','qm').'</p>';

      bp_study_record_activity(array(
      'action' => __('Student ','qm').bp_core_get_userlink($user_id).__(' removed from study ','qm').get_the_title($study_id),
      'content' => __('Student ','qm').bp_core_get_userlink($user_id).__(' removed from the study ','qm').get_the_title($study_id),
      'type' => 'remove_from_study',
      'primary_link' => get_permalink($study_id),
      'item_id' => $study_id,
      'secondary_item_id' => $user_id
    ));

	}else{
		echo '<p>'.__('There was issue in removing this user from the Study. Please contact admin.','qm').'</p>';
	}
	die();
}


add_action( 'wp_ajax_reset_study_user', 'reset_study_user' ); // RESETS STUDY FOR USER
function reset_study_user(){
	$study_id = $_POST['id'];
    $user_id = $_POST['user'];

    if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'qm_security') ){
        echo '<p>'.__('Security check failed !','qm').'</p>';
        die();
    }

    if ( !isset($user_id) || !$user_id){
        echo '<p>'.__(' Incorrect User selected.','qm').'</p>';
        die();
    }
      
      //delete_user_meta($user_id,$study_id) // DELETE ONLY IF USER SUBSCRIPTION EXPIRED
    $status = get_post_meta($study_id,$user_id,true);
    
    if(isset($status) && $status){  // Necessary for continue study

      do_action('quantipress_student_study_reset');
		  update_post_meta($study_id,$user_id,0);  	 
			$study_curriculum=qm_sanitize(get_post_meta($study_id,'qm_study_curriculum',false));

			foreach($study_curriculum as $c){
				if(is_numeric($c)){
					delete_user_meta($user_id,$c);
					
					if(get_post_type($c) == 'quiz'){
						delete_post_meta($c,$user_id);
						$questions = qm_sanitize(get_post_meta($c,'qm_quiz_questions',false));
            if(isset($questions) && is_array($questions) && is_Array($questions['ques']))
				      	foreach($questions['ques'] as $question){
				        	global $wpdb;
                  if(isset($question) && $question !='' && is_numeric($question))
				        	$wpdb->query($wpdb->prepare("UPDATE $wpdb->comments SET comment_approved='trash' WHERE comment_post_ID=%d AND user_id=%d",$question,$user_id));
				      	}
					}
				}
			}
      /*=== Fix in 1.5 : Reset  Badges and CErtificates on Study Reset === */
      $user_badges=qm_sanitize(get_user_meta($user_id,'badges',false));
      $user_certifications=qm_sanitize(get_user_meta($user_id,'certificates',false));

      if(isset($user_badges) && is_Array($user_badges) && in_array($study_id,$user_badges)){
          $key=array_search($study_id,$user_badges);
          unset($user_badges[$key]);
          $user_badges = array_values($user_badges);
          update_user_meta($user_id,'badges',$user_badges);
      }
      if(isset($user_certifications) && is_Array($user_certifications) && in_array($study_id,$user_certifications)){
          $key=array_search($study_id,$user_certifications);
          unset($user_certifications[$key]);
          $user_certifications = array_values($user_certifications);
          update_user_meta($user_id,'certificates',$user_certifications);
      }
      /*==== End Fix ======*/

			echo '<p>'.__('Study Reset for User','qm').'</p>';
      bp_study_record_activity(array(
      'action' => __('Study reset for student ','qm'),
      'content' => __('Study ','qm').get_the_title($study_id).__(' reset for student ','qm').bp_core_get_userlink($user_id),
      'type' => 'reset_study',
      'primary_link' => get_permalink($study_id),
      'item_id' => $study_id,
      'secondary_item_id' => $user_id
    ));
	}else{
		echo '<p>'.__('There was issue in resetting this study for the user. Please contact admin.','qm').'</p>';
	}
	die();
}

add_action( 'wp_ajax_reset_quiz_study', 'reset_quiz_study' ); // RESETS QUIZ FOR USER
function reset_quiz_study(){

    $quiz_id = $_POST['id'];
    $user_id = $_POST['user'];

     if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'qm_quiz') ){
        echo '<p>'.__('Security check failed !','qm').'</p>';
        die();
    }

    if ( !isset($user_id) || !$user_id){
        echo '<p>'.__(' Incorrect User selected.','qm').'</p>';
        die();
    }

    if(delete_user_meta($user_id,$quiz_id)){

      delete_post_meta($quiz_id,$user_id); // Optional validates that user can retake the quiz

      $questions = qm_sanitize(get_post_meta($quiz_id,'qm_quiz_questions',false));
      foreach($questions['ques'] as $question){
        global $wpdb;
        $wpdb->query($wpdb->prepare("UPDATE $wpdb->comments SET comment_approved='trash' WHERE comment_post_ID=%d AND user_id=%d",$question,$user_id));
      }
      echo '<p>'.__('Quiz Reset for Selected User','qm').'</p>';
    }else{
      echo '<p>'.__('Could not find Quiz results for User. Contact Admin.','qm').'</p>';
    }
	
    bp_study_record_activity(array(
      'action' => __('Instructor Reseted the Quiz for User','qm'),
      'content' => __('Quiz ','qm').get_the_title($quiz_id).__(' was reset by the Instructor for user','qm').bp_core_get_userlink( $user_id ),
      'type' => 'reset_quiz_study',
      'primary_link' => get_permalink($quiz_id),
      'item_id' => $quiz_id,
      'secondary_item_id' => $user_id
      ));
    die();
}


add_action( 'wp_ajax_give_marks_study', 'give_marks_study' ); // RESETS QUIZ FOR USER
function give_marks_study(){
    $answer_id=intval($_POST['aid']);
    $value=intval($_POST['aval']);
    
    if(is_numeric($answer_id) && is_numeric($value))
      update_comment_meta( $answer_id, 'marks',$value);

    die();
}

add_action( 'wp_ajax_complete_study_marks', 'complete_study_marks' ); // STUDY MARKS FOR USER
function complete_study_marks(){
    $user_id=intval($_POST['user']);
    $study_id=intval($_POST['study']);
    $marks=intval($_POST['marks']);

    $badge_per = get_post_meta($study_id,'qm_study_badge_percentage',true);
    $passing_per = get_post_meta($study_id,'qm_study_passing_percentage',true);

    if(isset($badge_per) && $badge_per && $marks > $badge_per){
        $badges = array();
        $badges= qm_sanitize(get_user_meta($user_id,'badges',false));

        if(!in_array($study_id,$badges)){
            $badges[]=$study_id;
            update_user_meta($user_id,'badges',$badges);
            bp_study_record_activity(array(
              'action' => __('Student got a Badge in the study ','qm'),
              'content' => __('Student ','qm').bp_core_get_userlink($user_id).__(' got a badge in the study ','qm').get_the_title($study_id),
              'type' => 'student_badge',
              'item_id' => $study_id,
              'primary_link'=>get_permalink($study_id),
              'secondary_item_id'=>$user_id
            )); 
        }
    }

    if(isset($passing_per) && $passing_per && $marks > $passing_per){
        $pass = array();
        $pass=qm_sanitize(get_user_meta($user_id,'certificates',false));
        if(!in_array($study_id,$pass)){
          $pass[]=$study_id;
          update_user_meta($user_id,'certificates',$pass);

          bp_study_record_activity(array(
            'action' => __('Student got a Certificate in the study ','qm'),
            'content' => __('Student ','qm').bp_core_get_userlink($user_id).__(' got a certificate in the study ','qm').get_the_title($study_id),
            'type' => 'student_certificate',
            'item_id' => $study_id,
            'primary_link'=>get_permalink($study_id),
            'secondary_item_id'=>$user_id
          )); 
        }
    }
    if(update_post_meta( $study_id,$user_id,$marks)){
      $message = __('You\'ve obtained ','qm').$marks.__(' out of 100 in Study :','qm').' <a href="'.get_permalink($study_id).'">'.get_the_title($study_id).'</a>';
      messages_new_message( array('sender_id' => get_current_user_id(), 'subject' => __('Study results available','qm'), 'content' => $message,   'recipients' => $user_id ) );
      echo __('STUDY MARKED COMPLETE','qm');
    }else{
      echo __('FAILED TO MARK STUDY, CONTACT ADMIN','qm');
    }

    $activity_id=bp_study_record_activity(array(
      'action' => __('Instructor evaluated Study for Student','qm'),
      'content' => __('Student ','qm').bp_core_get_userlink( $user_id ).__(' got ','qm').$marks.__(' out of 100 in study ','qm').get_the_title($study_id),
      'primary_link' => get_permalink($study_id),
      'type' => 'study_evaluated',
      'item_id' => $study_id,
      ));
    bp_study_record_activity_meta(array(
            'id' => $activity_id,
            'meta_key' => 'instructor',
            'meta_value' => get_post_field( 'post_author', $study_id )
            ));

    die();
}



add_action( 'wp_ajax_save_quiz_marks_study', 'save_quiz_marks_study' ); // RESETS QUIZ FOR USER
function save_quiz_marks_study(){
    $quiz_id=intval($_POST['quiz_id']);
    $user_id=intval($_POST['user_id']);
    $marks=intval($_POST['marks']);

    $ques = qm_sanitize(get_post_meta($quiz_id,'qm_quiz_questions',false));
    $max= array_sum($ques['marks']);

    
    update_post_meta( $quiz_id, $user_id,$marks);
    
    $message = __('You\'ve obtained ','qm').$marks.__(' out of ','qm').$max.__(' in Quiz','qm').' : <a href="'.trailingslashit( bp_core_get_user_domain( $user_id )) . BP_STUDY_SLUG. '/study-results/?action='.$quiz_id .'">'.get_the_title($quiz_id).'</a>';
    messages_new_message( array('sender_id' => get_current_user_id(), 'subject' => __('Quiz results available','qm'), 'content' => $message,   'recipients' => $user_id ) );
    
    $activity_id=bp_study_record_activity(array(
      'action' => __('Instructor evaluated Quiz for student ','qm'),
      'type' => 'quiz_evaluated',
      'content' => __('Student ','qm').bp_core_get_userlink( $user_id ).__(' got ','qm').$marks.__(' out of ','qm').$max.__(' in Quiz ','qm').get_the_title($study_id),
      'primary_link' => trailingslashit( bp_core_get_user_domain( $user_id ) . bp_get_study_slug()) . 'study-results/?action='.$quiz_id ,
      'item_id' => $quiz_id,
      ));
    bp_study_record_activity_meta(array(
      'id' => $activity_id,
      'meta_key' => 'instructor',
      'meta_value' => get_post_field( 'post_author', $quiz_id )
    ));
    die();
}

add_action( 'wp_ajax_evaluate_study', 'evaluate_study' ); // RESETS QUIZ FOR USER
function evaluate_study(){
    
    $study_id=intval($_POST['id']);
    $user_id=intval($_POST['user']);

    if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],$study_id) ){
        echo '<p>'.__('Security check failed !','qm').'</p>';
        die();
    }

    if ( !isset($user_id) || !$user_id || !is_numeric($user_id)){
        echo '<p>'.__(' Incorrect User selected.','qm').'</p>';
        die();
    }
    $sum=$max_sum=0;
    $curriculum=qm_sanitize(get_post_meta($study_id,'qm_study_curriculum',false));
     echo '<ul class="study_curriculum">';
    foreach($curriculum as $c){
      if(is_numeric($c)){
        if(get_post_type($c) == 'quiz'){
            $status = get_user_meta($user_id,$c,true);
            $marks=get_post_meta($c,$user_id,true);
            $sum +=$marks;

            $qmax=qm_sanitize(get_post_meta($c,'qm_quiz_questions',false));

            $max=array_sum($qmax['marks']);
            $max_sum +=$max;
            echo '<li>
                  <strong>'.get_the_title($c).' <span>'.((isset($status) && $status !='')?__('MARKS: ','qm').$marks.__(' out of ','qm').$max:__(' PENDING','qm')).'</span></strong>
                  </li>';
        }else{
            $status = get_user_meta($user_id,$c,true);
            echo '<li>
                  <strong>'.get_the_title($c).' <span>'.((isset($status) && $status !='')?'<i class="icon-check"></i> '.__('DONE','qm'):'<i class="icon-alarm-1"></i>'.__(' PENDING','qm')).'</span></strong>
                  </li>';
        } 
      }else{

      }
    }     
    do_action('quantipress_study_manual_evaluation',$study_id,$user_id);
    echo '</ul>';
    echo '<div id="total_marks">'.__('Total','qm').' <strong><span>'.apply_filters('quantipress_study_student_marks',$sum,$study_id,$user_id).'</span> / '.apply_filters('quantipress_study_maximum_marks',$max_sum,$study_id,$user_id).'</strong> </div>';
    echo '<div id="study_marks">'.__('Study Percentage (Out of 100)','qm').' <strong><span><input type="number" name="study_marks" id="study_marks_field" class="form_field" value="0" placegolder="'.__('Study Percentage out of 100','qm').'" /></span></div>';
    echo '<a href="#" id="study_complete" class="button full" data-study="'.$study_id.'" data-user="'.$user_id.'">'.__('Mark Study Complete','qm').'</a>';
  die();
}


add_action( 'wp_ajax_evaluate_quiz_study', 'evaluate_quiz_study' ); // RESETS QUIZ FOR USER
function evaluate_quiz_study(){

    $quiz_id=intval($_POST['id']);
    $user_id=intval($_POST['user']);

    if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'qm_quiz') ){
       echo '<p>'.__('Security check failed !','qm').'</p>';
        die();
    }

    if ( !isset($user_id) || !$user_id){
         echo '<p>'.__(' Incorrect User selected.','qm').'</p>';
        die();
    }

    if(get_post_type($quiz_id) != 'quiz'){
      echo '<p>'.__(' Incorrect Quiz Id.','qm').'</p>';
        die();
    }

  $questions = qm_sanitize(get_post_meta($quiz_id,'qm_quiz_questions',false));
  
  if(count($questions)):

    echo '<ul class="quiz_questions">';
    $sum=$max_sum=0;
    foreach($questions['ques'] as $key=>$question){
      if(isset($question) && $question){
      $q=get_post($question);
      echo '<li>
          <div class="q">'.apply_filters('the_content',$q->post_content).'</div>';
      $comments_query = new WP_Comment_Query;
      $comments = $comments_query->query( array('post_id'=> $question,'user_id'=>$user_id,'number'=>1,'status'=>'approve') );   
      echo '<strong>';
      _e('Marked Answer :','qm');
      echo '</strong>';

      $correct_answer=get_post_meta($question,'qm_question_answer',true);


      foreach($comments as $comment){ // This loop runs only once
        $type = get_post_meta($question,'qm_question_type',true);

          switch($type){
            case 'single': 
              $options = qm_sanitize(get_post_meta($question,'qm_question_options',false));
              
              echo $options[(intval($comment->comment_content)-1)]; // Reseting for the array
              if(isset($correct_answer) && $correct_answer !=''){
                $ans=$options[(intval($correct_answer)-1)];

              }
            break;  

            case 'multiple': 
              $options = qm_sanitize(get_post_meta($question,'qm_question_options',false));
              $ans=explode(',',$comment->comment_content);

              foreach($ans as $an){
                echo $options[intval($an)-1].' ';
              }

              $cans = explode(',',$correct_answer);
              $ans='';
              foreach($cans as $can){
                $ans .= $options[intval($can)-1].', ';
              }
            break;
            case 'sort': 
              $options = qm_sanitize(get_post_meta($question,'qm_question_options',false));
              $ans=explode(',',$comment->comment_content);

              foreach($ans as $an){
                echo $an.'. '.$options[intval($an)-1].' ';
              }

              $cans = explode(',',$correct_answer);
              $ans='';
              foreach($cans as $can){
                $ans .= $can.'. '.$options[intval($can)-1].', ';
              }
            break;
            case 'smalltext': 
                echo $comment->comment_content;
                $ans = $correct_answer;
            break;
            case 'largetext': 
                echo apply_filters('the_content',$comment->comment_content);
                $ans = $correct_answer;
            break;
        }
        $cid=$comment->comment_ID;
        $marks=get_comment_meta( $comment->comment_ID, 'marks', true );
      }

      if(isset($correct_answer) && $correct_answer !=''){
        echo '<strong>';
        _e('Correct Answer :','qm');
        echo '<span>'.$ans.'</span></strong>';


      }
      

    

      if(isset($marks) && $marks !=''){
          echo '<span class="marking">'.__('Marks Obtained','qm').' <input type="text" id="'.$cid.'" class="form_field small question_marks" value="'.$marks.'" placeholder="'.__('Give marks','qm').'" />
                <a href="#" class="give_marks_study button" data-ans-id="'.$cid.'">'.__('Update Marks','qm').'</a>';

          $sum = $sum+$marks;
      }else{
        echo '<span class="marking">'.__('Marks Obtained','qm').' <input type="text" id="'.$cid.'" class="form_field small question_marks" value="" placeholder="'.__('Give marks','qm').'" />
        <a href="#" class="give_marks_study button" data-ans-id="'.$cid.'">'.__('Give Marks','qm').'</a>';
      }
      $max_sum=$max_sum+intval($questions['marks'][$key]);
      echo '<span> Total Marks : '.$questions['marks'][$key].'</span>';
      echo '</li>';

      } // IF question check
    } 
    echo '</ul>';
    echo '<div id="total_marks">'.__('Total','qm').' <strong><span>'.$sum.'</span> / '.$max_sum.'</strong> </div>';
    echo '<a href="#" id="mark_complete" class="button full" data-quiz="'.$quiz_id.'" data-user="'.$user_id.'">'.__('Mark Quiz as Checked','qm').'</a>';
    endif;

    die();
}



add_action( 'wp_ajax_send_bulk_message_study', 'send_bulk_message_study' );
function send_bulk_message_study(){

    $study_id=$_POST['study'];
    if ( isset($_POST['security']) && wp_verify_nonce($_POST['security'],'security'.$study_id) ){
        echo 'Security check failed !';
        die();
    }
    $members = json_decode(stripslashes($_POST['members']));

    $sender = $_POST['sender'];
    $subject=stripslashes($_POST['subject']);
    if(!isset($subject)){
      _e('Set a Subject for the message','qm');
      die();  
    }
    $message=stripslashes($_POST['message']);
    if(!isset($message)){
      _e('Set a Subject for the message','qm');
      die();  
    }
    $sent=0;
    if(count($members) > 0){
      foreach($members as $member){
          if( messages_new_message( array('sender_id' => $sender, 'subject' => $subject, 'content' => $message,   'recipients' => $member ) ) ){
            $sent++;
          }
      }
      echo __('Messages Sent to ','qm').$sent.__(' members','qm');
    }else{
      echo __('Please select members','qm');
    }

    bp_study_record_activity(array(
      'action' => __('Instructor sent Bulk message to students : ','qm').$subject,
      'content' => __('Bulk Message sent to students ','qm').$message,
      'type' => 'bulk_action',
      'item_id' => $study_id,
      ));

    die();
}


add_action( 'wp_ajax_add_bulk_students_study', 'add_bulk_students_study' );
function add_bulk_students_study(){
    
    $study_id=$_POST['study'];
    if ( isset($_POST['security']) && wp_verify_nonce($_POST['security'],'security'.$study_id) ){
        echo 'Security check failed !';
        die();
    }

    $members = stripslashes($_POST['members']);
    if(strpos($members,',')){
      $members=explode(',',$members);
      foreach($members as $member){
        $user_id=bp_core_get_userid_from_nicename($member);
        if($user_id){
          if(update_post_meta($study_id,$user_id,0)){ // Move forward only if update is successful
           $study_duration = get_post_meta($study_id,'qm_duraiton',true);
           $duration = time() + $study_duration*86400;
            if(update_user_meta($user_id,$study_id,$duration)){ // Move forward only if update is successful
                $group_id=get_post_meta($study_id,'qm_group',true);
                if(isset($group_id) && $group_id !='')
                  groups_join_group($group_id, $user_id );  

                bp_study_record_activity(array(
                      'action' => __('Instructor added Student for study ','qm').get_the_title($study_id),
                      'content' => __('Instructore added Student ','qm').bp_core_get_userlink( $user_id ).__(' subscribed for study ','qm').get_the_title($study_id),
                      'type' => 'subscribe_study',
                      'item_id' => $study_id,
                      'primary_link'=>get_permalink($study_id),
                      'secondary_item_id'=>$user_id
                    ));      
                $field = qm_get_option('student_field');
                if(!isset($field) || !$field) $field = 'Location';

                echo '<li id="s'.$user_id.'">
                <input type="checkbox" class="member" value="'.$user_id.'">
                '.bp_core_fetch_avatar ( array( 'item_id' => $user_id, 'type' => 'full' ) ).'
                <h6>'.bp_core_get_userlink( $user_id ).'</h6><span>'.(function_exists('xprofile_get_field_data')?xprofile_get_field_data( $field, $user_id ):'').'</span><ul> 
                <li><a class="tip reset_study_user" data-study="'.$study_id.'" data-user="'.$user_id.'" title="" data-original-title="'.__('Reset Study for User','qm').'"><i class="icon-reload"></i></a></li>
                <li><a class="tip study_stats_user" data-study="'.$study_id.'" data-user="'.$user_id.'" title="" data-original-title="'.__('See Study stats for User','qm').'"><i class="icon-bars"></i></a></li>
                <li><a class="tip remove_user_study" data-study="'.$study_id.'" data-user="'.$user_id.'" title="" data-original-title="'.__('Remove User from this Study','qm').'"><i class="icon-x"></i></a></li>
                </ul></li>'; 
            } 
          }
        }

      }
    }else{ // Same Code as above, just assuming that there are no commas in the entry : re-check for better
        $user_id=bp_core_get_userid_from_nicename($members); 
        if($user_id){
          if(update_post_meta($study_id,$user_id,0)){ // Move forward only if update is successful
           $study_duration = get_post_meta($study_id,'qm_duration',true);
           $duration = time() + $study_duration*86400;
            if(update_user_meta($user_id,$study_id,$duration)){ // Move forward only if update is successful
                $group_id=get_post_meta($study_id,'qm_group',true);
                if(isset($group_id) && $group_id !='')
                  groups_join_group($group_id, $user_id );  

                bp_study_record_activity(array(
                      'action' => __('Instructor added Student for study ','qm').get_the_title($study_id),
                      'content' => __('Instructore added Student ','qm').bp_core_get_userlink( $user_id ).__(' subscribed for study ','qm').get_the_title($study_id),
                      'type' => 'subscribe_study',
                      'item_id' => $study_id,
                      'primary_link'=>get_permalink($study_id),
                      'secondary_item_id'=>$user_id
                    ));  
                $field = qm_get_option('student_field');
                if(!isset($field) || !$field) $field = 'Location';

                echo '<li id="s'.$user_id.'">
                <input type="checkbox" class="member" value="'.$user_id.'">
                '.bp_core_fetch_avatar ( array( 'item_id' => $user_id, 'type' => 'full' ) ).'
                <h6>'.bp_core_get_userlink( $user_id ).'</h6><span>'.(function_exists('xprofile_get_field_data')?xprofile_get_field_data( $field, $user_id ):'').'</span><ul> 
                <li><a class="tip reset_study_user" data-study="'.$study_id.'" data-user="'.$user_id.'" title="" data-original-title="'.__('Reset Study for User','qm').'"><i class="icon-reload"></i></a></li>
                <li><a class="tip study_stats_user" data-study="'.$study_id.'" data-user="'.$user_id.'" title="" data-original-title="'.__('See Study stats for User','qm').'"><i class="icon-bars"></i></a></li>
                <li><a class="tip remove_user_study" data-study="'.$study_id.'" data-user="'.$user_id.'" title="" data-original-title="'.__('Remove User from this Study','qm').'"><i class="icon-x"></i></a></li>
                </ul></li>';        
            } 
          }
        }
    }


    bp_study_record_activity(array(
      'action' => __('Instructor added students in study  ','qm'),
      'content' => __('Instructor added ','qm').count($members).__(' students in study ','qm'),
      'type' => 'bulk_action',
      'item_id' => $study_id,
      ));

    die();
}

/*=== ASSIGN CERTIFICATES & BADGES to STUDENTS FROM FRONT END v 1.5.4 =====*/
add_action( 'wp_ajax_assign_badge_certificates_study', 'assign_badge_certificates_study' );
function assign_badge_certificates_study(){

    $study_id=$_POST['study'];

    if ( isset($_POST['security']) && wp_verify_nonce($_POST['security'],'security'.$study_id) ){
        echo 'Security check failed !';
        die();
    }
    $members = json_decode(stripslashes($_POST['members']));

    $assign_action = $_POST['assign_action'];
    if(!isset($assign_action) && !$assign_action){
      _e('Select Assign Value','qm');
      die();  
    }

    $assigned=0;
    if(count($members) > 0){
      foreach($members as $mkey=>$member){ 
          if(is_numeric($member) && get_post_type($study_id) == 'study'){

            switch($assign_action){
              case 'add_badge':
                $badges = qm_sanitize(get_user_meta($member,'badges',false));
                if(isset($badges) && is_array($badges)){
                  $badges[]=$study_id;
                }else{
                  $badges = array($study_id);
                }
                update_user_meta($member,'badges',$badges);
              break;
              case 'add_certificate':
                $certificates = qm_sanitize(get_user_meta($member,'certificates',false));
                if(isset($certificates) && is_array($certificates)){
                  $certificates[]=$study_id;
                }else{
                    $certificates = array($study_id);
                }
                update_user_meta($member,'certificates',$certificates);
              break;
              case 'remove_badge': 
                $badges = qm_sanitize(get_user_meta($member,'badges',false));
                $k=array_search($study_id,$badges);
                if(isset($k))
                  unset($badges[$k]);
                $badges = array_values($badges);
                update_user_meta($member,'badges',$badges);
              break;
              case 'remove_certificate':
                $certificates = qm_sanitize(get_user_meta($member,'certificates',false));
                $k=array_search($study_id,$certificates);
                if(isset($k))
                  unset($certificates[$k]);
                $certificates = array_values($certificates);
                update_user_meta($member,'certificates',$certificates);
              break;
            }
            
            
            $flag=1;
            $assigned++;
          }else{
            $flag=0;
            break;
          }
      }


      if($flag){
        echo __('Action assigned to ','qm').$assigned.__(' members','qm');
        bp_study_record_activity(array(
        'action' => __('Instructor assigned/removed Certificate/Badges  ','qm'),
        'content' => __('Instructor added/removed Badges/Certificates from ','qm').count($members).__(' students in study ','qm'),
        'type' => 'bulk_action',
        'item_id' => $study_id,
        ));
      }else
        echo __('Could not assign action to members','qm');

    }else{
      echo __('Please select members','qm');
    }

    die();
}


add_action('wp_ajax_unit_traverse_study', 'unit_traverse_study');
add_action( 'wp_ajax_nopriv_unit_traverse_study', 'unit_traverse_study' );

function unit_traverse_study(){
  $unit_id= $_POST['id'];
  $study_id = $_POST['study_id'];
  if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'security') ){
     _e('Security check Failed. Contact Administrator.','qm');
     die();
  }

  // Check if user has taken the study
  $user_id = get_current_user_id();
  $studytaken=get_user_meta($user_id,$study_id,true);
  if(isset($studytaken) && $studytaken){
      
      $study_curriculum=qm_sanitize(get_post_meta($study_id,'qm_study_curriculum',false));
      

        $units=array();
          foreach($study_curriculum as $key=>$curriculum){
            if(is_numeric($curriculum)){
                $units[]=$curriculum;
            }
          }

      // Drip Feed Check  
      //     
      $drip_enable=get_post_meta($study_id,'qm_study_drip',true);

      
      if(qm_validate($drip_enable)){

          $drip_duration = get_post_meta($study_id,'qm_study_drip_duration',true);
          
          $unitkey = array_search($unit_id,$units);

          if($unitkey == 0){
            $pre_unit_time=get_post_meta($units[$unitkey],$user_id,true);
            if(!isset($pre_unit_time) || $pre_unit_time ==''){
              add_post_meta($units[$unitkey],$user_id,time());
            }
          }else{
             $pre_unit_time=get_post_meta($units[($unitkey-1)],$user_id,true);

             if(isset($pre_unit_time) && $pre_unit_time){

              //echo (($pre_unit_time + ($unitkey)*86400) - time());

               if(($pre_unit_time + ($unitkey)*86400) > time()){
                      echo '<div class="message"><p>'.__('Unit will be available in ','qm').tofriendlytime(($pre_unit_time + ($unitkey)*86400)-time()).'</p></div>';
                      die();
                  }else{
                      $pre_unit_time=get_post_meta($units[$unitkey],$user_id,true);
                      if(!isset($pre_unit_time) || $pre_unit_time ==''){
                        add_post_meta($units[$unitkey],$user_id,time());

                        bp_study_record_activity(array(
                          'action' => __('Student started a unit','qm'),
                          'content' => __('Student started the unit ','qm').get_the_title($unit_id).__(' in study ','qm').get_the_title($study_id),
                          'type' => 'unit',
                          'primary_link' => get_permalink($unit_id),
                          'item_id' => $unit_id,
                          'secondary_item_id' => $user_id
                        ));
                      }
                  } 
              }else{
                  echo '<div class="message"><p>'.__('Unit can not be accessed.','qm').'</p></div>';
                  die();
              }    
            }
          }  
        
      

      // END Drip Feed Check  
      
      echo '<div id="unit" class="unit_title" data-unit="'.$unit_id.'">';
      the_unit_tags($unit_id);
      the_unit_instructor($unit_id);
      $minutes=0;
      $minutes = get_post_meta($unit_id,'qm_duration',true);
      if($minutes){
        if($minutes > 60){
          $hours = intval($minutes/60);
          $minutes = $minutes - $hours*60;
        }
      
      do_action('quantipress_study_unit_meta');
      
      echo '<span><i class="icon-clock"></i> '.(isset($hours)?$hours.__(' Hours','qm'):'').' '.$minutes.__(' minutes','qm').'</span>';
      }
      echo '<div class="clear"></div>';
      echo '<h1>'.get_the_title($unit_id).'</h1>';
      echo '<h3>';
        the_sub_title($unit_id);
      echo '</h3></div>';
      the_unit($unit_id);  
      
      
              $unit_class='unit_button';
              $hide_unit=0;
              $nextunit_access = qm_get_option('nextunit_access');
              

              $k=array_search($unit_id,$units);
              $done_flag=get_user_meta($user_id,$unit_id,true);

              $next=$k+1;
              $prev=$k-1;
              $max=count($units)-1;

              echo  '<div class="unit_prevnext"><div class="col-md-3">';
              if($prev >=0){

                if(get_post_type($units[$prev]) == 'quiz')
                  echo '<a href="'.get_permalink($units[$prev]).'" class=" '.$unit_class.'">'.__('Back to Quiz','qm').'</a>';
                else    
                  echo '<a href="#" id="prev_unit" data-unit="'.$units[$prev].'" class="unit unit_button">'.__('Previous Unit','qm').'</a>';
              }
              echo '</div>';

              echo  '<div class="col-md-6">';
              if(get_post_type($units[($k)]) == 'quiz')
                  echo '<a href="'.get_permalink($units[($k)]).'"class=" unit_button">'.__('Start Quiz','qm').'</a>';
                else  
                  echo ((isset($done_flag) && $done_flag)?'': apply_filters('quantipress_unit_mark_complete','<a href="#" id="mark-complete" data-unit="'.$units[($k)].'" class="unit_button">'.__('Mark this Unit Complete','qm').'</a>',$unit_id,$study_id));

              echo '</div>';

              echo  '<div class="col-md-3">';

              if($next <= $max){

                if(isset($nextunit_access) && $nextunit_access){
                    $hide_unit=1;

                    if(isset($done_flag) && $done_flag){
                      $unit_class .=' ';
                      $hide_unit=0;
                    }else{
                      $unit_class .=' hide';
                      $hide_unit=1;
                    }
                }

                if(get_post_type($units[$next]) == 'quiz')
                  echo '<a href="'.get_permalink($units[$next]).'" id="next_unit" class=" '.$unit_class.'">'.__('Proceed to Quiz','qm').'</a>';
                else  
                  echo '<a href="#" id="next_unit" '.(($hide_unit)?'':'data-unit="'.$units[$next].'"').' class="unit '.$unit_class.'">'.__('Next Unit','qm').'</a>';
              }
              echo '</div></div>';
          
        }
        die();
}  

?>