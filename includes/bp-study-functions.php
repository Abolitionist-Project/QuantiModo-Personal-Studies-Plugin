<?php

/**
 * The -functions.php file is a good place to store miscellaneous functions needed by your plugin.
 *
 * @package BuddyPress_Study_Component
 * @since 1.6
 */

/**
 * bp_study_load_template_filter()
 *
 * You can define a custom load template filter for your component. This will allow
 * you to store and load template files from your plugin directory.
 *
 * This will also allow users to override these templates in their active theme and
 * replace the ones that are stored in the plugin directory.
 *
 * If you're not interested in using template files, then you don't need this function.
 *
 * This will become clearer in the function bp_study_screen_one() when you want to load
 * a template file.
 */
function bp_study_load_template_filter( $found_template, $templates ) {
	global $bp;

	/**
	 * Only filter the template location when we're on the study component pages.
	 */
	if ( $bp->current_component != $bp->study->slug )
		return $found_template;

	foreach ( (array) $templates as $template ) {
		if ( file_exists( STYLESHEETPATH . '/' . $template ) )
			$filtered_templates[] = STYLESHEETPATH . '/' . $template;
		else
			$filtered_templates[] = dirname( __FILE__ ) . '/templates/' . $template;
	}

	$found_template = $filtered_templates[0];

	return apply_filters( 'bp_study_load_template_filter', $found_template );
}
add_filter( 'bp_located_template', 'bp_study_load_template_filter', 10, 2 );

function all_study_page_title(){
    echo '<h1>'.__('Study Directory','qm').'</h1>
          <h5>'.__('All Studies by all instructors','qm').'</h5>';
}

function bp_user_can_create_study() { 
		        // Bail early if super admin 
		        if ( is_super_admin() ) 
		                return true; 

		        if ( current_user_can('edit_posts') ) 
		                return true;     
	
		        // Get group creation option, default to 0 (allowed) 
	        $restricted = (int) get_site_option( 'bp_restrict_study_creation', 0 ); 
		 
		        // Allow by default 
		        $can_create = true; 
		 
		        // Are regular users restricted? 
		        if ( $restricted ) 
		                $can_create = false; 
	
	return apply_filters( 'bp_user_can_create_study', $can_create ); 
} 
/**
 * bp_study_nav_menu()
 * Navigation menu for BuddyPress study
 */

function bp_study_nav_menu(){
    $defaults = array(
      'Home' => array(
                        'id' => 'home',
                        'label'=>__('Home','qm'),
                        'action' => '',
                        'link'=>bp_get_study_permalink(),
                    ),
      'curriculum' => array(
                        'id' => 'curriculum',
                        'label'=>__('Curriculum','qm'),
                        'action' => 'curriculum',
                        'link'=>bp_get_study_permalink(),
                    ),
      'members' => array(
                        'id' => 'members',
                        'label'=>__('Members','qm'),
                        'action' => 'members',
                        'link'=>bp_get_study_permalink(),
                    ),
      );

    $nav_menu = apply_filters('quantipress_study_nav_menu',$defaults);

    if(is_array($nav_menu))
      foreach($nav_menu as $menu_item){
          echo '<li id="'.$menu_item['id'].'" class="'.(($menu_item['action']==$_GET['action'])?'current':'').'"><a href="'.$menu_item['link'].''.(isset($menu_item['action'])?'?action='.$menu_item['action']:'').'">'.$menu_item['label'].'</a></li>';
      }
}
/**
 * bp_study_remove_data()
 *
 * It's always wise to clean up after a user is deleted. This stops the database from filling up with
 * redundant information.
 */
function bp_study_remove_data( $user_id ) {
	/* You'll want to run a function here that will delete all information from any component tables
	   for this $user_id */

	/* Remember to remove usermeta for this component for the user being deleted */
	delete_user_meta( $user_id, 'bp_study_some_setting' );

	do_action( 'bp_study_remove_data', $user_id );
}
add_action( 'wpmu_delete_user', 'bp_study_remove_data', 1 );
add_action( 'delete_user', 'bp_study_remove_data', 1 );


function bp_directory_study_search_form() {

	$default_search_value = bp_get_search_default_text( BP_STUDY_SLUG );
	$search_value         = !empty( $_REQUEST['s'] ) ? stripslashes( $_REQUEST['s'] ) : $default_search_value;

	$search_form_html = '<form action="" method="get" id="search-study-form">
		<label><input type="text" name="s" id="groups_search" placeholder="'. esc_attr( $search_value ) .'" /></label>
		<input type="submit" id="study_search_submit" name="study_search_submit" value="'. __( 'Search', 'qm' ) .'" />
	</form>';

	echo apply_filters( 'bp_directory_study_search_form', $search_form_html );

}


