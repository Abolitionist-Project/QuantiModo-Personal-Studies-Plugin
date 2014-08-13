<?php get_header( 'buddypress' ); 

global $bp;


if(bp_is_study_component()){
	if(bp_is_single_item()){
		bp_core_load_template('study/single/home');
	}
}


?>

<section id="memberstitle">
    <div class="container">
        <div class="row">
             <div class="col-md-9 col-sm-8">
                <div class="pagetitle">
                	<?php
                		all_study_page_title();
                	?>
                </div>
            </div>
            <div class="col-md-3 col-sm-4">
            	<?php 
            		$teacher_form = qm_get_option('teacher_form');
            		
					echo '<a href="'.(isset($teacher_form)?get_page_uri($teacher_form):'#').'" class="button create-group-button full">'. __( 'Become a Teacher', 'qm' ).'</a>';
				?>
            </div>
        </div>
    </div>
</section>
<section id="content">
	<div id="buddypress">
    <div class="container">

	<?php do_action( 'bp_before_directory_study_page' ); ?>

		<div class="padder">

		<?php do_action( 'bp_before_directory_study' ); ?>
		<div class="row">
			<div class="col-md-9 col-sm-8">
				<form action="" method="post" id="study-directory-form" class="dir-form">

					<?php do_action( 'bp_before_directory_study_content' ); ?>

					<?php do_action( 'template_notices' ); ?>

					<div class="item-list-tabs" role="navigation">
						<ul>
							<li class="selected" id="study-all"><a href="<?php echo trailingslashit( bp_get_root_domain() . '/' . bp_get_study_root_slug() ); ?>"><?php printf( __( 'All Studies <span>%s</span>', 'qm' ), bp_study_get_total_study_count( ) ); ?></a></li>

							<?php if ( is_user_logged_in() ) : ?>

								<li id="study-personal"><a href="<?php echo trailingslashit( bp_loggedin_user_domain() . bp_get_study_slug() . 'study' ); ?>"><?php printf( __( 'My Studies <span>%s</span>', 'qm' ), bp_study_get_total_study_count_for_user( bp_loggedin_user_id() ) ); ?></a></li>

							<?php endif; ?>

							<?php do_action( 'bp_study_directory_filter' ); ?>

						</ul>
					</div><!-- .item-list-tabs -->
					<div class="item-list-tabs" id="subnav" role="navigation">
						<ul>
							<?php do_action( 'bp_study_directory_study_types' ); ?>
							<li>
								<div id="group-dir-search" class="dir-search" role="search">
									<?php bp_directory_study_search_form(); ?>
								</div><!-- #group-dir-search -->
							</li>
							<li id="groups-order-select" class="last filter">

								<label for="groups-order-by"><?php _e( 'Order By:', 'qm' ); ?></label>
								<select id="groups-order-by">
									<option value="alphabetical"><?php _e( 'Alphabetical', 'qm' ); ?></option>
									<option value="popular"><?php _e( 'Most Members', 'qm' ); ?></option>
									<option value="newest"><?php _e( 'Newly Created', 'qm' ); ?></option>
									<option value="rated"><?php _e( 'Highest Rated', 'qm' ); ?></option>

									<?php do_action( 'bp_study_directory_order_options' ); ?>

								</select>
							</li>
						</ul>
					</div>
					<div id="study-dir-list" class="study dir-list">

						<?php  
					
							include('study-loop.php' );  
							?>

					</div><!-- #studies-dir-list -->

					<?php do_action( 'bp_directory_study_content' ); ?>

					<?php wp_nonce_field( 'directory_study', '_wpnonce-study-filter' ); ?>

					<?php do_action( 'bp_after_directory_study_content' ); ?>


				</form><!-- #study-directory-form -->
			</div>	
			<div class="col-md-3 col-sm-3">
				<?php get_sidebar( 'buddypress' ); ?>
			</div>
		</div>	
		<?php do_action( 'bp_after_directory_study' ); ?>

		</div><!-- .padder -->
	
	<?php do_action( 'bp_after_directory_study_page' ); ?>
</div><!-- #content -->
</div>
</section>

<?php get_footer( 'buddypress' ); ?>

