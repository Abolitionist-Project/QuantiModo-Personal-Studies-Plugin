<?php

/**
 *
 * @package BuddyPress_Skeleton_Component
 * @since 1.6
 */

$loop_number=qm_get_option('loop_number');
isset($loop_number)?$loop_number:$loop_number=5;
?>

<?php do_action( 'bp_before_study_loop' ); ?>



<?php 


if ( bp_study_has_items( bp_ajax_querystring( 'study' ).'&per_page='.$loop_number ) ) : ?>
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
					<?php bp_study_credits(); ?>
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