function the_study_button($id=NULL){
  global $post;
  if(isset($id) && $id)
    $study_id=$id;
   else 
    $study_id=get_the_ID();

  // Free Study
   $free_study= get_post_meta($study_id,'qm_study_free',true);

  if(!is_user_logged_in() && qm_validate($free_study)){
    echo apply_filters('quantipress_study_non_loggedin_user','<a href="'.get_permalink($study_id).'?error=login" class="study_button button full">'.__('TAKE THIS STUDY','qm').'</a>'); 
    return;
  }

   $take_study_page=get_permalink(qm_get_option('take_study_page'));
   $user_id = get_current_user_id();

   do_action('quantipress_the_study_button',$study_id,$user_id);

   $studytaken=get_user_meta($user_id,$study_id,true);
   
   if(isset($free_study) && $free_study && $free_study !='H' && is_user_logged_in()){

      $duration=get_post_meta($study_id,'qm_duration',true);
      $new_duration = time()+86400*$duration;

      $new_duration = apply_filters('quantipress_free_study_check',$new_duration);
      if(update_user_meta($user_id,$study_id,$new_duration)){
        $group_id=get_post_meta($study_id,'qm_group',true);
        if(isset($group_id) && $group_id !=''){
          groups_join_group($group_id, $user_id );
        }
      }
      $studytaken = $new_duration;      
   }

   if(isset($studytaken) && $studytaken && is_user_logged_in()){   // STUDY IS TAKEN & USER IS LOGGED IN
     
       
         if($studytaken > time()){  // STUDY ACTIVE

          $study_user=get_post_meta($study_id,$user_id,true); // Validates that a user has taken this study

            if((isset($study_user) && $study_user !='') || (isset($free_study) && $free_study && $free_study !='H' && is_user_logged_in())){ // STUDY PURCHASED SECONDARY VALIDATION

             echo '<form action="'.$take_study_page.'" method="post">';
             if(!$study_user){ // STUDY NOT STARTED
                  echo '<input type="submit" class="'.((isset($id) && $id )?'':'study_button full ').'button" value="'.__('START STUDY','qm').'">'; 
                  wp_nonce_field('start_study','start_study');
             }else{  // STUDY STARTED
              
                switch($study_user){
                  case 1:
                    echo '<input type="submit" class="'.((isset($id) && $id )?'':'study_button full ').'button" value="'.__('CONTINUE STUDY','qm').'">';
                    wp_nonce_field('continue_study','continue_study');
                  break;
                  case 2:
                    echo '<a href="#" class="full button">'.__('STUDY UNDER EVALUATION','qm').'</a>';
                  break;
                  default:
                    echo '<a href="#" class="full button">'.__('STUDY FINISHED','qm').'</a>';
                  break;
                }
             }  
                
             
             echo  '<input type="hidden" name="study_id" value="'.$study_id.'" />';
             
             echo  '</form>'; 
            }else{ 
                  $pid=get_post_meta($study_id,'qm_product',true); // SOME ISSUE IN PROCESS BUT STILL DISPLAYING THIS FOR NO REASON.
                  echo '<a href="'.get_permalink($pid).'" class="'.((isset($id) && $id )?'':'study_button full ').'button">'.__('STUDY ENABLED','qm').'</a><span>'.__('CONTACT ADMIN TO ENABLE','qm').'</span>';   
            }
      }else{ 
              $pid=get_post_meta($study_id,'qm_product',true);
              $pid=apply_filters('quantipress_study_product_id',$pid,$study_id); // $id checks for Single Study page or Study page in the my studies section
               echo '<a href="'.get_permalink($pid).'" class="'.((isset($id) && $id )?'':'study_button full ').'button">'.__('STUDY EXPIRED','qm').'</a>';   
      }
    
   }else{
      $pid=get_post_meta($study_id,'qm_product',true);
      $pid=apply_filters('quantipress_study_product_id',$pid,$study_id);
      if(isset($pid) && $pid)
        echo '<a href="'.get_permalink($pid).'" class="'.((isset($id) && $id )?'':'study_button full ').'button">'.__('TAKE THIS STUDY','qm').'</a>'; 
      else
        echo '<a href="'.apply_filters('quantipress_private_study_button','#').'" class="'.((isset($id) && $id )?'':'study_button full ').'button">'. apply_filters('quantipress_private_study_button_label',__('PRIVATE STUDY','qm')).'</a>'; 
   }
}


function the_study_details($args=NULL){
  echo get_the_study_details($args);
}

function get_the_study_details($args=NULL){
  $defaults=array(
    'study_id' =>get_the_ID(),
    );
  $r = wp_parse_args( $args, $defaults );
  extract( $r, EXTR_SKIP );


   global $post;
   $return ='<div class="study_details"><ul>';

   $return .= '<li><i class="icon-wallet-money"></i> <h5 class="credits">';
    $return .= bp_study_get_study_credits('study_id='.$study_id);
    $return .=  '</h5></li>';

    $prestudy=get_post_meta($study_id,'qm_pre_study',true);

    if(isset($prestudy) && $prestudy!='')
        $return .= '<li><i class="icon-clipboard-1"></i> '.__('* REQUIRES','qm').' <a href="'.get_permalink($prestudy).'">'.get_the_title($prestudy).'</a></li>'; 
           
   $return .= apply_filters('quantipress_study_details_time','<li><i class="icon-clock"></i>'.get_the_study_time('study_id='.$study_id).'</li>'); 

   $badge=get_post_meta($study_id,'qm_study_badge',true);
   

   if(isset($badge) && $badge && $badge !=' ')
      $return .=  '<li><i class="icon-award-stroke"></i> '.__('Study Badge','qm').'</li>';

   $certificate=get_post_meta($study_id,'qm_study_certificate',true);

   if(qm_validate($certificate))
      $return .=  '<li><i class="icon-certificate-file"></i>  '.__('Study Certificate','qm').'</li>';

    
   $return .=  '</ul></div>';

   return apply_filters('quantipress_study_front_details',$return);
}

