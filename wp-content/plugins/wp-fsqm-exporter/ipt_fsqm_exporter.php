<?php
/*
Plugin Name: Exporter for FSQM Pro
Plugin URI: http://ipanelthemes.com/fsqm/
Description: Extends WP Feedback, Survey & Quiz Manager Pro - v2 plugin to add the ability to export reports to XLSX, PDF, HTML XLS and/or submissions to CSV files.
Author: iPanelThemes
Version: 1.1.0
Author URI: http://ipanelthemes.com/
License: GPLv3
Text Domain: ipt_fsqm_exp
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
// Workaround for JPGraph 3.5 on Ubuntu per 0015246 @link http://www.mantisbt.org/bugs/view.php?id=15246
if( !function_exists( 'imageantialias' ) ) {
	function imageantialias( $image, $enabled ) {
		return false;
	}
}
require_once dirname( __FILE__ ) . '/classes/class-ipt-fsqm-exp-loader.php';
require_once dirname( __FILE__ ) . '/classes/class-ipt-fsqm-exp-export-api.php';

if ( is_admin() ) {

}

global $ipt_fsqm_exp_info, $ipt_fsqm_exp_settings;

$ipt_fsqm_exp = new IPT_FSQM_EXP_Loader( __FILE__, 'ipt_fsqm_exp', '1.1.0', 'ipt_fsqm_exp', 'http://ipanelthemes.com/fsqm-exporter-doc/', 'http://support.ipanelthemes.com/viewforum.php?f=9' );
$ipt_fsqm_exp->load();
