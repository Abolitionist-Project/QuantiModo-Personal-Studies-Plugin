<?php

/**
 * In this file you should create and register widgets for your component.
 *
 * Widgets should be small, contained functionality that a site administrator can drop into
 * a widget enabled zone (column, sidebar etc)
 *
 * Good studies of suitable widget functionality would be short lists of updates or featured content.
 *
 * For study the friends and groups components have widgets to show the active, newest and most popular
 * of each.
 */


add_action( 'widgets_init', 'bp_study_register_widgets' );

function bp_study_register_widgets() {
    register_widget('BP_Study_Widget');
    register_widget('BP_Instructor_Widget_Study');
    register_widget('BP_Study_Search_Widget');
}

class BP_Study_Widget extends WP_Widget {



	function BP_Study_widget() {
	  $widget_ops = array( 'classname' => 'BuddyPress Study Widget', 'description' => 'Displays Studies in single, list & carousel formats.' );
	  $control_ops = array( 'width' => 250, 'height' => 350,'id_base' => 'bp_study_widget');
	  $this->WP_Widget( 'bp_study_widget',  __('BuddyPress Study Widget','qm'), $widget_ops, $control_ops);
	  }

	function widget( $args, $instance ) {
		global $bp;

		extract( $args );

		extract( $instance, EXTR_SKIP );
		echo $before_widget;
		if(isset($title) && $title !='')
		echo $before_title .
		     $title .
		     $after_title; 

		     //Preparing Query
		     

		     if(isset($ids) && $ids !='' && strlen($ids) > 5){
		     	$study_ids = explode(',',$ids);
		     	$the_query= new WP_QUERY(array( 'post_type' => 'study', 'post__in' => $study_ids ) );
		     }else{

		     	$qargs = array('post_type' => 'study');
		     	if(isset($category) && $category !='' && $category != 'none'){
		     		$qargs['study-cat'] = $category;
		     	}
		     	if($orderby =='name' || $orderby == 'comment_count' || $orderby == 'date' || $orderby == 'title' || $orderby == 'rand'){
		     		$qargs['orderby'] = $orderby;
		     	}else{
		     		$qargs['orderby']='meta_value';
		     		$qargs['meta_key'] = $orderby;
		     	}

		     	$qargs['posts_per_page'] = $max_items;
		     	$qargs['order'] = $order;


		     	$the_query= new WP_Query($qargs);
		     }

		     switch($style){
		     	case 'list':
		     		echo '<ul class="widget_study_list no-ajax">';
		     	break;
		     	case 'carousel':
		     		echo '<div class="widget_carousel flexslider  no-ajax"><ul class="slides">';
		     	break;
		     }
		     ?>     
	<?php

	while($the_query->have_posts()):$the_query->the_post();
	global $post;
	switch($style){
		     	case 'list':
		     	echo '<li><a href="'.get_permalink($post->ID).'">'.get_the_post_thumbnail($post->ID,'thumbnail').'<h6>'.get_the_title($post->ID).'<span>'.__('by','qm').' '.bp_core_get_user_displayname($post->post_author).'</span></h6></a></li>';
		     	break;
		     	case 'carousel':
		     	echo '<li>';
		     	echo thumbnail_generator($post,'study','3','0',true,true);
		     	echo '</li>';
		     	break;
		     	default:
		     	echo '<div class="single_study">';
		     	echo thumbnail_generator($post,'study','3','0',true,true);
		     	echo '</div>';
		     	break;
		     }

	endwhile;
	wp_reset_postdata();
	?>
	<?php
		switch($style){
		     	case 'list':
		     		echo '</ul>';
		     	break;
		     	case 'carousel':
		     		echo '</ul></div>';
		     	break;
		     }
	?>
	<?php echo $after_widget; ?>
	<?php
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['style'] = strip_tags( $new_instance['style'] );
		$instance['category'] = strip_tags( $new_instance['category'] );
		$instance['orderby'] = strip_tags( $new_instance['orderby'] );
		$instance['order'] = strip_tags( $new_instance['order'] );
		$instance['ids'] = strip_tags( $new_instance['ids'] );
		$instance['max_items'] = strip_tags( $new_instance['max_items'] );
		

		return $instance;
	}

