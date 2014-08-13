<?php

class QmErrors {
	function QmErrors() {
		$this->localizionName = '';
		$this->errors = new WP_Error();
		$this->initialize_errors();
	}
	/* get_error - Returns an error message based on the passed code
	Parameters - $code (the error code as a string)
	Returns an error message */
	function get_error($code = '') {
	global $qm_options;
	if(isset($qm_options['disable_errors']) && $qm_options['disable_errors']){return '';}
	    $errorMessage ='<div class="alert alert-block"><span class="error"></span><button type="button" class="close" data-dismiss="alert">×</button>';
		$errorMessage .= $this->errors->get_error_message($code);
		if ($errorMessage == null) {
			return __("Unknown error.", 'qm');
		}
		$errorMessage .= '  ..<a href="http://quantimodo.com/forums/showthread.php?883-Error-Notices&p=2392" target="_blank">more..</a></div>';
		return $errorMessage;
	}
	/* Initializes all the error messages */
	function initialize_errors() {
	    $this->errors->add('initialize', __('Please save the changes again.', 'qm'));
	    $this->errors->add('unknown', __('Some Uknown issue appeared, please contact us.', 'qm'));
	    $this->errors->add('unsaved_editor', __('Please save the Page Builder changes !', 'qm'));
	    $this->errors->add('slider_not_found', __('Requested Slider was not found, Please check slider post id !', 'qm'));
	    $this->errors->add('no_posts', __('No Posts Id\'s found in given Custom Post Type.', 'qm'));
	    $this->errors->add('author_not_found', __('Author Information missing.', 'qm'));
            $this->errors->add('access_denied', __('You do not have permission to do that.','qm'));
            $this->errors->add('error_tweets', __('Unable to get tweets from Twitter !','qm'));
            $this->errors->add('no_tweets', __('No public tweets found on Twitter !','qm'));
            $this->errors->add('term_taxonomy_mismatch', __('Term : Taxonomy Mismatch: Selected Term does not exist in Taxonomy !','qm'));
            $this->errors->add('term_postype_mismatch', __('Post Type : Taxonomy Mismatch: Selected Taxonomy does not exist in Post Type !','qm'));
            $this->errors->add('no_featured', __('Featured Component does not Exist !','qm'));
            $this->errors->add('incorrect_audio', __('Incorrect or Incompatible Audio Format !','qm'));
            $this->errors->add('incorrect_video', __('Incorrect or Incompatible Video Embed Code !','qm'));
            $this->errors->add('incorrect_modal', __('Modal ID Incorrect, please recheck !','qm'));
            $this->errors->add('incorrect_testimonial', __('Testimonial ID Incorrect, please recheck !','qm'));
            $this->errors->add('incorrect_post', __('Post ID Incorrect, please recheck !','qm'));
	} //end function initialize_errors
}
/*
$error = new QmErrors();$error->get_error('unsaved_editor');
 if(!isset($atts) || !isset($atts['post_type'])){
   return $error->get_error('unsaved_editor');
 }
*/
?>