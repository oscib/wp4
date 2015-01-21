<?php
/**
 * IPT FSQM Export Loader
 *
 * @package IPT FSQM Export
 * @subpackage Loader
 * @author Swashata Ghosh <swashata@intechgrity.com>
 */
class IPT_FSQM_EXP_Loader {
	/**
	 * Holds the absolute path of the main plugin file directory
	 *
	 * @staticvar string
	 */
	static $abs_path;

	/**
	 * Holds the absolute path of the main plugin file
	 *
	 * @staticvar string
	 */
	static $abs_file;

	/**
	 * Holds the text domain
	 * Use the string directly instead
	 * But still set this for some methods, especially the loading of the textdomain
	 *
	 * @staticvar string
	 */
	static $text_domain;

	/**
	 * The current version of the plugin
	 *
	 * @staticvar string
	 */
	static $version;

	/**
	 * The abbreviated name of the plugin
	 * Mainly used for the enqueue style and script of the default admin.css and admin.js file
	 *
	 * @staticvar string
	 */
	static $abbr;

	/**
	 * The Documentation Link - From InTechgrity
	 *
	 * @var string
	 */
	static $documentation;

	/**
	 * The support forum link - From WordPress Extends
	 *
	 * @var string
	 */
	static $support_forum;

	/**
	 * Constructor
	 *
	 * @param string  $file_loc    The __FILE__ magic constant from the main file of the plugin
	 * @param string  $text_domain Text domain of the plugin
	 * @param string  $version     Version of the plugin
	 * @param string  $abbr        Abbr of the plugin (should be same as text domain)
	 * @param string  $doc         URL to documentation, for quick fetching purpose
	 * @param string  $sup         Support forum to the plugin, for quick fetching purpose
	 */
	public function __construct( $file_loc, $text_domain = 'default', $version = '1.0.0', $abbr = '', $doc = '', $sup = '' ) {
		self::$abs_path = dirname( $file_loc );
		self::$abs_file = $file_loc;
		self::$text_domain = $text_domain;
		self::$version = $version;
		self::$abbr = $abbr;
		self::$documentation = $doc;
		self::$support_forum = $sup;
		global $ipt_fsqm_exp_info, $ipt_fsqm_exp_settings;
		$ipt_fsqm_exp_info = get_option( 'ipt_fsqm_exp_info' );
		$ipt_fsqm_exp_settings = get_option( 'ipt_fsqm_exp_settings' );
	}

	public function load() {
		//activation hook
		register_activation_hook( self::$abs_file, array( $this, 'plugin_install' ) );
		//Load Text Domain For Translations
		add_action( 'plugins_loaded', array( &$this, 'plugin_textdomain' ) );
		//Check for version and database compatibility
		add_action( 'plugins_loaded', array( &$this, 'database_version' ) );

		if ( is_admin() ) {
			add_filter( 'ipt_fsqm_admin_menus', array( $this, 'admin_menus' ) );
			IPT_FSQM_EXP_Export_API::admin_load();
		}
		IPT_FSQM_EXP_Export_API::common_init();
	}

	public function admin_menus( $admin_classes ) {
		require_once self::$abs_path . '/classes/class-ipt-fsqm-exp-form-elements-export-raw.php';
		require_once self::$abs_path . '/classes/class-ipt-fsqm-exp-admin.php';
		$admin_classes[] = 'IPT_FSQM_EXP_Export_Report';
		$admin_classes[] = 'IPT_FSQM_EXP_View_All_Reports';
		$admin_classes[] = 'IPT_FSQM_EXP_Export_CSV';
		$admin_classes[] = 'IPT_FSQM_EXP_View_All_CSV';
		$admin_classes[] = 'IPT_FSQM_EXP_Settings';
		return $admin_classes;
	}

	public function plugin_install( $networkwide = false ) {
		require_once self::$abs_path . '/classes/class-ipt-fsqm-exp-install.php';
		$install = new IPT_FSQM_EXP_Install();
		$install->install( $networkwide );
	}

	public function plugin_textdomain() {
		load_plugin_textdomain( 'ipt_fsqm_exp', false, dirname( plugin_basename( self::$abs_file ) ) . '/translations/' );
	}

	public function database_version() {
		global $ipt_fsqm_exp_info;
		$d_version = is_array($ipt_fsqm_exp_info) && isset($ipt_fsqm_exp_info['version']) ? $ipt_fsqm_exp_info['version'] : '0';
		$s_version = self::$version;
		if ( $d_version != $s_version ) {
			$this->plugin_install();
		}
	}
}
