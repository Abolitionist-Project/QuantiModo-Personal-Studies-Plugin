<?php get_header( 'buddypress' );

 ?>
<section id="content">
	<div id="buddypress">
	    <div class="container">
	        <div class="row">
	            <div class="col-md-3 col-sm-3">
					<?php if ( bp_study_has_items() ) : while ( bp_study_has_items() ) : bp_study_the_item(); ?>

					<?php do_action( 'bp_before_study_home_content' ); ?>

					<div id="item-header" role="complementary">

						<?php locate_template( array( 'study/single/study-header.php' ), true ); ?>

					</div><!-- #item-header -->
			
				<div id="item-nav">
					<div class="item-list-tabs no-ajax" id="object-nav" role="navigation">
						<ul>
							<?php bp_get_options_nav(); ?>
							<li id="home" class="selected"><a href="<?php bp_study_permalink(); ?>"><?php  _e( 'Home', 'qm' ); ?></a></li>
							<li id="home"><a href="<?php bp_study_permalink(); ?>structure"><?php  _e( 'Structure', 'qm' ); ?></a></li>

							<?php 
							if(is_super_admin() || is_instructor()){
								?>
								<li id="home"><a href="<?php bp_study_permalink(); ?>"><?php  _e( 'Admin', 'qm' ); ?></a></li>
								<li id="home"><a href="<?php bp_study_permalink(); ?>"><?php  _e( 'Units', 'qm' ); ?></a></li>
								<?php
							}
							?>
							<?php do_action( 'bp_study_options_nav' ); ?>
						</ul>
					</div>
				</div><!-- #item-nav -->
			</div>
			<div class="col-md-9 col-sm-9">	
			<?php do_action( 'template_notices' ); ?>
			<div id="item-body">

				<?php 
				
				do_action( 'bp_before_study_body' );

				/**
				 * Does this next bit look familiar? If not, go check out WordPress's
				 * /wp-includes/template-loader.php file.
				 *
				 * @todo A real template hierarchy? Gasp!
				 */

				
					

					// Looking at home location
					if ( bp_is_study_home() ) :

						// Use custom front if one exists
						$custom_front = locate_template( array( 'study/single/front.php' ) );
						if     ( ! empty( $custom_front   ) ) : load_template( $custom_front, true );
						
						elseif ( bp_is_active( 'structure'  ) ) : locate_template( array( 'study/single/structure.php'  ), true );

						// Otherwise show members
						elseif ( bp_is_active( 'members'  ) ) : locate_template( array( 'study/single/members.php'  ), true );

						endif;

					// Not looking at home
					else :
						

						// Study Admin/Instructor
						if     ( bp_is_study_admin_page() ) : locate_template( array( 'study/single/admin.php'        ), true );

						// Study Members
						elseif ( bp_is_study_members()    ) : locate_template( array( 'study/single/members.php'      ), true );


						// Anything else (plugins mostly)
						else  : 
							
							locate_template( array( 'study/single/plugins.php'      ), true );

						endif;
					endif;


				do_action( 'bp_after_study_body' ); ?>

			</div><!-- #item-body -->

			<?php do_action( 'bp_after_study_home_content' ); ?>

			<?php endwhile; endif; ?>
			</div>

		</div><!-- .padder -->
		<div class="row">
		<?php
			// Looking at home location
					if ( bp_is_study_home() ) :
						if     ( ! empty( $custom_front   ) ){
							echo '<h3>'.__('Related Studies','qm').'</h3>';
						}
					endif;	
		?>
		</div>
	</div><!-- #container -->
	</div>
</section>	
<?php get_footer( 'buddypress' ); ?>