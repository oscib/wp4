<?php
/**
 * IPT FSQM Export Installation Class
 *
 * @package IPT FSQM Export
 * @subpackage Installation
 * @author Swashata Ghosh <swashata@intechgrity.com>
 */
class IPT_FSQM_EXP_Install {
	/**
	 * Database prefix
	 * Mainly used for MS compatibility
	 *
	 * @var string
	 */
	public $prefix;

	/**
	 * Holds any error found during installation
	 *
	 * @var array
	 */
	public $install_msg = array();

	public function __construct() {
		global $wpdb;
		$prefix = '';
		if ( is_multisite() ) {
			global $blog_id;
			$prefix = $wpdb->base_prefix . $blog_id . '_';
		} else {
			$prefix = $wpdb->prefix;
		}

		$this->prefix = $prefix;
	}

	public function install( $networkwide = false ) {
		$this->install_msg = $this->check_compatibility();
		if ( $networkwide == true ) {
			$this->install_msg[] = __( 'This plugin can not be network activated.', 'ipt_fsqm_exp' );
		}
		if ( ! empty( $this->install_msg ) ) {
			deactivate_plugins( plugin_basename( IPT_FSQM_EXP_Loader::$abs_file ) );
			wp_die( $this->incompat_notice(), __( 'Can not install Exporter for FSQM Pro', 'ipt_fsqm_exp' ), array(
				'back_link' => true,
			) );
		}
		$this->checkdb();
		$this->checkop();
	}

	public function incompat_notice() {
		return implode( '<br />', (array) $this->install_msg );
	}

	public function check_compatibility() {
		$msgs = array();
		if ( !class_exists( 'IPT_FSQM_Loader' ) ) {
			$msgs[] = __( 'The Exporter for FSQM Pro plugin requires <strong>WP Feedback, Survey & Quiz Manager - Pro</strong> to be installed and activated.', 'ipt_fsqm_exp' );
		}
		if ( version_compare( PHP_VERSION, '5.2.0', '<' ) ) {
			$msgs[] = __( 'The Exporter for FSQM Pro plugin requires PHP version greater than or equal to 5.2.x', 'ipt_fsqm_exp' );
		}
		if ( !extension_loaded( 'xml' ) ) {
			$msgs[] = __( 'The Exporter for FSQM Pro plugin requires PHP_XML extension to be loaded.', 'ipt_fsqm_exp' );
		}
		if ( !extension_loaded( 'zip' ) ) {
			$msgs[] = __( 'The Exporter for FSQM Pro plugin requires PHP_ZIP extension to be loaded.', 'ipt_fsqm_exp' );
		}
		if ( class_exists( 'IPT_FSQM_Loader' ) && version_compare( IPT_FSQM_Loader::$version, '2.1.6', '<' ) ) {
			$msgs[] = __('The Exporter for FSQM Pro plugin requires FSQM Pro Version 2.1.6 or greater.', 'ipt_fsqm_exp' );
		}
		return $msgs;
	}

