<?php
// Checks if Study Module is Installed
define( 'BP_STUDY_MOD_INSTALLED', 1 );

// Checks the Study Module Version and necessary changes are hooked to this component
define( 'BP_STUDY_MOD_VERSION', '1.0' );

// FILE PATHS of Study Module
define( 'BP_STUDY_MOD_PLUGIN_DIR', dirname( __FILE__ ) );

/* Database Version for Study Module */
define ( 'BP_STUDY_DB_VERSION', '1' );

define ( 'BP_STUDY_CPT', 'study' );
define ( 'BP_STUDY_SLUG', 'study' );


/* Only load the component if BuddyPress is loaded and initialized. */
function bp_study_init_study() {
	// Because our loader file uses BP_Component, it requires BP 1.5 or greater.
	if ( version_compare( BP_VERSION, '1.3', '>' ) )
		require( dirname( __FILE__ ) . '/includes/bp-study-loader.php' );
}
add_action( 'bp_include', 'bp_study_init_study' );

/* Setup procedures to be run when the plugin */
function bp_study_activate_study() {

}
register_activation_hook( __FILE__, 'bp_study_activate_study' );

/* clean up On deacativation */
function bp_study_deactivate_study() {
	
}
register_deactivation_hook( __FILE__, 'bp_study_deactivate_study' );



?>
