<?php
/**
 * IPT FSQM Loader
 * The library of loader class
 *
 * @author Swashata <swashata4u@gmail.com>
 * @package WP Feedback, Surver & Quiz Manager - Pro
 * @subpackage Loader
 */

class IPT_FSQM_Loader {
	/**
	 *
	 *
	 * @deprecated
	 * @var array stores the options
	 */
	public $op;

	/**
	 * The init classes used to generate the admin menu
	 * The class should initialize and hook itself
	 *
	 * @see /classes/admin-class.php and extend from the base abstract class
	 * @staticvar array
	 */
	static $init_classes = array();

	/**
	 *
	 *
	 * @staticvar string
	 * Holds the absolute path of the main plugin file directory
	 */
	static $abs_path;

	/**
	 *
	 *
	 * @staticvar string
	 * Holds the absolute path of the main plugin file
	 */
	static $abs_file;

	/**
	 * Holds the text domain
	 * Use the string directly instead
	 * But still set this for some methods, especially the loading of the textdomain
	 */
	static $text_domain;

	/**
	 *
	 *
	 * @staticvar string
	 * The current version of the plugin
	 */
	static $version;

	/**
	 *
	 *
	 * @staticvar string
	 * The abbreviated name of the plugin
	 * Mainly used for the enqueue style and script of the default admin.css and admin.js file
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
	 * Constructor function
	 *
	 * @global array $ipt_fsqm_info The information option variable
	 * @param type    $file_loc
	 * @param type    $classes
	 * @param type    $text_domain
	 * @param type    $version
	 * @param type    $abbr
	 */
	public function __construct( $file_loc, $text_domain = 'default', $version = '1.0.0', $abbr = '', $doc = '', $sup = '' ) {
		self::$abs_path = dirname( $file_loc );
		self::$abs_file = $file_loc;
		self::$text_domain = $text_domain;
		self::$version = $version;
		self::$abbr = $abbr;
		self::$init_classes = array( 'IPT_FSQM_Dashboard', 'IPT_FSQM_All_Forms', 'IPT_FSQM_New_Form', 'IPT_FSQM_Import_Export', 'IPT_FSQM_Report', 'IPT_FSQM_View_Submission', 'IPT_FSQM_View_All_Submissions', 'IPT_FSQM_Settings' );
		self::$documentation = $doc;
		self::$support_forum = $sup;
		global $ipt_fsqm_info, $ipt_fsqm_settings;
		$ipt_fsqm_info = get_option( 'ipt_fsqm_info' );
		$ipt_fsqm_settings = get_option( 'ipt_fsqm_settings' );
	}

	public function load() {
		//activation hook
		register_activation_hook( self::$abs_file, array( $this, 'plugin_install' ) );
		// deactivation hook
		register_deactivation_hook( self::$abs_file, array( $this, 'plugin_deactivate' ) );
		//* Load Text Domain For Translations //
		add_action( 'plugins_loaded', array( $this, 'plugin_textdomain' ) );
		// Check for version and database compatibility //
		add_action( 'plugins_loaded', array( $this, 'database_version' ) );


		//var_dump(self::$init_classes);

		//admin area
		if ( is_admin() ) {
			IPT_FSQM_Form_Elements_Static::admin_init();
			//admin menu items
			add_action( 'plugins_loaded', array( $this, 'init_admin_menus' ), 20 );
			add_action( 'admin_init', array( &$this, 'gen_admin_menu' ), 20 );
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_menu_style' ) );
		} else {
			//add frontend script + style
			add_action( 'wp_print_styles', array( &$this, 'enqueue_script_style' ) );
		}
		IPT_FSQM_Form_Elements_Static::common_init();

		//other filters + actions
		//add_action($tag, $function_to_add);
		//add_filter($tag, $function_to_add);
	}

	public function init_admin_menus() {
		self::$init_classes = apply_filters( 'ipt_fsqm_admin_menus', self::$init_classes );
		foreach ( (array) self::$init_classes as $class ) {
			if ( class_exists( $class ) ) {
				global ${'admin_menu' . $class};
				${'admin_menu' . $class} = new $class();
			}
		}
	}


	public function gen_admin_menu() {
		$admin_menus = array();
		foreach ( (array) self::$init_classes as $class ) {
			if ( class_exists( $class ) ) {
				global ${'admin_menu' . $class};
				$admin_menus[] = ${'admin_menu' . $class}->get_pagehook();
			}
		}

		foreach ( $admin_menus as $menu ) {
			add_action( 'admin_print_styles-' . $menu, array( &$this, 'admin_enqueue_script_style' ) );
		}

	}

	public function admin_menu_style() {
		wp_enqueue_style( 'ipt_fsqm_admin_menu', plugins_url( '/static/admin/css/admin-menu.css', self::$abs_file ), array(), self::$version );
	}

	public function admin_enqueue_script_style() {
		$ui = IPT_Plugin_UIF_Admin::instance( self::$text_domain );
		$ui->enqueue( plugins_url( '/lib/', self::$abs_file ), self::$version );
		wp_enqueue_style( 'ipt_fsqm_ui', plugins_url( '/static/admin/css/ipt-fsqm-ui.css', self::$abs_file ), array(), self::$version );
		wp_enqueue_style( 'ipt_fsqm_preview', plugins_url( '/static/common/css/ipt-fsqm-preview.css', self::$abs_file ), array(), self::$version );

		wp_enqueue_script( 'ipt_fsqm_admin_js', plugins_url( '/static/admin/js/ipt-fsqm-admin.js', self::$abs_file ), array(), self::$version );
	}

	public function enqueue_script_style() {
		/* Everything is handled by the shortcode or the form class */
	}

	public function plugin_install( $networkwide = false ) {
		include_once self::$abs_path . '/classes/class-ipt-fsqm-install.php';

		$install = new IPT_FSQM_Install();
		$install->install( $networkwide );
	}

	public function plugin_deactivate() {
		flush_rewrite_rules();
	}

	public function database_version() {
		global $ipt_fsqm_info;
		$d_version = $ipt_fsqm_info['version'];
		$s_version = self::$version;

		if ( version_compare( $d_version, $s_version, '<' ) ) {
			include_once self::$abs_path . '/classes/class-ipt-fsqm-install.php';

			$install = new IPT_FSQM_Install();
			$install->checkop();
			$install->checkdb();
		}
	}

	/**
	 * Load the text domain on plugin load
	 * Hooked to the plugins_loaded via the load method
	 *
	 * dirname( plugin_basename( self::$abs_file ) )
	 */
	public function plugin_textdomain() {
		load_plugin_textdomain( 'ipt_fsqm', false, basename( dirname( self::$abs_file ) ) . '/translations' );
	}
}