function take_study_page(){

}




if(!function_exists('the_question')){
  function the_question(){
    global $post;
    echo '<div id="question" data-ques="'.get_the_ID().'">';
    echo '<div class="question">';
    the_content();
    echo '</div>';

    $type = get_post_meta(get_the_ID(),'qm_question_type',true);

    switch($type){
      case 'single': 
        the_options('single');
      break;  
      case 'multiple': 
        the_options('multiple');
      break;
      case 'sort': 
        the_options('sort');
      break;
      case 'smalltext': 
        the_text();
      break;
      case 'largetext': 
        the_textarea();
      break;
    }
    echo '</div><div id="ajaxloader" class="disabled"></div>';
  }
}

if(!function_exists('the_options')){
  function the_options($type){
      global $post;
      $options = qm_sanitize(get_post_meta(get_the_ID(),'qm_question_options',false));
    

    if(isset($options) || $options){

      

        $user_id = get_current_user_id();

        $answers=get_comments(array(
          'post_id' => get_the_ID(),
          'status' => 'approve',
          'user_id' => $user_id
          ));


        if(isset($answers) && is_array($answers) && count($answers)){
            $answer = reset($answers);
            $content = explode(',',$answer->comment_content);
        }else{
            $content=array();
        }
    
      echo '<ul class="question_options '.$type.'">';
      if($type=='single'){
        foreach($options as $key=>$value){

          $k=$key+1;
          echo '<li>
                    <input type="radio" id="'.$post->post_name.$key.'" name="'.$post->post_name.'" value="'.$k.'" '.(in_array($k,$content)?'checked':'').'/>
                    <label for="'.$post->post_name.$key.'"><span></span> '.$value.'</label>
                </li>';
        }
      }else if($type == 'sort'){
        foreach($options as $key=>$value){
          echo '<li id="'.($key+1).'" class="sort_option">
                      <label for="'.$post->post_name.$key.'"><span></span> '.$value.'</label>
                  </li>';
        }        
      }else{
        foreach($options as $key=>$value){
          $k=$key+1;
          echo '<li>
                    <input type="checkbox" id="'.$post->post_name.$key.'" name="'.$post->post_name.$key.'" value="'.$k.'" '.(in_array($k,$content)?'checked':'').'/>
                    <label for="'.$post->post_name.$key.'"><span></span> '.$value.'</label>
                </li>';
        }
      }  
      echo '</ul>';

      global $withcomments;
      $withcomments = true;
      if($type == 'sort')
        comments_template('/answer-sort.php',true);
      else    
        comments_template('/answer-options.php',true);

    }


  }
}

if(!function_exists('student_quiz_retake')){
  function student_quiz_retake(){

      $quiz_id = get_the_ID();
      $user_id = get_current_user_id();

      if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'retake'.$user_id) ){
          wp_die(__('Security check failed !','qm'),__('Security Error','qm'),array('back_link' => true));
    }

    if ( !isset($user_id) || !$user_id){
        wp_die(__(' Incorrect User selected.','qm'),__('Security Error','qm'),array('back_link' => true));
    }

      if(delete_user_meta($user_id,$quiz_id)){

        delete_post_meta($quiz_id,$user_id); // Optional validates that user can retake the quiz

        $questions = qm_sanitize(get_post_meta($quiz_id,'qm_quiz_questions',false));
        foreach($questions['ques'] as $question){
          global $wpdb;
          $wpdb->query($wpdb->prepare("UPDATE $wpdb->comments SET comment_approved='trash' WHERE comment_post_ID=%d AND user_id=%d",$question,$user_id));
        }
      }
    
      bp_study_record_activity(array(
        'action' => __('Quiz retake by Student','qm'),
        'content' => __('Student ','qm').bp_core_get_userlink( $user_id ).__(' initiated quiz retake ','qm').get_the_title($quiz_id),
        'type' => 'retake_quiz',
        'primary_link' => get_permalink($quiz_id),
        'item_id' => $quiz_id,
        'secondary_item_id' => $user_id
      ));
  }
}

if(!function_exists('the_text')){
  function the_text(){
      global $post;
      echo '<div class="single_text">';
        global $withcomments;
        $withcomments = true;
       comments_template('/answer-text.php');
       echo '</div>';
  }
}

if(!function_exists('the_textarea')){
  function the_textarea(){
      echo '<div class="essay_text">';
        global $withcomments;
      $withcomments = true;
       comments_template('/answer-essay.php');
       echo '</div>';
  }
}

if(!function_exists('the_question_tags')){
  function the_question_tags($before,$saperator,$after){
    global $post;
      echo get_the_term_list($post->ID,'question-tag',$before,$saperator,$after);
       
  }
}