	public function checkdb() {
		/**
		 * Include the necessary files
		 * Also the global options
		 */
		if ( file_exists( ABSPATH . 'wp-admin/includes/upgrade.php' ) ) {
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		} else {
			require_once ABSPATH . 'wp-admin/upgrade-functions.php';
		}
		global $charset_collate;


		$prefix = $this->prefix;
		$sqls = array();

		$sqls[] = "CREATE TABLE {$prefix}fsq_export (
			id BIGINT(20) UNSIGNED NOT NULL auto_increment,
			form_id BIGINT(20) UNSIGNED NOT NULL default 0,
			created DATETIME NOT NULL default '0000-00-00 00:00:00',
			complete TINYINT(1) UNSIGNED NOT NULL default 1,
			start_date DATETIME NOT NULL default '0000-00-00 00:00:00',
			end_date DATETIME NOT NULL default '0000-00-00 00:00:00',
			survey LONGTEXT NOT NULL,
			feedback LONGTEXT NOT NULL,
			PRIMARY KEY  (id)
	  	) $charset_collate";
		$sqls[] = "CREATE TABLE {$prefix}fsq_exp_raw (
			id BIGINT(20) UNSIGNED NOT NULL auto_increment,
			form_id BIGINT(20) UNSIGNED NOT NULL default 0,
			created DATETIME NOT NULL default '0000-00-00 00:00:00',
			complete TINYINT(1) UNSIGNED NOT NULL default 1,
			start_date DATETIME NOT NULL default '0000-00-00 00:00:00',
			end_date DATETIME NOT NULL default '0000-00-00 00:00:00',
			mcq LONGTEXT NOT NULL,
			freetype LONGTEXT NOT NULL,
			pinfo LONGTEXT NOT NULL,
			PRIMARY KEY  (id)
		) $charset_collate";

		foreach ( $sqls as $sql ) {
			dbDelta( $sql );
		}
	}


	public function checkop() {
		$prefix = $this->prefix;
		$ipt_fsqm_exp_info = array(
			'version' => IPT_FSQM_EXP_Loader::$version,
			'exp_table' => $prefix . 'fsq_export',
			'raw_table' => $prefix . 'fsq_exp_raw',
		);
		$ipt_fsqm_exp_settings = array(
			'html_header' => '<p style="text-align: center;"><img style="display: block; margin: 0 auto 10px;" src="' . plugins_url( '/static/admin/images/pdf-image.png', IPT_FSQM_EXP_Loader::$abs_file ) . '" /></p>',
			'html_footer' => '<p style="text-align: center;">' . sprintf( __('Copyright %s - %s | All rights reserved', 'ipt_fsqm_exp'), get_bloginfo( 'name' ), date( 'Y', current_time( 'timestamp' ) ) ) . '</p>',
			'memory' => '256',
			'execution_time' => '200',
			'download_pdf' => false,
			'style' => '
/* Main Page */
html {
	font-family: Arial, Helvetica, sans-serif;
	font-size: 11pt;
	background-color: #fff;
}
/* Headings */
h1, h2, h3, h4, h5, h6 {
	font-family: Arial, Helvetica, sans-serif;
}
/* Grids and Tables */
table {
	margin: 20px auto;
	width: 100%;
	border-collapse: collapse;
	vertical-align: middle;
}
/* Cells */
table td,
table th {
	padding: 10px;
	border: 1px solid #01a8Bd;
}
/* Heading Rows and Cells */
table tr.row0 td.column0,
table thead tr th,
table tfoot tr th,
table tr.head th {
	background-color: #3ac7ff;
	color: #fff;
	text-shadow: 0 0 2px #666;
}
			',
			'delete_uninstall' => false,
		);

		if ( !get_option( 'ipt_fsqm_exp_info' ) ) {
			add_option( 'ipt_fsqm_exp_info', $ipt_fsqm_exp_info );
			add_option( 'ipt_fsqm_exp_settings', $ipt_fsqm_exp_settings );
		} else {
			$old_option = get_option( 'ipt_fsqm_exp_info' );
			switch ( $old_option['version'] ) {
			default :
			case '1.0.0' :
				//new installation
				break;
			case '1.0.1' :
				// still nothing
				break;
			}
			update_option( 'ipt_fsqm_exp_info', $ipt_fsqm_exp_info );
			$settings = wp_parse_args( get_option( 'ipt_fsqm_exp_settings', $ipt_fsqm_exp_settings ), $ipt_fsqm_exp_settings );
			update_option( 'ipt_fsqm_exp_settings', $settings );
		}

		global $ipt_fsqm_exp_info, $ipt_fsqm_exp_settings;
		$ipt_fsqm_exp_info = get_option( 'ipt_fsqm_exp_info' );
		$ipt_fsqm_exp_settings = get_option( 'ipt_fsqm_exp_settings' );
	}
}
