<?php
global $post;
$id= get_the_ID();

?>

<div class="study_title">
	<h2><?php  _e('Study Curriculum','qm'); ?></h2>
</div>

<div class="study_curriculum">
<?php
$study_curriculum = qm_sanitize(get_post_meta($id,'qm_study_curriculum',false));

if(isset($study_curriculum)){


	foreach($study_curriculum as $lesson){
		if(is_numeric($lesson)){
			$icon = get_post_meta($lesson,'qm_type',true);

			if(get_post_type($lesson) == 'quiz')
				$icon='task';

					$href=get_the_title($lesson);
					$free='';
					$free = get_post_meta($lesson,'qm_free',true);
					if(qm_validate($free)){
						$href='<a href="'.get_permalink($lesson).'?id='.get_the_ID().'">'.get_the_title($lesson).'<span>'.__('FREE','qm').'</span></a>';
					}

			echo '<div class="study_lesson">

					<i class="icon-'.$icon.'"></i><h6>'.$href.'</h6>';
					$minutes=0;
					$minutes = get_post_meta($lesson,'qm_duration',true);

					if($minutes){
						if($minutes > 60){
							$hours = intval($minutes/60);
							$minutes = $minutes - $hours*60;
						}
					echo '<span><i class="icon-clock"></i> '.(isset($hours)?$hours.__(' Hours','qm'):'').' '.$minutes.__(' minutes','qm').'</span>';
					}	

					echo '</div>';
		}else{
			echo '<h5 class="study_section">'.$lesson.'</h5>';
		}
	}
}
	?>
</div>

<?php

?>