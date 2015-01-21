<?php
/**
 * WP Feedback, Surver & Quiz Manager - Pro Form Elements Class
 * Base class
 *
 * Populates the actual form with all the hooks and filters
 *
 * @package WP Feedback, Surver & Quiz Manager - Pro
 * @subpackage Form Elements
 * @author Swashata Ghosh <swashata@intechgrity.com>
 */
class IPT_FSQM_Form_Elements_Base {
	/*==========================================================================
	 * DATABASE REFERENCE VARIABLES
	 *========================================================================*/
	public $form_id = null;

	public $name = '';
	public $type = '1';
	public $settings = array();

	public $mcq = array();
	public $pinfo = array();
	public $freetype = array();
	public $design = array();
	public $layout = array();

	/*==========================================================================
	 * INTERNAL VARIABLES
	 *========================================================================*/
	public $elements = array();
	public $post = array();
	public $post_raw = array();

	public $compatibility = false;


	/*==========================================================================
	 * CONSTRUCTOR
	 *========================================================================*/
	public function __construct( $form_id = null, $do_init = true ) {
		if ( $_SERVER['REQUEST_METHOD'] == 'POST' ) {
			//$this->post = $_POST;

			//we do not need to check on magic quotes
			//as wordpress always adds magic quotes
			//@link http://codex.wordpress.org/Function_Reference/stripslashes_deep
			$this->post = array_map( 'stripslashes_deep', $_POST );
			$this->post_raw = $_POST;

			// Now check if the ajax has send as post
			// Along with parsable string
			// This addresses issue #11
			if (  isset( $this->post['ipt_ps_send_as_str'] ) && $this->post['ipt_ps_send_as_str'] == 'true' && isset( $this->post['ipt_ps_look_into'] ) ) {
				$parse_post = array();
				parse_str( $this->post[$this->post['ipt_ps_look_into']], $parse_post );
				if ( get_magic_quotes_gpc() ) {
					$parse_post = array_map( 'stripslashes_deep', $parse_post );
				}
				$this->post = $parse_post;
			}

			//convert html to special characters
			//array_walk_recursive ($this->post, array($this, 'htmlspecialchar_ify'));
			//No need really Do it the way WordPress does it
		}

		$this->set_valid_elements();

		if ( $do_init ) {
			$this->init( $form_id );
		}
	}


