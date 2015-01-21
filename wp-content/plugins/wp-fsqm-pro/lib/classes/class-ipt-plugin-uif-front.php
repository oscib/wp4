<?php
/**
 * iPanelThemes User Interface for Plugin's Framework
 * Front Area
 *
 * Generates all user interface/form elements
 * It needs to have the ipt_plugin_uif.admin.css and ipt_plugin_uif.admin.js file
 *
 * @depends base, jQueryUI{menu, autocomplete}
 * @version 1.0.2
 *
 * This part of the framework is meant to ship with FSQM Pro plugin only (as of 30th aug, 2013)
 */

if ( !class_exists( 'IPT_Plugin_UIF_Front' ) ) :
	class IPT_Plugin_UIF_Front extends IPT_Plugin_UIF_Base {
	/**
	 * Default Messages
	 *
	 * Shortcut to all the messages
	 *
	 * @var array All the default messages
	 */
	public $default_messages = array();

	/*==========================================================================
	 * System API
	 *========================================================================*/
	public static function instance( $text_domain ) {
		return parent::instance( $text_domain, __CLASS__ );
	}

	public function __construct( $text_domain = 'default', $classname = __CLASS__ ) {
		$this->default_messages = array(
			'ajax_loader' => __( 'Please Wait', $text_domain ),
			'messages' => array(
				'green' => __( 'Success', $text_domain ),
				'okay' => __( 'Success', $text_domain ),
				'update' => __( 'Updated', $text_domain ),
				'yellow' => __( 'Updated', $text_domain ),
				'red' => __( 'Error', $text_domain ),
				'error' => __( 'Error', $text_domain ),
			),
			'uploader' => array(
				'select' => __( 'Select files', $text_domain ),
				'dragdrop' => __( 'Drag \'n Drop files here', $text_domain ),
				'start' => __( 'Start All Uploads', $text_domain ),
				'cancel' => __( 'Cancel All Uploads', $text_domain ),
				'delete' => __( 'Delete Selected', $text_domain ),
				'processing_singular' => __( 'Processing&hellip;', $text_domain ),
				'start_singular' => __( 'Start', $text_domain ),
				'cancel_singular' => __( 'Cancel', $text_domain ),
				'error_singular' => __( 'Error', $text_domain ),
				'delete_singular' => __( 'Delete', $text_domain ),
			),
			'validationEngine' => array(
				'required' => array(
					'alertText' =>  __( '* This field is required', $text_domain ),
					'alertTextCheckboxMultiple' =>  __( '* Please select an option', $text_domain ),
					'alertTextCheckboxe' =>  __( '* This checkbox is required', $text_domain ),
					'alertTextDateRange' =>  __( '* Both date range fields are required', $text_domain )
				),
				'requiredInFunction' => array(
					'alertText' =>  __( '* Incorrect answer. The correct answer is ', $text_domain )
				),
				'dateRange' => array(
					'alertText' =>  __( '* Invalid ', $text_domain ),
					'alertText2' =>  __( 'Date Range', $text_domain )
				),
				'dateTimeRange' => array(
					'alertText' =>  __( '* Invalid ', $text_domain ),
					'alertText2' =>  __( 'Date Time Range', $text_domain )
				),
				'minSize' => array(
					'alertText' =>  __( '* Minimum ', $text_domain ),
					'alertText2' =>  __( ' characters required', $text_domain )
				),
				'maxSize' => array(
					'alertText' =>  __( '* Maximum ', $text_domain ),
					'alertText2' =>  __( ' characters allowed', $text_domain )
				),
				'groupRequired' => array(
					'alertText' =>  __( '* You must fill one of the following fields', $text_domain )
				),
				'min' => array(
					'alertText' =>  __( '* Minimum value is ', $text_domain )
				),
				'max' => array(
					'alertText' =>  __( '* Maximum value is ', $text_domain )
				),
				'past' => array(
					'alertText' =>  __( '* Date prior to ', $text_domain )
				),
				'future' => array(
					'alertText' =>  __( '* Date past ', $text_domain )
				),
				'maxCheckbox' => array(
					'alertText' =>  __( '* Maximum ', $text_domain ),
					'alertText2' =>  __( ' option(s) allowed', $text_domain )
				),
				'minCheckbox' => array(
					'alertText' =>  __( '* Please select ', $text_domain ),
					'alertText2' =>  __( ' option(s)', $text_domain )
				),
				'equals' => array(
					'alertText' =>  __( '* Fields do not match', $text_domain )
				),
				'creditCard' => array(
					'alertText' =>  __( '* Invalid credit card number', $text_domain )
				),
				'phone' => array(
					// credit => jquery.h5validate.js / orefalo
					'regex' => "/^([\+][0-9]{1,3}[\ \.\-])?([\(]{1}[0-9]{2,6}[\)])?([0-9\ \.\-\/]{3,20})((x|ext|extension)[\ ]?[0-9]{1,4})?$/",
					'alertText' =>  __( '* Invalid phone number', $text_domain )
				),
				'email' => array(
					// HTML5 compatible email regex ( http =>//www.whatwg.org/specs/web-apps/current-work/multipage/states-of-the-type-attribute.html#    e-mail-state-%28type=email%29 )
					'regex' => "/^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/",
					'alertText' =>  __( '* Invalid email address', $text_domain )
				),
				'integer' => array(
					'regex' => "/^[\-\+]?\d+$/",
					'alertText' =>  __( '* Not a valid integer', $text_domain )
				),
				'number' => array(
					// Number, including positive, negative, and floating decimal. credit => orefalo
					'regex' => "/^[\-\+]?((([0-9]{1,3})([,][0-9]{3})*)|([0-9]+))?([\.]([0-9]+))?$/",
					'alertText' =>  __( '* Invalid floating decimal number', $text_domain )
				),
				'date' => array(
					// Check if date is valid by leap year
					'alertText' =>  __( '* Invalid date, must be in YYYY-MM-DD format', $text_domain )
				),
				'ipv4' => array(
					'regex' => "/^((([01]?[0-9]{1,2})|(2[0-4][0-9])|(25[0-5]))[.]){3}(([0-1]?[0-9]{1,2})|(2[0-4][0-9])|(25[0-5]))$/",
					'alertText' =>  __( '* Invalid IP address', $text_domain )
				),
				'url' => array(
					'regex' => "/^(https?|ftp):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(\#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i",
					'alertText' =>  __( '* Invalid URL', $text_domain )
				),
				'onlyNumberSp' => array(
					'regex' => "/^[0-9\ ]+$/",
					'alertText' =>  __( '* Numbers only', $text_domain )
				),
				'onlyLetterSp' => array(
					'regex' => "/^[a-zA-Z\ \']+$/",
					'alertText' =>  __( '* Letters only', $text_domain )
				),
				'onlyLetterNumber' => array(
					'regex' => "/^[0-9a-zA-Z]+$/",
					'alertText' =>  __( '* No spaces or special characters allowed', $text_domain )
				),
				'onlyLetterNumberSp' => array(
					'regex' => "/^[0-9a-zA-Z\ ]+$/",
					'alertText' =>  __( '* Only letters, number and spaces allowed', $text_domain )
				),
				'noSpecialCharacter' => array(
					'regex' => "/^[0-9a-zA-Z\ \.\,\?\\\"\']+$/",
					'alertText' => __( '* No special characters allowed', $text_domain ),
				),
				'personName' => array(
					'regex' => "/^[a-zA-Z\ \.]+$/",
					'alertText' => __( 'Valid name only, no special characters except dots and single quote for salutation', $text_domain ),
				),
				//tls warning =>homegrown not fielded
				'dateFormat' => array(
					'regex' => "/^\d{4}[\/\-](0?[1-9]|1[012])[\/\-](0?[1-9]|[12][0-9]|3[01])$|^(?:(?:(?:0?[13578]|1[02])(\/|-)31)|(?:(?:0?[1,3-9]|1[0-2])(\/|-)(?:29|30)))(\/|-)(?:[1-9]\d\d\d|\d[1-9]\d\d|\d\d[1-9]\d|\d\d\d[1-9])$|^(?:(?:0?[1-9]|1[0-2])(\/|-)(?:0?[1-9]|1\d|2[0-8]))(\/|-)(?:[1-9]\d\d\d|\d[1-9]\d\d|\d\d[1-9]\d|\d\d\d[1-9])$|^(0?2(\/|-)29)(\/|-)(?:(?:0[48]00|[13579][26]00|[2468][048]00)|(?:\d\d)?(?:0[48]|[2468][048]|[13579][26]))$/",
					'alertText' =>  __( '* Invalid Date', $text_domain )
				),
				//tls warning =>homegrown not fielded
				'dateTimeFormat' => array(
					'regex' => "/^\d{4}[\/\-](0?[1-9]|1[012])[\/\-](0?[1-9]|[12][0-9]|3[01])\s+(1[012]|0?[1-9]){1}:(0?[1-5]|[0-6][0-9]){1}:(0?[0-6]|[0-6][0-9]){1}\s+(am|pm|AM|PM){1}$|^(?:(?:(?:0?[13578]|1[02])(\/|-)31)|(?:(?:0?[1,3-9]|1[0-2])(\/|-)(?:29|30)))(\/|-)(?:[1-9]\d\d\d|\d[1-9]\d\d|\d\d[1-9]\d|\d\d\d[1-9])$|^((1[012]|0?[1-9]){1}\/(0?[1-9]|[12][0-9]|3[01]){1}\/\d{2,4}\s+(1[012]|0?[1-9]){1}:(0?[1-5]|[0-6][0-9]){1}:(0?[0-6]|[0-6][0-9]){1}\s+(am|pm|AM|PM){1})$/",
					'alertText' =>  __( '* Invalid Date or Date Format', $text_domain ),
					'alertText2' =>  __( 'Expected Format => ', $text_domain ),
					'alertText3' =>  __( 'mm/dd/yyyy hh =>mm =>ss AM|PM or ', $text_domain ),
					'alertText4' =>  __( 'yyyy-mm-dd hh =>mm =>ss AM|PM', $text_domain )
				),
			),
		);
		// Add the filters for the richtext
		add_filter( 'ipt_uif_richtext', 'wptexturize'        );
		add_filter( 'ipt_uif_richtext', 'convert_smilies'    );
		add_filter( 'ipt_uif_richtext', 'convert_chars'      );
		add_filter( 'ipt_uif_richtext', 'wpautop'            );
		add_filter( 'ipt_uif_richtext', 'shortcode_unautop'  );
		add_filter( 'ipt_uif_richtext', 'do_shortcode', 11   );
		add_filter( 'ipt_uif_richtext', 'prepend_attachment' );
		global $wp_embed;
		if ( class_exists( 'WP_Embed' ) && $wp_embed instanceof WP_Embed ) {
			add_filter( 'ipt_uif_richtext', array( $wp_embed, 'run_shortcode' ), 8 );
			add_filter( 'ipt_uif_richtext', array( $wp_embed, 'autoembed' ), 8 );
		}
		parent::__construct( $text_domain, $classname );
	}

	/*==========================================================================
	 * FILE DEPENDENCIES
	 *========================================================================*/
	/**
	 * Enqueues Scripts and Style
	 *
	 * @param string  $static_location The URL to the static admin directory
	 * @param string  $version         Version of the scripts/stylesheets
	 */
	public function enqueue( $static_location, $version, $ignore_css = array(), $ignore_js = array() ) {
		parent::enqueue( $static_location, $version, $ignore_css, $ignore_js );
		$static_location = $this->static_location;
		$version = $this->version;

		// Styles
		$styles = array(
			'ipt-plugin-uif-validation-engine-css' => array( $static_location . 'css/validationEngine.jquery.css', array() ),
			'ipt-plugin-uif-fileupload-main-css' => array( $static_location . 'css/jquery.fileupload.css', array() ),
			'ipt-plugin-uif-fileupload-ui-css' => array( $static_location . 'css/jquery.fileupload-ui.css', array() ),
			'ipt-plugin-uif-blueimp-gallery-css' => array( $static_location . 'css/blueimp-gallery.min.css', array() ),
			'ipt-plugin-uif-animate-css' => array( $static_location . 'css/animate.css', array() ),
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
			'jquery-ui-autocomplete' => array(),
			'ipt-plugin-uif-keyboard' => array( $static_location . 'js/jquery.keyboard.min.js', array( 'jquery' ) ),
			'ipt-plugin-uif-validation-engine' => array( $static_location . 'js/jquery.validationEngine.js', array( 'jquery' ) ),
			'ipt-plugin-uif-validation-engine-lang' => array( $static_location . 'js/jquery.validationEngine-all.js', array( 'jquery' ) ),
			'ipt-plugin-uif-nivo-slider' => array( $static_location . 'js/jquery.nivo.slider.pack.js', array( 'jquery' ) ),
			'ipt-plugin-uif-typewatch' => array( $static_location . 'js/jquery.typewatch.js', array( 'jquery' ) ),
			'ipt-plugin-uif-tmpl' => array( $static_location . 'js/tmpl.min.js', array() ),
			'ipt-plugin-uif-load-image' => array( $static_location . 'js/load-image.min.js', array() ),
			'ipt-plugin-uif-canvas-to-blob' => array( $static_location . 'js/canvas-to-blob.min.js', array() ),
			'ipt-plugin-uif-blueimp-gallery' => array( $static_location . 'js/blueimp-gallery.min.js', array( 'jquery' ) ),
			'ipt-plugin-uif-blueimp-gallery-fullscreen' => array( $static_location . 'js/blueimp-gallery-fullscreen.js', array( 'jquery' ) ),
			'ipt-plugin-uif-blueimp-gallery-indicator' => array( $static_location . 'js/blueimp-gallery-indicator.js', array( 'jquery' ) ),
			'ipt-plugin-uif-blueimp-gallery-video' => array( $static_location . 'js/blueimp-gallery-video.js', array( 'jquery' ) ),
			'ipt-plugin-uif-blueimp-gallery-jquery' => array( $static_location . 'js/jquery.blueimp-gallery.min.js', array( 'jquery' ) ),
			'ipt-plugin-uif-iframe-transport' => array( $static_location . 'js/jquery.iframe-transport.js', array( 'jquery' ) ),
			'ipt-plugin-uif-fileupload' => array( $static_location . 'js/jquery.fileupload.js', array( 'jquery', 'jquery-ui-widget' ) ),
			'ipt-plugin-uif-fileupload-process' => array( $static_location . 'js/jquery.fileupload-process.js', array( 'jquery' ) ),
			'ipt-plugin-uif-fileupload-image' => array( $static_location . 'js/jquery.fileupload-image.js', array( 'jquery' ) ),
			'ipt-plugin-uif-fileupload-audio' => array( $static_location . 'js/jquery.fileupload-audio.js', array( 'jquery' ) ),
			'ipt-plugin-uif-fileupload-video' => array( $static_location . 'js/jquery.fileupload-video.js', array( 'jquery' ) ),
			'ipt-plugin-uif-fileupload-validate' => array( $static_location . 'js/jquery.fileupload-validate.js', array( 'jquery' ) ),
			'ipt-plugin-uif-fileupload-ui' => array( $static_location . 'js/jquery.fileupload-ui.js', array( 'jquery' ) ),
			'ipt-plugin-uif-fileupload-jquery-ui' => array( $static_location . 'js/jquery.fileupload-jquery-ui.js', array( 'jquery' ) ),
			'waypoints' => array( $static_location . 'js/waypoints.min.js', array( 'jquery' ) ),
			'ipt-plugin-uif-front-js' => array( $static_location . 'js/jquery.ipt-plugin-uif-front.js', array( 'jquery' ) ),
		);
		$scripts_localize = array(
			'ipt-plugin-uif-validation-engine-lang' => array(
				'object_name' => 'iptPluginValidationEn',
				'l10n' => array(
					'L10n' => $this->default_messages['validationEngine'],
				),
			),
			'ipt-plugin-uif-front-js' => array(
				'object_name' => 'iptPluginUIFFront',
				'l10n' => array(
					'location' => $static_location,
					'version' => $version,
					'L10n' => $this->default_messages,
					'ajaxurl' => admin_url( 'admin-ajax.php' ),
				),
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
	}

	/*==========================================================================
	 * HTML UI ElEMENTS
	 *========================================================================*/
	/**
	 * Prints a group of radio items for a single HTML name
	 *
	 * @param string  $name        The HTML name of the radio group
	 * @param array   $items       Associative array of all the radio items.
	 *  array(
	 *      'value' => '',
	 *      'label' => '',
	 *      'disabled' => true|false,//optional
	 *      'data' => array('key' => 'value'[,...]), //optional HTML 5 data attributes inside an associative array
	 *  )
	 * @param string  $checked     The value of the checked item
	 * @param array   $validation  Array of the validation clauses
	 * @param int     $column      Number of columns 1|2|3|4
	 * @param bool    $conditional Whether the group represents conditional questions. This will wrap it inside a conditional div
	 * which will be fired using jQuery. It does not populate or create anything inside the conditional div.
	 * The id of the conditional divs should be given inside the data value of the items in the form
	 * condID => 'ID_OF_DIV'
	 * @param bool    $disabled    Set TRUE if all the items are disabled
	 * @return void
	 */
	public function radios( $name, $items, $checked, $validation = false, $column = 2, $conditional = false, $disabled = false, $icon = 0xe18e ) {
		if ( !is_array( $items ) || empty( $items ) ) {
			return;
		}
		$validation_class = $this->convert_validation_class( $validation );

		if ( !is_array( $checked ) ) {
			$checked = (array) $checked;
		}


		$id_prefix = $this->generate_id_from_name( $name );

		if ( $conditional == true ) {
			echo '<div class="ipt_uif_conditional_input">';
		}

		$items = $this->standardize_items( $items );

		$icon_attr = '';
		if ( $icon != '' && $icon != 'none' ) {
			$icon_attr = ' data-labelcon="&#x' . dechex( $icon ) . ';"';
		}

		foreach ( (array) $items as $item ) :
		$data = isset( $item['data'] ) ? $item['data'] : '';
		$data_attr = $this->convert_data_attributes( $data );
		$id = $this->generate_id_from_name( '', $id_prefix . '_' . $item['value'] );
		$disabled_item = ( $disabled == true || ( isset( $item['disabled'] ) && true == $item['disabled'] ) ) ? 'disabled' : '';
?>
<div class="ipt_uif_label_column column_<?php echo $column; ?>">
	<input<?php echo in_array( $item['value'], $checked, true ) ? ' checked="checked"' : ''; ?>
		<?php echo $data_attr; ?>
		<?php echo $this->convert_state_to_attribute( $disabled_item ); ?>
		type="radio"
		class="<?php echo trim( $validation_class ); ?> ipt_uif_radio"
		name="<?php echo $name; ?>"
		id="<?php echo $id; ?>"
		value="<?php echo $item['value']; ?>" />
	<label for="<?php echo $id; ?>"<?php echo $icon_attr; ?>>
		 <?php echo $item['label']; ?>
	</label>
</div>
			<?php
		endforeach;
		$this->clear();
		if ( $conditional == true ) {
			echo '</div>';
		}
	}

	public function rating( $name, $value, $max, $required, $style = 'star' ) {
		$value = (string) $value;
?>
<div class="ipt_uif_rating ipt_uif_rating_<?php echo esc_attr( $style ); ?>">
	<?php for ( $i = 1; $i <= (int) $max; $i++ ) : ?>
	<?php $id = $this->generate_id_from_name( $name ) . '_' . $i; ?>
	<input<?php echo ( $value === (string) $i ) ? ' checked="checked"' : ''; ?>
		type="radio"
		class="<?php if ( $required ) : ?>check_me validate[required]<?php endif; ?> ipt_uif_radio"
		name="<?php echo $name; ?>"
		id="<?php echo $id; ?>"
		value="<?php echo $i; ?>" />
	<label for="<?php echo $id; ?>"></label>
	<?php endfor; ?>
</div>
		<?php
	}

	/**
	 * Prints a group of checkbox items for a single HTML name
	 *
	 * @param string  $name        The HTML name of the radio group
	 * @param array   $items       Associative array of all the radio items.
	 *  array(
	 *      'value' => '',
	 *      'label' => '',
	 *      'disabled' => true|false,//optional
	 *      'data' => array('key' => 'value'[,...]), //optional HTML 5 data attributes inside an associative array
	 *  )
	 * @param string  $checked     The value of the checked item
	 * @param array   $validation  Array of the validation clauses
	 * @param int     $column      Number of columns 1|2|3|4s
	 * @param bool    $conditional Whether the group represents conditional questions. This will wrap it inside a conditional div
	 * which will be fired using jQuery. It does not populate or create anything inside the conditional div.
	 * The id of the conditional divs should be given inside the data value of the items in the form
	 * condID => 'ID_OF_DIV'
	 * @param bool    $disabled    Set TRUE if all the items are disabled
	 * @return void
	 */
	public function checkboxes( $name, $items, $checked, $validation = false, $column = 2, $conditional = false, $disabled = false, $icon = 0xe18e ) {
		if ( !is_array( $items ) || empty( $items ) ) {
			return;
		}

		$validation_class = $this->convert_validation_class( $validation );

		if ( !is_array( $checked ) ) {
			$checked = (array) $checked;
		}

		$id_prefix = $this->generate_id_from_name( $name );

		if ( $conditional == true ) {
			echo '<div class="ipt_uif_conditional_input">';
		}

		$items = $this->standardize_items( $items );

		$icon_attr = '';
		if ( $icon != '' && $icon != 'none' ) {
			$icon_attr = ' data-labelcon="&#x' . dechex( $icon ) . ';"';
		}

		foreach ( (array) $items as $item ) :
			$data = isset( $item['data'] ) ? $item['data'] : '';
		$data_attr = $this->convert_data_attributes( $data );
		$id = $this->generate_id_from_name( '', $id_prefix . '_' . $item['value'] );
		$disabled_item = ( $disabled == true || ( isset( $item['disabled'] ) && true == $item['disabled'] ) ) ? 'disabled' : '';
?>
<div class="ipt_uif_label_column column_<?php echo $column; ?>">
	<input<?php echo in_array( $item['value'], (array) $checked, true ) ? ' checked="checked"' : ''; ?>
		<?php echo $data_attr; ?>
		<?php echo $this->convert_state_to_attribute( $disabled_item ); ?>
		type="checkbox"
		class="<?php echo trim( $validation_class ); ?> ipt_uif_checkbox"
		name="<?php echo $name; ?>" id="<?php echo $id; ?>"
		value="<?php echo $item['value']; ?>" />
	<label for="<?php echo $id; ?>"<?php echo $icon_attr; ?>>
		 <?php echo $item['label']; ?>
	</label>
</div>
			<?php
		endforeach;
		$this->clear();
		if ( $conditional == true ) {
			echo '</div>';
		}
	}

	/**
	 * Prints a select dropdown form element
	 *
	 * @param string  $name         The HTML name of the radio group
	 * @param array   $items        Associative array of all the radio items.
	 *  array(
	 *      'value' => '',
	 *      'label' => '',
	 *      'data' => array('key' => 'value'[,...]), //optional HTML 5 data attributes inside an associative array
	 *  )
	 * @param string  $selected     The value of the selected item
	 * @param array   $validation   Array of the validation clauses
	 * @param bool    $conditional  Whether the group represents conditional questions. This will wrap it inside a conditional div
	 * which will be fired using jQuery. It does not populate or create anything inside the conditional div.
	 * The id of the conditional divs should be given inside the data value of the items in the form
	 * condID => 'ID_OF_DIV'
	 * @param bool    $disabled     Set TRUE if all the items are disabled
	 * @param bool    $print_select Whether or not to print the select html
	 * @return void
	 */
	public function select( $name, $items, $selected, $validation = false, $conditional = false, $print_select = true, $disabled = false ) {
		if ( !is_array( $items ) || empty( $items ) ) {
			return;
		}
		$validation_class = $this->convert_validation_class( $validation );

		$classes = array();
		$classes[] = $validation_class;
		$classes[] = 'ipt_uif_select';

		if ( !is_array( $selected ) ) {
			$selected = (array) $selected;
		}

		$id = $this->generate_id_from_name( $name );

		if ( $conditional == true ) {
			echo '<div class="ipt_uif_conditional_select">';
		}

		$items = $this->standardize_items( $items );

		if ( $print_select ) {
			echo '<select class="' . implode( ' ', $classes ) . '" name="' . esc_attr( trim( $name ) ) . '" id="' . $id . '" ' . $this->convert_state_to_attribute( ( $disabled == true ) ? 'disabled' : '' ) . '>';
		}

		foreach ( (array) $items as $item ) :
			$data = isset( $item['data'] ) ? $item['data'] : '';
		$data_attr = $this->convert_data_attributes( $data );
?>
<option value="<?php echo $item['value']; ?>"<?php if ( in_array( $item['value'], (array) $selected, true ) ) echo ' selected="selected"'; ?><?php echo $data_attr; ?>><?php echo $item['label']; ?></option>
			<?php
		endforeach;

		if ( $print_select ) {
			echo '</select>';
			$this->clear();
		}

		if ( $conditional == true ) {
			echo '</div>';
		}
	}

	/**
	 * Prints a single checkbox item
	 *
	 * @param string  $name        The HTML name of the radio group
	 * @param array   $items       Associative array of all the radio items.
	 *  array(
	 *      'value' => '',
	 *      'label' => '',
	 *  )
	 * @param bool    $checked     TRUE if the item is checked, FALSE otherwise
	 * @param array   $validation  Array of the validation clauses
	 * @param array   $conditional Whether or not it will show some conditional clause. If true, then you must add 'data' key to the item
	 * with the following structure
	 *  array(
	 *      'condid' => 'id of the conditional show wrapper'
	 *  )
	 * @param string  $sep         Separator HTML
	 * @param bool    $disabled    Set TRUE if the item is disabled
	 * @return void
	 */
	public function checkbox( $name, $item, $checked, $validation = false, $conditional = false, $disabled = false, $icon = 0xe18e ) {
		if ( !is_array( $item ) || empty( $item ) ) {
			return;
		}

		if ( true === $checked || $item['value'] === $checked ) {
			$checked = $item['value'];
		} else {
			$checked = false;
		}

		$this->checkboxes( $name, array( $item ), array( $checked ), $validation, 1, $conditional, $disabled, $icon );
	}

	/**
	 * Print a Toggle HTML item
	 *
	 * @param string  $name        The HTML name of the toggle
	 * @param string  $on          ON text
	 * @param string  $off         OFF text
	 * @param bool    $checked     TRUE if checked
	 * @param string  $value       The HTML value of the toggle checkbox (Optional, default to '1')
	 * @param bool    $disabled    True to make it disabled
	 * @param bool    $conditional Whether the group represents conditional questions. This will wrap it inside a conditional div
	 * which will be fired using jQuery. It does not populate or create anything inside the conditional div.
	 * The id of the conditional divs should be given inside the data value of the items in the form
	 * condID => 'ID_OF_DIV'
	 * @param array   $data        HTML 5 data attributes in the form
	 * array('key' => 'value'[,...])
	 */
	public function toggle( $name, $on, $off, $checked, $value = '1', $disabled = false, $conditional = false, $data = array() ) {
		if ( '' == trim( $on ) ) {
			$on = __( 'On' );
		}
		if ( '' == trim( $off ) ) {
			$off = __( 'Off' );
		}

		if ( $conditional == true ) {
			echo '<div class="ipt_uif_conditional_input">';
		}

		$id = $this->generate_id_from_name( $name );
?>
<input<?php echo $this->convert_data_attributes( $data ); ?> type="checkbox"<?php echo $this->convert_state_to_attribute( $disabled == true ? 'disabled' : '' ); ?><?php if ( $checked ) : ?> checked="checked"<?php endif; ?> class="ipt_uif_switch" name="<?php echo $name; ?>" id="<?php echo $id; ?>" value="<?php echo esc_attr( $value ); ?>" />
<label for="<?php echo $id; ?>" data-on="<?php echo $on; ?>" data-off="<?php echo $off; ?>"></label>
		<?php

		if ( $conditional == true ) {
			echo '</div>';
		}
		$this->clear();
	}

	/**
	 * Generate input type text HTML
	 *
	 * @param string  $name        HTML name of the text input
	 * @param string  $value       Initial value of the text input
	 * @param string  $placeholder Default placeholder
	 * @param string  $size        Size of the text input
	 * @param string  $state       readonly or disabled state
	 * @param array   $classes     Array of additional classes
	 * @param array   $validation  Associative array of all validation clauses @see IPT_Plugin_UIF_Admin::convert_validation_class
	 * @param array   $data        HTML 5 data attributes in associative array @see IPT_Plugin_UIF_Admin::convert_data_attributes
	 */
	public function text( $name, $value, $placeholder, $icon = 'pencil', $state = 'normal', $classes = array(), $validation = false, $data = false ) {
		$id = $this->generate_id_from_name( $name );
		if ( !is_array( $classes ) ) {
			$classes = (array) $classes;
		}
		$validation_attr = $this->convert_validation_class( $validation );
		if ( '' != $validation_attr ) {
			$classes[] = $validation_attr;
		}

		$data_attr = $this->convert_data_attributes( $data );
		$div_class = array( 'ipt_uif_icon_and_form_elem_holder' );
		if ( $icon == 'none' || empty( $icon ) ) {
			$div_class[] = 'ipt_uif_text_no_icon';
		}
?>
<div class="<?php echo implode( ' ', $div_class ); ?>">
<input class="<?php echo implode( ' ', $classes ); ?> ipt_uif_text"
	<?php echo $data_attr; ?>
	<?php echo $this->convert_state_to_attribute( $state ); ?>
	type="text"
	placeholder="<?php echo esc_attr( $placeholder ); ?>"
	name="<?php echo esc_attr( $name ); ?>"
	id="<?php echo $id; ?>"
	value="<?php echo esc_textarea( $value ); ?>" />
<?php $this->print_icon_by_class( $icon ); ?>
</div>
		<?php
	}

	/**
	 * Generate textarea HTML
	 *
	 * @param string  $name        HTML name of the text input
	 * @param string  $value       Initial value of the text input
	 * @param string  $placeholder Default placeholder
	 * @param string  $size        Size of the text input
	 * @param string  $state       readonly or disabled state
	 * @param array   $classes     Array of additional classes
	 * @param array   $validation  Associative array of all validation clauses @see IPT_Plugin_UIF_Admin::convert_validation_class
	 * @param array   $data        HTML 5 data attributes in associative array @see IPT_Plugin_UIF_Admin::convert_data_attributes
	 */
	public function textarea( $name, $value, $placeholder, $state = 'normal', $classes = array(), $validation = false, $data = false ) {
		$id = $this->generate_id_from_name( $name );
		if ( !is_array( $classes ) ) {
			$classes = (array) $classes;
		}
		$classes[] = 'ipt_uif_textarea';
		$validation_attr = $this->convert_validation_class( $validation );
		if ( '' != $validation_attr ) {
			$classes[] = $validation_attr;
		}

		$data_attr = $this->convert_data_attributes( $data );
?>
<textarea class="<?php echo implode( ' ', $classes ); ?>"
		  rows="4"
	<?php echo $data_attr; ?>
	<?php echo $this->convert_state_to_attribute( $state ); ?>
	type="text"
	placeholder="<?php echo esc_attr( $placeholder ); ?>"
	name="<?php echo esc_attr( $name ); ?>"
	id="<?php echo $id; ?>"><?php echo esc_textarea( $value ); ?></textarea>
		<?php
	}

	public function anchor_button( $text, $href, $target = '_self', $size = 'medium', $icon = 'none' ) {
?>
<a target="<?php echo esc_attr( $target ); ?>" class="ipt_uif_anchor_button <?php echo esc_attr( $size ); ?>" href="<?php echo esc_url( $href ); ?>"><?php $this->print_icon_by_data( $icon ); ?><?php echo $text; ?></a>
		<?php
	}

	public function password( $name_prefix, $value, $placeholder = '', $state = 'normal', $confirm = false, $classes = array(), $validation = false, $data = false ) {
		$name = $name_prefix . '[value]';
		$id = $this->generate_id_from_name( $name );
		if ( !is_array( $classes ) ) {
			$classes = (array) $classes;
		}
		$validation_attr = $this->convert_validation_class( $validation );
		if ( '' != $validation_attr ) {
			$classes[] = $validation_attr;
		}

		$data_attr = $this->convert_data_attributes( $data );
?>
<div class="ipt_uif_icon_and_form_elem_holder">
<input class="<?php echo implode( ' ', $classes ); ?> ipt_uif_text ipt_uif_password"
	<?php echo $data_attr; ?>
	<?php echo $this->convert_state_to_attribute( $state ); ?>
	type="password"
	placeholder="<?php echo esc_attr( $placeholder ); ?>"
	name="<?php echo esc_attr( $name ); ?>"
	id="<?php echo $id; ?>"
	value="<?php echo esc_textarea( $value ); ?>" />
<?php $this->print_icon_by_class( 'console' ); ?>
</div>
<?php if ( $confirm !== false ) : ?>
<div class="ipt_uif_icon_and_form_elem_holder">
<input class="ipt_uif_text ipt_uif_password ipt_uif_password_confirm check_me validate[equals[<?php echo $id; ?>]]"
	type="password"
	placeholder="<?php echo $confirm; ?>"
	name="<?php echo esc_attr( $name_prefix ); ?>[confirm]"
	id="<?php echo $this->generate_id_from_name( $name_prefix . '[confirm]' ); ?>"
	value="<?php echo esc_textarea( $value ); ?>" />
<?php $this->print_icon_by_class( 'console' ); ?>
</div>
<?php endif; ?>
		<?php
	}

	public function keypad( $name, $value, $settings, $placeholder, $mask = false, $multiline = false, $state = 'normal', $classes = array(), $validation = false, $data = false ) {
		$id = $this->generate_id_from_name( $name );
		if ( !is_array( $classes ) ) {
			$classes = (array) $classes;
		}
		$classes[] = 'ipt_uif_keypad';
		$validation_attr = $this->convert_validation_class( $validation );
		if ( '' != $validation_attr ) {
			$classes[] = $validation_attr;
		}

		$data_attr = $this->convert_data_attributes( $data );

		if ( $mask ) {
?>
<div class="ipt_uif_icon_and_form_elem_holder">
<input class="<?php echo implode( ' ', $classes ); ?> ipt_uif_text ipt_uif_password"
	<?php echo $data_attr; ?>
	<?php echo $this->convert_state_to_attribute( $state ); ?>
	data-settings="<?php echo esc_attr( json_encode( (object) $settings ) ); ?>"
	type="password"
	placeholder="<?php echo esc_attr( $placeholder ); ?>"
	name="<?php echo esc_attr( $name ); ?>"
	id="<?php echo $id; ?>"
	value="<?php echo esc_textarea( $value ); ?>" />
<?php $this->print_icon_by_class( 'keyboard' ); ?>
</div>
			<?php
		} else {
			if ( $multiline ) {
?>
<textarea class="<?php echo implode( ' ', $classes ); ?> ipt_uif_textarea"
		  rows="4"
	<?php echo $data_attr; ?>
	<?php echo $this->convert_state_to_attribute( $state ); ?>
	data-settings="<?php echo esc_attr( json_encode( (object) $settings ) ); ?>"
	type="text"
	placeholder="<?php echo esc_attr( $placeholder ); ?>"
	name="<?php echo esc_attr( $name ); ?>"
	id="<?php echo $id; ?>"><?php echo esc_textarea( $value ); ?></textarea>
				<?php
			} else {
?>
<div class="ipt_uif_icon_and_form_elem_holder">
<input class="<?php echo implode( ' ', $classes ); ?> ipt_uif_text"
	<?php echo $data_attr; ?>
	<?php echo $this->convert_state_to_attribute( $state ); ?>
	data-settings="<?php echo esc_attr( json_encode( (object) $settings ) ); ?>"
	type="text"
	placeholder="<?php echo esc_attr( $placeholder ); ?>"
	name="<?php echo esc_attr( $name ); ?>"
	id="<?php echo $id; ?>"
	value="<?php echo esc_textarea( $value ); ?>" />
<?php $this->print_icon_by_class( 'keyboard' ); ?>
</div>
				<?php
			}
		}
	}

	public function address( $name_prefix, $values, $placeholders, $validation = false ) {
		$country_list = array(
			"Afghanistan",
			"Albania",
			"Algeria",
			"Andorra",
			"Angola",
			"Antigua and Barbuda",
			"Argentina",
			"Armenia",
			"Australia",
			"Austria",
			"Azerbaijan",
			"Bahamas",
			"Bahrain",
			"Bangladesh",
			"Barbados",
			"Belarus",
			"Belgium",
			"Belize",
			"Benin",
			"Bhutan",
			"Bolivia",
			"Bosnia and Herzegovina",
			"Botswana",
			"Brazil",
			"Brunei",
			"Bulgaria",
			"Burkina Faso",
			"Burundi",
			"Cambodia",
			"Cameroon",
			"Canada",
			"Cape Verde",
			"Central African Republic",
			"Chad",
			"Chile",
			"China",
			"Colombi",
			"Comoros",
			"Congo (Brazzaville)",
			"Congo",
			"Costa Rica",
			"Cote d'Ivoire",
			"Croatia",
			"Cuba",
			"Cyprus",
			"Czech Republic",
			"Denmark",
			"Djibouti",
			"Dominica",
			"Dominican Republic",
			"East Timor (Timor Timur)",
			"Ecuador",
			"Egypt",
			"El Salvador",
			"Equatorial Guinea",
			"Eritrea",
			"Estonia",
			"Ethiopia",
			"Fiji",
			"Finland",
			"France",
			"Gabon",
			"Gambia, The",
			"Georgia",
			"Germany",
			"Ghana",
			"Greece",
			"Grenada",
			"Guatemala",
			"Guinea",
			"Guinea-Bissau",
			"Guyana",
			"Haiti",
			"Honduras",
			"Hungary",
			"Iceland",
			"India",
			"Indonesia",
			"Iran",
			"Iraq",
			"Ireland",
			"Israel",
			"Italy",
			"Jamaica",
			"Japan",
			"Jordan",
			"Kazakhstan",
			"Kenya",
			"Kiribati",
			"Korea, North",
			"Korea, South",
			"Kuwait",
			"Kyrgyzstan",
			"Laos",
			"Latvia",
			"Lebanon",
			"Lesotho",
			"Liberia",
			"Libya",
			"Liechtenstein",
			"Lithuania",
			"Luxembourg",
			"Macedonia",
			"Madagascar",
			"Malawi",
			"Malaysia",
			"Maldives",
			"Mali",
			"Malta",
			"Marshall Islands",
			"Mauritania",
			"Mauritius",
			"Mexico",
			"Micronesia",
			"Moldova",
			"Monaco",
			"Mongolia",
			"Morocco",
			"Mozambique",
			"Myanmar",
			"Namibia",
			"Nauru",
			"Nepal",
			"Netherlands",
			"New Zealand",
			"Nicaragua",
			"Niger",
			"Nigeria",
			"Norway",
			"Oman",
			"Pakistan",
			"Palau",
			"Panama",
			"Papua New Guinea",
			"Paraguay",
			"Peru",
			"Philippines",
			"Poland",
			"Portugal",
			"Qatar",
			"Romania",
			"Russia",
			"Rwanda",
			"Saint Kitts and Nevis",
			"Saint Lucia",
			"Saint Vincent",
			"Samoa",
			"San Marino",
			"Sao Tome and Principe",
			"Saudi Arabia",
			"Senegal",
			"Serbia and Montenegro",
			"Seychelles",
			"Sierra Leone",
			"Singapore",
			"Slovakia",
			"Slovenia",
			"Solomon Islands",
			"Somalia",
			"South Africa",
			"Spain",
			"Sri Lanka",
			"Sudan",
			"Suriname",
			"Swaziland",
			"Sweden",
			"Switzerland",
			"Syria",
			"Taiwan",
			"Tajikistan",
			"Tanzania",
			"Thailand",
			"Togo",
			"Tonga",
			"Trinidad and Tobago",
			"Tunisia",
			"Turkey",
			"Turkmenistan",
			"Tuvalu",
			"Uganda",
			"Ukraine",
			"United Arab Emirates",
			"United Kingdom",
			"United States",
			"Uruguay",
			"Uzbekistan",
			"Vanuatu",
			"Vatican City",
			"Venezuela",
			"Vietnam",
			"Yemen",
			"Zambia",
			"Zimbabwe"
		);

		$recipient_validation = array(
			'required' => $validation['required'],
			'filters' => array(
				'type' => 'personName',
			),
		);
		$this->text( $name_prefix . '[recipient]', $values['recipient'], $placeholders['recipient'], 'users', 'normal', array(), $recipient_validation );
		$this->text( $name_prefix . '[line_one]', $values['line_one'], $placeholders['line_one'], 'address-book', 'normal', array(), $validation );
		$this->text( $name_prefix . '[line_two]', $values['line_two'], $placeholders['line_two'], 'address-book', 'normal', array(), $validation );
		$this->text( $name_prefix . '[line_three]', $values['line_three'], $placeholders['line_three'], 'address-book', 'normal', array(), false );
		$this->autocomplete( $name_prefix . '[country]', $values['country'], $placeholders['country'], $country_list, 'flag', 'normal', array(), $validation );
	}

	public function autocomplete( $name, $value, $placeholder, $autocomplete, $icon = 'pencil', $state = 'normal', $classes = array(), $validation = false ) {
		$data = array(
			'autocomplete' => json_encode( (array) $autocomplete ),
		);
		if ( !is_array( $classes ) ) {
			$classes = (array) $classes;
		}
		$classes[] = 'ipt_uif_autocomplete';
		$this->text( $name, $value, $placeholder, $icon, $state, $classes, $validation, $data );
	}

	/**
	 * Generate more than a single button inside a single container.
	 *
	 * @param array   $buttons           Associative array of all button elements. See ::button to find more.
	 * @param string  $container_id      The HTML ID of the container (Optional)
	 * @param array   $container_classes Additional Classes of the container (Optional)
	 * @return type
	 */
	public function buttons( $buttons, $container_id = '', $container_classes = '' ) {
		if ( !is_array( $buttons ) || empty( $buttons ) ) {
			$this->msg_error( 'Please pass a valid arrays to the <code>IPT_Plugin_UIF_Front::buttons</code> method' );
			return;
		}

		$id_attr = '';
		if ( '' != trim( $container_id ) ) {
			$id_attr = ' id="' . esc_attr( trim( $container_id ) ) . '"';
		}

		if ( !is_array( $container_classes ) ) {
			$container_classes = (array) $container_classes;
		}
		$container_classes[] = 'ipt_uif_button_container';

		echo "\n" . '<div' . $id_attr . ' class="' . implode( ' ', $container_classes ) . '">' . "\n";

		foreach ( $buttons as $button_index ) {
			$button_index = array_values( $button_index );
			$button = array();
			foreach ( array( 'text', 'name', 'size', 'style', 'state', 'classes', 'type', 'data', 'atts', 'url', 'icon', 'icon_position' ) as $b_key => $b_val ) {
				if ( isset( $button_index[$b_key] ) ) {
					$button[$b_val] = $button_index[$b_key];
				}
			}
			if ( !isset( $button['text'] ) || '' == trim( $button['text'] ) ) {
				continue;
			}
			$text = $button['text'];
			$name = isset( $button['name'] ) ? $button['name'] : '';
			$size = isset( $button['size'] ) ? $button['size'] : 'medium';
			$style = isset( $button['style'] ) ? $button['style'] : 'primary';
			$state = isset( $button['state'] ) ? $button['state'] : 'normal';
			$classes = isset( $button['classes'] ) ? $button['classes'] : array();
			$type = isset( $button['type'] ) ? $button['type'] : 'button';
			$data = isset( $button['data'] ) ? $button['data'] : array();
			$atts = isset( $button['atts'] ) ? $button['atts'] : array();
			$url = isset( $button['url'] ) ? $button['url'] : '';
			$icon = isset( $button['icon'] ) ? $button['icon'] : '';
			$icon_position = isset( $button['icon_position'] ) ? $button['icon_position'] : 'before';

			$this->button( $text, $name, $size, $style, $state, $classes, $type, false, $data, $atts, $url, $icon, $icon_position );
		}

		echo "\n" . '<div class="clear"></div></div>' . "\n";
	}

	public function print_button( $id, $text ) {
?>
<div class="ipt_uif_button_container">
	<button class="ipt_uif_button ipt_uif_printelement" data-printid="<?php echo esc_attr( $id ); ?>"><span class="button-icon ipt-icomoon-print"></span> <?php echo $text; ?></button>
</div>
		<?php
	}

	/**
	 * Generates a single button
	 *
	 * @param string  $text      The text of the button
	 * @param string  $name      HTML name. ID is generated automatically (unless name is an array, ID is identical to name).
	 * @param string  $size      Size large|medium|small
	 * @param string  $style     Style primary|ui
	 * @param string  $state     HTML state normal|readonly|disabled
	 * @param array   $classes   Array of additional classes
	 * @param string  $type      The HTML type of the button button|submit|reset|anchor
	 * @param bool    $container Whether or not to print the container.
	 * @param array   $data HTML5 data attributes
	 */
	public function button( $text, $name = '', $size = 'medium', $style = 'primary', $state = 'normal', $classes = array(), $type = 'button', $container = true, $data = array(), $atts = array(), $url = '', $icon = '', $icon_position = 'before' ) {
		if ( !is_array( $classes ) ) {
			$classes = (array) $classes;
		}

		switch ( $size ) {
		case 'large' :
		case 'medium' :
		case 'small' :
		case 'auto' :
			$classes[] = $size;
			break;
		default :
			$classes[] = 'medium';
		}
		switch ( $style ) {
		default :
		case 'primary' :
		case '0' :
			$classes[] = 'primary-button';
			break;
		case 'secondary' :
		case '1' :
			$classes[] = 'secondary-button';
			break;
		case 'ui' :
		case '2' :
		default :
			$classes[] = 'ipt-ui-button';
			break;
		}
		$name_id_attr = '';
		if ( '' != trim( $name ) ) {
			$name_id_attr = ' name="' . esc_attr( trim( $name ) ) . '" id="' . $this->generate_id_from_name( $name ) . '"';
		}
		$state_attr = $this->convert_state_to_attribute( $state );

		$type_attr = '';
		if ( '' != trim( $type ) && $type != 'anchor' ) {
			$type_attr = ' type="' . esc_attr( trim( $type ) ) . '"';
		} else {
			$type_attr = ' href="' . esc_url( $url ) . '"';
		}

		$data_attr = '';
		if ( is_array( $data ) ) {
			$data_attr = $this->convert_data_attributes( $data );
		}
		$tag = $type == 'anchor' ? 'a' : 'button';

		$icon_span = '';
		if ( $icon != '' && $icon != 'none' ) {
			$icon_span .= '<span class="button-icon';
			if ( is_numeric( $icon ) ) {
				$icon_span .= '" data-ipt-icomoon="' . '&#x' . hexdec( $icon ) . '">';
			} else {
				$icon_span .= ' ipt-icomoon-' . $icon . '">';
			}
			$icon_span .= '</span>';
		}
		if ( $icon_position == 'before' ) {
			$text = $icon_span . ' ' . $text;
		} else {
			$text .= ' ' . $icon_span;
		}

		$html_atts = '';
		if ( ! empty( $atts ) ) {
			$html_atts = $this->convert_html_attributes( $atts );
		}
?>
<?php if ( true == $container ) : ?>
<div class="ipt_uif_button_container">
<?php endif; ?>
	<<?php echo $tag; ?><?php echo $type_attr . $data_attr . $html_atts; ?> class="ipt_uif_button <?php echo implode( ' ', $classes ); ?>"<?php echo $name_id_attr . $state_attr; ?>><?php echo $text; ?></<?php echo $tag; ?>>
<?php if ( true == $container ) : ?>
</div>
<?php endif; ?>
		<?php
	}

	/**
	 * Generate a spinner to select numerical value
	 *
	 * @param string  $name        HTML name
	 * @param string  $value       Initial value of the range
	 * @param string  $placeholder HTML placeholder
	 * @param int     $min         Minimum of the range
	 * @param int     $max         Maximum of the range
	 * @param int     $step        spinner move step
	 */
	public function spinner( $name, $value, $placeholder = '', $min = '', $max = '', $step = 1, $required = false ) {
		$validation = array(
			'required' => $required,
			'filters' => array(
				'type' => 'number',
				'min' => $min,
				'max' => $max,
			),
		);
		$validation_attr = $this->convert_validation_class( $validation );
?>
<input type="text" placeholder="<?php echo $placeholder; ?>" class="ipt_uif_text code ipt_uif_uispinner <?php echo esc_attr( $validation_attr ); ?>" min="<?php echo $min; ?>" max="<?php echo $max; ?>" step="<?php echo $step; ?>" name="<?php echo esc_attr( trim( $name ) ); ?>" id="<?php echo $this->generate_id_from_name( $name ); ?>" value="<?php echo esc_attr( $value ); ?>" />
		<?php
	}

	/**
	 * Generate a horizontal slider to select between numerical values
	 *
	 * @param string  $name       HTML name
	 * @param string  $value      Initial value of the range
	 * @param bool    $show_count Whether or not to show the count
	 * @param int     $min        Minimum of the range
	 * @param int     $max        Maximum of the range
	 * @param int     $step       Slider move step
	 */
	public function slider( $name, $value, $show_count = true, $min = 0, $max = 100, $step = 1, $prefix = '', $suffix = '' ) {
		$min = (float) $min;
		$max = (float) $max;
		$step = (float) $step;
		$value = $value == '' ? $min : (float) $value;
		if ( $value < $min )
			$value = $min;
		if ( $value > $max )
			$value = $max;
?>
<div class="ipt_uif_empty_box">
	<input type="hidden" class="ipt_uif_slider" data-min="<?php echo $min; ?>" data-max="<?php echo $max; ?>" data-step="<?php echo $step; ?>" name="<?php echo esc_attr( trim( $name ) ); ?>" id="<?php echo $this->generate_id_from_name( $name ); ?>" value="<?php echo esc_attr( $value ); ?>" />
	<?php if ( $show_count ) : ?>
	<div class="ipt_uif_slider_count">
		<?php echo $prefix; ?><span class="ipt_uif_slider_count_single"><?php echo $value != '' ? $value : $min; ?></span><?php echo $suffix; ?>
	</div>
	<?php endif; ?>
</div>
		<?php
	}

	/**
	 * Generate a horizontal slider to select a range between numerical values
	 *
	 * @param mixed   array|string $names HTML names in the order Min value -> Max value. If string is given the [max] and [min] is added to make an array
	 * @param array   $values     Initial values of the range in the same order
	 * @param bool    $show_count Whether or not to show the count
	 * @param int     $min        Minimum of the range
	 * @param int     $max        Maximum of the range
	 * @param int     $step       Slider move step
	 */
	public function slider_range( $names, $values, $show_count = true, $min = 0, $max = 100, $step = 1, $prefix = '', $suffix = '' ) {
		$min = (float) $min;
		$max = (float) $max;
		$step = (float) $step;
		if ( !is_array( $names ) ) {
			$name = (string) $names;
			$names = array(
				$name . '[min]', $name . '[max]',
			);
		}

		if ( !is_array( $values ) ) {
			$value = (int) $values;
			$values = array(
				$value, $value,
			);
		}
		if ( !isset( $values[0] ) ) {
			$values[0] = $values['min'];
			$values[1] = $values['max'];
		}
		$value_min = $values[0] != '' ? $values[0] : $min;
		$value_max = $values[1] != '' ? $values[1] : $min;

		if ( $value_min < $min )
			$value_min = $min;
		if ( $value_min > $max )
			$value_min = $max;
		if ( $value_max < $min )
			$value_max = $min;
		if ( $value_max > $max )
			$value_max = $max;
?>
<div class="ipt_uif_empty_box">
	<input type="hidden" class="ipt_uif_slider slider_range" data-min="<?php echo $min; ?>" data-max="<?php echo $max; ?>" data-step="<?php echo $step; ?>" name="<?php echo esc_attr( trim( $names[0] ) ); ?>" id="<?php echo $this->generate_id_from_name( $names[0] ); ?>" value="<?php echo esc_attr( $value_min ); ?>" />
	<input type="hidden" class="" name="<?php echo esc_attr( trim( $names[1] ) ); ?>" id="<?php echo $this->generate_id_from_name( $names[1] ); ?>" value="<?php echo esc_attr( $value_max ); ?>" />
	<?php if ( $show_count ) : ?>
	<div class="ipt_uif_slider_count">
		<?php echo $prefix; ?><span class="ipt_uif_slider_count_min"><?php echo $value_min; ?></span><?php echo $suffix; ?>
		 - <?php echo $prefix; ?><span class="ipt_uif_slider_count_max"><?php echo $value_max; ?></span><?php echo $suffix; ?>
	</div>
	<?php endif; ?>
</div>
		<?php
	}

	/**
	 * Populates multiple sliders or ranges
	 *
	 * @param string  $name       The name of slider
	 * @param array   $sliders    An associative array of the sliders
	 * array(
	 *      'type' => 'range' | 'single',
	 *      'name' => 'HTML Name',
	 *      'value' => int|array(min,max),
	 *      'title' => 'Title or Label',
	 * )
	 * @param bool    $show_count Whether or not to show the count
	 * @param int     $min        Minimum of the range
	 * @param int     $max        Maximum of the range
	 * @param int     $step       Slider move step
	 */
	public function sliders( $name, $sliders, $show_count = false, $min = 0, $max = 100, $step = 1 ) {
		foreach ( $sliders as $slider ) {
			$params = array( $slider['name'], $slider['value'], $show_count, $min, $max, $step, $slider['prefix'], $slider['suffix'] );
			if ( $slider['type'] == 'range' ) {
				$callback = array( array( $this, 'slider_range' ), $params );
			} else {
				$callback = array( array( $this, 'slider' ), $params );
			}
			$this->question_container( $name, $slider['title'], '', $callback, false );
		}
	}

	public function spinners( $spinners ) {
		foreach ( $spinners as $spinner ) {
			$params = array( $spinner['name'], $spinner['value'], $spinner['placeholder'], $spinner['min'], $spinner['max'], $spinner['step'], $spinner['required'] );
			$this->question_container( $spinner['name'], $spinner['title'], '', array( array( $this, 'spinner' ), $params ), false );
		}
	}

	public function ratings( $ratings, $style ) {
		foreach ( $ratings as $rating ) {
			$params = array( $rating['name'], $rating['value'], $rating['max'], $rating['required'], $style );
			$this->question_container( $rating['name'], $rating['title'], '', array( array( $this, 'rating' ), $params ), false );
		}
	}

	public function matrix( $name_prefix, $rows, $columns, $values, $multiple, $required, $icon = 0xe18e ) {
		$type = $multiple == true ? 'checkbox' : 'radio';
		$validation = array(
			'required' => $required,
		);
		$validation_attr = $this->convert_validation_class( $validation );
		if ( !is_array( $values ) ) {
			$values = (array) $values;
		}
		$icon_attr = '';
		if ( $icon != '' && $icon != 'none' ) {
			$icon_attr = ' data-labelcon="&#x' . dechex( $icon ) . ';"';
		}
?>
<div class="ipt_uif_matrix_container">
	<table class="ipt_uif_matrix">
		<thead>
			<tr>
				<th scope="col"></th>
				<?php foreach ( $columns as $column ) : ?>
				<th scope="col"><div class="ipt_uif_matrix_div_cell"><?php echo $column; ?></div></th>
				<?php endforeach; ?>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<th scope="col"></th>
				<?php foreach ( $columns as $column ) : ?>
				<th scope="col"><div class="ipt_uif_matrix_div_cell"><?php echo $column; ?></div></th>
				<?php endforeach; ?>
			</tr>
		</tfoot>
		<tbody>
			<?php foreach ( $rows as $r_key => $row ) : ?>
			<?php
			if ( !isset( $values[$r_key] ) ) {
				$values[$r_key] = array();
			} else {
			$values[$r_key] = (array) $values[$r_key];
		}
?>
			<tr>
				<th scope="row"><div class="ipt_uif_matrix_div_cell"><?php echo $row; ?></div></th>
				<?php foreach ( $columns as $c_key => $column ) : ?>
				<?php
			$name = $name_prefix . '[rows][' . $r_key . '][]';
		$id = $this->generate_id_from_name( $name ) . '_' . $c_key;
?>
				<td><div class="ipt_uif_matrix_div_cell">
					<input type="<?php echo $type; ?>" class="ipt_uif_<?php echo $type . ' ' . $validation_attr; ?>"
						   value="<?php echo $c_key; ?>"
						   name="<?php echo $name; ?>" id="<?php echo $id; ?>"
						   <?php if ( in_array( (string) $c_key, $values[$r_key], true ) ) echo 'checked="checked"'; ?> />
					<label for="<?php echo $id; ?>"<?php echo $icon_attr; ?>></label>
				</div></td>
				<?php endforeach; ?>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>

		<?php
	}

	public function sortables( $name_prefix, $items, $order = array(), $randomize = false ) {
		if ( !is_array( $items ) || empty( $items ) ) {
			return;
		}
		$keys = array_keys( $items );

		if ( !empty( $order ) ) {
			$keys = $order;
		}

		if ( $randomize && empty( $order ) ) {
			shuffle( $keys );
		}
?>
<div class="ipt_uif_sorting">
	<?php foreach ( $keys as $key ) : ?>
	<div class="ipt_uif_sortme">
		<a class="ipt_uif_sorting_handle" href="javascript:;"><?php $this->print_icon_by_class( 'unsorted' ); ?></a>
		<input type="hidden" name="<?php echo $name_prefix; ?>" value="<?php echo $key; ?>" />
		<?php echo esc_attr( $items[$key]['label'] ); ?>
	</div>
	<?php endforeach; ?>
</div>
		<?php
	}

	/**
	 * Generates a simple jQuery UI Progressbar
	 * Minumum value is 0 and maximum is 100.
	 * So always calculate in percentage.
	 *
	 * @param string  $id      The HTML ID
	 * @param numeric $start   The start value
	 * @param array   $classes Additional classes
	 */
	public function progressbar( $id = '', $start = 0, $classes = array() ) {
		if ( !is_array( $classes ) ) {
			$classes = (array) $classes;
		}
		$classes[] = 'ipt_uif_progress_bar';
		$id_attr = '';
		if ( $id != '' ) {
			$id_attr = ' id="' . esc_attr( $id ) . '"';
		}
?>
<div class="<?php echo implode( ' ', $classes ); ?>" data-start="<?php echo $start; ?>"<?php echo $id_attr; ?>>
	<div class="ipt_uif_progress_value"></div>
</div>
		<?php
	}

	public function datetime( $name, $value, $type = 'date', $state = 'normal', $classes = array(), $validation = false, $date_format = 'yy-mm-dd', $time_format = 'HH:mm:ss', $placeholder = '' ) {
		if ( !is_array( $classes ) ) {
			$classes = (array) $classes;
		}
		$classes[] = 'datepicker';
		$icon = 'calendar';

		switch ( $type ) {
		case 'date' :
			$classes[] = 'ipt_uif_datepicker';
			break;
		case 'time' :
			$classes[] = 'ipt_uif_timepicker';
			$icon = 'clock';
			break;
		case 'datetime' :
			$classes[] = 'ipt_uif_datetimepicker';
			break;
		}

		$data = array(
			'dateFormat' => $date_format,
			'timeFormat' => $time_format,
		);

		$this->text( $name, $value, $placeholder, $icon, $state, $classes, $validation, $data );
	}

	public function hiddens( $hiddens, $name_prefix = '' ) {
		if ( !is_array( $hiddens ) || empty( $hiddens ) ) {
			return;
		}
?>
<?php foreach ( $hiddens as $h_key => $h_val ) : ?>
<?php $name = $name_prefix != '' ? $name_prefix . '[' . $h_key . ']' : $h_key; ?>
<input type="hidden" name="<?php echo $name; ?>" value="<?php echo $h_val; ?>" id="<?php echo $this->generate_id_from_name( $name ); ?>" />
<?php endforeach; ?>
		<?php
	}

	public function checkbox_toggler( $id, $label, $selector, $checked = false ) {
		$id = esc_attr( trim( $id ) );
?>
<input<?php echo true == $checked ? ' checked="checked"' : ''; ?>
	data-selector="<?php echo esc_attr( $selector ); ?>"
	type="checkbox"
	class="ipt_uif_checkbox ipt_uif_checkbox_toggler"
	id="<?php echo $id; ?>" />
<label for="<?php echo $id; ?>">
	 <?php echo $label; ?>
</label>
		<?php
	}

	/**
	 * Prints a container for BlueIMP Uploader
	 *
	 * @param  string $name        The HTML name of the type="file" input field
	 * @param  string $name_id     The HTML name of the hidden input fields with ID to the uploaded file
	 * @param  array  $labels      Labels of default fields. @see $this->default_messages['uploader']
	 * @param  array  $settings    Array of settings
	 * @param  array  $attributes  An array of other attributes which would be used by JavaScript. This has a preset datatype
	 * @param  array  $form_data   An array of data that is being submitted when uploading or fetching files. This is program specific and can be of any type
	 * @param  string $description Any description text.
	 * @return void
	 */
	public function uploader( $name, $name_id, $settings, $attributes, $form_data, $description = '', $labels = array(), $validation = true, $max_upload_size = 0, $show_ui = true ) {
		$labels = wp_parse_args( (array) $labels, $this->default_messages['uploader'] );

		$settings = wp_parse_args( (array) $settings, array(
			'accept_file_types'    => 'gif,jpeg,png',
			'max_number_of_files'  => '',
			'min_number_of_files'  => '',
			'max_file_size'        => '1000000',
			'min_file_size'        => '1',
			'show_drop_zone'       => true,
			'wp_media_integration' => false,
			'auto_upload'          => false,
			'single_upload'        => false,
			'drag_n_drop'          => true,
			'progress_bar'         => true,
			'preview_media'        => true,
			'can_delete'           => true,
			'required'             => $validation,
		) );

		$configuration = array(
			'id' => $this->generate_id_from_name( $name ),
			'upload_url' => '?action=' . $attributes['ajax_upload'],
			'download_url' => '?action=' . $attributes['ajax_download'],
			'do_download' => $attributes['fetch_files'],
		);
		$toggler_id = $configuration['id'] . '_toggler';
		$max_upload_size = ( $max_upload_size == 0 ? $settings['max_file_size'] : $max_upload_size );
		// Change the value if settings is lower
		// Fixes issue #12
		if ( $settings['max_file_size'] < $max_upload_size ) {
			$max_upload_size = $settings['max_file_size'];
		}
		$settings['max_file_size'] = $max_upload_size;
		if ( $validation == true && '' == $settings['min_number_of_files'] ) {
			$settings['min_number_of_files'] = '1';
		}
		$meta = array();
		if ( $max_upload_size > ( 1024 * 1024 ) ) {
			$meta[] = sprintf( __( 'Max file size: %.2f MB.', 'ipt_fsqm' ), ( $max_upload_size / (1024 * 1024 ) ) );
		} else {
			$meta[] = sprintf( __( 'Max file size: %.2f KB.', 'ipt_fsqm' ), ( $max_upload_size / 1024 ) );
		}

		$meta[] = sprintf( __( 'Allowed file types: %s', 'ipt_fsqm' ), $settings['accept_file_types'] );
		if ( $settings['max_number_of_files'] > 0 ) {
			$meta[] = sprintf( _n( 'Max number of file: %d', 'Max number of files: %d', $settings['max_number_of_files'], 'ipt_fsqm' ), $settings['max_number_of_files'] );
		}
		if ( $settings['min_number_of_files'] > 0 ) {
			$meta[] = sprintf( _n( 'Min number of file: %d', 'Min number of files: %d', $settings['min_number_of_files'], 'ipt_fsqm' ), $settings['min_number_of_files'] );
		}
		$colspan = 4;
		?>
<div class="ipt_uif_uploader" id="<?php echo esc_attr( $configuration['id'] . '_uploader_wrap' ); ?>" data-settings="<?php echo esc_attr( json_encode( (object) $settings ) ); ?>" data-configuration="<?php echo esc_attr( json_encode( (object) $configuration ) ); ?>" data-formData="<?php echo esc_attr( json_encode( (object) $form_data ) ); ?>">
	<?php if ( '' !== trim( $description ) ) : ?>
	<div class="fileupload-description ipt_uif_richtext">
		<?php echo wpautop( $description ); ?>
	</div>
	<?php endif; ?>
	<?php if ( $settings['drag_n_drop'] == true && $show_ui == true ) : ?>
	<div class="fileinput-dragdrop ui-state-active">
		<span><?php echo $labels['dragdrop']; ?></span>
	</div>
	<?php endif; ?>

	<div class="fileupload-meta">
		<p><?php echo implode( ' | ', $meta ); ?></p>
	</div>


	<!-- The table listing the files available for upload/download -->
	<div class="ipt_fsqm_fileuploader_list_wrap">
		<table role="presentation" class="ipt_fsqm_fileuploader_list">
			<?php if ( $show_ui ) : ?>
			<thead>
				<tr>
					<td colspan="<?php echo $colspan; ?>">
						<div class="fileupload-buttonbar">
							<div class="fileupload-buttons">
								<span class="fileinput-button">
									<button type="button" class="select"><?php echo $labels['select']; ?></button>
									<input class="ipt_uif_uploader_handle" type="file"<?php if ( ! $settings['single_upload'] ) : ?> multiple="multiple"<?php endif; ?> name="<?php echo esc_attr( $name ); ?>" id="<?php echo $this->generate_id_from_name( $name ); ?>" />
								</span>
								<?php if ( $settings['auto_upload'] === false ) : ?>
								<button type="submit" class="start"><?php echo $labels['start']; ?></button>
								<?php endif; ?>
								<button type="reset" class="cancel"><?php echo $labels['cancel']; ?></button>
								<span class="fileupload-process"></span>
							</div>
							<?php if ( $settings['progress_bar'] == true ) : ?>
							<!-- The global progress state -->
							<div class="fileupload-progress fade" style="display:none">
								<!-- The global progress bar -->
								<div class="progress" role="progressbar" aria-valuemin="0" aria-valuemax="100"></div>
								<!-- The extended global progress state -->
								<div class="progress-extended">&nbsp;</div>
							</div>
							<?php endif; ?>
						</div>
					</td>

					<?php if ( $settings['can_delete'] ) : ?>
					<td class="delete_button">
						<div class="fileupload-buttonbar">
							<div class="fileupload-buttons">
								<button type="button" class="delete"><?php echo $labels['delete']; ?></button>
							</div>
						</div>
					</td>
					<td class="delete_toggle">
						<div class="fileupload-buttonbar">
							<div class="fileupload-buttons">
								<div class="ipt_uif_label_column">
									<input type="checkbox" class="toggle ipt_uif_checkbox" id="<?php echo $toggler_id; ?>" />
									<label data-labelcon="&#xe18e;" for="<?php echo $toggler_id ?>"></label>
								</div>
							</div>
						</div>
					</td>
					<?php endif; ?>
				</tr>
			</thead>
			<?php endif; ?>
			<tbody class="files"></tbody>
		</table>
	</div>

	<!-- The template to display files available for upload -->
	<script class="template-upload" id="<?php echo $this->generate_id_from_name( $name ) . '_tmpl_upload'; ?>" type="text/x-tmpl">
	{% for (var i=0, file; file=o.files[i]; i++) { %}
		<tr class="template-upload fade">
			<td>
				<span class="preview"></span>
			</td>
			<td>
				<p class="name">{%=file.name%}</p>
				<strong class="error"></strong>
			</td>
			<td class="fileupload_list_pb">
				<p class="size"><?php echo $labels['processing_singular']; ?></p>
				<div class="progress"></div>
			</td>
			<td colspan="<?php echo ( $settings['can_delete'] == true ? '3' : '1' );  ?>">
				{% if (!i && !o.options.autoUpload) { %}
					<button class="start" disabled><?php echo $labels['start_singular']; ?></button>
				{% } %}
				{% if (!i) { %}
					<button class="cancel"><?php echo $labels['cancel_singular']; ?></button>
				{% } %}
			</td>
		</tr>
	{% } %}
	</script>
	<!-- The template to display files available for download -->
	<script class="template-download" id="<?php echo $this->generate_id_from_name( $name ) . '_tmpl_download'; ?>" type="text/x-tmpl">
	{%
		window.ipt_fsqm_upload_count_global;
		if ( window.ipt_fsqm_upload_count_global == undefined ) {
			window.ipt_fsqm_upload_count_global = 0;
		}
	%}
	{% for (var i=0, file; file=o.files[i]; i++) { %}
	{% var toggler_check_id = window.ipt_fsqm_upload_count_global++; %}
		<tr class="template-download fade">
			<td class="preview_td" colspan="2">
				<span class="preview">
					{% if (file.thumbnailUrl) { %}
						<a href="{%=file.url%}" title="{%=file.name%}" download="{%=file.name%}"<?php if ( $settings['preview_media'] == true ) : ?> data-gallery<?php endif; ?>><img src="{%=file.thumbnailUrl%}" /></a>
					{% } else if ( file.validAudio ) { %}
						<?php if ( $settings['preview_media'] == true ) : ?>
						<audio controls="controls">
							<source src="{%=file.url%}" type="{%=file.type%}" />
							<?php _e( 'Your browser does not support audio element.', 'ipt_fsqm' ); ?>
						</audio>
						<?php endif; ?>
					{% } else if ( file.validVideo ) { %}
						<?php if ( $settings['preview_media'] == true ) : ?>
						<video controls="controls" height="100" width="200">
							<source src="{%=file.url%}" type="{%=file.type%}" />
							<?php _e( 'Your browser does not support video element.', 'ipt_fsqm' ); ?>
						</video>
						<?php endif; ?>
					{% } %}
				</span>
			</td>
			<td>
				<p class="name">
					<a href="{%=file.url%}" title="{%=file.name%}" download="{%=file.name%}" {%=file.thumbnailUrl?'<?php if ( $settings['preview_media'] == true ) : ?>data-gallery<?php endif; ?>':''%}>{%=file.name%}</a>
				</p>
				{% if (file.error) { %}
					<div><span class="error"><?php echo $labels['error_singular']; ?></span> {%=file.error%}</div>
				{% } %}
				<input type="hidden" name="<?php echo $name_id; ?>" value="{%=file.id%}" />
			</td>
			<td>
				<span class="size">{%=o.formatFileSize(file.size)%}</span>
			</td>
			<?php if ( $settings['can_delete'] ) : ?>
			<td class="delete_button">
				<button class="delete" data-type="{%=file.deleteType%}" data-url="{%=file.deleteUrl%}"{% if (file.deleteWithCredentials) { %} data-xhr-fields='{"withCredentials":true}'{% } %}><?php echo $labels['delete_singular']; ?></button>
			</td>
			<td class="delete_toggle">
				<div class="ipt_uif_label_column">
					<input type="checkbox" name="delete" value="1" class="toggle ipt_uif_checkbox" id="<?php echo $toggler_id; ?>_files_{%=toggler_check_id%}" />
					<label data-labelcon="&#xe18e;" for="<?php echo $toggler_id; ?>_files_{%=toggler_check_id%}"></label>
				</div>
			</td>
			<?php endif; ?>
		</tr>
	{% } %}
	</script>
</div>
		<?php
	}

	/*==========================================================================
	 * TABS AND BOXES
	 *========================================================================*/
	/**
	 * Generate Tabs with callback populators
	 * Generates all necessary HTMLs. No need to write any classes manually.
	 *
	 * @param array   $tabs Associative array of all the tab elements.
	 * $tab = array(
	 *      'id' => 'ipt_fsqm_form_name',
	 *      'label' => 'Form Name',
	 *      'callback' => 'function',
	 *      'scroll' => false,
	 *      'classes' => array(),
	 *      'has_inner_tab' => false,
	 *  );
	 * @param array   $data The HTML 5 data in forms of key => value
	 */
	public function tabs( $tabs, $data = array(), $vertical = false, $classes = array() ) {
		if ( !is_array( $classes ) ) {
			$classes = (array) $classes;
		}
		$classes[] = 'ipt_uif_tabs';
		$data_attr = $this->convert_data_attributes( $data );
		$classes[] = ( $vertical == true ) ? 'vertical' : 'horizontal';
?>
<div<?php echo $data_attr; ?> class="<?php echo implode( ' ', $classes ); ?>">
	<a href="javascript:;" class="ipt_uif_tabs_toggler ipt_uif_button"><span class="ipt_uif_text_icon_no_bg ipt-icomoon-menu2"></span></a>
	<ul>
		<?php foreach ( $tabs as $tab ) : ?>
		<?php $tab = wp_parse_args( $tab, array(
			'id' => '',
			'label' => '',
			'sublabel' => '',
			'callback' => '',
			'icon' => 'none',
			'classes' => array(),
		) ); ?>
		<li><a href="#<?php echo $tab['id']; ?>"><?php $this->print_icon( $tab['icon'], false ); ?><?php echo $tab['label']; ?><?php if ( ! empty( $tab['sublabel'] ) ) echo '<span class="ipt_uif_tab_subtitle">' . $tab['sublabel'] . '</span>'; ?></a></li>
		<?php endforeach; ?>
	</ul>
	<?php foreach ( $tabs as $tab ) : ?>
	<?php
		$tab = wp_parse_args( $tab, array(
			'id' => '',
			'label' => '',
			'callback' => '',
			'icon' => 'none',
			'classes' => array(),
		) );

		if ( !$this->check_callback( $tab['callback'] ) ) {
			$tab['callback'] = array(
				array( $this, 'msg_error' ), 'Invalid Callback',
			);
		}
		$tab['callback'][1][] = $tab;
		$tab_classes = isset( $tab['classes'] ) && is_array( $tab['classes'] ) ? $tab['classes'] : array();
?>
	<div id="<?php echo $tab['id']; ?>" class="<?php echo implode( ' ', $tab_classes ); ?>">
		<?php call_user_func_array( $tab['callback'][0], $tab['callback'][1] ); ?>
		<?php $this->clear(); ?>
	</div>
	<?php endforeach; ?>
</div>
<div class="clear"></div>
		<?php
	}

	public function column( $callback, $size = 'full', $side_margin = true, $id = '', $additional_classes = array() ) {
		if ( !$this->check_callback( $callback ) ) {
			$this->msg_error( 'Invalid Callback supplied' );
			return;
		}
		$this->column_head( $id, $size, $side_margin, $additional_classes );
		call_user_func_array( $callback[0], $callback[1] );
		$this->column_tail();
	}

	public function column_head( $id = '', $size = 'full', $side_margin = true, $additional_classes = array() ) {
		$classes = array( 'ipt_uif_column', 'ipt_uif_column_' . esc_attr( $size ), 'ipt_uif_conditional' );
		if ( $size != 'full' ) {
			$classes[] = 'ipt_uif_column_custom';
		}
		$classes = array_unique( array_merge( $classes, (array) $additional_classes ) );
		$id_attr = '';
		$id = trim( $id );
		if ( '' != $id ) {
			$id_attr .= ' id="' . esc_attr( $id ) . '"';
		}
?>
<div class="<?php echo implode( ' ', $classes ); ?>"<?php echo $id_attr; ?>>
	<div class="ipt_uif_column_inner<?php if ( $side_margin ) echo ' side_margin'; ?>">
		<?php
	}

	public function column_tail() {
		$this->clear();
?>
	</div>
</div>
		<?php
	}

	/**
	 * Creates a nice looking container with an icon on top
	 *
	 * @param string  $label   The heading
	 * @param mixed   (array|string) $callback The callback function to populate.
	 * @param string  $icon    The icon. Consult the /static/fonts/fonts.css to pass class name
	 * @param int     $scroll  The scroll height value in pixels. 0 if no scroll. Default is 400.
	 * @param string  $id      HTML ID
	 * @param array   $classes HTML classes
	 * @return type
	 */
	public function container( $callback, $label, $icon = 'none', $collapsible = false, $opened = false, $after = '',  $scroll_top = false ) {
		if ( !$this->check_callback( $callback ) ) {
			$this->msg_error( 'Invalid Callback supplied' );
			return;
		}

		$this->container_head( $label, $icon, $collapsible, $opened, $after );
		call_user_func_array( $callback[0], $callback[1] );
		$this->container_tail( $scroll_top );
	}

	public function container_head( $label, $icon = 'none', $collapsible = false, $opened = false, $after = '' ) {
		if ( '' != $after ) {
			$after = '<div class="ipt_uif_float_right">' . $after . '</div>';
		}
		$classes = array( 'ipt_uif_container' );
		if ( $collapsible ) {
			$classes[] = 'ipt_uif_collapsible';
		}
		if ( $icon != 'none' ) {
			$classes[] = 'ipt_uif_iconbox';
		}
		$tmp_icon = (int) $icon;
		if ( $tmp_icon != 0 ) {
			$icon = $tmp_icon;
		}
?>
<div class="<?php echo implode( ' ', $classes ); ?>" data-opened="<?php echo $opened; ?>">
	<?php if ( trim( $label ) !== '' ) : ?>
	<div class="ipt_uif_container_head">
		<?php echo $after; ?>
		<h3><?php if ( $collapsible ) echo '<a href="javascript:;"><span class="ipt-icomoon-arrow-down3 collapsible_state"></span>'; ?>
			<?php if ( is_string( $icon ) ) : ?>
			<?php $this->print_icon_by_class( $icon ); ?>
			<?php else : ?>
			<?php $this->print_icon_by_data( $icon ); ?>
			<?php endif; ?>
			<?php echo $label; ?>
			<?php if ( $collapsible ) echo '</a>'; ?>
		</h3>
	</div>
	<?php endif; ?>
	<div class="ipt_uif_container_inner">
		<?php
	}

	public function container_tail() {
?>
		<?php $this->clear(); ?>
	</div>
</div>
		<?php
	}

	public function iconbox( $callback, $label, $icon, $after = '' ) {
		$this->container( $callback, $label, $icon, false, false, $after );
	}

	public function iconbox_head( $label, $icon, $after = '' ) {
		$this->container_head( $label, $icon, false, false, $after );
	}

	public function iconbox_tail() {
		$this->container_tail();
	}

	public function collapsible( $callback, $label, $icon = 'file-3', $after = '', $opened = false ) {
		$this->container( $callback, $label, $icon, true, $opened );
	}

	public function collapsible_head( $label, $opened = false, $icon = 'file-3' ) {
		$this->container_head( $label, $icon, true, $opened );
	}

	public function collapsible_tail() {
		$this->container_tail();
	}

	public function fancy_container( $callback ) {
		$this->div( 'ipt_uif_fancy_container', $callback );
	}

	public function divider( $text = '', $type = 'div', $align = 'center', $icon = 'none', $scroll_top = false, $classes = array(), $no_bg = false ) {
		if ( !is_array( $classes ) ) {
			$classes = (array) $classes;
		}
		$classes[] = 'ipt_uif_divider';
		$classes[] = 'ipt_uif_align_' . $align;
		$text = trim( $text );
		if ( '' === $text && ! $scroll_top ) {
			$classes[] = 'ipt_uif_empty_divider';
		}
		if ( 'none' === $icon || '' === $icon ) {
			$classes[] = 'ipt_uif_divider_no_icon';
		}
		if ( $no_bg === true ) {
			$classes[] = 'ipt_uif_divider_icon_no_bg';
		}
		if ( $scroll_top ) {
			$classes[] = 'ipt_uif_divider_has_scroll';
		}
		if ( '' === $text ) {
			$classes[] = 'ipt_uif_divider_no_text';
		}
?>
<<?php echo $type; ?> class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">
	<?php if ( $scroll_top || $text !== '' ) : ?>
	<span class="ipt_uif_divider_text">
		<?php if ( $scroll_top ) : ?>
		<?php $this->print_scroll_to_top(); ?>
		<?php endif; ?>
		<?php if ( $text != '' ) : ?>
		<?php if ( ! $no_bg ) : ?>
		<?php $this->print_icon( $icon, true ); ?>
		<?php endif; ?>
		<span class="ipt_uif_divider_text_inner">
			<?php if ( $no_bg ) : ?>
			<?php $this->print_icon( $icon, false ); ?>
			<?php endif; ?>
			<?php echo $text; ?>
		</span>
		<?php endif; ?>
	</span>
	<?php endif; ?>
</<?php echo $type; ?>>
		<?php
	}

	public function heading( $text, $type = 'h2', $align = 'center', $icon = 'none', $scroll_top = false, $no_bg = false, $classes = array() ) {
		if ( trim( $text ) == '' ) {
			return;
		}
		if ( ! is_array( $classes ) ) {
			$classes = (array) $classes;
		}
		$classes[] = 'ipt_uif_heading';
		$this->divider( $text, $type, $align, $icon, $scroll_top, $classes, $no_bg );
	}


	/*==========================================================================
	 * Image Slider
	 *========================================================================*/
	public function imageslider( $id, $images, $settings, $classes = array() ) {
		if ( !is_array( $classes ) ) {
			$classes = (array) $classes;
		}
		$classes[] = 'ipt_uif_image_slider_wrap';
		$classes[] = 'theme-ipt-uif-imageslider';
		$settings = wp_parse_args( $settings, array(
				'autoslide' => true,
				'duration' => 5,
				'transition' => 1,
				'animation' => 'random',
				'on_play' => 'ipt-icomoon-pause2',
				'on_pause' => 'ipt-icomoon-play3',
			) );
?>
<div class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" data-settings="<?php echo esc_attr( json_encode( (object) $settings ) ); ?>">
	<div id="<?php echo esc_attr( $id ); ?>" class="ipt_uif_image_slider nivoSlider">
		<?php foreach ( $images as $image ) : ?>
		<?php if ( $image['url'] != '' ) : ?>
		<a href="<?php echo esc_attr( $image['url'] ); ?>">
		<?php endif; ?>
			<img src="<?php echo esc_attr( $image['src'] ); ?>" title="<?php echo esc_attr( $image['title'] ); ?>" />
		<?php if ( $image['url'] != '' ) : ?>
		</a>
		<?php endif; ?>
		<?php endforeach; ?>
	</div>
	<div class="ribbon"></div>
</div>
		<?php
	}

	/*==========================================================================
	 * Messages
	 *========================================================================*/
	/**
	 * Prints an error message in style.
	 *
	 * @param string  $msg  The message
	 * @param bool    $echo TRUE(default) to echo the output, FALSE to just return
	 * @return string The HTML output
	 */
	public function msg_error( $msg = '', $echo = true, $title = '', $wpautop = true ) {
		return $this->print_message( 'red', $msg, $echo, $title, 'info', $wpautop );
	}

	/**
	 * Prints an update message in style.
	 *
	 * @param string  $msg  The message
	 * @param bool    $echo TRUE(default) to echo the output, FALSE to just return
	 * @return string The HTML output
	 */
	public function msg_update( $msg = '', $echo = true, $title = '', $wpautop = true ) {
		return $this->print_message( 'yellow', $msg, $echo, $title, 'info', $wpautop );
	}

	/**
	 * Prints an okay message in style.
	 *
	 * @param string  $msg  The message
	 * @param bool    $echo TRUE(default) to echo the output, FALSE to just return
	 * @return string The HTML output
	 */
	public function msg_okay( $msg = '', $echo = true, $title = '', $wpautop = true ) {
		return $this->print_message( 'green', $msg, $echo, $title, 'info', $wpautop );
	}

	public function print_message( $style, $msg = '', $echo = true, $title = '', $icon = 'info', $wpautop = true ) {
		if ( $title == '' ) {
			if ( isset( $this->default_messages['messages'][$style] ) ) {
				$title = $this->default_messages['messages'][$style];
			}
		}
		switch ( $style ) {
		case 'yellow' :
		case 'update' :
			$icon = 'info3';
			break;
		case 'red' :
		case 'error' :
			$icon = 'cancel-circle';
			break;
		case 'okay' :
		case 'green' :
			$icon = 'checkmark-circle';
			break;
		}
		ob_start();
?>
<div class="ipt_uif_message ui-widget ui-widget-content ui-corner-all ipt_uif_widget_box">
	<?php if ( $title != '' ) : ?>
	<div class="ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
		<h3>
			<?php if ( $icon != '' ) : ?>
			<span class="ipt-icomoon-<?php echo $icon; ?> ipt_uif_text_icon_no_bg"></span>
			<?php endif; ?>
			<?php echo $title; ?>
		</h3>
	</div>
	<?php endif; ?>
	<div class="ui-widget-content ui-corner-all">
		<?php if ( $wpautop ) : ?>
			<?php echo wpautop( $msg ); ?>
		<?php else : ?>
			<?php echo $msg; ?>
		<?php endif; ?>
	</div>
</div>
		<?php
		$output = ob_get_clean();
		if ( $echo )
			echo $output;
		return $output;
	}

	/*==========================================================================
	 * CSS 3 Loader
	 *========================================================================*/
	/**
	 * Creates the HTML for the CSS3 Loader.
	 *
	 * @param bool    $hidden  TRUE(default) if hidden in inital state (Optional).
	 * @param string  $id      HTML ID (Optional).
	 * @param array   $labels  Labels which will be converted to HTML data attribute
	 * @param bool    $inline  Whether inline(true) or overlay (false)
	 * @param string  $default Default text
	 * @param array   $classes Array of additional classes (Optional).
	 *
	 */
	public function ajax_loader( $hidden = true, $id = '', $labels = array(), $inline = false, $default = null, $classes = array() ) {
		if ( !is_array( $classes ) ) {
			$classes = (array) $classes;
		}
		if ( !$inline ) {
			$classes[] = 'ipt_uif_ajax_loader';
		} else {
			$classes[] = 'ipt_uif_ajax_loader_inline';
		}
		$id_attr = '';
		if ( $id != '' ) {
			$id_attr = ' id="' . esc_attr( trim( $id ) ) . '"';
		}
		$style_attr = '';
		if ( $hidden == true ) {
			$style_attr = ' style="display: none;"';
		}
		$data_attr = $this->convert_data_attributes( $labels );
		if ( $default === null ) {
			$default = $this->default_messages['ajax_loader'];
		}
?>
<div class="<?php echo implode( ' ', $classes ); ?>"<?php echo $id_attr . $style_attr . $data_attr; ?>>
	<div class="ipt_uif_ajax_loader_inner ipt_uif_ajax_loader_animate">
		<div class="ipt_uif_ajax_loader_icon ipt_uif_ajax_loader_spin">
			<span class="ipt-icomoon-cog spinner-large"></span>
			<span class="ipt-icomoon-cog2 spinner-small"></span>
		</div>
		<div class="ipt_uif_ajax_loader_hellip">
			<span class="dot1">.</span><span class="dot2">.</span><span class="dot3">.</span>
		</div>
		<div class="ipt_uif_ajax_loader_text"><?php echo $default; ?></div>
		<div class="clear"></div>
	</div>
</div>
		<?php
	}


	public function print_icon_by_class( $icon = 'none', $background = true ) {
		if ( is_numeric( $icon ) ) {
			$this->print_icon_by_data( $icon, $background );
			return;
		}
?>
<?php if ( $icon != 'none' ) : ?>
<span class="<?php echo ( $background == false ) ? 'ipt_uif_text_icon_no_bg' : 'ipt_uif_text_icon'; ?>"><i class="ipt-icomoon-<?php echo esc_attr( $icon ); ?> ipticm"></i></span>
<?php endif; ?>
		<?php
	}

	public function print_icon_by_data( $data = 'none', $background = true ) {
		if ( ! is_numeric( $data ) ) {
			$this->print_icon_by_class( $data, $background );
			return;
		}
?>
<?php if ( $data != 'none' ) : ?>
<span class="<?php echo ( $background == false ) ? 'ipt_uif_text_icon_no_bg' : 'ipt_uif_text_icon'; ?>"><i class="ipticm" data-ipt-icomoon="&#x<?php echo dechex( $data ); ?>;"></i></span>
<?php endif; ?>
		<?php
	}

	public function print_icon( $icon = 'none', $background = true ) {
		if ( 'none' == $icon || empty( $icon ) ) {
			return;
		}
		if ( is_numeric( $icon ) ) {
			$this->print_icon_by_data( $icon, $background );
		} else {
			$this->print_icon_by_class( $icon, $background );
		}
	}

	public function print_scroll_to_top() {
?>
<a href="#" class="ipt_uif_scroll_to_top ipt-icomoon-arrow-up4"></a>
		<?php
	}

	public function question_container( $name, $title, $subtitle, $callback, $required = false, $fancy_box = false, $vertical = false, $description = '' ) {
		if ( !$this->check_callback( $callback ) ) {
			$this->msg_error( __( 'Invalid Callback', 'ipt_fsqm' ) );
			return;
		}
		if ( $required ) {
			$title .= '<span class="ipt_uif_question_required">*</span>';
		}
		$description = trim( (string) $description );
?>
<div class="ipt_uif_question<?php if ( $vertical ) echo ' ipt_uif_question_vertical' ?>">
	<div class="ipt_uif_question_label">
		<?php $this->generate_label( $name, $title, '', 'ipt_uif_question_title' ); ?>
		<?php $this->clear(); ?>
		<?php if ( $subtitle != '' ) : ?>
		<?php $this->generate_label( $name, $subtitle, '', 'ipt_uif_question_subtitle' ); ?>
		<?php endif; ?>
		<?php if ( $description !== '' ) : ?>
		<div class="ipt_uif_richtext">
			<?php echo apply_filters( 'ipt_uif_richtext', $description ); ?>
		</div>
		<?php endif; ?>
	</div>
	<div class="ipt_uif_question_content">
		<?php if ( $fancy_box ) : ?>
		<div class="ipt_uif_fancy_container">
		<?php endif; ?>
		<?php call_user_func_array( $callback[0], $callback[1] ); ?>
		<?php $this->clear(); ?>
		<?php if ( $fancy_box ) : ?>
		</div>
		<?php endif; ?>
	</div>
</div>
		<?php
	}

}
endif;