if(!function_exists('the_quiz')){
  function the_quiz($args=NULL){

    $defaults=array(
    'quiz_id' =>get_the_ID(),
    'ques_id'=> ''
    );
  $r = wp_parse_args( $args, $defaults );
  extract( $r, EXTR_SKIP );

    $user_id = get_current_user_id();
    $questions=qm_sanitize(get_post_meta($quiz_id,'qm_quiz_questions',false));
    if(isset($questions['ques']) && is_array($questions['ques']))
      $key=array_search($ques_id,$questions['ques']);

    if($ques_id){
      $the_query = new WP_Query(array(
        'post_type'=>'question',
        'p'=>$ques_id
        ));
      while ( $the_query->have_posts() ) : $the_query->the_post(); 
        the_question();

        if($key == 0){ // FIRST QUESTION
          if($key != (count($questions['ques'])-1)) // First But not the Last
            echo '<a href="#" class="ques_link right quiz_question" data-quiz="'.$quiz_id.'" data-qid="'.$questions['ques'][($key+1)].'">'.__('Next Question','qm').'</a>';

        }elseif($key == (count($questions['ques'])-1)){ // LAST QUESTION

          echo '<a href="#" class="ques_link left quiz_question" data-quiz="'.$quiz_id.'" data-qid="'.$questions['ques'][($key-1)].'">'.__('Previous Question','qm').'</a>';

        }else{
          echo '<a href="#" class="ques_link left quiz_question" data-quiz="'.$quiz_id.'" data-qid="'.$questions['ques'][($key-1)].'">'.__('Previous Question','qm').'</a>';
          echo '<a href="#" class="ques_link right quiz_question" data-quiz="'.$quiz_id.'" data-qid="'.$questions['ques'][($key+1)].'">'.__('Next Question','qm').'</a>';
        }
      endwhile;
      wp_reset_postdata();
    }else{
        
        $quiz_taken=get_user_meta($user_id,$quiz_id,true);

        if(isset($quiz_taken) && $quiz_taken && ($quiz_taken < time())){
          
          $message=get_post_meta($quiz_id,'qm_quiz_message',true);
       
          echo $message;
        }else{
          the_content();
        }
    } 
  }
}

if(!function_exists('the_quiz_timer')){
  function the_quiz_timer($start){
    global $post;

      $user_id = get_current_user_id();
      $quiztaken=get_user_meta($user_id,get_the_ID(),true);

      

      if(!isset($quiztaken) || !$quiztaken){
          
          $minutes=intval(get_post_meta($post->ID,'qm_duration',true));

          if(!$minutes) {$minutes=1; echo "Duration not Set";}

          $minutes= $minutes*60;

      }else{
          if($quiztaken>time())
            $minutes=$quiztaken-time();
          else{
            $minutes=1;
          }
      } 
      
      

      echo '<div class="quiz_timer '.(($start)?'start':'').'" data-time="'.$minutes.'">
      <span class="timer" data-timer="'.$minutes.'"></span>
      <span class="countdown">'.minutes_to_hms($minutes).'</span>
      <span>'.__('Time Remaining','qm').'</span>
      <span><strong>'.__('Mins','qm').'</strong> '.__('Secs','qm').'</span>
      </div>';
       
  }
}

if(!function_exists('the_quiz_timeline')){
  function the_quiz_timeline($id=NULL){
    global $post;
    $quiz_id =$post->ID;
    $user_id = get_current_user_id();

    $questions = qm_sanitize(get_post_meta($post->ID,'qm_quiz_questions',false));


    $quess=$questions['ques'];
    $marks=$questions['marks'];

    if(isset($quess) && is_array($quess)){
      echo '<div class="quiz_timeline"><ul>';
      
      //Randomise questions
      //shuffle($quess);
        foreach($quess as $i => $ques){
          $class='';


          $answers=get_comments(array(
            'post_id' => $ques,
            'status' => 'approve',
            'user_id' => $user_id,
            'count' => true,
            ));
          if($answers){
              $class="done";
          }


          if(isset($ques) && $ques){
            if(isset($id) && $ques == $id){
              $class="active";
            }
            echo '<li id="ques'.$ques.'" class="'.$class.'"><span></span> <a href="#" data-quiz="'.$quiz_id.'" data-qid="'.$ques.'" class="'.(is_user_logged_in()?'quiz_question':'').'">'.__('QUESTION','qm').' '.($i+1).'<span>'.$marks[$i].'</span></a></li>';
          }
        }   
      echo '</ul></div>';  
    }   
  }
}

if(!function_exists('minutes_to_hms')){
  function minutes_to_hms($sec){
    if($sec > 60){
        $minutes = floor($sec/60);
        $secs = $sec%60;
        if($secs < 10) $secs = '0'.$secs;
        return $minutes.':'.$secs;
    }else{
      $secs = $sec;
      return '00:'.$secs;
    }
  }
}

