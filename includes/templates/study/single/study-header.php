<?php

do_action( 'bp_before_study_header' );

?>

	<div id="item-header-avatar">
		<a href="<?php bp_study_permalink(); ?>" title="<?php bp_study_name(); ?>">

			<?php bp_study_avatar(); ?>

		</a>
	</div><!-- #item-header-avatar -->


<div id="item-header-content">
	<span class="highlight"><?php bp_study_type(); ?></span>
	<h3><a href="<?php bp_study_permalink(); ?>" title="<?php bp_study_name(); ?>"><?php bp_study_name(); ?></a></h3>
	 <!--span class="activity"><?php //printf( __( 'active %s', 'qm' ), bp_get_study_last_active() ); ?></span-->

	<?php do_action( 'bp_before_study_header_meta' ); ?>

	<div id="item-meta">
		<?php bp_study_meta() ?>
											
		<div id="item-buttons">
			<?php bp_study_action() ?>
			<?php do_action( 'bp_study_header_actions' ); ?>

		</div><!-- #item-buttons -->

		<?php do_action( 'bp_study_header_meta' ); ?>

	</div>
</div><!-- #item-header-content -->

<div id="item-admins">

<h3><?php _e( 'Instructors', 'qm' ); ?></h3>
	<div class="item-avatar">
	<?php 
	bp_study_instructor_avatar();
	?>
	</div>
	<?php
	bp_study_instructor();

	do_action( 'bp_after_study_menu_instructors' );
	?>
</div><!-- #item-actions -->

<?php
do_action( 'bp_after_study_header' );
?>