	/* =========================================================================
	 * BASIC ABSTRACTIONS & API
	 * =======================================================================*/
	public function init( $form_id = null ) {
		global $wpdb, $ipt_fsqm_info;
		$this->form_id = null;
		$this->name = '';
		$this->type = '1';
		$this->settings = $this->get_default_settings();
		$this->mcq = array();
		$this->pinfo = array();
		$this->freetype = array();
		$this->design = array();
		$this->layout = array();
		if ( $form_id != null ) {
			$form_data = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$ipt_fsqm_info['form_table']} WHERE id = %d", $form_id ) );
			if ( null == $form_data ) {
				return;
			}
			$this->form_id = $form_id;
			$this->name = $form_data->name;
			$this->type = $form_data->type;
			$this->settings = maybe_unserialize( $form_data->settings );
			$this->mcq = maybe_unserialize( $form_data->mcq );
			$this->pinfo = maybe_unserialize( $form_data->pinfo );
			$this->freetype = maybe_unserialize( $form_data->freetype );
			$this->design = maybe_unserialize( $form_data->design );
			$this->layout = maybe_unserialize( $form_data->layout );
		}
		$this->compat_layout();
	}

	public function set_valid_elements() {
		$elements = array();

		// Layout Elements //
		$elements['layout'] = array(
			'title' => __( 'Layout & Structure', 'ipt_fsqm' ),
			'description' => __( 'Select the structure of the appearance of the form.', 'ipt_fsqm' ),
			'id' => 'ipt_fsqm_builder_layout',
		);
		$elements['layout']['elements'] = array(
			'tab' => array(
				'title' => __( 'Tabular Structure', 'ipt_fsqm' ),
				'description' => __( 'Tab like appearance with next/previous and submit button.', 'ipt_fsqm' ),
			),
			'pagination' => array(
				'title' => __( 'Paginated Structure', 'ipt_fsqm' ),
				'description' => __( 'Paginated appearance with progress bar.', 'ipt_fsqm' ),
			),
			'normal' => array(
				'title' => __( 'Normal Structure', 'ipt_fsqm' ),
				'description' => __( 'Normal continuous appearance without any page breaks.', 'ipt_fsqm' ),
			),
		);

		// Design Elements //
		$elements['design'] = array(
			'title' => __( 'Design & Security (D)', 'ipt_fsqm' ),
			'description' => __( 'Form Design & Security Tools.', 'ipt_fsqm' ),
			'id' => 'ipt_fsqm_builder_design',
		);
		$elements['design']['elements'] = array(
			'heading' => array(
				'title' => __( 'Heading', 'ipt_fsqm' ),
				'description' => __( 'Show a large heading text with optional scroll to top icon.', 'ipt_fsqm' )
			),
			'richtext' => array(
				'title' => __( 'Rich Text', 'ipt_fsqm' ),
				'description' => __( 'A Rich content (HTML) box. Can contain shortcodes.', 'ipt_fsqm' )
			),
			'embed' => array(
				'title' => __( 'Embed Code', 'ipt_fsqm' ),
				'description' => __( 'Embed any code, YouTube, FaceBook, iFrame etc.', 'ipt_fsqm' )
			),
			'collapsible' => array(
				'title' => __( 'Collapsible Content', 'ipt_fsqm' ),
				'description' => __( 'Collapsible content box. Can contain other elements inside it.', 'ipt_fsqm' ),
				'droppable' => true,
			),
			'container' => array(
				'title' => __( 'Styled Container', 'ipt_fsqm' ),
				'description' => __( 'Custom content box with style. Can contain other elements inside it.', 'ipt_fsqm' ),
				'droppable' => true,
			),
			'blank_container' => array(
				'title' => __( 'Simple Container', 'ipt_fsqm' ),
				'description' => __( 'Simple content box. Useful to add grouped conditional elements. Can contain other elements inside it.', 'ipt_fsqm' ),
				'droppable' => true,
			),
			'iconbox' => array(
				'title' => __( 'Icons and Buttons', 'ipt_fsqm' ),
				'description' => __( 'List of icons and/or texts optionally linked to some URL.', 'ipt_fsqm' ),
			),
			'col_half' => array(
				'title' => __( 'Column Half', 'ipt_fsqm' ),
				'description' => __( 'Column element with width half of the container.', 'ipt_fsqm' ),
				'droppable' => true,
			),
			'col_third' => array(
				'title' => __( 'Column Third', 'ipt_fsqm' ),
				'description' => __( 'Column element with width one third of the container.', 'ipt_fsqm' ),
				'droppable' => true,
			),
			'col_two_third' => array(
				'title' => __( 'Column Two Third', 'ipt_fsqm' ),
				'description' => __( 'Column element with width two third of the container.', 'ipt_fsqm' ),
				'droppable' => true,
			),
			'col_forth' => array(
				'title' => __( 'Column Forth', 'ipt_fsqm' ),
				'description' => __( 'Column element with width one forth of the container.', 'ipt_fsqm' ),
				'droppable' => true,
			),
			'col_three_forth' => array(
				'title' => __( 'Column Three Forth', 'ipt_fsqm' ),
				'description' => __( 'Column element with width three forth of the container.', 'ipt_fsqm' ),
				'droppable' => true,
			),
			'col_three_forth' => array(
				'title' => __( 'Column Three Forth', 'ipt_fsqm' ),
				'description' => __( 'Column element with width three forth of the container.', 'ipt_fsqm' ),
				'droppable' => true,
			),
			'clear' => array(
				'title' => __( 'Clear Columns', 'ipt_fsqm' ),
				'description' => __( 'Clears the floating contents. Use this if after the last column of a group.', 'ipt_fsqm' ),
			),
			'horizontal_line' => array(
				'title' => __( 'Horizontal Line', 'ipt_fsqm' ),
				'description' => __( 'Horizontal line with scroll to top.', 'ipt_fsqm' ),
			),
			'divider' => array(
				'title' => __( 'Divider', 'ipt_fsqm' ),
				'description' => __( 'Divider with optional text, icon and/or scroll to top.', 'ipt_fsqm' ),
			),
			/*
			'button' => array(
				'title' => __('Button', 'ipt_fsqm'),
				'description' => __('Select from preset buttons with icons.', 'ipt_fsqm'),
			),
			*/
			'imageslider' => array(
				'title' => __( 'Image Slider', 'ipt_fsqm' ),
				'description' => __( 'Image gallery slider.', 'ipt_fsqm' )
			),
			'captcha' => array(
				'title' => __( 'Security Captcha', 'ipt_fsqm' ),
				'description' => __( 'Security challenge for anti bot protection.', 'ipt_fsqm' ),
			),
		);

		// MCQ Elements //
		$elements['mcq'] = array(
			'title' => __( 'Multiple Choice Questions (M)', 'ipt_fsqm' ),
			'description' => __( 'Used for survey and/or Quiz.', 'ipt_fsqm' ),
			'id' => 'ipt_fsqm_builder_mcq',
		);

		$elements['mcq']['elements'] = array(
			'radio' => array(
				'title' => __( 'Single Options', 'ipt_fsqm' ),
				'description' => __( 'Can select only one option from the list of options.', 'ipt_fsqm' ),
			),
			'checkbox' => array(
				'title' => __( 'Multiple Options', 'ipt_fsqm' ),
				'description' => __( 'Can select multiple options from the list of options.', 'ipt_fsqm' ),
			),
			'select' => array(
				'title' => __( 'Dropdown Options', 'ipt_fsqm' ),
				'description' => __( 'Can select only one or multiple options from a list of dropdown menu.', 'ipt_fsqm' ),
			),

			'slider' => array(
				'title' => __( 'Single Slider', 'ipt_fsqm' ),
				'description' => __( 'Can enter a number within a specified range using a slider.', 'ipt_fsqm' ),
			),
			'range' => array(
				'title' => __( 'Single Range', 'ipt_fsqm' ),
				'description' => __( 'Can enter a number within a specified range using a slider.', 'ipt_fsqm' ),
			),
			'spinners' => array(
				'title' => __( 'Spinners', 'ipt_fsqm' ),
				'description' => __( 'Can select one value from a list of available values for a number of options.', 'ipt_fsqm' ),
			),
			'grading' => array(
				'title' => __( 'Multiple Grading', 'ipt_fsqm' ),
				'description' => __( 'Can grade multiple options.', 'ipt_fsqm' ),
			),
			'starrating' => array(
				'title' => __( 'Star Ratings', 'ipt_fsqm' ),
				'description' => __( 'Can rate multiple options using star rating.', 'ipt_fsqm' ),
			),
			'scalerating' => array(
				'title' => __( 'Scale Ratings', 'ipt_fsqm' ),
				'description' => __( 'Can rate multiple options using radio buttons.', 'ipt_fsqm' ),
			),

			'matrix' => array(
				'title' => __( 'Matrix Question', 'ipt_fsqm' ),
				'description' => __( 'Format multiple questions and options inside a matrix.', 'ipt_fsqm' ),
			),
			'toggle' => array(
				'title' => __( 'Toggle Option', 'ipt_fsqm' ),
				'description' => __( 'Can select between two options.', 'ipt_fsqm' ),
			),
			'sorting' => array(
				'title' => __( 'Sortable List', 'ipt_fsqm' ),
				'description' => __( 'User has to sort in correct order to get better score.', 'ipt_fsqm' ),
			),
		);

		// FEEDBACK Elements //
		$elements['freetype'] = array(
			'title' => __( 'Feedback &amp; Upload (F)', 'ipt_fsqm' ),
			'description' => __( 'Gather and/or email feedbacks.', 'ipt_fsqm' ),
			'id' => 'ipt_fsqm_builder_freetype',
		);
		$elements['freetype']['elements'] = array(
			'feedback_large' => array(
				'title' => __( 'Feedback Large Text', 'ipt_fsqm' ),
				'description' => __( 'Can input texts with multiple lines.', 'ipt_fsqm' ),
			),
			'feedback_small' => array(
				'title' => __( 'Feedback Small Text', 'ipt_fsqm' ),
				'description' => __( 'Can input texts within a single line.', 'ipt_fsqm' ),
			),
			'upload' => array(
				'title' => __( 'File Upload', 'ipt_fsqm' ),
				'description' => __( 'Upload multiple files and media.', 'ipt_fsqm' ),
			),
		);

		// PINFO Elements //
		$elements['pinfo'] = array(
			'title' => __( 'Other Form Elements (O)', 'ipt_fsqm' ),
			'description' => __( 'All other form elements.', 'ipt_fsqm' ),
			'id' => 'ipt_fsqm_builder_pinfo',
		);
		$elements['pinfo']['elements'] = array(
			'f_name' => array(
				'title' => __( 'First Name (Stored in DB)', 'ipt_fsqm' ),
				'description' => __( 'Use this to collect the first name of the surveyee. Can populate in the list of entries. Can only be used once.', 'ipt_fsqm' ),
				'dbmap' => true,
			),
			'l_name' => array(
				'title' => __( 'Last Name (Stored in DB)', 'ipt_fsqm' ),
				'description' => __( 'Use this to collect the last name of the surveyee. Can populate in the list of entries. Can only be used once.', 'ipt_fsqm' ),
				'dbmap' => true,
			),
			'email' => array(
				'title' => __( 'Email (Stored in DB)', 'ipt_fsqm' ),
				'description' => __( 'Use this to collect the email of the surveyee. Can populate in the list of entries. Can only be used once.', 'ipt_fsqm' ),
				'dbmap' => true,
			),
			'phone' => array(
				'title' => __( 'Phone (Stored in DB)', 'ipt_fsqm' ),
				'description' => __( 'Use this to collect the phone number of the surveyee. Can populate in the list of entries. Can only be used once.', 'ipt_fsqm' ),
				'dbmap' => true,
			),
			'p_name' => array(
				'title' => __( 'Full Name', 'ipt_fsqm' ),
				'description' => __( 'Use this to collect names. By default only allows alphabetic characters with space.', 'ipt_fsqm' ),
			),
			'p_email' => array(
				'title' => __( 'Email Address', 'ipt_fsqm' ),
				'description' => __( 'Use this to collect additional email of the surveyee. Validates the email.', 'ipt_fsqm' ),
			),
			'p_phone' => array(
				'title' => __( 'Phone Number', 'ipt_fsqm' ),
				'description' => __( 'Use this to collect additional phone numbers of the surveyee. Validates the number.', 'ipt_fsqm' ),
			),
			'textinput' => array(
				'title' => __( 'Small Text', 'ipt_fsqm' ),
				'description' => __( 'Can input texts in a single line.', 'ipt_fsqm' ),
			),
			'textarea' => array(
				'title' => __( 'Large Text', 'ipt_fsqm' ),
				'description' => __( 'Can input texts with multiple lines.', 'ipt_fsqm' ),
			),
			'password' => array(
				'title' => __( 'Password', 'ipt_fsqm' ),
				'description' => __( 'Hidden text input.', 'ipt_fsqm' ),
			),
			'p_radio' => array(
				'title' => __( 'Radio Options', 'ipt_fsqm' ),
				'description' => __( 'Can select only one options from a list.', 'ipt_fsqm' ),
			),
			'p_checkbox' => array(
				'title' => __( 'Checkbox Options', 'ipt_fsqm' ),
				'description' => __( 'Can select multiple options from a list.', 'ipt_fsqm' ),
			),
			's_checkbox' => array(
				'title' => __( 'Single Checkbox', 'ipt_fsqm' ),
				'description' => __( 'Can tick or untick an option.', 'ipt_fsqm' ),
			),
			'p_select' => array(
				'title' => __( 'Dropdown Option', 'ipt_fsqm' ),
				'description' => __( 'Can select only one or multiple options from a list of dropdown menu.', 'ipt_fsqm' ),
			),
			'address' => array(
				'title' => __( 'Address', 'ipt_fsqm' ),
				'description' => __( 'Formatted address input boxes.', 'ipt_fsqm' ),
			),
			'keypad' => array(
				'title' => __( 'Keypad', 'ipt_fsqm' ),
				'description' => __( 'Keypad to enter numbers and/or text.', 'ipt_fsqm' ),
			),
			'datetime' => array(
				'title' => __( 'Date Time', 'ipt_fsqm' ),
				'description' => __( 'Formatted date/time input boxes.', 'ipt_fsqm' ),
			),
			'p_sorting' => array(
				'title' => __( 'Sortable Choices', 'ipt_fsqm' ),
				'description' => __( 'User can sort options according to their choices.', 'ipt_fsqm' ),
			),
		);

		foreach ( $elements as $e_key => $element ) {
			foreach ( $element['elements'] as $el_key => $el ) {
				$elements[$e_key]['elements'][$el_key]['m_type'] = $e_key;
				$elements[$e_key]['elements'][$el_key]['type'] = $el_key;
			}
		}

		$this->elements = apply_filters( 'ipt_fsqm_filter_valid_elements', $elements, $this->form_id );
	}

	public function get_element_structure( $element ) {
		$default = array(
			'type' => $element,
			'title' => '',
			'validation' => array(),
			'subtitle' => '',
			'description' => '',
			'conditional' => array(
				'active' => false, // True to use conditional logic, false to ignore
				'status' => false, // Initial status -> True for shown, false for hidden
				'change' => true, // Change to status -> True for shown, false for hide
				// 'relation' => 'indi', // AND, OR, INDI relationship to verify against the logic (and,or,indi) ALWAYS indi
				'logic' => array( // element dependent logics
					// 0 => array(
					// 	'm_type' => '', // Mother type
					// 	'key' => '', // Key of the element
					// 	'check' => 'val', //value(val), length(len)
					// 	'operator' => 'eq', // equals(eq), not equals(neq), greater than(gt), less than(lt), contains (ct), does not contain (dct), starts with (sw), ends with (ew)
					// 	'value' => '',
					// 	'rel' => 'and', // (and, or)
					// ),
				),
			),
		);

		switch ( $element ) {
		default :
			$default = false;
			break;

		// Layout Elements - Stored directly inside layout //
		case 'tab' :
		case 'pagination' :
		case 'normal' :
			$default['m_type'] = 'layout';
			$default['elements'] = array();
			$default['icon'] = 'none';
			// $default['time_limit'] = ''; #defered for FSQM 2.2.6
			unset( $default['validation'] );
			unset( $default['conditional'] );
			break;

		// Design Elements - Stored directly inside design //
		case 'heading' :
			$default['m_type'] = 'design'; //mother type
			$default['settings'] = array(
				'type' => 'h2',
				'align' => 'left',
				'icon' => 'none',
				'show_top' => true,
			);
			break;

		case 'richtext' :
			$default['m_type'] = 'design';
			$default['settings'] = array(
				'icon' => 0xe10f,
			);
			break;

		case 'embed' :
			$default['m_type'] = 'design';
			break;

		case 'collapsible' :
			$default['m_type'] = 'design';
			$default['settings'] = array(
				'icon' => 'none',
				'expanded' => false,
			);
			$default['elements'] = array();
			break;

		case 'container' :
			$default['m_type'] = 'design';
			$default['settings'] = array(
				'icon' => 'none',
			);
			$default['elements'] = array();
			break;

		case 'blank_container' :
			$default['m_type'] = 'design';
			$default['elements'] = array();
			break;

		case 'iconbox' :
			$default['m_type'] = 'design';
			$default['settings'] = array(
				'align' => 'center',
				'elements' => array(),
			);
			break;

		case 'col_half' :
		case 'col_third' :
		case 'col_two_third' :
		case 'col_forth' :
		case 'col_three_forth' :
			$default['m_type'] = 'design';
			$default['elements'] = array();
			break;

		case 'clear' :
			$default['m_type'] = 'design';
			unset( $default['conditional'] );
			break;

		case 'horizontal_line' :
			$default['m_type'] = 'design';
			$default['settings'] = array(
				'show_top' => true,
			);
			break;

		case 'divider' :
			$default['m_type'] = 'design';
			$default['settings'] = array(
				'align' => 'center',
				'icon' => 0xe195,
				'show_top' => true,
			);
			break;
			/*
			case 'button' :
				$default['m_type'] = 'design';
				$default['settings'] = array(
					'url' => '',
					'new_tab' => true,
					'size' => 'medium',
					'icon' => 'none',
				);
				break;
			defered forever icon serves the same purpose*/
		case 'imageslider' :
			$default['m_type'] = 'design';
			$default['settings'] = array(
				'autoslide' => true,
				'duration' => '5',
				'transition' => '0.5',
				'animation' => 'random',
				'images' => array(),
			);
			break;

		case 'captcha' :
			$default['m_type'] = 'design';
			$default['settings'] = array(
				'type' => 'math', //can be quiz, reCaptcha(future)
				'answer' => '',
			);
			break;
		// END Design Elements //

		// MCQ Type - Stored in mcq and populated in report //
		case 'radio' :
			$default['m_type'] = 'mcq';
			$default['settings'] = array(
				'options' => array(), //array(label => value, score => value)
				'columns' => '2',
				'vertical' => false,
				'others' => false,
				'o_label' => __( 'Others', 'ipt_fsqm' ),
				'icon' => 0xe18e,
			);
			$default['validation'] = array(
				'required' => true
			);
			break;

		case 'checkbox' :
			$default['m_type'] = 'mcq';
			$default['settings'] = array(
				'options' => array(),
				'columns' => '2',
				'vertical' => false,
				'others' => false,
				'o_label' => __( 'Others', 'ipt_fsqm' ),
				'icon' => 0xe18e,
			);
			$default['validation'] = array(
				'required' => true,
				'filters' => array(
					'minCheckbox' => '',
					'maxCheckbox' => '',
				),
			);
			break;

		case 'select' :
			$default['m_type'] = 'mcq';
			$default['settings'] = array(
				'options' => array(),
				'vertical' => false,
				'others' => false,
				'o_label' => __( 'Others', 'ipt_fsqm' ),
				'e_label' => '',
			);
			$default['validation'] = array(
				'required' => true,
			);
			break;

		case 'slider' :
			$default['m_type'] = 'mcq';
			$default['settings'] = array(
				'min' => '0',
				'max' => '100',
				'step' => '1',
				'show_count' => true,
				'vertical' => false,
				'prefix' => '',
				'suffix' => '',
			);
			break;

		case 'range' :
			$default['m_type'] = 'mcq';
			$default['settings'] = array(
				'min' => '0',
				'max' => '100',
				'step' => '1',
				'show_count' => true,
				'vertical' => false,
				'prefix' => '',
				'suffix' => '',
			);
			break;

		case 'spinners' :
			$default['m_type'] = 'mcq';
			$default['settings'] = array(
				'options' => array(),
				'min' => '',
				'max' => '',
				'step' => '1',
				'vertical' => false,
			);
			$default['validation'] = array(
				'required' => true,
			);
			break;

		case 'grading' :
			$default['m_type'] = 'mcq';
			$default['settings'] = array(
				'options' => array(),
				'min' => '0',
				'max' => '100',
				'step' => '1',
				'show_count' => true,
				'range' => false,
				'vertical' => false,
			);
			break;

		case 'starrating' :
			$default['m_type'] = 'mcq';
			$default['settings'] = array(
				'options' => array(),
				'max' => '10',
				'vertical' => false,
			);
			$default['validation'] = array(
				'required' => true,
			);
			break;

		case 'scalerating' :
			$default['m_type'] = 'mcq';
			$default['settings'] = array(
				'options' => array(),
				'max' => '10',
				'show_previous' => false,
				'vertical' => false,
			);
			$default['validation'] = array(
				'required' => true,
			);
			break;

		case 'matrix' :
			$default['m_type'] = 'mcq';
			$default['settings'] = array(
				'rows' => array(),
				'columns' => array(),
				'scores' => array(),
				'multiple' => false,
				'vertical' => true,
				'icon' => 0xe18e,
			);
			$default['validation'] = array(
				'required' => true,
			);
			break;

		case 'toggle' :
			$default['m_type'] = 'mcq';
			$default['settings'] = array(
				'on' => __( 'On', 'ipt_fsqm' ),
				'off' => __( 'Off', 'ipt_fsqm' ),
				'checked' => false,
				'vertical' => false,
			);
			break;

		case 'sorting' :
			$default['m_type'] = 'mcq';
			$default['settings'] = array(
				'score_type' => 'individual', //Can be individual or combined
				'base_score' => '0',
				'options' => array(),
				'no_shuffle' => false,
				'vertical' => false,
			);
			break;
		// END MCQ Elements //

		// FEEDBACK Elements - Stored in freetype and emails to admins when filled //
		case 'feedback_large' :
			$default['m_type'] = 'freetype';
			$default['settings'] = array(
				'email' => '',
				'placeholder' => __( 'Write here', 'ipt_fsqm' ),
				'score' => '',
				'vertical' => false,
			);
			$default['validation'] = array(
				'required' => true,
				'filters' => array(
					'type' => 'all', //phone, url, email, date, number, integer, ipv4, onlyNumberSp, onlyLetterSp, onlyLetterNumber
					'min' => '',
					'max' => '',
					'minSize' => '',
					'maxSize' => '',
				),
			);
			break;

		case 'feedback_small' :
			$default['m_type'] = 'freetype';
			$default['settings'] = array(
				'email' => '',
				'icon' => 0xe001,
				'placeholder' => __( 'Write here', 'ipt_fsqm' ),
				'score' => '',
				'vertical' => false,
			);
			$default['validation'] = array(
				'required' => true,
				'filters' => array(
					'type' => 'all', //phone, url, email, date, number, integer, ipv4, onlyNumberSp, onlyLetterSp, onlyLetterNumber
					'min' => '',
					'max' => '',
					'minSize' => '',
					'maxSize' => '',
				),
			);
			break;
		case 'upload' :
			$default['m_type'] = 'freetype';
			$default['settings'] = array(
				'icon' => 0xe002,
				'accept_file_types' => 'gif,jpeg,png,jpg',
				'max_number_of_files' => '',
				'min_number_of_files' => '',
				'max_file_size' => '1000000',
				'min_file_size' => '1',
				'wp_media_integration' => false,
				'auto_upload' => true,
				// Adding feature #7
				'single_upload' => false,
				// --
				'drag_n_drop' => true,
				'progress_bar' => true,
				'preview_media' => true,
				'can_delete' => true,
			);
			$default['validation'] = array(
				'required' => true,
			);

			break;
		// END FEEDBACK Elements //

		// PINFO Elements - Stored in pinfo (named after personal information) //
		case 'f_name' :
		case 'l_name' :
		case 'email' :
		case 'phone' :
		case 'p_name' :
		case 'p_email' :
		case 'p_phone' :
			$default['m_type'] = 'pinfo';
			$default['settings'] = array(
				'placeholder' => __( 'Write here', 'ipt_fsqm' ),
				'vertical' => false,
			);
			$default['validation'] = array(
				'required' => true,
			);
			break;

		case 'textinput' :
			$default['m_type'] = 'pinfo';
			$default['settings'] = array(
				'icon' => 0xe001,
				'placeholder' => __( 'Write here', 'ipt_fsqm' ),
				'vertical' => false,
			);
			$default['validation'] = array(
				'required' => false,
				'filters' => array(
					'type' => 'all', //phone, url, email, date, number, integer, ipv4, onlyNumberSp, onlyLetterSp, onlyLetterNumber
					'min' => '',
					'max' => '',
					'minSize' => '',
					'maxSize' => '',
				),
			);
			break;

		case 'textarea' :
			$default['m_type'] = 'pinfo';
			$default['settings'] = array(
				'placeholder' => __( 'Write here', 'ipt_fsqm' ),
				'vertical' => false,
			);
			$default['validation'] = array(
				'required' => true,
				'filters' => array(
					'type' => 'all', //phone, url, email, date, number, integer, ipv4, onlyNumberSp, onlyLetterSp, onlyLetterNumber
					'min' => '',
					'max' => '',
					'minSize' => '',
					'maxSize' => '',
				),
			);
			break;

		case 'password' :
			$default['m_type'] = 'pinfo';
			$default['settings'] = array(
				'confirm_duplicate' => false,
				'placeholder' => __( 'Write here', 'ipt_fsqm' ),
				'vertical' => false,
			);
			$default['validation'] = array(
				'required' => true,
			);
			break;

		case 'p_radio' :
			$default['m_type'] = 'pinfo';
			$default['settings'] = array(
				'options' => array(), //array(label => value)
				'columns' => '2',
				'others' => false,
				'o_label' => __( 'Others', 'ipt_fsqm' ),
				'vertical' => false,
				'icon' => 0xe18e,
			);
			$default['validation'] = array(
				'required' => true,
			);
			break;

		case 'p_checkbox' :
			$default['m_type'] = 'pinfo';
			$default['settings'] = array(
				'options' => array(),
				'columns' => '2',
				'others' => false,
				'o_label' => __( 'Others', 'ipt_fsqm' ),
				'vertical' => false,
				'icon' => 0xe18e,
			);
			$default['validation'] = array(
				'required' => true,
				'filters' => array(
					'minCheckbox' => '',
					'maxCheckbox' => '',
				),
			);
			break;

		case 'p_select' :
			$default['m_type'] = 'pinfo';
			$default['settings'] = array(
				'options' => array(),
				'others' => false,
				'o_label' => __( 'Others', 'ipt_fsqm' ),
				'e_label' => '',
				'vertical' => false,
			);
			$default['validation'] = array(
				'required' => true,
			);
			break;

		case 's_checkbox' : //Single checkbox
			$default['m_type'] = 'pinfo';
			$default['settings'] = array(
				'checked' => false,
				'icon' => 0xe18e,
			);
			$default['validation'] = array(
				'required' => true,
			);
			break;

		case 'address' :
			$default['m_type'] = 'pinfo';
			$default['settings'] = array(
				'recipient' => __( 'Recipient', 'ipt_fsqm' ),
				'line_one' => __( 'Address line one', 'ipt_fsqm' ),
				'line_two' => __( 'Address line two', 'ipt_fsqm' ),
				'line_three' => __( 'Address line three', 'ipt_fsqm' ),
				'country' => __( 'Country', 'ipt_fsqm' ),
				'vertical' => false,
			);
			$default['validation'] = array(
				'required' => true,
			);
			break;

		case 'keypad' :
			$default['m_type'] = 'pinfo';
			$default['settings'] = array(
				'mask' => true,
				'multiline' => false,
				'type' => 'qwerty', //keyboard|international|alpha|dvorak|num
				'placeholder' => __( 'Write here', 'ipt_fsqm' ),
				'vertical' => false,
			);
			$default['validation'] = array(
				'required' => true,
				'filters' => array(
					'type' => 'all', //phone, url, email, date, number, integer, ipv4, onlyNumberSp, onlyLetterSp, onlyLetterNumber
					'min' => '',
					'max' => '',
					'minSize' => '',
					'maxSize' => '',
				),
			);
			break;

		case 'datetime' :
			$default['m_type'] = 'pinfo';
			$default['settings'] = array(
				'show_current' => false,
				'type' => 'datetime', //date|time|datetime,
				'date_format' => 'yy-mm-dd',
				'time_format' => 'HH:mm:ss',
				'placeholder' => __( 'Click here', 'ipt_fsqm' ),
				'vertical' => false,
			);
			$default['validation'] = array(
				'required' => true,
				'filters' => array(
					'past' => '',
					'future' => '',
				),
			);
			break;
		case 'p_sorting' :
			$default['m_type'] = 'pinfo';
			$default['settings'] = array(
				'options' => array(),
				'vertical' => false,
			);
			break;
		// END PINFO Elements //
		}

		if ( $default['m_type'] == 'design' ) {
			unset( $default['validation'] );
		}

		return apply_filters( 'ipt_fsqm_form_element_structure', $default, $element, $this->form_id );

	}

	public function get_submission_structure( $element ) {
		$default = array(
			'type' => $element,
		);

		switch ( $element ) {
		default :
			$default = false;
			break;

		// Design Elements //
		case 'captcha' :
			$default['m_type'] = 'design';
			$default['hash'] = '';
			$default['value'] = '';
		// End Design Elements //

		// MCQ Type - Stored in mcq and populated in report //
		case 'checkbox' :
		case 'radio' :
		case 'select' :
			$default['m_type'] = 'mcq';
			$default['options'] = array();
			$default['others'] = '';
			$default['scoredata'] = array();
			break;

		case 'slider' :
			$default['m_type'] = 'mcq';
			$default['value'] = '';
			break;

		case 'range' :
			$default['m_type'] = 'mcq';
			$default['values'] = array(
				'min' => '',
				'max' => '',
			);
			break;

		case 'grading' :
			$default['m_type'] = 'mcq';
			$default['options'] = array(
				/*
					0 => array(
						'min' => '',
						'max' => '',
						) || 0 => string
					),
				*/
			);
			break;

		case 'starrating' :
		case 'scalerating' :
		case 'spinners' :
			$default['m_type'] = 'mcq';
			$default['options'] = array(
				/*
					0 => '',
				*/
			);
			break;

		case 'matrix' :
			$default['m_type'] = 'mcq';
			$default['rows'] = array(
				/*
					0 => array([Columns,...]),
				*/
			);
			$default['scoredata'] = array();
			break;

		case 'toggle' :
			$default['m_type'] = 'mcq';
			$default['value'] = false;
			break;

		case 'sorting' :
			$default['m_type'] = 'mcq';
			$default['order'] = array();
			$default['scoredata'] = array();
			break;
		// END MCQ Elements //

		// FEEDBACK Elements - Stored in freetype and emails to admins when filled //
		case 'feedback_large' :
		case 'feedback_small' :
			$default['m_type'] = 'freetype';
			$default['value'] = '';
			$default['score'] = '';
			break;
		case 'upload' :
			$default['m_type'] = 'freetype';
			$default['id'] = array();
		break;
		// END FEEDBACK Elements //

		// PINFO Elements - Stored in pinfo (named after personal information) //
		case 'f_name' :
		case 'l_name' :
		case 'email' :
		case 'phone' :
		case 'p_name' :
		case 'p_email' :
		case 'p_phone' :
		case 'textinput' :
		case 'textarea' :
		case 'password' :
		case 'keypad' :
		case 'datetime' :
			$default['m_type'] = 'pinfo';
			$default['value'] = '';
			break;

		case 'p_radio' :
		case 'p_checkbox' :
		case 'p_select' :
			$default['m_type'] = 'pinfo';
			$default['options'] = array();
			$default['others'] = '';
			break;

		case 's_checkbox' : //Single checkbox
			$default['m_type'] = 'pinfo';
			$default['value'] = false;
			break;

		case 'address' :
			$default['m_type'] = 'pinfo';
			$default['values'] = array(
				'recipient' => '',
				'line_one' => '',
				'line_two' => '',
				'line_three' => '',
				'country' => '',
			);
			break;

		case 'p_sorting' :
			$default['m_type'] = 'pinfo';
			$default['order'] = array();
			break;
		// END PINFO Elements //
		}

		return apply_filters( 'ipt_fsqm_filter_form_data_structure', $default, $element, $this->form_id );
	}

	public function get_default_settings() {
		$settings = array(
			'general' => array(
				'terms_page' => '',
				'terms_phrase' => __( 'By submitting this form, you hereby agree to accept our <a href="%1$s" target="_blank">Terms & Conditions</a>. Your IP address <strong>%2$s</strong> will be stored in our database.', 'ipt_fsqm' ),
				'comment_title' => __( 'Administrator Remarks', 'ipt_fsqm' ),
				'default_comment' => __( 'Processing', 'ipt_fsqm' ),
				'can_edit' => false,
				'edit_time' => '',
			),
			'user' => array(
				'notification_sub' => __( 'We have got your answers.', 'ipt_fsqm' ),
				'notification_msg' => __( 'Thank you %NAME% for taking the quiz/survey/feedback.' . "\n" . 'We have received your answers. You can view it anytime from this link below:' . "\n" . '%TRACK_LINK%' . "\n" . 'Here is a copy of your submission:' . "\n" . '%SUBMISSION%', 'ipt_fsqm' ),
				'notification_from' => get_bloginfo( 'name' ),
				'notification_email' => get_option( 'admin_email' ),
				'smtp' => false,
				'smtp_config' => array(
					'enc_type' => 'ssl',
					'host' => 'smtp.gmail.com',
					'port' => '465',
					'username' => '',
					'password' => '',
				),
			),
			'admin' => array(
				'email' => get_option( 'admin_email' ),
				'mail_submission' => false,
				'send_from_user' => false,
			),
			'limitation' => array(
				'email_limit' => '0',
				'ip_limit' => '0',
				'user_limit' => '0',
				'user_limit_msg' => __( 'Your submission limit has been exceeded. You can check <a href="%PORTAL_LINK%">your portal page</a> to access previous submissions.', 'ipt_fsqm' ),
				'logged_in' => false,
				'logged_in_fallback' => 'show_login', // show_login => Show login form | redirect => Redirect to a specific page
				'non_logged_redirect' => wp_login_url( '' ) . '?redirect_to=_self_',
			),
			'type_specific' => array(
				'pagination' => array(
					'show_progress_bar' => true,
				),
				'tab' => array(
					'can_previous' => true,
					'block_previous' => false,
				),
				'normal' => array(
					'wrapper' => true,
				),
			),
			'buttons' => array(
				'next' => __( 'Next', 'ipt_fsqm' ),
				'prev' => __( 'Previous', 'ipt_fsqm' ),
				'submit' => __( 'Submit', 'ipt_fsqm' ),
			),
			'submission' => array(
				'no_auto_complete' => false,
				'process_title' => __( 'Processing you request', 'ipt_fsqm' ),
				'success_title' => __( 'Your form has been submitted', 'ipt_fsqm' ),
				'success_message' => __( 'Thank you for giving your answers', 'ipt_fsqm' ),
			),
			'redirection' => array(
				'type' => 'none', // 'none'|'flat'|'score'
				'delay' => '1000',
				'top' => false,
				'url' => '%TRACKBACK%',
				'score' => array(),
			),
			'ranking' => array(
				'enabled' => false,
				'title' => __( 'Designation', 'ipt_fsqm' ),
				'ranks' => array(),
			),
			/* Deferred for 2.5.0
			'trackback' => array(
				'f_name' => true,
				'l_name' => true,
				'email' => true,
				'phone' => true,
				'ip' => true,
				'total_score' => true,
				'designation' => true,
				'user_account' => true,
				'link' => true,
				'individual_score' => true,
				'hide_options' => false,
				'before' => '',
				'after' => '',
			),
			// Deferred for 2.6.0
			'quiz' => array(
				'time_limit' => false,
				'time_limit_type' => 'overall', // overall | page_specific
				'overall_limit' => '120',
			), */
			'theme' => array(
				'template' => 'designer-4',
				'logo' => '',
				'waypoint' => true,
				'custom_style' => false,
				'style' => array(
					'head_font' => 'oswald',
					'body_font' => 'roboto',
					'base_font_size' => 12,
					'head_font_typo' => array(
						'bold' => false,
						'italic' => false,
					),
					'custom' => '',
				),
			),
		);

		return apply_filters( 'ipt_fsqm_filter_default_settings', $settings, $this->form_id );
	}

	public function get_available_themes() {
		$path = plugins_url( '/static/front/css/ui-themes/', IPT_FSQM_Loader::$abs_file );
		$themes = array(
			'designer' => array(
				'label' => __( 'Custom designer themes', 'ipt_fsqm' ),
				'themes' => array(
					'designer-1' => array(
						'label' => 'Designer Theme - Dark Green',
						'src' => array(
							'common' => array(
								$path . 'designer-1/1.10/jquery-ui-1.10.3.custom.css',
								$path . 'designer-1/form.css',
								$path . 'designer-tab-pb.css',
							),
						),
						'js' => array(
							'designer_form' => plugins_url( '/static/front/js/jquery.ipt-fsqm-designer-forms.js', IPT_FSQM_Loader::$abs_file ),
						),
						'colors' => array( '5e7151', 'ffffff', '4e6046' ),
					),
					'designer-2' => array(
						'label' => 'Designer Theme - Light Grey',
						'src' => array(
							'common' => array(
								$path . 'designer-2/1.10/jquery-ui-1.10.3.custom.css',
								$path . 'designer-2/form.css',
								$path . 'designer-tab-pb.css',
							),
						),
						'js' => array(
							'designer_form' => plugins_url( '/static/front/js/jquery.ipt-fsqm-designer-forms.js', IPT_FSQM_Loader::$abs_file ),
						),
						'colors' => array( 'b7b7b7', '979797', '5d5d5d' ),
					),
					'designer-3' => array(
						'label' => 'Designer Theme - Dark Grey',
						'src' => array(
							'common' => array(
								$path . 'designer-3/1.10/jquery-ui-1.10.3.custom.css',
								$path . 'designer-3/form.css',
								$path . 'designer-tab-pb.css',
							),
						),
						'js' => array(
							'designer_form' => plugins_url( '/static/front/js/jquery.ipt-fsqm-designer-forms.js', IPT_FSQM_Loader::$abs_file ),
						),
						'colors' => array( '484848', '282828', '7a7a7a' ),
					),
					'designer-4' => array(
						'label' => 'Designer Theme - Moonlight White',
						'src' => array(
							'common' => array(
								$path . 'designer-4/1.10/jquery-ui-1.10.3.custom.css',
								$path . 'designer-4/form.css',
								$path . 'designer-tab-pb.css',
							),
						),
						'js' => array(
							'designer_form' => plugins_url( '/static/front/js/jquery.ipt-fsqm-designer-forms.js', IPT_FSQM_Loader::$abs_file ),
						),
						'colors' => array( 'c7c5c5', 'f5f5f5', '484848', 'ffffff' ),
					),
					'designer-5' => array(
						'label' => 'Designer Theme - Midnight Dark',
						'src' => array(
							'common' => array(
								$path . 'designer-5/1.10/jquery-ui-1.10.3.custom.css',
								$path . 'designer-5/form.css',
								$path . 'designer-tab-pb.css',
							),
						),
						'js' => array(
							'designer_form' => plugins_url( '/static/front/js/jquery.ipt-fsqm-designer-forms.js', IPT_FSQM_Loader::$abs_file ),
						),
						'colors' => array( '181818', '282828', 'ffffff' ),
					),
				),
			),
			'bootstrap' => array(
				'label' => __( 'Bootstrap Themes', 'ipt_fsqm' ),
				'themes' => array(
					'bootstrap' => array(
						'label' => 'Bootstrap Basic',
						'src' => array(
							'common' => array(
								$path . 'bootstrap/1.10/jquery-ui-1.10.3.custom.css',
								$path . 'bootstrap/1.10/jquery-ui-1.10.3.theme.css',
								$path . 'bootstrap/form.css',
							),
						),
						'colors' => array( 'eeeeee', 'dddddd', 'cccccc', '999999', '333333', '428bca', '52a8ec' ),
					),
				),
			),
			'light' => array(
				'label' => __( 'Light Themes from jQuery UI', 'ipt_fsqm' ),
				'themes' => array(
					'default' => array(
						'label' => 'Default Theme',
						'src' => array(),
						'colors' => array( 'f4fcfd', '333333', '3ac7ff', '01a8bd' ),
					),
					'excite-bike' => array(
						'label' => 'Excite Bike',
						'src' => array(
							'common' => $path . 'excite-bike/form.css',
							'1.9' => $path . 'excite-bike/1.9/jquery-ui-1.9.2.custom.min.css',
							'1.10' => $path . 'excite-bike/1.10/jquery-ui-1.10.3.custom.min.css',
						),
						'colors' => array( 'eeeeee', '282828', '2293f7', 'e69700' ),
					),
					'cupertino' => array(
						'label' => 'Cupertino',
						'src' => array(
							'common' => $path . 'cupertino/form.css',
							'1.9' => $path . 'cupertino/1.9/jquery-ui-1.9.2.custom.min.css',
							'1.10' => $path . 'cupertino/1.10/jquery-ui-1.10.3.custom.min.css',
						),
						'colors' => array( 'f2f5f7', '362b36', 'aed0ea', '74b2e2' ),
					),
					'blitzer' => array(
						'label' => 'Blitzer',
						'src' => array(
							'common' => $path . 'blitzer/form.css',
							'1.9' => $path . 'blitzer/1.9/jquery-ui-1.9.2.custom.min.css',
							'1.10' => $path . 'blitzer/1.10/jquery-ui-1.10.3.custom.min.css',
						),
						'colors' => array( 'ffffff', '333333', 'e3a1a1', 'cc0000' )
					),
					'black-tie' => array(
						'label' => 'Black Tie',
						'src' => array(
							'common' => $path . 'black-tie/form.css',
							'1.9' => $path . 'black-tie/1.9/jquery-ui-1.9.2.custom.min.css',
							'1.10' => $path . 'black-tie/1.10/jquery-ui-1.10.3.custom.min.css',
						),
						'colors' => array( 'f9f9f9', '000000', 'cccccc', '777777' ),
					),
					'hot-sneaks' => array(
						'label' => 'Hot Sneaks',
						'src' => array(
							'common' => $path . 'hot-sneaks/form.css',
							'1.9' => $path . 'hot-sneaks/1.9/jquery-ui-1.9.2.custom.min.css',
							'1.10' => $path . 'hot-sneaks/1.10/jquery-ui-1.10.3.custom.min.css',
						),
						'colors' => array( 'ffffff', '232323', 'ccd232', 'db4865' ),
					),
					'humanity' => array(
						'label' => 'Humanity',
						'src' => array(
							'common' => $path . 'humanity/form.css',
							'1.9' => $path . 'humanity/1.9/jquery-ui-1.9.2.custom.min.css',
							'1.10' => $path . 'humanity/1.10/jquery-ui-1.10.3.custom.min.css',
						),
						'colors' => array( 'f4f0ec', '232323', 'f5ad66', 'd49768', 'cb842e' ),
					),
					'redmond' => array(
						'label' => 'Redmond',
						'src' => array(
							'common' => $path . 'redmond/form.css',
							'1.9' => $path . 'redmond/1.9/jquery-ui-1.9.2.custom.min.css',
							'1.10' => $path . 'redmond/1.10/jquery-ui-1.10.3.custom.min.css',
						),
						'colors' => array( 'fcfdfd', '333333', 'accbe3', '5c9ccc' ),
					),
					'pepper-grinder' => array(
						'label' => 'Pepper Grinder',
						'src' => array(
							'common' => $path . 'pepper-grinder/form.css',
							'1.9' => $path . 'pepper-grinder/1.9/jquery-ui-1.9.2.custom.min.css',
							'1.10' => $path . 'pepper-grinder/1.10/jquery-ui-1.10.3.custom.min.css',
						),
						'colors' => array( 'eceadf', '1f1f1f', 'cbc7bd', '654b24' ),
					),
					'start' => array(
						'label' => 'Start',
						'src' => array(
							'common' => $path . 'start/form.css',
							'1.9' => $path . 'start/1.9/jquery-ui-1.9.2.custom.min.css',
							'1.10' => $path . 'start/1.10/jquery-ui-1.10.3.custom.min.css',
						),
						'colors' => array( 'fcfdfd', '222222', 'a6c9e2', '4297d7', 'acdd4a', '6eac2c' ),
					),
					'smoothness' => array(
						'label' => 'Smoothness',
						'src' => array(
							'common' => $path . 'smoothness/form.css',
							'1.9' => $path . 'smoothness/1.9/jquery-ui-1.9.2.custom.min.css',
							'1.10' => $path . 'smoothness/1.10/jquery-ui-1.10.3.custom.min.css',
						),
						'colors' => array( 'ffffff', '333333', 'd6d5d5', 'cccccc', '939292' ),
					),
					'south-street' => array(
						'label' => 'South Street',
						'src' => array(
							'common' => $path . 'south-street/form.css',
							'1.9' => $path . 'south-street/1.9/jquery-ui-1.9.2.custom.min.css',
							'1.10' => $path . 'south-street/1.10/jquery-ui-1.10.3.custom.min.css',
						),
						'colors' => array( 'f5f3e5', '312e25', 'd4ccb0', '459e00', '327e04' ),
					),
					'flick' => array(
						'label' => 'Flick',
						'src' => array(
							'common' => $path . 'flick/form.css',
							'1.9' => $path . 'flick/1.9/jquery-ui-1.9.2.custom.min.css',
							'1.10' => $path . 'flick/1.10/jquery-ui-1.10.3.custom.min.css',
						),
						'colors' => array( 'ffffff', '0073ea', 'cccccc', '1b4168' ),
					),
					'ui-lightness' => array(
						'label' => 'UI Lightness',
						'src' => array(
							'common' => $path . 'ui-lightness/form.css',
							'1.9' => $path . 'ui-lightness/1.9/jquery-ui-1.9.2.custom.min.css',
							'1.10' => $path . 'ui-lightness/1.10/jquery-ui-1.10.3.custom.min.css',
						),
						'colors' => array( 'eeeeee', '333333', 'f1c581', 'e78f08' ),
					),
					'overcast' => array(
						'label' => 'Overcast',
						'src' => array(
							'common' => $path . 'overcast/form.css',
							'1.9' => $path . 'overcast/1.9/jquery-ui-1.9.2.custom.min.css',
							'1.10' => $path . 'overcast/1.10/jquery-ui-1.10.3.custom.min.css',
						),
						'colors' => array( 'c9c9c9', '232323', 'f8f8f8', 'dddddd', 'aaaaaa' ),
					),
					'sunny' => array(
						'label' => 'Sunny',
						'src' => array(
							'common' => $path . 'sunny/form.css',
							'1.9' => $path . 'sunny/1.9/jquery-ui-1.9.2.custom.min.css',
							'1.10' => $path . 'sunny/1.10/jquery-ui-1.10.3.custom.min.css',
						),
						'colors' => array( 'feeebd', '383838', 'ccc09c', 'ffdd57', 'a45b13', '655e4e' ),
					),
				),
			),
			'dark' => array(
				'label' => __( 'Dark Themes from jQuery UI', 'ipt_fsqm' ),
				'themes' => array(
					'dot-luv' => array(
						'label' => 'Dot Luv',
						'src' => array(
							'common' => $path . 'dot-luv/form.css',
							'1.9' => $path . 'dot-luv/1.9/jquery-ui-1.9.2.custom.min.css',
							'1.10' => $path . 'dot-luv/1.10/jquery-ui-1.10.3.custom.min.css',
						),
						'colors' => array( '111111', 'fff', '505050', '164777', '096ac8' ),
					),
					'swanky-purse' => array(
						'label' => 'Swanky Purse',
						'src' => array(
							'common' => $path . 'swanky-purse/form.css',
							'1.9' => $path . 'swanky-purse/1.9/jquery-ui-1.9.2.custom.min.css',
							'1.10' => $path . 'swanky-purse/1.10/jquery-ui-1.10.3.custom.min.css',
						),
						'colors' => array( '443113', 'fff', 'baaa5a', 'efec9f' ),
					),
					'ui-darkness' => array(
						'label' => 'UI Darkness',
						'src' => array(
							'common' => $path . 'ui-darkness/form.css',
							'1.9' => $path . 'ui-darkness/1.9/jquery-ui-1.9.2.custom.min.css',
							'1.10' => $path . 'ui-darkness/1.10/jquery-ui-1.10.3.custom.min.css',
						),
						'colors' => array( '000000', 'ffffff', '666666', '59b4d4', 'f58400' ),
					),
					'eggplant' => array(
						'label' => 'Eggplant',
						'src' => array(
							'common' => $path . 'eggplant/form.css',
							'1.9' => $path . 'eggplant/1.9/jquery-ui-1.9.2.custom.min.css',
							'1.10' => $path . 'eggplant/1.10/jquery-ui-1.10.3.custom.min.css',
						),
						'colors' => array( '3d3644', 'eae6ea', 'd4d1bf', 'dcd9de', 'd1c5d8' ),
					),
					'le-frog' => array(
						'label' => 'Le Frog',
						'src' => array(
							'common' => $path . 'le-frog/form.css',
							'1.9' => $path . 'le-frog/1.9/jquery-ui-1.9.2.custom.min.css',
							'1.10' => $path . 'le-frog/1.10/jquery-ui-1.10.3.custom.min.css',
						),
						'colors' => array( '285c00', 'ffffff', '4eb305', '8bd83b' ),
					),
					'mint-choc' => array(
						'label' => 'Mint Choc',
						'src' => array(
							'common' => $path . 'mint-choc/form.css',
							'1.9' => $path . 'mint-choc/1.9/jquery-ui-1.9.2.custom.min.css',
							'1.10' => $path . 'mint-choc/1.10/jquery-ui-1.10.3.custom.min.css',
						),
						'colors' => array( '201913', 'ffffff', '695444', 'a19982' ),
					),
					'trontastic' => array(
						'label' => 'Trontastic',
						'src' => array(
							'common' => $path . 'trontastic/form.css',
							'1.9' => $path . 'trontastic/1.9/jquery-ui-1.9.2.custom.min.css',
							'1.10' => $path . 'trontastic/1.10/jquery-ui-1.10.3.custom.min.css',
						),
						'colors' => array( '000000', 'ffffff', '696969', '9fda58' ),
					),
					'dark-hive' => array(
						'label' => 'Dark Hive',
						'src' => array(
							'common' => $path . 'dark-hive/form.css',
							'1.9' => $path . 'dark-hive/1.9/jquery-ui-1.9.2.custom.min.css',
							'1.10' => $path . 'dark-hive/1.10/jquery-ui-1.10.3.custom.min.css',
						),
						'colors' => array( '000000', 'ffffff', '0d3c52', '0972a5', '26b3f7' ),
					),
					'vader' => array(
						'label' => 'Vader',
						'src' => array(
							'common' => $path . 'vader/form.css',
							'1.9' => $path . 'vader/1.9/jquery-ui-1.9.2.custom.min.css',
							'1.10' => $path . 'vader/1.10/jquery-ui-1.10.3.custom.min.css',
						),
						'colors' => array( '121212', 'dddddd', '404040', '888888', 'adadad', ),
					),
				),
			),
		);

		return apply_filters( 'ipt_fsqm_filter_available_themes', $themes );
	}

	public function get_theme_by_id( $id ) {
		$return = array(
			'label' => '',
			'src' => array(),
			'js' => array(),
			'icons' => '333', // 000 | 333 | 666 | ccc | ddd | fff
		);
		$themes = $this->get_available_themes();
		foreach ( $themes as $theme_type ) {
			foreach ( $theme_type['themes'] as $theme_id => $theme ) {
				if ( $theme_id == $id ) {
					$return = $this->merge_elements( $theme, $return );
					break 2;
				}
			}
		}
		global $wp_version;
		$return['include'] = array();
		if ( version_compare( $wp_version, '3.6' ) < 0 ) {
			if ( isset( $return['src']['1.9'] ) ) {
				$return['include'] = array_merge( $return['include'], (array) $return['src']['1.9'] );
			}
		} else {
			if ( isset( $return['src']['1.10'] ) ) {
				$return['include'] = array_merge( $return['include'], (array) $return['src']['1.10'] );
			}
		}
		if ( isset( $return['src']['common'] ) ) {
			$return['include'] = array_merge( $return['include'], (array) $return['src']['common'] );
		}

		//Append the version
		foreach( $return['include'] as $i_key => $href ) {
			$return['include'][$i_key] = add_query_arg('version', IPT_FSQM_Loader::$version, $href);
		}

		return $return;
	}

	public function get_available_webfonts() {
		$web_fonts = array(
			'oswald' => array(
				'label' => "'Oswald', 'Arial Narrow', sans-serif",
				'include' => 'Oswald',
			),
			'roboto' => array(
				'label' => "'Roboto', Tahoma, Geneva, sans-serif",
				'include' => 'Roboto',
			),
			'quando' => array(
				'label' => "Quando, Georgia, serif",
				'include' => 'Quando',
			),
			'signika_negative' => array(
				'label' => "'Signika Negative', Verdana, sans-serif",
				'include' => 'Signika+Negative',
			),
			'lobster' => array(
				'label' => "'Lobster', Georgia, Times, serif",
				'include' => 'Lobster',
			),
			'cabin' => array(
				'label' => "'Cabin', Helvetica, Arial, sans-serif",
				'include' => 'Cabin',
			),
			'allerta' => array(
				'label' => "'Allerta', Helvetica, Arial, sans-serif",
				'include' => 'Allerta',
			),
			'crimson' => array(
				'label' => "'Crimson Text', Georgia, Times, serif",
				'include' => 'Crimson+Text',
			),
			'arvo' => array(
				'label' => "'Arvo', Georgia, Times, serif",
				'include' => 'Arvo',
			),
			'pt_sans' => array(
				'label' => "'PT Sans', Helvetica, Arial, sans-serif",
				'include' => 'PT+Sans',
			),
			'dancing_script' => array(
				'label' => "'Dancing Script', Georgia, Times, serif",
				'include' => 'Dancing+Script',
			),
			'josefin_sans' => array(
				'label' => "'Josefin Sans', Helvetica, Arial, sans-serif",
				'include' => 'Josefin+Sans',
			),
			'allan' => array(
				'label' => "'Allan', Helvetica, Arial, sans-serif",
				'include' => 'Allan',
			),
			'cardo' => array(
				'label' => "'Cardo', Georgia, Times, serif",
				'include' => 'Cardo',
			),
			'molengo' => array(
				'label' => "'Molengo', Georgia, Times, serif",
				'include' => 'Molengo',
			),
			'lekton' => array(
				'label' => "'Lekton', Helvetica, Arial, sans-serif",
				'include' => 'Lekton',
			),
			'droid_sans' => array(
				'label' => "'Droid Sans', Helvetica, Arial, sans-serif",
				'include' => 'Droid+Sans',
			),
			'droid_serif' => array(
				'label' => "'Droid Serif', Georgia, Times, serif",
				'include' => 'Droid+Serif',
			),
			'corben' => array(
				'label' => "'Corben', Georgia, Times, serif",
				'include' => 'Corben',
			),
			'nobile' => array(
				'label' => "'Nobile', Helvetica, Arial, sans-serif",
				'include' => 'Nobile',
			),
			'ubuntu' => array(
				'label' => "'Ubuntu', Helvetica, Arial, sans-serif",
				'include' => 'Ubuntu',
			),
			'vollkorn' => array(
				'label' => "'Vollkorn', Georgia, Times, serif",
				'include' => 'Vollkorn',
			),
			'bree_serif' => array(
				'label' => "'Bree Serif', Georgia, serif",
				'include' => 'Bree+Serif',
			),
			'open_sans' => array(
				'label' => "'Open Sans', Verdana, Helvetica, sans-serif",
				'include' => 'Open+Sans',
			),
			'bevan' => array(
				'label' => "'Bevan', Georgia, serif",
				'include' => 'Bevan',
			),
			'pontano_sans' => array(
				'label' => "'Pontano Sans', Verdana, Helvetica, sans-serif",
				'include' => 'Pontano+Sans',
			),
			'abril_fatface' => array(
				'label' => "'Abril Fatface', Georgia, serif",
				'include' => 'Abril+Fatface',
			),
			'average' => array(
				'label' => "'Average', Garamond, Georgia, serif",
				'include' => 'Average',
			),
			'lato' => array(
				'label' => "'Lato', sans-serif",
				'include' => 'Lato',
			),
			'Roboto_Condensed' => array(
				'label' => "'Roboto Condensed', 'Arial', sans-serif",
				'include' => 'Roboto+Condensed',
			),
			'Nato_Sans' => array(
				'label' => "'Nato Sans', Arial, sans-serif",
				'include' => 'Nato+Sans',
			),
			'Titillium Web' => array(
				'label' => "'Titillium Web', Arial, serif",
				'include' => 'Titillium+Web',
			),
			'Oxygen' => array(
				'label' => "'Oxygen', Arial, serif",
				'include' => 'Oxygen',
			),
			'Crafty_Girls' => array(
				'label' => "'Crafty Girls', cursive",
				'include' => 'Crafty+Girls',
			),
			'Dancing_Script' => array(
				'label' => "'Dancing Script', Arial, serif",
				'include' => 'Dancing+Script',
			),
			'Cuprum' => array(
				'label' => "'Cuprum', Arial, serif",
				'include' => 'Cuprum',
			),
			'Josefin_Sans' => array(
				'label' => "'Josefin Sans', sans-serif",
				'include' => 'Josefin+Sans',
			),
			'Philosopher' => array(
				'label' => "'Philosopher', sans-serif",
				'include' => 'Philosopher',
			),
			'Libre_Baskerville' => array(
				'label' => "'Libre Baskerville', serif",
				'include' => 'Libre+Baskerville',
			),
			'Merriweather_Sans' => array(
				'label' => "'Merriweather Sans', sans-serif",
				'include' => 'Merriweather+Sans',
			),
			'Asap' => array(
				'label' => "'Asap', sans-serif",
				'include' => 'Asap',
			),
			'Rokkitt' => array(
				'label' => "'Rokkitt', serif",
				'include' => 'Rokkitt',
			),
			'Gilda_Display' => array(
				'label' => "'Gilda Display', serif",
				'include' => 'Gilda+Display',
			),
			'Pinyon_Script' => array(
				'label' => "'Pinyon Script', cursive",
				'include' => 'Pinyon+Script',
			),
			'Tinos' => array(
				'label' => "'Tinos', serif",
				'include' => 'Tinos',
			),
			'Cabin_Condensed' => array(
				'label' => "'Cabin Condensed', sans-serif",
				'include' => 'Cabin+Condensed',
			),
			'Montserrat_Alternates' => array(
				'label' => "'Montserrat Alternates', sans-serif",
				'include' => 'Montserrat+Alternates',
			),
			'PT_Sans_Caption' => array(
				'label' => "'PT Sans Caption', sans-serif",
				'include' => 'PT+Sans+Caption',
			),
			'Economica' => array(
				'label' => "'Economica', sans-serif",
				'include' => 'Economica',
			),
			'Playfair_Display_SC' => array(
				'label' => "'Playfair Display SC', serif",
				'include' => 'Playfair+Display+SC',
			),
			'Hammersmith_One' => array(
				'label' => "'Hammersmith One', sans-serif",
				'include' => 'Hammersmith+One',
			),
			'Exo' => array(
				'label' => "'Exo', sans-serif",
				'include' => 'Exo',
			),
			'Poiret_One' => array(
				'label' => "'Poiret One', cursive",
				'include' => 'Poiret+One',
			),
			'Oleo_Script' => array(
				'label' => "'Oleo Script', cursive",
				'include' => 'Oleo+Script',
			),
			'Satisfy' => array(
				'label' => "'Satisfy', cursive",
				'include' => 'Satisfy',
			),
			'Chivo' => array(
				'label' => "'Chivo', sans-serif",
				'include' => 'Chivo',
			),
			'Marvel' => array(
				'label' => "'Marvel', sans-serif",
				'include' => 'Marvel',
			),
			'Quattrocento' => array(
				'label' => "'Quattrocento', serif",
				'include' => 'Quattrocento',
			),
			'Metrophobic' => array(
				'label' => "'Metrophobic', sans-serif",
				'include' => 'Metrophobic',
			),
			'Judson' => array(
				'label' => "'Judson', serif",
				'include' => 'Judson',
			),
			'Arbutus_Slab' => array(
				'label' => "'Arbutus Slab', serif",
				'include' => 'Arbutus+Slab',
			),
			'Electrolize' => array(
				'label' => "'Electrolize', sans-serif",
				'include' => 'Electrolize',
			),
			'Varela' => array(
				'label' => "'Varela', sans-serif",
				'include' => 'Varela',
			),
			'Julius_Sans_One' => array(
				'label' => "'Julius Sans One', sans-serif",
				'include' => 'Julius+Sans+One',
			),
			'ABeeZee' => array(
				'label' => "'ABeeZee', sans-serif",
				'include' => 'ABeeZee',
			),
			'Kite_One' => array(
				'label' => "'Kite One', sans-serif",
				'include' => 'Kite+One',
			),
			'Noto_Sans' => array(
				'label' => "'Noto Sans', sans-serif",
				'include' => 'Noto+Sans',
			),
			'Cinzel' => array(
				'label' => "'Cinzel', serif",
				'include' => 'Cinzel',
			),
			'Trykker' => array(
				'label' => "'Trykker', serif",
				'include' => 'Trykker',
			),
			'Jacques_Francois' => array(
				'label' => "'Jacques Francois', serif",
				'include' => 'Jacques+Francois',
			),
			'Domine' => array(
				'label' => "'Domine', serif",
				'include' => 'Domine',
			),
			'Comfortaa' => array(
				'label' => "'Comfortaa', cursive",
				'include' => 'Comfortaa',
			),
			'Salsa' => array(
				'label' => "'Salsa', cursive",
				'include' => 'Salsa',
			),
			'Nova_Square' => array(
				'label' => "'Nova Square', cursive",
				'include' => 'Nova+Square',
			),
			'Iceland' => array(
				'label' => "'Iceland', cursive",
				'include' => 'Iceland',
			),
			'Lancelot' => array(
				'label' => "'Lancelot', cursive",
				'include' => 'Lancelot',
			),
			'Supermercado_One' => array(
				'label' => "'Supermercado One', cursive",
				'include' => 'Supermercado+One',
			),
			'Averia_Libre' => array(
				'label' => "'Averia Libre', cursive",
				'include' => 'Averia+Libre',
			),
			'Croissant_One' => array(
				'label' => "'Croissant One', cursive",
				'include' => 'Croissant+One',
			),
			'Averia_Gruesa_Libre' => array(
				'label' => "'Averia Gruesa Libre', cursive",
				'include' => 'Averia+Gruesa+Libre',
			),
			'Overlock' => array(
				'label' => "'Overlock', cursive",
				'include' => 'Overlock',
			),
			'Lobster_Two' => array(
				'label' => "'Lobster Two', cursive",
				'include' => 'Lobster+Two',
			),
			'Bevan' => array(
				'label' => "'Bevan', cursive",
				'include' => 'Bevan',
			),
			'Pompiere' => array(
				'label' => "'Pompiere', cursive",
				'include' => 'Pompiere',
			),
			'Kelly_Slab' => array(
				'label' => "'Kelly Slab', cursive",
				'include' => 'Kelly+Slab',
			),
			'Carter_One' => array(
				'label' => "'Carter One', cursive",
				'include' => 'Carter+One',
			),
			'Inconsolata' => array(
				'label' => "'Inconsolata'",
				'include' => 'Inconsolata',
			),
			'Ubuntu_Mono' => array(
				'label' => "'Ubuntu Mono'",
				'include' => 'Ubuntu+Mono',
			),
			'Droid_Sans_Mono' => array(
				'label' => "'Droid Sans Mono'",
				'include' => 'Droid+Sans+Mono',
			),
			'Source_Code_Pro' => array(
				'label' => "'Source Code Pro'",
				'include' => 'Source+Code+Pro',
			),
			'Nova_Mono' => array(
				'label' => "'Nova Mono'",
				'include' => 'Nova+Mono',
			),
			'PT_Mono' => array(
				'label' => "'PT Mono'",
				'include' => 'PT+Mono',
			),
			'Cutive_Mono' => array(
				'label' => "'Cutive Mono'",
				'include' => 'Cutive+Mono',
			),
			'Crete_Round' => array(
				'label' => "'Crete Round', serif",
				'include' => 'Crete Round',
			),
			'EB_Garamond' => array(
				'label' => "'EB Garamond', serif",
				'include' => 'EB+Garamond',
			),
			'Cardo' => array(
				'label' => "'Cardo', serif",
				'include' => 'Cardo',
			),
			'Fanwood_Text' => array(
				'label' => "'Fanwood Text', serif",
				'include' => 'Fanwood+Text',
			),
			'Trocchi' => array(
				'label' => "'Trocchi', serif",
				'include' => 'Trocchi',
			),
			'Fauna_One' => array(
				'label' => "'Fauna One', serif",
				'include' => 'Fauna+One',
			),
			'Prata' => array(
				'label' => "'Prata', serif",
				'include' => 'Prata',
			),
		);

		foreach ( $web_fonts as $key => $font ) {
			$web_fonts[$key]['include'] = $font['include'] . ':400,400italic,700,700italic'; // Include the normal, italic, bold and bold italic
		}

		return apply_filters( 'ipt_fsqm_filter_available_webfonts', $web_fonts );
	}

	public function get_element_definition( $element_structure ) {
		return $this->elements[$element_structure['m_type']]['elements'][$element_structure['type']];
	}

	public function get_element_from_layout( $layout_element ) {
		return isset( $this->{$layout_element['m_type']}[$layout_element['key']] ) ? $this->{$layout_element['m_type']}[$layout_element['key']] : array();
	}


	public function build_element_html( $element, $key, $element_data = null, $submission_data = null, $name_prefix = '' ) {
		$type = '';
		if ( is_array( $element ) && isset( $element['type'] ) ) {
			$type = $element['type'];
		} else {
			$type = (string) $element;
		}
		$element_structure = $this->get_element_structure( $element );

		if ( false == $element_structure ) {
			$this->print_error( __( 'Invalid Element type supplied: ', 'ipt_fsqm' ) . $element );
			return false;
		}

		if ( $element_data != null ) {
			$element_data = $this->merge_elements( $element_data, $element_structure );
		} else {
			$element_data = $element_structure;
		}

		$submission_structure = $this->get_submission_structure( $element );

		if ( false == $submission_structure && $element_structure['m_type'] != 'design' ) {
			$this->print_error( __( 'Form submission type not set: ', 'ipt_fsqm' ) . $element );
			return false;
		}

		if ( $submission_data != null && false != $submission_structure ) {
			$submission_data = $this->merge_elements( $submission_data, $submission_structure );
		} else {
			$submission_data = $submission_structure;
		}

		$name_prefix = trim( $name_prefix );
		if ( $name_prefix == '' ) {
			$name_prefix .= $element_structure['m_type'] . '[' . $key . ']';
		} else {
			$name_prefix = $name_prefix . '[' . $element_structure['m_type'] . '][' . $key . ']';
		}
		$element_definition = $this->get_element_definition( $element_structure );
		$param = array( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $this );
		if ( method_exists( $this, 'build_' . $element ) ) {
			call_user_func_array( array( $this, 'build_' . $element ), $param );
		} else {
			if ( is_callable( $element_definition['callback'] ) ) {
				call_user_func_array( $element_definition['callback'], $param );
			} else {
				$this->print_error( __( 'No valid callback assigned.', 'ipt_fsqm' ) );
				return false;
			}
		}

		return true;
	}

	public function get_keys_from_layouts_by_types( $types, $layouts ) {
		$keys = array();
		if ( empty( $layouts ) || !is_array( $layouts ) ) {
			return $keys;
		}

		foreach ( $layouts as $layout ) {
			if ( !is_array( $layout ) || empty( $layout ) || !isset( $layout['elements'] ) || !is_array( $layout['elements'] ) || empty( $layout['elements'] ) ) {
				continue;
			}

			$keys = array_merge( $keys, $this->get_keys_from_layout_by_types( $types, $layout ) );
		}

		return $keys;
	}

	public function get_keys_from_layout_by_types( $types, $layout ) {
		$keys = array();
		if ( !is_array( $types ) ) {
			$types = (array) $types;
		}

		if ( empty( $layout ) || !is_array( $layout ) || !isset( $layout['elements'] ) || empty( $layout['elements'] ) ) {
			return $keys;
		}

		foreach ( $layout['elements'] as $element ) {
			if ( in_array( $element['type'], $types, true ) ) {
				$keys[] = $element['key'];
			} else {
				$element_definition = $this->get_element_definition( $element );
				if ( isset( $element_definition['droppable'] ) && $element_definition['droppable'] == true ) {
					$keys = array_merge( $keys, $this->get_keys_from_layout_by_types( $types, $this->get_element_from_layout( $element ) ) );
				}
			}
		}

		return $keys;
	}

	public function get_keys_from_layouts_by_m_type( $m_type, $layouts ) {
		$keys = array();
		if ( empty( $layouts ) || !is_array( $layouts ) ) {
			return $keys;
		}

		foreach ( $layouts as $layout ) {
			if ( !is_array( $layout ) || empty( $layout ) || !isset( $layout['elements'] ) || !is_array( $layout['elements'] ) || empty( $layout['elements'] ) ) {
				continue;
			}

			$keys = array_merge( $keys, $this->get_keys_from_layout_by_m_type( $m_type, $layout ) );
		}

		return $keys;
	}

	public function get_keys_from_layout_by_m_type( $m_type, $layout ) {
		$keys = array();

		if ( empty( $layout ) || !is_array( $layout ) || !isset( $layout['elements'] ) || empty( $layout['elements'] ) ) {
			return $keys;
		}

		foreach ( $layout['elements'] as $element ) {
			if ( $element['m_type'] == $m_type ) {
				$keys[] = $element['key'];
			} else {
				$element_definition = $this->get_element_definition( $element );
				if ( isset( $element_definition['droppable'] ) && $element_definition['droppable'] == true ) {
					$keys = array_merge( $keys, $this->get_keys_from_layout_by_m_type( $m_type, $this->get_element_from_layout( $element ) ) );
				}
			}
		}

		return $keys;
	}

	public function sanitize_min_max_step( $settings ) {
		if ( !is_array( $settings ) || !isset( $settings['min'] ) || !isset( $settings['max'] ) ) {
			return $settings;
		}
		$max = max( array( $settings['max'], $settings['min'] ) );
		$min = min( array( $settings['max'], $settings['min'] ) );
		$settings['max'] = $max;
		$settings['min'] = $min;

		if ( !isset( $settings['step'] ) ) {
			return $settings;
		}

		$settings['step'] = abs( $settings['step'] );
		if ( $settings['step'] == '0' ) {
			$settings['step'] = '1';
		}

		return $settings;
	}

	protected function encrypt( $input_string ) {
		return IPT_FSQM_Form_Elements_Static::encrypt( $input_string );
	}

	protected function decrypt( $encrypted_input_string ) {
		return IPT_FSQM_Form_Elements_Static::decrypt( $encrypted_input_string );
	}

	/**
	 * Recursively checks for the structure and copy value from the element
	 *
	 * @param array   $element
	 * @param array   $structure
	 * @return mixed
	 */
	public function merge_elements( $element, $structure, $merge_only = false ) {
		$fresh = array();
		foreach ( (array) $structure as $s_key => $sval ) {
			if ( is_array( $sval ) ) {
				//sda arrays in structures are always empty
				if ( empty( $sval ) ) {
					$fresh[$s_key] = isset( $element[$s_key] ) ? $element[$s_key] : array();
				} else {
					$new_element = isset( $element[$s_key] ) ? $element[$s_key] : array();
					$fresh[$s_key] = $this->merge_elements( $new_element, $sval );
				}
				//Check for settings
				if ( $s_key == 'settings' && $merge_only == false ) {
					$fresh[$s_key] = $this->sanitize_min_max_step( $fresh[$s_key] );
				}
			} elseif ( is_bool( $sval ) ) {
					$fresh[$s_key] = ( isset( $element[$s_key] ) && null !== $element[$s_key] && false !== $element[$s_key] && '' !== $element[$s_key] ) ? true : ( $merge_only ? $sval : false ); //Check for ajax submission as well
					//var_dump($element[$s_key], $fresh[$s_key]);
			} else {
				$fresh[$s_key] = isset( $element[$s_key] ) ? $element[$s_key] : $sval;
			}
		}

		return $fresh;
	}

	/*==========================================================================
	 * BASIC DATABASE ABSTRACTIONS
	 *========================================================================*/
	public function get_total_submissions() {
		global $ipt_fsqm_info, $wpdb;
		return (float) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(id) FROM {$ipt_fsqm_info['data_table']} WHERE form_id = %d", $this->form_id ) );
	}


	/*==========================================================================
	 * DEFAULT ELEMENTS - OVERRIDE
	 *========================================================================*/
	public function build_heading( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->print_error( __( 'Please override this method', 'ipt_fsqm' ) );
	}

	public function build_richtext( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->print_error( __( 'Please override this method', 'ipt_fsqm' ) );
	}

	public function build_embed( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->print_error( __( 'Please override this method', 'ipt_fsqm' ) );
	}

	public function build_collapsible( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->print_error( __( 'Please override this method', 'ipt_fsqm' ) );
	}

	public function build_container( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->print_error( __( 'Please override this method', 'ipt_fsqm' ) );
	}

	public function build_iconbox( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->print_error( __( 'Please override this method', 'ipt_fsqm' ) );
	}

	public function build_col_half( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->print_error( __( 'Please override this method', 'ipt_fsqm' ) );
	}

	public function build_col_third( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->print_error( __( 'Please override this method', 'ipt_fsqm' ) );
	}

	public function build_col_two_third( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->print_error( __( 'Please override this method', 'ipt_fsqm' ) );
	}

	public function build_col_forth( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->print_error( __( 'Please override this method', 'ipt_fsqm' ) );
	}

	public function build_col_three_forth( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->print_error( __( 'Please override this method', 'ipt_fsqm' ) );
	}

	public function build_clear( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->print_error( __( 'Please override this method', 'ipt_fsqm' ) );
	}

	public function build_horizontal_line( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->print_error( __( 'Please override this method', 'ipt_fsqm' ) );
	}

	public function build_divider( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->print_error( __( 'Please override this method', 'ipt_fsqm' ) );
	}

	public function build_button( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->print_error( __( 'Please override this method', 'ipt_fsqm' ) );
	}

	public function build_imageslider( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->print_error( __( 'Please override this method', 'ipt_fsqm' ) );
	}

	public function build_captcha( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->print_error( __( 'Please override this method', 'ipt_fsqm' ) );
	}

	public function build_radio( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->print_error( __( 'Please override this method', 'ipt_fsqm' ) );
	}

	public function build_checkbox( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->print_error( __( 'Please override this method', 'ipt_fsqm' ) );
	}

	public function build_select( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->print_error( __( 'Please override this method', 'ipt_fsqm' ) );
	}

	public function build_slider( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->print_error( __( 'Please override this method', 'ipt_fsqm' ) );
	}

	public function build_range( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->print_error( __( 'Please override this method', 'ipt_fsqm' ) );
	}

	public function build_spinners( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->print_error( __( 'Please override this method', 'ipt_fsqm' ) );
	}

	public function build_grading( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->print_error( __( 'Please override this method', 'ipt_fsqm' ) );
	}

	public function build_starrating( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->print_error( __( 'Please override this method', 'ipt_fsqm' ) );
	}

	public function build_scalerating( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->print_error( __( 'Please override this method', 'ipt_fsqm' ) );
	}

	public function build_matrix( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->print_error( __( 'Please override this method', 'ipt_fsqm' ) );
	}

	public function build_toggle( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->print_error( __( 'Please override this method', 'ipt_fsqm' ) );
	}

	public function build_sorting( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->print_error( __( 'Please override this method', 'ipt_fsqm' ) );
	}

	public function build_feedback_large( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->print_error( __( 'Please override this method', 'ipt_fsqm' ) );
	}

	public function build_feedback_small( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->print_error( __( 'Please override this method', 'ipt_fsqm' ) );
	}

	public function build_f_name( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->print_error( __( 'Please override this method', 'ipt_fsqm' ) );
	}

	public function build_l_name( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->print_error( __( 'Please override this method', 'ipt_fsqm' ) );
	}

	public function build_email( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->print_error( __( 'Please override this method', 'ipt_fsqm' ) );
	}

	public function build_phone( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->print_error( __( 'Please override this method', 'ipt_fsqm' ) );
	}

	public function build_p_name( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->print_error( __( 'Please override this method', 'ipt_fsqm' ) );
	}

	public function build_p_email( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->print_error( __( 'Please override this method', 'ipt_fsqm' ) );
	}

	public function build_p_phone( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->print_error( __( 'Please override this method', 'ipt_fsqm' ) );
	}

	public function build_textinput( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->print_error( __( 'Please override this method', 'ipt_fsqm' ) );
	}

	public function build_textarea( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->print_error( __( 'Please override this method', 'ipt_fsqm' ) );
	}

	public function build_password( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->print_error( __( 'Please override this method', 'ipt_fsqm' ) );
	}

	public function build_p_radio( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->print_error( __( 'Please override this method', 'ipt_fsqm' ) );
	}

	public function build_p_checkbox( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->print_error( __( 'Please override this method', 'ipt_fsqm' ) );
	}

	public function build_p_select( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->print_error( __( 'Please override this method', 'ipt_fsqm' ) );
	}

	public function build_s_checkbox( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->print_error( __( 'Please override this method', 'ipt_fsqm' ) );
	}

	public function build_address( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->print_error( __( 'Please override this method', 'ipt_fsqm' ) );
	}

	public function build_keypad( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->print_error( __( 'Please override this method', 'ipt_fsqm' ) );
	}

	public function build_datetime( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->print_error( __( 'Please override this method', 'ipt_fsqm' ) );
	}

	public function build_p_sorting( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->print_error( __( 'Please override this method', 'ipt_fsqm' ) );
	}

	/*==========================================================================
	 * COMPATIBILITY LAYER WITH VERSION < 2
	 *========================================================================*/
	public function compat_notice() {
		$this->print_update( __( 'The form you are working with currently, has an outdated structure. This happens if you are coming from an older version of WP Feedback, Survey & Quiz Manager. Please edit the form and save it to get it updated.', 'ipt_fsqm' ) );
	}
	public function compat_layout() {
		if ( null == $this->form_id || !empty( $this->layout ) ) {
			$default_settings = $this->get_default_settings();
			$this->settings = $this->merge_elements( $this->settings, $default_settings, true );
			$new_layout = $this->layout;
			foreach ( $new_layout as $l_key => $layout ) {
				$new_layout[$l_key] = $this->merge_elements( $layout, $this->get_element_structure( 'tab' ), true );
			}
			$this->layout = $new_layout;
			$this->compatibility = false;
			return;
		} else {
			//Check to see if this is just an empty form
			if ( empty( $this->mcq ) && empty( $this->freetype ) && empty( $this->pinfo ) ) {
				$default_settings = $this->get_default_settings();
				$this->settings = $this->merge_elements( $this->settings, $default_settings );
				$this->compatibility = false;
				return;
			}
			$this->compatibility = true;
			//var_dump($this->pinfo);
			//set the layout at per with old settings
			$this->layout = array();
			$this->type = '1';

			//the setup is tab type
			//loop along with tab order
			$layout_key = 0;
			$theme_shortcode = array(
				'survey' => 'mcq',
				'feedback' => 'free',
				'pinfo' => 'p',
			);

			if ( !isset( $this->settings['tab_order'] ) ) {
				$this->settings['tab_order'] = array(
					0 => 'survey',
					1 => 'feedback',
					2 => 'pinfo',
				);
			}
			foreach ( $this->settings['tab_order'] as $tab ) {
				if ( true != $this->settings['enable_' . $tab] ) {
					continue;
				}

				$layout = $this->get_element_structure( 'tab' );

				//make the title, subtitle, description
				$layout['title'] = $this->settings[$tab . '_title'];
				$layout['subtitle'] = $this->settings[$tab . '_subtitle'];
				$layout['description'] = $this->settings[$tab . '_description'];

				//call the method to update the layout elements and also modify the member variable
				call_user_func_array( array( $this, 'compat_' . $tab ), array( &$layout ) );

				//update this->layout
				$this->layout[$layout_key] = $layout;

				$layout_key++;
			}

			//compat the settings
			$this->compat_settings();
		}
	}

	public function compat_survey( &$layout ) {
		//Make the default survey type question array to replace $this->mcq
		$survey = array();


		//Loop through old mcqs
		foreach ( $this->mcq as $m_key => $mcq ) {
			//delete if not enabled
			if ( false == $mcq['enabled'] ) {
				continue;
			}

			//store the key to the layout elements
			$layout['elements'][] = array(
				'm_type' => 'mcq',
				'key' => $m_key,
				'type' => $mcq['type'] == 'single' ? 'radio' :  'checkbox',
			);

			//either radio or checkbox
			$survey[$m_key] = $mcq['type'] == 'single' ? $this->get_element_structure( 'radio' ) : $this->get_element_structure( 'checkbox' );

			//set the title
			$survey[$m_key]['title'] = $mcq['question'];

			//set the options
			$options = $this->split_options( $mcq['options'] );
			foreach ( $options as $option ) {
				$survey[$m_key]['settings']['options'][] = array(
					'label' => $option,
					'score' => '',
				);
			}

			//set others
			$survey[$m_key]['settings']['others'] = $mcq['others'];
			$survey[$m_key]['settings']['o_label'] = $mcq['o_label'];

			//set validation
			$survey[$m_key]['validation']['required'] = $mcq['required'];

			//Set types
			$survey[$m_key]['type'] = $mcq['type'] == 'single' ? 'radio' :  'checkbox';
			$survey[$m_key]['m_type'] = 'mcq';
		}

		//All set, now replace
		$this->mcq = $survey;
	}

	public function compat_feedback( &$layout ) {
		//make the new array to replace $this->freetype
		$feedback = array();

		//Loop through older feedbacks
		foreach ( $this->freetype as $f_key => $freetype ) {
			//delete if not enabled
			if ( false == $freetype['enabled'] ) {
				continue;
			}

			//Store the key to the layout element
			$layout['elements'][] = array(
				'm_type' => 'freetype',
				'key' => $f_key,
				'type' => 'feedback_large',
			);

			//get the default structure
			$feedback[$f_key] = $this->get_element_structure( 'feedback_large' );

			//set title
			$feedback[$f_key]['title'] = $freetype['name'];

			//set description
			$feedback[$f_key]['subtitle'] = $freetype['description'];

			//set email
			$feedback[$f_key]['settings']['email'] = $freetype['email'];

			//set validation
			$feedback[$f_key]['validation']['required'] = $freetype['required'];

			//Set the types
			$feedback[$f_key]['type'] = 'feedback_large';
			$feedback[$f_key]['m_type'] = 'freetype';
		}

		//Replace the variable
		$this->freetype = $feedback;
	}

	public function compat_pinfo( &$layout ) {
		//make the new array to store modified elements
		$others = array();

		//Loop through older pinfo
		$last_p_key = count( $this->pinfo );
		$pinfo_dbmap = array(
			'f_name' => __( 'First Name', 'ipt_fsqm' ),
			'l_name' => __( 'Last Name', 'ipt_fsqm' ),
			'email' => __( 'Email Address', 'ipt_fsqm' ),
			'phone' => __( 'Phone Number', 'ipt_fsqm' ),
		);
		foreach ( $this->pinfo as $p_key => $pinfo ) {
			//delete if not enabled
			if ( false == $pinfo['enabled'] ) {
				continue;
			}

			if ( !isset( $pinfo['type'] ) ) {
				$pinfo['type'] = 'dbmap';
			}

			$type = $p_key;
			$new_p_key = $pinfo['type'] == 'dbmap' ? $last_p_key++ : $p_key;

			//get the structure
			switch ( $pinfo['type'] ) {
			default :
				//These are presets, just need to check the structure and title.
				//Enabled is already checked
				//Required will be checked after this switch/case
				$others[$new_p_key] = $this->get_element_structure( $p_key );
				$others[$new_p_key]['title'] = $pinfo_dbmap[$p_key];
				break;
			case 'single' :
				$others[$new_p_key] = $this->get_element_structure( 'p_radio' );
				$options = $this->split_options( $pinfo['options'] );
				$others[$new_p_key]['settings']['options'] = array();
				foreach ( $options as $option ) {
					$others[$new_p_key]['settings']['options'][] = array( 'label' => $option );
				}
				$others[$new_p_key]['title'] = $pinfo['question'];
				$type = 'p_radio';
				break;
			case 'multiple' :
				$others[$new_p_key] = $this->get_element_structure( 'p_checkbox' );
				$options = $this->split_options( $pinfo['options'] );
				$others[$new_p_key]['settings']['options'] = array();
				foreach ( $options as $option ) {
					$others[$new_p_key]['settings']['options'][] = array( 'label' => $option );
				}
				$others[$new_p_key]['title'] = $pinfo['question'];
				$type = 'p_checkbox';
				break;
			case 'free-input' :
				$others[$new_p_key] = $this->get_element_structure( 'textinput' );
				$others[$new_p_key]['title'] = $pinfo['question'];
				$type = 'textinput';
				break;
			case 'free-text' :
				$others[$new_p_key] = $this->get_element_structure( 'textarea' );
				$others[$new_p_key]['title'] = $pinfo['question'];
				$type = 'textarea';
				break;
			case 'required-checkbox' :
				$others[$new_p_key] = $this->get_element_structure( 's_checkbox' );
				$others[$new_p_key]['title'] = $pinfo['question'];
				$type = 's_checkbox';
				break;
			}

			//Store the key to the layout element
			$layout['elements'][] = array(
				'm_type' => 'pinfo',
				'key' => $new_p_key,
				'type' => $type,
			);

			//Validation copy
			$others[$new_p_key]['validation']['required'] = isset( $pinfo['required'] ) ? $pinfo['required'] : false;
			if ( $pinfo['type'] == 'required-checkbox' ) {
				$others[$new_p_key]['validation']['required'] = true;
			}

			//Set types
			$others[$new_p_key]['type'] = $type;
			$others[$new_p_key]['m_type'] = 'pinfo';
		}
		//Append the captcha
		$captcha = $this->get_element_structure( 'captcha' );
		$layout['elements'][] = array(
			'type' => $captcha['type'],
			'm_type' => $captcha['m_type'],
			'key' => '0'
		);
		$this->design = array(
			0 => $captcha,
		);

		$this->pinfo = $others;
	}

	public function compat_settings() {
		$default_settings = $this->get_default_settings();

		$compat_settings = array(
			'general' => array(
				'terms_page' => $this->settings['terms_page'],
				'comment_title' => $this->settings['comment_title'],
				'default_comment' => $this->settings['default_comment'],
			),
			'user' => array(
				'notification_sub' => $this->settings['notification_sub'],
				'notification_msg' => $this->settings['notification_msg'],
				'notification_from' => $this->settings['notification_from'],
				'notification_email' => $this->settings['notification_email'],
			),
			'admin' => array(
				'email' => $this->settings['email'],
				'mail_submission' => isset( $this->settings['mail_submission'] ) ? $this->settings['mail_submission'] : false,
			),
			'limitation' => array(
				'email_limit' => $this->settings['unique_email'] == true ? '1' : '0',
				'ip_limit' => isset( $this->settings['ip_limit'] ) ? $this->settings['ip_limit'] : '0',
			),
			'type_specific' => array(
				'pagination' => array(
					'show_progress_bar' => true,
				),
				'tab' => array(
					'can_previous' => true,
				),
				'normal' => array(
					'wrapper' => false,
				),
			),
			'buttons' => array(
				'next' => __( 'Next', 'ipt_fsqm' ),
				'prev' => __( 'Previous', 'ipt_fsqm' ),
				'submit' => __( 'Submit', 'ipt_fsqm' ),
			),
			'submission' => array(
				'process_title' => $this->settings['process_title'],
				'success_title' => $this->settings['success_title'],
				'success_message' => $this->settings['success_message'],
			),
			'redirection' => array(
				'type' => 'none',
				'delay' => '1000',
				'url' => '',
				'score' => array(),
			),
			'theme' => array(
				'template' => $this->settings['theme'] == 'hot-sneak' ? 'hot-sneaks' : $this->settings['theme'],
				'custom_style' => $this->settings['custom'],
				'style' => array(
					'head_font' => $this->settings['css']['head_font'],
					'body_font' => $this->settings['css']['body_font'],
				),
			),
		);


		$this->settings = $this->merge_elements( $compat_settings, $default_settings );
	}


	/*==========================================================================
	 * INTERNAL HTML FORM ELEMENTS METHODS
	 *========================================================================*/
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
			if ( $d_val != '' )
				$data_attr .= ' data-' . esc_attr( $d_key ) . '="' . esc_attr( $d_val ) . '"';
		}

		return $data_attr;
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

	protected function convert_php_size_to_bytes( $sSize ) {
		if ( is_numeric( $sSize ) ) {
			return $sSize;
		}

		$sSuffix = substr($sSize, -1);
		$iValue = substr($sSize, 0, -1);
		switch(strtoupper($sSuffix)){
		case 'P':
			$iValue *= 1024;
		case 'T':
			$iValue *= 1024;
		case 'G':
			$iValue *= 1024;
		case 'M':
			$iValue *= 1024;
		case 'K':
			$iValue *= 1024;
			break;
		}
		return $iValue;
	}

	public function get_maximum_file_upload_size() {
		return min( $this->convert_php_size_to_bytes( ini_get( 'post_max_size' ) ), $this->convert_php_size_to_bytes( ini_get( 'upload_max_filesize' ) ) );
	}

	/**
	 * Prints error msg in WP style
	 *
	 * @param string  $msg
	 */
	protected function print_error( $msg = '', $echo = true ) {
		$output = '<div class="p-message red"><p>' . $msg . '</p></div>';
		if ( $echo )
			echo $output;
		else
			return $output;
	}

	protected function print_update( $msg = '', $echo = true ) {
		$output = '<div class="updated fade"><p>' . $msg . '</p></div>';
		if ( $echo )
			echo $output;
		else
			return $output;
	}

	protected function print_p_error( $msg = '', $echo = true ) {
		$output = '<div class="p-message red"><p>' . $msg . '</p></div>';
		if ( $echo )
			echo $output;
		return $output;
	}

	protected function print_p_update( $msg = '', $echo = true ) {
		$output = '<div class="p-message yellow"><p>' . $msg . '</p></div>';
		if ( $echo )
			echo $output;
		return $output;
	}

	protected function print_p_okay( $msg = '', $echo = true ) {
		$output = '<div class="p-message green"><p>' . $msg . '</p></div>';
		if ( $echo )
			echo $output;
		return $output;
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

	protected function split_options( $option ) {
		$option = explode( "\n", str_replace( "\r", '', $option ) );
		$clean = array();
		array_walk( $option, 'trim' );
		foreach ( $option as $v ) {
			if ( '' != $v )
				$clean[] = $v;
		}
		return $clean;
	}

	/**
	 *
	 *
	 * @deprecated since 1.0.0
	 * @param type    $value
	 */
	protected function clean_options( &$value ) {
		$value = htmlspecialchars( trim( strip_tags( htmlspecialchars_decode( $value ) ) ) );
	}
}
