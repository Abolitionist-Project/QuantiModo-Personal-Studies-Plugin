<?php
/**
 * 	Template Name: Bargraph page
 * 	Description: Page based on original PHP website
 */
function quantimodo_personal_studies_scripts_css()
{
// Register Scripts and css 

    wp_register_script( 'jquery-dropdown', plugins_url( '/js/libs/jquery.dropdown.min.js', __FILE__ ), array( 'jquery' ) );
	wp_register_script( 'jquery-datetimepicker', plugins_url( '/js/libs/jquery.datetimepicker.js', __FILE__ ), array( 'jquery' ) );
	wp_register_script( 'jquery-touch', plugins_url( '/js/libs/jquery.ui.touch-punch.min.js', __FILE__ ), array( 'jquery' ) );
	wp_register_script( 'jquery-fancybox', plugins_url( '/js/libs/jquery.fancybox.pack.js', __FILE__ ), array( 'jquery' ) );
	wp_register_script( 'qm-math', plugins_url( '/js/math.js', __FILE__ ), array( 'jquery' ) );
	wp_register_script( 'timezone', plugins_url( '/js/jstz.min.js', __FILE__ ), array( 'jquery' ));
    wp_register_script( 'qm-sdk', plugins_url( '/js/libs/quantimodo-api.js', __FILE__ ), array( 'jquery' ) );
    wp_register_script( 'jquery-simpletip', plugins_url( '/js/libs/jquery.simpletip-1.3.1.js', __FILE__ ), array( 'jquery' ) );		
	wp_register_script( 'highcharts', plugins_url( '/js/libs/highstock.js', __FILE__  ), array( 'jquery' ) );		
    wp_register_script( 'highcharts-more', plugins_url( '/js/libs/highcharts-more.js', __FILE__  ), array( 'highcharts' ) );
    wp_register_script( 'correlate-charts', plugins_url( '/js/bargraph_charts.js', __FILE__ ) );	
	
	wp_register_style( 'correlate', plugins_url( '/css/bargraph.css', __FILE__  ));
	wp_register_style( 'shared-styles', plugins_url( '/css/_shared_styles.css', __FILE__  ));
	wp_register_style( 'jquery-ui-flick', plugins_url( '/css/jquery-ui-flick.css', __FILE__  ));
	wp_register_style( 'jquery-dropdown', plugins_url( '/css/jquery.dropdown.css', __FILE__  ));
	wp_register_style( 'jquery-fancybox', plugins_url( '/css/jquery.fancybox.css', __FILE__  ));
	wp_register_style( 'jquery-tip', plugins_url( '/css/simpletip.css', __FILE__  ));
	wp_register_style( 'seperator', plugins_url( '/css/seperator.css', __FILE__  ));

	


//Execute Scripts and css
wp_enqueue_style("seperator");
wp_enqueue_style("correlate");
wp_enqueue_style("shared-styles");
wp_enqueue_style("jquery-ui-flick");
wp_enqueue_style("jquery-fancybox");
wp_enqueue_style("jquery-dropdown");
wp_enqueue_style("jquery-tip");
wp_enqueue_style("jquery-datetimepicker");

wp_enqueue_script("jquery", true);
wp_enqueue_script("jquery-ui-core");
wp_enqueue_script("jquery-ui-dialog");
wp_enqueue_script("jquery-ui-datepicker");
wp_enqueue_script("jquery-ui-button");
wp_enqueue_script("jquery-ui-sortable");
wp_enqueue_script("jquery-ui-autocomplete");
wp_enqueue_script("jquery-dropdown", "jquery");
wp_enqueue_script("jquery-datetimepicker","jquery");
wp_enqueue_script("jquery-touch", "jquery");
wp_enqueue_script("jquery-fancybox","jquery");
wp_enqueue_script("qm-math", false, true);
wp_enqueue_script("timezone", "jquery", false, true);
wp_enqueue_script("qm-sdk", false, true);
wp_enqueue_script("jquery-simpletip", "jquery", false, true);
wp_enqueue_script("highcharts", "jquery", false, true);
wp_enqueue_script("highcharts-more", "highcharts", false, true);
wp_enqueue_script("correlate-charts", array("highcharts-more", "qm-sdk", "qm-math"), false, true);

wp_enqueue_script("other-shared", $stylesheet_dir . "/js/_other_shared.js", array("jquery"), false, true);
wp_enqueue_script("variable-settings", $stylesheet_dir . "/js/_variable_settings.js", array("jquery"), false, true);
wp_enqueue_script("refresh-shared", $stylesheet_dir . "/js/_data_refresh.js", array("jquery"), false, true);
wp_enqueue_script("variable-picker", $stylesheet_dir . "/js/_variable_picker.js", array("jquery"), false, true);

wp_enqueue_script("correlate", $stylesheet_dir . "/js/bargraph.js", array("correlate-charts", "jquery-ui-datepicker", "jquery-ui-button"), false, true);
}
add_action( 'wp_enqueue_scripts', 'quantimodo_personal_studies_scripts_css' );

get_header();
?>

<?php if (!is_user_logged_in()): ?>
    <div class="dialog-background" id="login-dialog-background"></div>
    <div class="dialog" id="login-dialog">
        <?php login_with_ajax(); ?>
    </div>
<?php endif; ?>


<?php require "modules/dialog_add_measurement.php"; ?>
<?php require "modules/dialog_delete_measurements.php"; ?>
<?php require "modules/dialog_share.php"; ?>
<?php require "modules/variable_settings.php"; ?>