if(!function_exists('tofriendlytime')){
  function tofriendlytime($seconds) {
  $measures = array(
    'year' =>365*30*24*60*60,
    'month' =>30*24*60*60,
    'week' =>7*24*60*60,
    'day' =>24*60*60,
    'hour' =>60*60,
    'minute' =>60,
    'second' =>1,
    );
  foreach ($measures as $label=>$amount) {
    if ($seconds >= $amount) {  
      $howMany = floor($seconds / $amount);
      $timelabel=apply_filters('quantipress_time_labels',$label.($howMany > 1 ? "s" : ""));
      return apply_filters('quantipress_friendly_time',$howMany." ".$timelabel,$seconds);
    }
  } 
  return __('Right now','qm');
} 
}

add_action('wp_ajax_submit_quiz', 'submit_quiz');
if(!function_exists('submit_quiz')){
  function submit_quiz(){
    $id= $_POST['id'];
    $user_id = get_current_user_id();
    update_user_meta($user_id,$id,time());
    update_post_meta($id,$user_id,0);

    bp_study_record_activity(array(
      'action' => __('Student submitted the Quiz','qm'),
      'content' => __('Quiz ','qm').get_the_title($id).__(' was submitted by student','qm').bp_core_get_userlink( $user_id ),
      'type' => 'submit_quiz',
      'primary_link' => get_permalink($id),
      'item_id' => $id,
      'secondary_item_id' => $user_id
      ));

    bp_study_quiz_auto_submit($id,$user_id);
    die();
  }
}

//BEGIN QUIZ
add_action('wp_ajax_begin_quiz', 'begin_quiz'); // Only for LoggedIn Users
if(!function_exists('begin_quiz')){
  function begin_quiz(){
      $id= $_POST['id'];
      if ( isset($_POST['start_quiz']) && wp_verify_nonce($_POST['start_quiz'],'start_quiz') ){

        $user_id = get_current_user_id();
        $quiztaken=get_user_meta($user_id,$id,true);
        

         if(!isset($quiztaken) || !$quiztaken){
            
            $quiz_duration = get_post_meta($id,'qm_duration',true) * 60; // Quiz duration in seconds
            $expire=time()+$quiz_duration;
            add_user_meta($user_id,$id,$expire);
            $quiz_questions = qm_sanitize(get_post_meta($id,'qm_quiz_questions',false));
            the_quiz('quiz_id='.$id.'&ques_id='.$quiz_questions['ques'][0]);

            bp_study_record_activity(array(
              'action' => __('Student started a quiz','qm'),
              'content' => __('Student ','qm').bp_core_get_userlink($user_id).__(' started the quiz ','qm').get_the_title($id),
              'type' => 'start_quiz',
              'primary_link' => get_permalink($id),
              'item_id' => $id,
              'secondary_item_id' => $user_id
            ));

         }else{

          if($quiztaken > time()){
            $quiz_questions = qm_sanitize(get_post_meta($id,'qm_quiz_questions',false));

            the_quiz('quiz_id='.$id.'&ques_id='.$quiz_questions['ques'][0]);

          }else{
            echo '<div class="message error"><h3>'.__('Quiz Timed Out .','qm').'</h3>'; 
            echo '<p>'.__('If you want to attempt again, Contact Instructor to reset the quiz.','qm').'</p></div>';
          }
          
         }

     }else{
        echo '<h3>'.__('Quiz Already Attempted.','qm').'</h3>'; 
        echo '<p>'.__('Security Check Failed. Contact Site Admin.','qm').'</p>'; 
     }
     die();
  }  
}


//BEGIN QUIZ
add_action('wp_ajax_quiz_question', 'quiz_question'); // Only for LoggedIn Users
if(!function_exists('quiz_question')){
  function quiz_question(){
      
      $quiz_id= $_POST['quiz_id'];
      $ques_id= $_POST['ques_id'];

      

      if ( isset($_POST['start_quiz']) && wp_verify_nonce($_POST['start_quiz'],'start_quiz') ){ // Same NONCE just for validation

        $user_id = get_current_user_id();
        $quiztaken=get_user_meta($user_id,$quiz_id,true);
        

         if(isset($quiztaken) && $quiztaken){
            if($quiztaken > time()){
                the_quiz('quiz_id='.$quiz_id.'&ques_id='.$ques_id);  
            }else{
              echo '<div class="message error"><h3>'.__('Quiz Timed Out .','qm').'</h3>'; 
        echo '<p>'.__('If you want to attempt again, Contact Instructor to reset the quiz.','qm').'</p></div>';
            }
            
         }else{
            echo '<div class="message info"><h3>'.__('Start Quiz to begin quiz.','qm').'</h3>'; 
            echo '<p>'.__('Click "Start Quiz" button to start the Quiz.','qm').'</p></div>';
         }

     }else{
                echo '<div class="message error"><h3>'.__('Security Check Failed .','qm').'</h3>'; 
                echo '<p>'.__('Contact Site Admin.','qm').'</p></div>';
     }
     die();
  }  
}

