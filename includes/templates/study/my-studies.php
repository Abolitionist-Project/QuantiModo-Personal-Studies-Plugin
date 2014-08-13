<?php

/**
 *
 * @package BuddyPress_Skeleton_Component
 * @since 1.6
 */


?>

<?php do_action( 'bp_before_study_loop' ); ?>



<?php 
$user_id=get_current_user_id();

if ( bp_study_has_items( bp_ajax_querystring( 'study' ).'&user='.$user_id.'&per_page=5' ) ) : ?>
<?php // global $items_template; var_dump( $items_template ) ?>
	<div id="pag-top" class="pagination">

		<div class="pag-count" id="study-dir-count-top">

			<?php bp_study_pagination_count(); ?>

		</div>

		<div class="pagination-links" id="study-dir-pag-top">

			<?php bp_study_item_pagination(); ?>

		</div>

	</div>
	
	<?php do_action( 'bp_before_directory_study_list' ); ?>

	<ul id="study-list" class="item-list" role="main">

	<?php while ( bp_study_has_items() ) : bp_study_the_item(); ?>

		<li>
			<div class="item-avatar">
				<?php bp_study_avatar(); ?>

			</div>
			<div class="item">
				<div class="item-title"><?php bp_study_title() ?></div>
				<div class="item-meta"><?php bp_study_meta() ?></div>
				<div class="item-desc"><?php bp_study_desc() ?></div>
				<div class="item-credits">
					<?php 
					$live=get_post_meta($id,$user_id,true);
						if(isset($live) && $live !=''){
							echo '<strong>';
							switch($live){
								case 0:
									echo '<a href="'.get_permalink($id).'" class="button">'.__('Start Study','qm').'</a>';
								break;
								case 1:
									echo '<a href="'.get_permalink($id).'" class="button">'.__('Continue Study','qm').'</a>';
								break;
								default:
									echo '<a href="'.get_permalink($id).'" class="button">'.__('Study Finished','qm').'</a>';
								break;
							}
							echo '</strong>';
						}else{
							bp_study_credits();		
						}
					 ?>
				</div>
				<div class="item-instructor">
					<?php bp_study_instructor_avatar(); ?>
					<?php bp_study_instructor(); ?>
				</div>
				<div class="item-action"><?php bp_study_action() ?></div>
				<?php do_action( 'bp_directory_study_item' ); ?>

			</div>

			<div class="clear"></div>
		</li>

	<?php endwhile; ?>

	</ul>

	<?php do_action( 'bp_after_directory_study_list' ); ?>

	<div id="pag-bottom" class="pagination">

		<div class="pag-count" id="study-dir-count-bottom">

			<?php bp_study_pagination_count(); ?>

		</div>

		<div class="pagination-links" id="study-dir-pag-bottom">

			<?php bp_study_item_pagination(); ?>

		</div>

	</div>

<?php else: ?>

	<div id="message" class="info">
		<p><?php _e( 'You have not subscribed to any Study.', 'qm' ); ?></p>
	</div>

<?php endif;  ?>


<?php do_action( 'bp_after_study_loop' ); ?>