	function form( $instance ) {
		$defaults = array( 'title'=> 'Study','style' => 'single','orderby'=>'name','order'=>'ASC','category'=>'','ids'=>'', 'max_items' => 5 );

		$instance = wp_parse_args( (array) $instance, $defaults );
		
		$study_cats=get_terms('study-cat','orderby=count&hide_empty=0');

		extract( $instance, EXTR_SKIP );

		?>
		<p><label for="bp-study-widget-ids"><?php _e( 'Widget Title', 'qm' ); ?> <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" style="width: 30%" /></label></p>
		<p><label for="bp-study-widget-style"><?php _e( 'Style', 'qm' ); ?> 
		<select id="<?php echo $this->get_field_id( 'style' ); ?>" name="<?php echo $this->get_field_name( 'style' ); ?>">
			<option value="single" <?php selected('single',esc_attr( $style )); ?>><?php _e('Single'); ?></option>
			<option value="list" <?php selected('list',esc_attr( $style )); ?>><?php _e('List'); ?></option>
			<option value="carousel" <?php selected('carousel',esc_attr( $style )); ?>><?php _e('Carousel'); ?></option>
		</select>
		</p>
		<p><label for="bp-study-widget-category"><?php _e( 'Select Study Category', 'qm' ); ?> 
		<select id="<?php echo $this->get_field_id( 'category' ); ?>" name="<?php echo $this->get_field_name( 'category' ); ?>">
			<option value="">None</option>
		<?php
			foreach($study_cats as $study_cat){
				echo '<option value="'.$study_cat->slug.'" '.selected($study_cat->slug,esc_attr( $category )).'>'.$study_cat->name.'</option>';
			}
		?>
		</select>
		</p>
		<p><label for="bp-study-widget-orderby"><?php _e( 'Order By', 'qm' ); ?> 
		<select id="<?php echo $this->get_field_id( 'orderby' ); ?>" name="<?php echo $this->get_field_name( 'orderby' ); ?>">
			<option value="rand" <?php selected('rand',$orderby); ?>><?php _e('Random'); ?></option>
			<option value="name" <?php selected('name',$orderby); ?>><?php _e('Name'); ?></option>
			<option value="title" <?php selected('title',$orderby ); ?>><?php _e('Study Title'); ?></option>
			<option value="comment_count" <?php selected('comment_count', $orderby ); ?>><?php _e('Number of Reviews'); ?></option>
			<option value="date" <?php selected('date',$orderby ); ?>><?php _e('Date Published'); ?></option>
			<option value="average_rating" <?php selected('average_rating',$orderby ); ?>><?php _e('Rating'); ?></option>
			<option value="qm_students" <?php selected('qm_students',$orderby ); ?>><?php _e('Number of Students'); ?></option>
		</select>
		</p>
		<p><label for="bp-study-widget-order"><?php _e( 'Sort ', 'qm' ); ?> 
		<select id="<?php echo $this->get_field_id( 'order' ); ?>" name="<?php echo $this->get_field_name( 'order' ); ?>">
			<option value="ASC" <?php selected('ASC',esc_attr( $order )); ?>><?php _e('Ascending'); ?></option>
			<option value="DESC" <?php selected('DESC',esc_attr( $order )); ?>><?php _e('Decending'); ?></option>
		</select>
		</p>
		<p><label for="bp-study-widget-ids"><?php _e( 'Specific Studies (enter comma saperated ids)', 'qm' ); ?> <input class="widefat" id="<?php echo $this->get_field_id( 'ids' ); ?>" name="<?php echo $this->get_field_name( 'ids' ); ?>" type="text" value="<?php echo esc_attr( $ids ); ?>" style="width: 30%" /></label></p>
		<p><label for="bp-study-widget-max"><?php _e( 'Number of Studies to show', 'bp-study' ); ?> <input class="widefat" id="<?php echo $this->get_field_id( 'max_items' ); ?>" name="<?php echo $this->get_field_name( 'max_items' ); ?>" type="text" value="<?php echo esc_attr( $max_items ); ?>" style="width: 30%" /></label></p>
	<?php
	}
}



class BP_Instructor_Widget_Study extends WP_Widget {



	function BP_Instructor_Widget_Study() {
	  $widget_ops = array( 'classname' => 'BuddyPress Instructor Widget', 'description' => 'Displays Current Instructor details widget.' );
	  $control_ops = array( 'width' => 250, 'height' => 350,'id_base' => 'bp_instructor_widget_study');
	  $this->WP_Widget( 'bp_instructor_widget_study',  __('BuddyPress Instructor Widget','qm'), $widget_ops, $control_ops);
	  }

