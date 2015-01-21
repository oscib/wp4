<?php
/**
 * iPanelThemes User Interface for Plugin's Framework
 * Base Class
 *
 * Generates all user interface/form elements
 * It needs to have the ipt-plugin-uif.css and ipt-plugin-uif.js file
 *
 * @depends jQuery, jQueryUI{core, widget, tabs, slider, spinner, dialog, mouse, datepicker, draggable, droppable, sortable, progressbar}
 *
 * @version 1.0.2
 */
if ( !class_exists( 'IPT_Plugin_UIF_Base' ) ) :
	class IPT_Plugin_UIF_Base {
	/**
	 * Store all the instances
	 *
	 * @static
	 * @var array
	 */
	static $instance = array();

	public $text_domain;

	public $version;

	public $static_location;

	public $ui_theme_location;

	/*==========================================================================
	 * FILE DEPENDENCIES
	 *========================================================================*/
	public function enqueue( $static_location, $version, $ignore_css = array(), $ignore_js = array() ) {
		global $wp_locale;
		$datetime_l10n = array(
			'closeText'         => __( 'Done', $this->text_domain ),
			'currentText'       => __( 'Today', $this->text_domain ),
			'tcurrentText' => __( 'Now', $this->text_domain ),
			'monthNames'        => array_values( $wp_locale->month ),
			'monthNamesShort'   => array_values( $wp_locale->month_abbrev ),
			'monthStatus'       => __( 'Show a different month', $this->text_domain ),
			'dayNames'          => array_values( $wp_locale->weekday ),
			'dayNamesShort'     => array_values( $wp_locale->weekday_abbrev ),
			'dayNamesMin'       => array_values( $wp_locale->weekday_initial ),
			// get the start of week from WP general setting
			'firstDay'          => get_option( 'start_of_week' ),
			// is Right to left language? default is false
			'isRTL'             => $wp_locale->is_rtl(),
			'amNames' => array( __( 'AM', $this->text_domain ), __( 'A', $this->text_domain ) ),
			'pmNames' => array( __( 'PM', $this->text_domain ), __( 'P', $this->text_domain ) ),
			/* translators: Change %s to the time suffix. %s is always replaced by an empty string */
			'timeSuffix' => sprintf( _x( '%s', 'timeSuffix', $this->text_domain ), '' ),
			'timeOnlyTitle' => __( 'Choose Time', $this->text_domain ),
			'timeText' => __( 'Time', $this->text_domain ),
			'hourText' => __( 'Hour', $this->text_domain ),
			'minuteText' => __( 'Minute', $this->text_domain ),
			'secondText' => __( 'Second', $this->text_domain ),
			'millisecText' => __( 'Millisecond', $this->text_domain ),
			'microsecText' => __( 'Microsecond', $this->text_domain ),
			'timezoneText' => __( 'Timezone', $this->text_domain ),
		);
		$static_location = trailingslashit( $static_location );
		$this->static_location = $static_location;
		$this->version = $version;
		global $wp_version;
		$ui_theme_location = 'css/jquery.ui.ipt-uif';
		if ( version_compare( $wp_version, '3.6' ) == -1 ) {
			$ui_theme_location .= '.1.9';
		} else {
			$ui_theme_location .= '.1.10';
		}
		$this->ui_theme_location = $ui_theme_location;
		//Styles
		$styles = array(
			'ipt-plugin-uif-jquery-ui' => array( $static_location . $ui_theme_location . '/ipt-uif.min.css', array() ),
			'ipt-icomoon-fonts' => array( $static_location . 'fonts/icomoon.css', array() ),
		);
		foreach ( $styles as $style_id => $style_prop ) {
			if ( ! in_array( $style_id, $ignore_css ) ) {
				if ( empty( $style_prop ) ) {
					wp_enqueue_style( $style_id );
				} else {
					wp_enqueue_style( $style_id, $style_prop[0], $style_prop[1], $version );
				}
			}
		}

		//Scripts
		$scripts = array(
			'jquery-ui-core' => array(),
			'jquery-ui-widget' => array(),
			'jquery-ui-mouse' => array(),
			'jquery-ui-draggable' => array(),
			'jquery-ui-droppable' => array(),
			'jquery-ui-sortable' => array(),
			'jquery-ui-datepicker' => array(),
			'jquery-ui-dialog' => array(),
			'jquery-ui-tabs' => array(),
			'jquery-ui-slider' => array(),
			'jquery-ui-spinner' => array(),
			'jquery-ui-progressbar' => array(),
			/* Give more generic names to libraries #19 */
			'jquery-timepicker-addon' => array( $static_location . 'js/jquery-ui-timepicker-addon.js', array( 'jquery', 'jquery-ui-datepicker' ) ),
			'jquery-print-element' => array( $static_location . 'js/jquery.printElement.min.js', array( 'jquery' ) ),
			'jquery-mwheelIntent' => array( $static_location . 'js/mwheelIntent.js', array( 'jquery' ) ),
			'jquery-mousewheel' => array( $static_location . 'js/jquery.mousewheel.js', array( 'jquery' ) ),
		);
		$scripts_localize = array(
			'jquery-timepicker-addon' => array(
				'object_name' => 'iptPluginUIFDTPL10n',
				'l10n' => $datetime_l10n,
			),
		);
		foreach ( $scripts as $script_id => $script_prop ) {
			if ( ! in_array( $script_id, $ignore_js ) ) {
				if ( empty( $script_prop ) ) {
					wp_enqueue_script( $script_id );
				} else {
					wp_enqueue_script( $script_id, $script_prop[0], $script_prop[1], $version );
				}
				if ( isset( $scripts_localize[$script_id] ) && is_array( $scripts_localize[$script_id] ) && isset( $scripts_localize[$script_id]['object_name'] ) && isset( $scripts_localize[$script_id]['l10n'] ) ) {
					wp_localize_script( $script_id, $scripts_localize[$script_id]['object_name'], $scripts_localize[$script_id]['l10n'] );
				}
			}
		}

		do_action( 'ipt_plugin_ui_enqueue', $this );
	}

	/*==========================================================================
	 * IcoMoon Data and File Names
	 *========================================================================*/
	 public function get_icon_image_names() {
	 	include dirname( __FILE__ ) . '/var-ipt-icomoon-icons.php';
		return apply_filters( 'ipt_uif_valid_icons_image', $icomoon_images );
	}

	public function get_icon_image_name( $hex ) {
		$icons = $this->get_icon_image_names();
		if ( isset( $icons[$hex] ) ) {
			return $icons[$hex];
		} else {
			return false;
		}
	}

	public function get_valid_icons() {
		include dirname( __FILE__ ) . '/var-ipt-icomoon-icons.php';
		return apply_filters( 'ipt_uif_valid_icons_hex', $icomoon_icons );
	}

	/*==========================================================================
	 * ICON MENU
	 *========================================================================*/
	public function iconmenu( $items, $alignment = 'center' ) {
?>
<ul class="ipt_uif_ul_menu ipt_uif_align_<?php echo esc_attr( 'center' ); ?>">
	<?php foreach ( $items as $item ) : ?>
	<?php
		$href = '' == $item['url'] ? 'javascript:;' : esc_attr( $item['url'] );
		$text = '' == trim( $item['text'] ) ? '' : $item['text'];
?>
	<li>
		<a href="<?php echo $href; ?>">
			 <?php if ( isset( $item['icon'] ) && $item['icon'] !== '' && $item['icon'] != 'none' ) : ?>
			<i <?php if ( is_numeric( $item['icon'] ) ) : ?> class="ipticm" data-ipt-icomoon="&#x<?php echo dechex( $item['icon'] ) ?>;"<?php else : ?> class="ipt-icomoon-<?php echo esc_attr( $item['icon'] ); ?> ipticm"<?php endif; ?>>
			</i>
			<?php endif; ?>
			<?php echo $text; ?></a>
	</li>
	<?php endforeach; ?>
</ul>
		<?php
	}

	/*==========================================================================
	 * SYSTEM API
	 *========================================================================*/
	/**
	 * Returns an instance object.
	 *
	 * Creates one if not already instantiated.
	 *
	 * @return IPT_Plugin_UIF_Base
	 */
	public static function instance( $text_domain = 'default', $classname = __CLASS__ ) {
		if ( !isset( self::$instance[$classname . $text_domain] ) || !is_array( self::$instance[$classname . $text_domain] ) || empty( self::$instance[$classname . $text_domain] ) ) {
			self::$instance[$classname . $text_domain] = array();
			new $classname( $text_domain, $classname );
		}
		return self::$instance[$classname . $text_domain][count( self::$instance[$classname . $text_domain] ) - 1];
	}

	public function __construct( $text_domain = 'default', $classname = __CLASS__ ) {
		self::$instance[$classname . $text_domain][] = $this;
		$this->text_domain = $text_domain;
	}

	/*==========================================================================
	 * INTERNAL HTML FORM ELEMENTS METHODS
	 * Can also be used publicly
	 *========================================================================*/
	/**
	 * Create a div
	 *
	 * @param mixed   (array|string) $styles The HTML style. Can be a single string when only one div will be produced,
	 * or array in which case the 0th style will be used to create the main div
	 * and other styles will be nested inside as individual divs.
	 * @param mixed   (array|string) $callback The callback function to populate.
	 * @param int     $scroll  The scroll height value in pixels. 0 if no scroll.
	 * @param string  $id      HTML ID
	 * @param array   $classes HTML classes
	 */
	public function div( $styles, $callback, $scroll = 0, $id = '', $classes = array() ) {
		if ( !$this->check_callback( $callback ) ) {
			$this->msg_error( 'Invalid Callback supplied' );
			return;
		}
		if ( !is_array( $classes ) ) {
			$classes = (array) $classes;
		}

		if ( is_array( $styles ) && count( $styles ) > 1 ) {
			$classes = array_merge( $classes, (array) $styles[0] );
		} else {
			$classes[] = (string) $styles;
		}
		$style_attr = '';
		if ( (int) $scroll != 0 ) {
			$style_attr = ' style="max-height: ' . (int) $scroll . 'px; overflow: auto;"';
			$classes[] = 'ipt_uif_scroll';
		}
		$id_attr = '';
		if ( trim( $id ) != '' ) {
			$id_attr = ' id="' . esc_attr( trim( $id ) ) . '"';
		}
?>
<div class="<?php echo implode( ' ', $classes ); ?>"<?php echo $id_attr . $style_attr; ?>>
	<?php if ( is_array( $styles ) && count( $styles ) > 1 ) : ?>
	<?php for ( $i = 1; $i < count( $styles ); $i++ ) : ?>
	<div class="<?php echo implode( ' ', (array) $styles[$i] ); ?>">
	<?php endfor; ?>
	<?php endif; ?>

	<?php call_user_func_array( $callback[0], $callback[1] ); ?>

	<?php if ( is_array( $styles ) && count( $styles ) > 1 ) : ?>
	<?php for ( $i = 1; $i < count( $styles ); $i++ ) : ?>
	</div>
	<?php endfor; ?>
	<?php endif; ?>
</div>
		<?php
	}

	public function clear() {
		echo '<div class="clear"></div>';
	}

	/**
	 * Convert a valid state of HTML form elements to proper attribute="value" pair
	 *
	 * @param string  $state The state of the HTML item
	 * @return string
	 */
	public function convert_state_to_attribute( $state ) {
		$output = '';
		switch ( $state ) {
		case 'disable' :
		case 'disabled' :
			$output = ' disabled="disabled"';
			break;
		case 'readonly' :
		case 'noedit' :
			$output = ' readonly="readonly"';
			break;
		}
		return $output;
	}

	/**
	 * Converts valid size string to proper HTML class value
	 *
	 * @param string  $size Valid size string
	 * @return string
	 */
	public function convert_size_to_class( $size ) {
		$class = '';
		switch ( $size ) {
		case 'regular' :
		case 'medium' :
			$class = 'regular-text';
			break;
		case 'large' :
		case 'big' :
			$class = 'large-text';
			break;
		case 'small' :
		case 'tiny' :
			$class = 'small-text';
			break;
		case 'fit' :
			$class = 'fit-text';
			break;
		default :
			$class = esc_attr( $size );
		}
		return $class;
	}

	/**
	 * Generate Label for an element
	 *
	 * @param string  $name The name of the element
	 * @param type    $text
	 */
	public function generate_label( $name, $text, $id = '', $classes = array() ) {
		if ( !is_array( $classes ) ) {
			$classes = (array) $classes;
		}
		$classes[] = 'ipt_uif_label';
?>
<label class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" for="<?php echo $this->generate_id_from_name( $name, $id ); ?>"><?php echo $text; ?></label>
		<?php
	}

	public function generate_id_from_name( $name, $id = '' ) {
		if ( '' == trim( $id ) ) {
			return esc_attr( str_replace( array( '[', ']' ), array( '_', '' ), trim( $name ) ) );
		} else {
			return esc_attr( trim( $id ) );
		}
	}

	public function convert_data_attributes( $data ) {
		if ( false == $data || !is_array( $data ) || empty( $data ) ) {
			return '';
		}

		$data_attr = '';
		foreach ( $data as $d_key => $d_val ) {
			$data_attr .= ' data-' . esc_attr( $d_key ) . '="' . esc_attr( $d_val ) . '"';
		}

		return $data_attr;
	}

	public function convert_html_attributes( $atts ) {
		if ( false == $atts || ! is_array( $atts ) || empty( $atts ) ) {
			return '';
		}

		$html_atts = '';
		foreach ( $atts as $attr => $val ) {
			$html_atts .= ' ' . $attr . '="' . esc_attr( $val ) . '"';
		}

		return $html_atts;
	}


	public function convert_validation_class( $validation = false ) {
		if ( $validation == false || !is_array( $validation ) || empty( $validation ) ) {
			return '';
		}

		$classes = array();

		//check if required
		if ( true == $validation['required'] ) {
			$classes[] = 'required';
		}

		//check for any custom regex
		if ( isset( $validation['filters'] ) && is_array( $validation['filters'] ) ) {
			if ( isset( $validation['filters']['type'] ) ) {
				if ( 'all' != $validation['filters']['type'] ) {
					$classes[] = 'custom[' . esc_attr( $validation['filters']['type'] ) . ']';
				}

				// Now delete the unnecessary filters
				if ( in_array( $validation['filters']['type'], array( 'number', 'integer' ) ) ) {
					$validation['filters']['minSize'] = '';
					$validation['filters']['maxSize'] = '';
				}
				if ( in_array( $validation['filters']['type'], array( 'all', 'onlyNumberSp', 'onlyLetterSp', 'onlyLetterNumber', 'onlyLetterNumberSp', 'noSpecialCharacter' ) ) ) {
					$validation['filters']['min'] = '';
					$validation['filters']['max'] = '';
				}
			}



			//check for others
			foreach ( $validation['filters'] as $f_key => $f_val ) {
				if ( 'type' == $f_key ) {
					continue;
				}

				if ( $f_val != '' ) {
					$classes[] = esc_attr( $f_key ) . '[' . esc_attr( $f_val ) . ']';
				}
			}
		}

		if ( isset( $validation['funccall'] ) && is_string( $validation['funccall'] ) ) {
			$classes[] = 'funcCall[' . $validation['funccall'] . ']';
		}


		$added = implode( ',', $classes );

		if ( $added != '' ) {
			return ' check_me validate[' . $added . ']';
		} else {
			return '';
		}
	}

	/**
	 * Get the first image from a string
	 *
	 * @param string  $html
	 * @return mixed string|bool The src value on success or boolean false if no src found
	 */
	public function get_first_image( $html ) {
		$matches = array();
		$output = preg_match_all( '/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $html, $matches );
		if ( !$output ) {
			return false;
		}
		else {
			$src = $matches[1][0];
			return trim( $src );
		}
	}


	/**
	 * Shortens a string to a specified character length.
	 * Also removes incomplete last word, if any
	 *
	 * @param string  $text The main string
	 * @param string  $char Character length
	 * @param string  $cont Continue character(…)
	 * @return string
	 */
	public function shorten_string( $text, $char, $cont = '…' ) {
		$text = strip_tags( strip_shortcodes( $text ) );
		$text = substr( $text, 0, $char ); //First chop the string to the given character length
		if ( substr( $text, 0, strrpos( $text, ' ' ) )!='' ) $text = substr( $text, 0, strrpos( $text, ' ' ) ); //If there exists any space just before the end of the chopped string take upto that portion only.
		//In this way we remove any incomplete word from the paragraph
		$text = $text.$cont; //Add continuation ... sign
		return $text; //Return the value
	}

	/**
	 * Wrap a RAW JS inside <script> tag
	 *
	 * @param String  $string The JS
	 * @return String The wrapped JS to be used under HTMl document
	 */
	public function js_wrap( $string ) {
		return "\n<script type='text/javascript'>\n" . $string . "\n</script>\n";
	}

	/**
	 * Wrap a RAW CSS inside <style> tag
	 *
	 * @param String  $string The CSS
	 * @return String The wrapped CSS to be used under HTMl document
	 */
	public function css_wrap( $string ) {
		return "\n<style type='text/css'>\n" . $string . "\n</style>\n";
	}

	/*==========================================================================
	 * OTHER INTERNAL METHODS
	 *========================================================================*/

	protected function standardize_items( $items ) {
		$new_items = array();
		if ( !is_array( $items ) ) {
			$items = (array) $items;
		}
		foreach ( $items as $i_key => $item ) {
			if ( is_array( $item ) ) {
				if ( isset( $item['value'] ) ) {
					$new_items[] = array(
						'label' => isset( $item['label'] ) ? $item['label'] : ucfirst( $item['value'] ),
						'value' => esc_attr( (string) $item['value'] ),
						'data' => isset( $item['data'] ) ? $item['data'] : array(),
					);
				}
			} elseif ( is_string( $item ) ) {
				if ( is_numeric( $i_key ) ) {
					$new_items[] = array(
						'label' => ucfirst( $item ),
						'value' => esc_attr( (string) $item ),
					);
				} else {
					$new_items[] = array(
						'label' => $item,
						'value' => esc_attr( (string) $i_key ),
					);
				}
			}
		}

		return $new_items;
	}

	public function convert_old_items( $ops, $inner = false ) {
		$items = array();
		foreach ( $ops as $o_key => $op ) {
			if ( !is_array( $op ) ) {
				if ( !$inner ) {
					$items[] = array(
						'label' => ucfirst( $op ),
						'value' => $op,
					);
				} else {
					$items[] = array(
						'label' => $op,
						'value' => $o_key,
					);
				}
			} else {
				$items[] = array(
					'label' => $op['label'],
					'value' => $op['val'],
				);
			}
		}
		return $items;
	}

	/**
	 * Check the validity of the callback function.
	 * Also appends null variable as argument which is passed by default.
	 *
	 * @param mixed   string|array $callback Callback function
	 * @return boolean TRUE if valid callback, FALSE otherwise
	 */
	public function check_callback( &$callback ) {
		//var_dump($callback);
		//can not be callback if not string or array
		if ( !is_array( $callback ) && !is_string( $callback ) ) {
			return false;
		}
		$callback_backup = $callback;
		//Standardize the variable
		if ( is_string( $callback ) ) {
			//Possibility of single function name
			$callback = array( $callback_backup, array() );
		} else if ( is_array( $callback ) ) {
				//Possibility of object,method or array(array(object,method),arguments) or function,argument
				if ( is_array( $callback[0] ) ) {
					//Definitely array(array(object,method),arguments)
					//Just append null arguments if not present
					if ( !isset( $callback[1] ) ) {
						$callback[1] = array();
					} else {
						$callback[1] = (array) $callback[1];
					}
				} else {
					//Can be either object,method or function,argument
					$callback[0] = $callback_backup;
					$callback[1] = array();
				}
			}

		//Final check for arguments
		if ( !is_array( $callback[1] ) ) {
			$callback[1] = (array) $callback[1];
		}

		//Check for validity
		if ( is_string( $callback[0] ) ) {
			if ( function_exists( $callback[0] ) ) {
				return true;
			} else {
				return false;
			}
		} else if ( is_array( $callback[0] ) ) {
				if ( method_exists( $callback[0][0], $callback[0][1] ) ) {
					return true;
				} else {
					return false;
				}
			} else {
			return false;
		}
	}

	/**
	 * stripslashes gpc
	 * Strips Slashes added by magic quotes gpc thingy
	 *
	 * @access protected
	 * @param string  $value
	 */
	protected function stripslashes_gpc( &$value ) {
		$value = stripslashes( $value );
	}

	protected function htmlspecialchar_ify( &$value ) {
		$value = htmlspecialchars( $value );
	}
}
endif;