add_action('wp_ajax_continue_quiz', 'continue_quiz'); // Only for LoggedIn Users
if(!function_exists('continue_quiz')){
  function continue_quiz(){
      $user_id = get_current_user_id();
      $quiztaken=get_user_meta($user_id,get_the_ID(),true);
      
      if ( isset($_POST['start_quiz']) && wp_verify_nonce($_POST['start_quiz'],'start_quiz') ){ // Same NONCE just for validation
       if(isset($quiztaken) && $quiztaken && $quiztaken > time()){
          $questions = qm_sanitize(get_post_meta($id,'qm_quiz_questions',false));
          the_quiz('quiz_id='.get_the_ID().'&ques_id='.$questions['ques'][0]);  
          //the_quiz();
          
       }else{
         echo '<div class="message error"><h3>'.__('Quiz Timed Out .','qm').'</h3>'; 
        echo '<p>'.__('If you want to attempt again, Contact Instructor to reset the quiz.','qm').'</p></div>';
       }
     }else{
          echo '<div class="message error"><h3>'.__('Quiz Already Attempted .','qm').'</h3>'; 
          echo '<p>'.__('If you want to attempt again, Contact Instructor to reset the quiz.','qm').'</p></div>'; 
     }
      die();
  }  
}


if(!function_exists('the_study_timeline')){
  function the_study_timeline($study_id=NULL,$uid=NULL){

   $user_id = get_current_user_id(); 
   $return ='<div class="study_timeline">
                <ul>';
    $study_curriculum=qm_sanitize(get_post_meta($study_id,'qm_study_curriculum',false));

    if(isset($study_curriculum) && is_array($study_curriculum)){
        foreach($study_curriculum as $unit_id){
          if(is_numeric($unit_id)){
            $nextunit_access = qm_get_option('nextunit_access');
            $unittaken=get_user_meta($user_id,$unit_id,true);
            $class='';$flag=0;
            if($uid == $unit_id){
              $class .=' active';
              $flag = 1;
            }
            if(isset($unittaken) && $unittaken){
              $class .=' done';
              $flag = 1;
            } 
            if(isset($nextunit_access) && $nextunit_access){
              /* == Force No Access if PRevious units not marked complete v 1.5.3 == */
              if($flag)
                $return .= '<li id="unit'.$unit_id.'" class="unit_line '.$class.'"><span></span> <a class="unit" data-unit="'.$unit_id.'">'.get_the_title($unit_id).'</a></li>';
              else
                $return .= '<li id="unit'.$unit_id.'" class="unit_line '.$class.'"><span></span> <a>'.get_the_title($unit_id).'</a></li>';

            }else{
                $return .= '<li id="unit'.$unit_id.'" class="unit_line '.$class.'"><span></span> <a class="unit" data-unit="'.$unit_id.'">'.get_the_title($unit_id).'</a></li>';
            }
          }else{
           $return .='<li class="section"><h4>'.$unit_id.'</h4></li>';
          }
        } // End For
      }else{
        $return .= '<li><h3>';
        $return .=__('Study Curriculum Not Set.','qm');
        $return .= '</h3></li>';
      }      
            
   $return .='</ul></div>';             
   return $return;
  }

}

function the_unit_study($id=NULL){
  if(!isset($id))
    return;
  

  do_action('quantipress_before_every_unit',$id);

  $the_query = new WP_Query( 'post_type=unit&p='.$id );
  
  while ( $the_query->have_posts() ):$the_query->the_post();
  

  echo '<div class="main_unit_content">';

  the_content();

  wp_link_pages('before=<div class="unit-page-links page-links"><div class="page-link">&link_before=<span>&link_after=</span>&after=</div></div>');

  echo '</div>';
  endwhile;
  wp_reset_postdata();

  do_action('quantipress_after_every_unit',$id);

  $attachments =& get_children( 'post_type=attachment&output=ARRAY_N&orderby=menu_order&order=ASC&post_parent='.$id);
  if($attachments && count($attachments)){
    $att= '';

    $count=0;
  foreach( $attachments as $attachmentsID => $attachmentsPost ){
  
  $type=get_post_mime_type($attachmentsID);

  if($type != 'image/jpeg' && $type != 'image/png' && $type != 'image/gif'){
      
      if($type == 'application/zip')
        $type='icon-compressed-zip-file';
      else if($type == 'video/mpeg' || $type== 'video/mp4' || $type== 'video/quicktime')
        $type='icon-movie-play-file-1';
      else if($type == 'text/csv' || $type== 'text/plain' || $type== 'text/xml')
        $type='icon-document-file-1';
      else if($type == 'audio/mp3' || $type== 'audio/ogg' || $type== 'audio/wmv')
        $type='icon-music-file-1';
      else if($type == 'application/pdf')
        $type='icon-text-document';
      else
        $type='icon-file';

      $count++;

      $att .='<li><i class="'.$type.'"></i>'.wp_get_attachment_link($attachmentsID).'</li>';
    }
  }
    if($count){
      echo '<div class="unitattachments"><h4>'.__('Attachments','qm').'<span><i class="icon-download-3"></i>'.$count.'</span></h4><ul id="attachments">';
      echo $att;
     echo '</ul></div>';
    }
  }

  $forum=get_post_meta($id,'qm_forum',true);
  if(isset($forum) && $forum){
    echo '<div class="unitforum"><a href="'.get_permalink($forum).'" target="_blank">'.__('Have Questions ? Ask in the Unit Forums','qm').'</a></div>';
  }
}


