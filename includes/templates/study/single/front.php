<?php
global $post;
$id= get_the_ID();


if(isset($_REQUEST['error'])){ 
	switch($_REQUEST['error']){
		case 'prestudy':
			$pre=get_post_meta($id,'qm_pre_study',true);
			echo '<div id="message" class="notice"><p>'.__('Requires completition of study ','qm').get_the_title($pre).'</p></div>';
		break;
	}
}

if(have_posts()):
while(have_posts()):the_post();
?>

<div class="study_title">
	<?php qm_breadcrumbs(); ?>
	<h1><?php the_title(); ?></h1>
	<h6><?php the_excerpt(); ?></h6>
</div>
<div class="students_undertaking">
	<?php
	$students_undertaking=array();
	$students_undertaking = bp_study_get_students_undertaking();
	$students=get_post_meta(get_the_ID(),'qm_students',true);

	echo '<strong>'.$students.' STUDENTS ENROLLED</strong>';

	echo '<ul>';
	$i=0;
	foreach($students_undertaking as $student){
		$i++;
		echo '<li>'.get_avatar($student).'</li>';
		if($i>5)
			break;
	}
	echo '</ul>';
	?>
</div>

<div class="study_description">
	<div class="small_desc">
	<?php $content=get_the_content(); 
		echo substr(apply_filters('the_content',$content),0,1200).' <a href="#" id="more_desc" class="link">READ MORE</a>';
		
	?>
	</div>
	<div class="full_desc">
		<?php
			echo substr(apply_filters('the_content',$content),1200,-1);
			echo '<a href="#" id="less_desc" class="link">LESS</a>';
		?>
	</div>
</div>


<div class="study_reviews">
<?php
	 comments_template('/study-review.php',true);
?>
</div>

<?php
endwhile;
endif;
?>