<section id="content">

     
        <div class="container">
             <!-- Fetch Post data and title -->
			 <div id="content" class="widecolumn">
               <?php if (have_posts()) : while (have_posts()) : the_post();?>
               <div class="post">
               <h1 id="post-<?php the_ID(); ?>" style="text-align:center;"><?php the_title();?></h1>
               <div class="entrytext">
                 <?php the_content('<p class="serif">Read the rest of this page »</p>'); ?>
               </div>
             </div>
        <?php endwhile; endif; ?>
		<div class="separator"></div>
        </div>
        	 <div class="row">
             <!-- Content -->
                <div class="col-md-10 col-sm-9">
                    <section id="section-configure">
                       <?php include 'modules/variable_picker.php'; ?>
                    </section>

                    <section id="section-bargraph">
                        <div id="bar-graph">
                            <header class="card-header">
                                <h3 class="heading">
                                    <span>Correlations</span>
                                    <div id="gauge-timeline-settingsicon" data-dropdown="#dropdown-barchart-settings" class="gear-icon"></div>
                                    <div class="icon-question-sign icon-large questionMark"></div>
                                </h3>
                            </header>
                            <div class="graph-content">
                                <div style="width:100%;text-align: center;">
                                    <img src=" <?php echo $stylesheet_dir; ?>/css/images/brainloading.gif" class="barloading" style="margin-top: 20px; display:none" />
                                </div>
                                <span class="no-data" style="display:none"> <br />  <center> No data found </center> <br /><br /></span>
                                <div id="graph-bar"></div>
                                <input type="hidden"  id="selectBargraphInputVariable" value="" />
                                <input type="hidden"  id="selectBargraphInputCategory" value="" />
                            </div>
                        </div>                        
                    </section>


                </div>
            </div>

            <div id="section-analyze" style="display: none;">
                <div class="open" id="correlation-gauge">
                    <div class="inner">
                        <header id="card-header-detail" class="card-header">
                            <h3 class="heading">
                                <span>Details</span>
                            </h3>
                        </header>
                        <div id="gauge-graph-content" class="graph-content" style="width: 100%; overflow: hidden;">
                            <div style="float: left;  width: 360px; margin-top: -10px; margin-left: 10px; height:400px;" id="gauge-correlation"></div>
                            <div style="overflow: hidden; height: 70%; padding-left: 5px;">
                                <table style="height: 100%;">
                                    <tr>
                                        <td>
                                            <strong>Statistical Relationship</strong>
                                        </td>
                                        <td id="statisticalRelationshipValue">
                                            Significant
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <strong>Effect Size</strong>
                                        </td>
                                        <td id="effectSizeValue">
                                            Large
                                        </td>
                                    </tr>

                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="open" id="scatterplot-graph" >
                    <div class="inner">                               
                        <header class="card-header">
                            <h3 class="heading">
                                <span>Scatterplot</span>
                                <div id="graph-scatterplot-settingsicon" data-dropdown="#dropdown-scatterplot-settings" class="gear-icon"></div>
                                <div class="icon-plus-sign icon-2x plusMark" title="Click to add a measurement."></div>
                                <div class="icon-question-sign icon-large questionMark questionMarkPlus"></div>
                            </h3>
                        </header>                                   


                        <div class="graph-content" id="graph-scatterplot"></div>             

                    </div>
                </div>                        
                <div id="timeline-graph">                            
                    <header class="card-header">
                        <h3 class="heading">
                            <span>Timeline</span>
                            <div id="gauge-timeline-settingsicon" data-dropdown="#dropdown-timeline-settings" class="gear-icon"></div>              
                            <div class="icon-plus-sign icon-2x plusMark" title="Click to add a measurement." ></div>
                            <div class="icon-question-sign icon-large questionMark questionMarkPlus"></div>                                    
                        </h3>
                    </header>                            
                    <div class="graph-content" id="graph-timeline"></div>
                </div>

            </div>


            <!-- Menu for correlation gauge settings -->
            <div id="dropdown-gauge-settings" class="dropdown dropdown-tip">
                <ul class="dropdown-menu">
                    <li><a id="shareCorrelationGauge">Share graph</a></li>
                </ul>
            </div>
            <!-- Menu for timeline settings -->
            <div id="dropdown-timeline-settings" class="dropdown dropdown-tip dropdown-anchor-right">
                <ul class="dropdown-menu">
                    <li><label><input name="tl-enable-markers" type="checkbox" /> Show markers</label></li>
                    <li><label><input name="tl-smooth-graph" type="checkbox" /> Smoothen graph</label></li>
                    <li><label><input name="tl-enable-horizontal-guides" type="checkbox" /> Show horizontal guides</label></li>
                    <li class="dropdown-divider"></li>
                    <li><a id="shareTimeline">Share graph</a></li>
                </ul>
            </div>

            <!-- Menu for timeline settings -->
            <div id="dropdown-scatterplot-settings" class="dropdown dropdown-tip dropdown-anchor-right">
                <ul class="dropdown-menu">
                    <li><label><input name="sp-show-linear-regression" type="checkbox" /> Show linear regression</label></li>
                    <li class="dropdown-divider"></li>
                    <li><a id="shareScatterplot" >Share graph</a></li>
                </ul>
            </div>

            <!-- Menu for barchart settings -->
            <div id="dropdown-barchart-settings" class="dropdown dropdown-tip dropdown-anchor-right">
                <ul class="dropdown-menu">
                    <li><a id="" onclick="sortByCorrelation()">Sort By Correlation</a></li>		
                    <li><a id="shareScatterplot"  onclick="sortByCausality()">Sort By Causality Factor</a></li>
                    <li  style="padding:3px 15px;"><input type="text" id="minimumNumberOfSamples" placeholder="Min. Number of Samples"></li>
                </ul>
            </div>

        </div>
    </div>
</section>

<?php
get_footer('buddypress');
?>