if(!function_exists('the_unit_tags')){
  function the_unit_tags($id){
      echo get_the_term_list($id,'module-tag','<ul class="tags"><li>','</li><li>','</li></ul>');
  }
}

if(!function_exists('the_unit_instructor')){
  function the_unit_instructor($id){
      global $post,$bp;
      if(isset($id)){
        $author_id = get_post_field( 'post_author', $id );
      }else{
        $author_id = get_the_author_meta('ID');
      }
     
      echo '<div class="instructor">
      <a href="'.bp_core_get_user_domain($author_id).'" title="'.bp_core_get_user_displayname( $author_id) .'"> '.get_avatar($author_id).' <span><strong>'.__('Instructor','qm').'</strong><br />'.bp_core_get_user_displayname( $author_id) .'</span></a>
      </div>';
       
  }
}

function quantipress_user_study_check($user_id,$study_id){

  if(!isset($user_id) || !$user_id)
    $user_id = get_current_user_id();
  if(!isset($study_id) || !$studyr_id)
    $study_id = get_the_ID();

  $check = get_user_meta($user_id,$study_id,true);
  if(isset($check) && $check)
    return true;

  return false;
}

function quantipress_user_study_active_check($user_id,$study_id){

  if(!isset($user_id) || !$user_id)
    $user_id = get_current_user_id();
  if(!isset($study_id) || !$studyr_id)
    $study_id = get_the_ID();

  $check = get_user_meta($user_id,$study_id,true);
  if(isset($check) && $check){
    $study_check = get_post_meta($study_id,$user_id,true);
    if(isset($study_check) && $study_check < 3 ) // Check status of the Study 0 : Start, 1: Continue, 2: Finished and under evaluation, >2: Evaluated
      return true;
  }  

  return false;
}



function the_study_time($args){
  echo '<strong>'.__('Time Remaining','qm').' : <span>'.get_the_study_time($args).'</span></strong>';
}

function get_the_study_time($args){
  $defaults=array(
    'study_id' =>get_the_ID(),
    'user_id'=> get_current_user_id()
    );
  $r = wp_parse_args( $args, $defaults );
  extract( $r, EXTR_SKIP );

      $d=get_user_meta($user_id,$study_id,true);
      if(isset($d) && $d !='')
          return apply_filters('study_friendly_time',tofriendlytime(($d-time())),($d-time()));
      else{
          $d=get_post_meta($study_id,'qm_duration',true);
          return apply_filters('study_friendly_time',tofriendlytime(($d*86400)),($d*86400));
      }              
}

function bp_get_study_badge($id=NULL){
  if(!isset($id))
    $id=get_the_ID();

  $badge=get_post_meta($id,'qm_study_badge',true);

  return $badge;
}
function bp_get_total_instructor_count_study(){
  $args =  array(
    'role' => 'Instructor',
    'count_total' => true
    );
  $users = new WP_User_Query($args);
  return count($users->results);
  
}

function bp_get_study_certificate($args){
  $defaults=array(
    'study_id' =>get_the_ID(),
    'user_id'=> get_current_user_id()
    );


  $r = wp_parse_args( $args, $defaults );
  extract( $r, EXTR_SKIP );

  $certificate_template_id=get_post_meta($study_id,'qm_certificate_template',true);

  if(isset($certificate_template_id) && $certificate_template_id){
      $pid = $certificate_template_id;
  }else{
      $pid=qm_get_option('certificate_page');
  }

  $url = get_permalink($pid).'?c='.$study_id.'&u='.$user_id;
  return $url;
}

function bp_study_quiz_auto_submit($quiz_id,$user_id){
    $quiz_auto_evaluate=get_post_meta($quiz_id,'qm_quiz_auto_evaluate',true);

    if(qm_validate($quiz_auto_evaluate)){ // Auto Evaluate Enabled
        $total_marks=0;
        $questions = qm_sanitize(get_post_meta($quiz_id,'qm_quiz_questions',false));
        if(count($questions)){
            $sum=$max_sum=0;
            foreach($questions['ques'] as $key=>$question){ // Grab all the Questions
              if(isset($question) && $question){
                  $type = get_post_meta($question,'qm_question_type',true); 

                  $auto_evaluate_question_types = qm_get_option('auto_eval_question_type');
                  if(isset($auto_evaluate_question_types) && is_Array($auto_evaluate_question_types) && count($auto_evaluate_question_types)){
                      // Validated
                  }else{
                    $auto_evaluate_question_types=array('single');
                  }                            
                  if(isset($type) && in_array($type,$auto_evaluate_question_types) ){
                    $correct_answer=get_post_meta($question,'qm_question_answer',true);
                    $comments_query = new WP_Comment_Query;
                    
                    $comments = $comments_query->query( array('post_id'=> $question,'user_id'=>$user_id,'number'=>1,'status'=>'approve') );   
                    foreach($comments as $comment){
                      if($comment->comment_content == $correct_answer){
                        $marks=$questions['marks'][$key];
                        $total_marks = $total_marks+$marks;
                      }else{
                        $marks = 0;
                      }
                      update_comment_meta( $comment->comment_ID, 'marks', $marks );
                    }//END-For
                  }
              }
            }
            update_post_meta( $quiz_id, $user_id,$total_marks);
            bp_study_record_activity(array(
              'action' => __('Quiz Auto Evaluated','qm'),
              'content' => __('Quiz ','qm').get_the_title($quiz_id).__(' auto evaluated for student ','qm').bp_core_get_userlink($user_id).__(' with marks ','qm').$total_marks,
              'type' => 'evaluate_quiz',
              'primary_link' => get_permalink($quiz_id),
              'item_id' => $quiz_id,
              'secondary_item_id' => $user_id
            ));
          }  
    }  
}
function bp_study_validate_certificate($args){
  $defaults=array(
    'study_id' =>get_the_ID(),
    'user_id'=> get_current_user_id()
    );
  $r = wp_parse_args( $args, $defaults );
  extract( $r, EXTR_SKIP );
  $meta = qm_sanitize(get_user_meta($user_id,'certificates',false));
  if((in_array($study_id,$meta) && is_array($meta)) || (!is_array($meta) && $study_id==$meta)){
    return;
  }else{
    wp_die(__('Certificate not valid for user','qm'));
  }
}


