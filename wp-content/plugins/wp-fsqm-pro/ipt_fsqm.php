<?php
/*
Plugin Name: WP Feedback, Survey & Quiz Manager - Pro
Plugin URI: http://ipanelthemes.com/fsqm/
Description: A robust plugin to gather feedback and run survey or host Quiz on your WordPress Blog. Stores the gathered data on database.
Author: iPanelThemes
Version: 2.4.0
Author URI: http://ipanelthemes.com/
License: GPLv3
Text Domain: ipt_fsqm
 */

/**
 * Copyright iPanelThemes.com, 2013
 * This WordPress Plugin is comprised of two parts: (1) The PHP code and integrated
 * HTML are licensed under the GPL license as is WordPress itself.
 * You will find a copy of the license text in the same directory as this text file.
 * Or you can read it here: http://wordpress.org/about/gpl/
 * (2) All other parts of the plugin including,
 * but not limited to the CSS code, images, and design are licensed according to
 * the license purchased. Read about licensing details here:
 * http://themeforest.net/licenses/regular_extended
 */

include_once dirname( __FILE__ ) . '/classes/class-ipt-fsqm-loader.php';
include_once dirname( __FILE__ ) . '/classes/class-ipt-fsqm-form-elements-base.php';
include_once dirname( __FILE__ ) . '/classes/class-ipt-fsqm-form-elements-uploader.php';
include_once dirname( __FILE__ ) . '/classes/class-ipt-fsqm-form-elements-data.php';
include_once dirname( __FILE__ ) . '/classes/class-ipt-fsqm-form-elements-utilities.php';
include_once dirname( __FILE__ ) . '/classes/class-ipt-fsqm-form-elements-static.php';
include_once dirname( __FILE__ ) . '/classes/class-ipt-fsqm-form-elements-front.php';
include_once dirname( __FILE__ ) . '/lib/classes/class-ipt-plugin-uif-base.php';
include_once dirname( __FILE__ ) . '/lib/classes/class-ipt-plugin-uif-front.php';

if ( is_admin() ) {
	include_once dirname( __FILE__ ) . '/classes/class-ipt-fsqm-form-elements-admin.php';
	include_once dirname( __FILE__ ) . '/classes/class-ipt-fsqm-admin.php';
	include_once dirname( __FILE__ ) . '/lib/classes/class-ipt-plugin-uif-admin.php';
} else {

}

/**
 * Holds the plugin information
 *
 * @global array $ipt_fsqm_info
 */
global $ipt_fsqm_info;

/**
 * Holds the global settings
 *
 * @global array $ipt_fsqm_settings
 */
global $ipt_fsqm_settings;

$ipt_fsqm = new IPT_FSQM_Loader( __FILE__, 'ipt_fsqm', '2.4.0', 'ipt_fsqm', 'http://ipanelthemes.com/kb/fsqm/', 'http://ipanelthemes.com/kb/support/forum/wordpress-plugins/wp-feedback-survey-quiz-manager-pro/' );

$ipt_fsqm->load();