	function widget( $args, $instance ) {
		global $bp;

		extract( $args );

		extract( $instance, EXTR_SKIP );
		echo $before_widget;
		if(isset($title) && $title !='')
		echo $before_title .
		     $title .
		     $after_title; 

		     if(is_single()){
		     	global $post;
				$instructor=$post->post_author;
		     }

		    echo '<div class="study_instructor_widget">';
		    echo bp_study_get_instructor_avatar('item_id='.$instructor);
		    echo bp_study_get_instructor('instructor_id='.$instructor);
		    echo '<div class="description">'.bp_study_get_instructor_description('instructor_id='.$instructor).'</div>';
		    echo '<a href="'.get_author_posts_url($instructor).'" class="tip" title="'.__('Check all Studies created by ','qm').bp_core_get_user_displayname($instructor).'"><i class="icon-plus-1"></i></a>';
		    echo '<h5>'.__('More Studies by ','qm').bp_core_get_user_displayname($instructor).'</h5>';
		    echo '<ul class="widget_study_list">';
		    $query = new WP_Query( 'post_type=study&author='.$instructor.'&posts_per_page='.$max_items );
		    while($query->have_posts()):$query->the_post();
		    global $post;
		    echo '<li><a href="'.get_permalink($post->ID).'">'.get_the_post_thumbnail($post->ID,'thumbnail').'<h6>'.get_the_title($post->ID).'<span>'.__('by','qm').' '.bp_core_get_user_displayname($post->post_author).'</span></h6></a>';
		    endwhile;
		    wp_reset_postdata();
		    echo '</ul>';
		    echo '</div>'; 
		     //Preparing Query
		    
	 echo $after_widget; ?>
	<?php
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['instructor'] = strip_tags( $new_instance['instructor'] );
		$instance['max_items'] = strip_tags( $new_instance['max_items'] );
		

		return $instance;
	}

	function form( $instance ) {
		$defaults = array( 'title'=> 'Instructor Details',
			'instructor' => '1','max_items' => 5 );

		$instance = wp_parse_args( (array) $instance, $defaults );

		extract( $instance, EXTR_SKIP );
		$title = esc_attr($instance['title']);
		?>
		<p><label for="bp-instructor-widget-title"><?php _e( 'Widget Title', 'qm' ); ?> <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" style="width: 30%" /></label></p>
		<p><label for="bp-instructor-widget-title"><?php _e( 'Fallback Instructor ID', 'qm' ); ?> <input class="widefat" id="<?php echo $this->get_field_id( 'instructor' ); ?>" name="<?php echo $this->get_field_name( 'instructor' ); ?>" type="text" value="<?php echo esc_attr( $instructor ); ?>" style="width: 30%" /></label></p>
		<p><label for="bp-instructor-widget-max"><?php _e( 'Number of Studies by the Instructor to show', 'qm' ); ?> <input class="widefat" id="<?php echo $this->get_field_id( 'max_items' ); ?>" name="<?php echo $this->get_field_name( 'max_items' ); ?>" type="text" value="<?php echo esc_attr( $max_items ); ?>" style="width: 30%" /></label></p>
	<?php
	}
}



class BP_Study_Search_Widget extends WP_Widget {



	function BP_Study_Search_Widget() {
	  $widget_ops = array( 'classname' => 'buddypress-study-search-widget', 'description' => 'Displays Advanced search for Studies.' );
	  $control_ops = array( 'width' => 250, 'height' => 350,'id_base' => 'bp_study_search_widget');
	  $this->WP_Widget( 'bp_study_search_widget',  __('BuddyPress Study Search Widget','qm'), $widget_ops, $control_ops);
	  }

