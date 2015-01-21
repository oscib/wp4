=== WP Feedback, Survey & Quiz Manager Pro===
Contributors: swashata, akash
Donate link: N/A
Tags: feedback, survey, form, web-form, database form, quiz, opinion
Requires at least: 3.5
Tested up to: 3.6
License: GPLv3

Gather feedbacks and run surveys on your WordPress Blog. Stores the gathered data in database. Displays the form & trends with shortcodes.

== Description ==

Please check the online documentation http://ipanelthemes.com/fsqm-doc/#!/documenter_cover

== Developer's Notes ==
Items below are vaguley documented or not documented at all.
The reason for this is, we haven't released any of the APIs publicly and we will not
until we have them completely. Till then, if you wish to do something with them,
feel free to experiment. Also check the source code to learn more about them.

=== APIS (Undocumented) ===
-- Admin Classes (/classes/class-ipt-fsqm-admin.php) --
ipt_fsqm_submission_deleted - Hook
ipt_fsqm_submissions_deleted - Hook
ipt_fsqm_form_deleted - Hook
ipt_fsqm_forms_deleted - Hook

-- Modification of the Base Class --
ipt_fsqm_default_settings - FILTER -
ipt_fsqm_form_element_structure - FILTER -
ipt_fsqm_valid_elements - FILTER -
ipt_fsqm_form_data_structure - FILTER -

-- Data Class (/classes/class-ipt-fsqm-form-elements-data.php) --
ipt_fsqm_filter_data_errors - Filter
ipt_fsqm_filter_save_error - Filter -
ipt_fsqm_hook_save_error - Hook -
ipt_fsqm_hook_save_insert - Hook
ipt_fsqm_hook_save_update - Hook
ipt_fsqm_hook_save_success - Hook
ipt_fsqm_hook_save_fileupload - Hook

-- Static Class (/classes/class-ipt-fsqm-form-elements-static.php) --
ipt_fsqm_shortcode_wizard - Hook
ipt_fsqm_filter_static_report_print - Filter

-- Utilities Class (/classes/class-ipt-fsqm-form-elements-utilities.php)--
ipt_fsqm_filter_utilities_report_print - Filter
ipt_fsqm_report_js - Filter

-- Form Admin Class (/classes/class-ipt-fsqm-form-elements-admin.php) --
ipt_fsqm_form_updated - Hook
ipt_fsqm_form_created - Hook

-- Upload Class (/classes/class-ipt-fsqm-form-elements-uploader.php) --
ipt_fsqm_files_blacklist - Filter

-- Enqueue Hooks --
ipt_fsqm_form_elements_front_enqueue
ipt_fsqm_report_enqueue
-- Other Filters --
ipt_fsqm_form_elements_quick_preview_email_style

-- Adding Elements (valid_elements filter)

/**
 * Valid callback arguments
 *
 * callback => Responsible for
 * 				Admin 		(While creating a form),
 * 				Front 		(When showing up the form),
 * 				Data 		(For quick preview and emailing)
 *     			@param 		$element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $this
 *
 * callback_report => Responsible for
 * 				Utilities	While populating reports
 * 				@param 		bool $do_data Whether or not to show sensitive data
 * 				@return  	array data that is to be used by javascript
 *
 * callback_data_validation => Responsible for
 * 				Data 		While validating the element internally (server side)
 * 				@param  	$element, $data, $key
 * 				@return 	array Associative array with following components
 * 				            data_tampering => true | false
 * 				            required_validation => true | false, You have to return true if it passes
 * 				            errors => array('msgs'))
 *
 * callback_report_calculator => Responsible for
 * 				Static 		While calculating the Report
 * 				@param 		$element, $data, $key, $do_data, $existing_data
 * 				@return  	array data that is to be used by javascript
 */

-- Adding to Report generators --

1. Hook into valid elements to register your elements. Make sure callback_report return proper $data for JS
2. Filter ipt_fsqm_report_js and add to callbacks & gcallbacks with same element types and the name of your function
3. Hook into ipt_fsqm_report_enqueue to bring your js and css