function array_to_csv_download_study($array, $filename = "export.csv", $delimiter=";") {
    // open raw memory as file so no temp files needed, you might run out of memory though
    $f = fopen('php://memory', 'w'); 
    // loop over the input array
    foreach ($array as $line) { 
        // generate csv lines from the inner arrays
        fputcsv($f, $line, $delimiter); 
    }
    // rewrind the "file" with the csv lines
    fseek($f, 0);
    // tell the browser it's going to be a csv file
    header('Content-Type: application/csv');
    // tell the browser we want to save it instead of displaying it
    header('Content-Disposition: attachement; filename="'.$filename.'";');
    // make php send the generated csv lines to the browser
    fpassthru($f);
}


function bp_study_instructor_controls(){
  global $bp,$wpdb;
  $user_id=$bp->loggedin_user->id;
  $study_id = get_the_ID();

  $curriculum=qm_sanitize(get_post_meta($study_id,'qm_study_curriculum',false));
  $study_quizes=array();
  if(isset($curriculum))
    foreach($curriculum as $c){
      if(is_numeric($c)){
        if(get_post_type($c) == 'quiz'){
            $study_quizes[]=$c;
          }
      }
  }

  echo '<ul class="instructor_action_buttons">';

  $study_query = $wpdb->get_results($wpdb->prepare("SELECT COUNT(meta_key) as num FROM {$wpdb->postmeta} WHERE post_id = %d AND meta_value = %d",$study_id,2));
  $num=0;
  if(isset($study_query) && $study_query !='')
    $num=$study_query[0]->num;
  else
    $num=0;

  echo '<li><a href="'.get_permalink($study_id).'/?action=admin&submissions" class="action_icon tip" title="'.__('Evaluate study submissions','qm').'"><i class="icon-task"></i><span>'.$num.'</span></a></li>';  

  if(isset($study_quizes) && count($study_quizes)){
    if(count($study_quizes) > 1){
      $quiz_ids = join(',',$study_quizes);  
      $query = $wpdb->get_results($wpdb->prepare("SELECT COUNT(meta_key) as num FROM {$wpdb->postmeta} WHERE post_id IN {$quiz_ids} AND meta_value = %d",0));
    }else{
      $query = $wpdb->get_results($wpdb->prepare("SELECT COUNT(meta_key) as num FROM {$wpdb->postmeta} WHERE post_id = %d AND meta_value = %d",$study_quizes,0));
    }
    
    if(isset($query) && $query !='')
      $num=intval($query[0]->num);
    else
      $num=0;

    echo '<li><a href="'.get_permalink($study_id).'/?action=admin&submissions" class="action_icon tip"  title="'.__('Evaluate Quiz submissions','qm').'"><i class="icon-check-clipboard-1"></i><span>'.$num.'</span></a></li>';
  } 

  $n=get_post_meta($study_id,'qm_students',true);
  if(isset($n) && $n !=''){$num=$n;}else{$num=0;}
  echo '<li><a href="'.get_permalink($study_id).'/?action=admin&members" class="action_icon tip"  title="'.__('Manage Students','qm').'"><i class="icon-users"></i><span>'.$num.'</span></a></li>';
  echo '<li><a href="'.get_permalink($study_id).'/?action=admin&stats" class="action_icon tip"  title="'.__('See Stats','qm').'"><i class="icon-analytics-chart-graph"></i></a></li>';
  echo '<li><a href="'.get_permalink($study_id).'/?action=admin&activity" class="action_icon tip"  title="'.__('See all Activity','qm').'"><i class="icon-atom"></i></a></li>';
  echo '</ul>';
}


function bp_quantipress_get_theme_color_study(){
  $option = get_option('qm_customizer');
  if(isset($option) && is_Array($option)){
    if(isset($option['primary_bg']))
     return $option['primary_bg'];
  }
  return '#78c8c9';
}
?>