	function widget( $args, $instance ) {
		global $bp;

		extract( $args );

		extract( $instance, EXTR_SKIP );
		echo $before_widget;
		if(isset($title) && $title !='')
		echo $before_title .
		     $title .
		     $after_title; 

		     $html .='<form role="search" method="get" id="searchform" action="'.home_url( '/' ).'">
		     			<input type="hidden" name="post_type" value="'.BP_STUDY_SLUG.'" />
		     			<ul>';

		     if(isset($cats) && $cats == 1){

		     	$cat_val=$_GET['study-cat'];

		     	$study_cats = get_terms('study-cat');
		     	$html .= '<li><select name="study-cat" class="chosen chzn-select">';
		     	$html .='<option value="">'.__('Select Study Category','qm').'</option>';
		     	foreach($study_cats as $term){
		     		$html .='<option value="'.$term->slug.'" '.(isset($cat_val)?selected($cat_val,$term->slug,false):'').'>'.$term->name.'</option>';
		     	}
		     	$html .= '</select></li>'; 
		     }

		     if(isset($instructors) && $instructors == 1){
		     	$args = array(
	                'role' => 'instructor' // instructor
	    		);
				$user_query = new WP_User_Query( $args );

				if ( !empty( $user_query->results ) ) {
					$inst_val = $_GET['instructor'];
					$html .='<li><select name="instructor" class="chosen chzn-select">';
					$html .='<option value="">'.__('Select Instructor','qm').'</option>';
			        foreach ( $user_query->results as $user ) {
			        	$html .='<option value="'.$user->ID.'" '.(isset($inst_val)?selected($inst_val,$user->ID,false):'').'>'.$user->display_name.'</option>';
			        }
					$html .='</select></li>';        
				}	        
		     }

		     
				$html .='<li><input type="text" value="'.(isset($_GET['s'])?$_GET['s']:'').'" name="s" id="s" placeholder="'.__('Type Keywords..','qm').'" /></li>
					     <li><input type="submit" id="searchsubmit" value="Search" /></li></ul>
					</form>';

					echo $html;
		     /*
		     if(is_single()){
		     	global $post;
				$instructor=$post->post_author;
		     }

		    echo '<div class="study_instructor_widget">';
		    echo bp_study_get_instructor_avatar('item_id='.$instructor);
		    echo bp_study_get_instructor('instructor_id='.$instructor);
		    echo '<div class="description">'.bp_study_get_instructor_description('instructor_id='.$instructor).'</div>';
		    echo '<a href="'.get_author_posts_url($instructor).'" class="tip" title="'.__('Check all Studies created by ','qm').bp_core_get_user_displayname($instructor).'"><i class="icon-plus-1"></i></a>';
		    echo '<h5>'.__('More Studies by ','qm').bp_core_get_user_displayname($instructor).'</h5>';
		    echo '<ul class="widget_study_list">';
		    $query = new WP_Query( 'post_type=study&author='.$instructor.'&post_per_page='.$max_items );
		    while($query->have_posts()):$query->the_post();
		    global $post;
		    echo '<li><a href="'.get_permalink($post->ID).'">'.get_the_post_thumbnail($post->ID,'thumbnail').'<h6>'.get_the_title($post->ID).'<span>by '.bp_core_get_user_displayname($post->post_author).'</span></h6></a>';
		    endwhile;
		    wp_reset_postdata();
		    echo '</ul>';
		    echo '</div>'; 
		    */
		     //Preparing Query
		    
	 echo $after_widget; ?>
	<?php
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['cats'] = strip_tags( $new_instance['cats'] );
		$instance['instructors'] = strip_tags( $new_instance['instructors'] );
		

		return $instance;
	}

	function form( $instance ) {
		$defaults = array( 
			'title'=> 'Advanced Study Search Widget',
			'instructors' => 1,
			'cats' => 1 );

		$instance = wp_parse_args( (array) $instance, $defaults );

		extract( $instance, EXTR_SKIP );
		$title = esc_attr($instance['title']);
		$cats = esc_attr($instance['cats']);
		$instructors = esc_attr($instance['instructors']);
		?>
		<p><label for="bp-study-search-widget-title"><?php _e( 'Widget Title', 'qm' ); ?> <input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" style="width: 30%" /></label></p>
		<p><label for="bp-study-cat-dropdown"><?php _e( 'Show Study Category Dropdown', 'qm' ); ?> <input class="checkbox" id="<?php echo $this->get_field_id( 'cats' ); ?>" name="<?php echo $this->get_field_name( 'cats' ); ?>" type="checkbox" value="1" <?php checked($cats,1,true) ?>/></label></p>
		<p><label for="bp-instructor-dropdown"><?php _e( 'Show Instructor Dropdown', 'qm' ); ?> <input class="checkbox" id="<?php echo $this->get_field_id( 'instructors' ); ?>" name="<?php echo $this->get_field_name( 'instructors' ); ?>" type="checkbox" value="1"  <?php checked($instructors,1,true) ?>/></label></p>
	<?php
	}
}

?>