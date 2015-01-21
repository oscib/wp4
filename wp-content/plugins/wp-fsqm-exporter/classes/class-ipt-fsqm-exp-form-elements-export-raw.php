<?php
/**
 * IPT FSQM Export RAW (CSV)
 *
 * @package IPT FSQM Export
 * @subpackage Form Elements
 * @author Swashata Ghosh <swashata@intechgrity.com>
 */
class IPT_FSQM_EXP_Form_Elements_Export_RAW extends IPT_FSQM_Form_Elements_Data {
	/**
	 * Path to the RAW Upload Directory
	 * This is set internally and shouldn't be modified
	 * @var string
	 */
	protected $wpath = null;

	/**
	 * Unique path to this RAW export
	 * @var string
	 */
	protected $uniqpath = null;

	/**
	 * ID of the RAW database entry
	 * @var string
	 */
	protected $raw_id = null;

	/**
	 * RAW data directly from the database
	 * @var stdClass
	 */
	protected $raw_data = null;

	/**
	 * Errors
	 * @var array
	 */
	protected $errors = array();

	/**
	 * CSV Delimiter
	 * @var string
	 */
	protected $delimiter = ',';

	/**
	 * CSV Enclosure
	 * @var string
	 */
	protected $enclosure = '"';

	/**
	 * CSV Multi Option Delimiter
	 * @var string
	 */
	protected $option_delimiter = '::';

	/**
	 * CSV Multi Row Delimiter
	 * @var string
	 */
	protected $row_delimiter = '~';

	/**
	 * CSV Multi Number Range Delimiter
	 * @var string
	 */
	protected $range_delimiter = '#';

	/**
	 * The UI Class for Admin
	 * @var IPT_Plugin_UIF_Admin
	 */
	public $ui = null;

	private $db_maps = array( 'f_name', 'l_name', 'email' );

	/*==========================================================================
	 * A few setters for config
	 *========================================================================*/
	/**
	 * Set the Multi Option Delimiter
	 * @param string $delimiter The delimiter character/string
	 */
	public function set_option_delimiter( $delimiter = '::' ) {
		$this->option_delimiter = $delimiter;
	}

	/**
	 * Set the Multi Row Delimiter
	 * @param string $delimiter The delimiter character/string
	 */
	public function set_row_delimiter( $delimiter = '~' ) {
		$this->row_delimiter = $delimiter;
	}

	/**
	 * Set the CSV Delimiter
	 * @param string $delimiter The delimiter character (Default ',')
	 */
	public function set_delimiter( $delimiter = ',' ) {
		if ( ! strlen( $delimiter ) ) {
			$delimiter = ',';
		}
		if ( $delimiter == '\t' ) {
			$delimiter = chr( 9 );
		}

		$delimiter = substr( $delimiter, 0, 1 );
		$this->delimiter = $delimiter;
	}

	/**
	 * Set the Enclosure
	 * @param string $enclosure The enclosure character (Default '"')
	 */
	public function set_enclosure( $enclosure = '"' ) {
		if ( ! strlen( $enclosure ) ) {
			$enclosure = '"';
		}

		$enclosure = substr( $enclosure, 0, 1 );
		$this->enclosure = $enclosure;
	}

	/**
	 * Set the Range Delimiter
	 * @param string $delimiter The range delimiter (Default '/')
	 */
	public function set_range_delimiter( $delimiter = '/' ) {
		if ( ! strlen( $delimiter ) ) {
			$delimiter = '/';
		}

		$this->range_delimiter = $delimiter;
	}

	/*==========================================================================
	 * Getters for some config
	 *========================================================================*/
	/**
	 * Gets the Path to the RAW Upload Directory
	 *
	 * @return string
	 */
	public function get_wpath() {
		return $this->wpath;
	}

	/**
	 * Gets the Unique path to this RAW export.
	 *
	 * @return string
	 */
	public function get_uniqpath() {
		return $this->uniqpath;
	}

	/**
	 * Gets the ID of the RAW database entry.
	 *
	 * @return string
	 */
	public function get_raw_id() {
		return $this->raw_id;
	}

	/**
	 * Gets the RAW data directly from the database.
	 *
	 * @return stdClass
	 */
	public function get_raw_data() {
		return $this->raw_data;
	}

	/**
	 * Gets the CSV Delimiter.
	 *
	 * @return string
	 */
	public function get_delimiter() {
		return $this->delimiter;
	}

	/**
	 * Gets the CSV Enclosure.
	 *
	 * @return string
	 */
	public function get_enclosure() {
		return $this->enclosure;
	}

	/**
	 * Gets the CSV Multi Option Delimiter.
	 *
	 * @return string
	 */
	public function get_option_delimiter() {
		return $this->option_delimiter;
	}

	/**
	 * Gets the CSV Multi Row Delimiter.
	 *
	 * @return string
	 */
	public function get_row_delimiter() {
		return $this->row_delimiter;
	}

	/**
	 * Gets the Range Delimiter.
	 *
	 * @return string
	 */
	public function get_range_delimiter() {
		return $this->range_delimiter;
	}

	/**
	 * Gets the The UI Class for Admin.
	 *
	 * @return IPT_Plugin_UIF_Admin
	 */
	public function get_ui() {
		return $this->ui;
	}

	/**
	 * Gets the errors
	 *
	 * @return array The error array, empty if no errors
	 */
	public function get_errors() {
		return $this->errors;
	}


	/*==========================================================================
	 * Constructor
	 *========================================================================*/

	public function __construct( $raw_id, $form_id = null, $for_wizard = false, $start_date = '', $end_date = '', $mcq = array(), $freetype = array(), $pinfo = array() ) {
		$this->ui = IPT_Plugin_UIF_Admin::instance( 'ipt_fsqm_exp' );
		if ( $form_id == null && $raw_id != null ) {
			global $wpdb, $ipt_fsqm_exp_info;
			$form_id = $wpdb->get_var( $wpdb->prepare( "SELECT form_id FROM {$ipt_fsqm_exp_info['raw_table']} WHERE id = %d", $raw_id ) );
		}
		if ( ! $for_wizard ) {
			parent::__construct( null, $form_id );
			$this->validate_raw_database( $raw_id, $start_date, $end_date, $mcq, $freetype, $pinfo );
			$this->populate_raw_variables();
			$this->set_paths();
		} else {
			// Init with a dummy form ID as we are just going to do wizard
			global $wpdb, $ipt_fsqm_info;
			$form_id = $wpdb->get_var( "SELECT id FROM {$ipt_fsqm_info['form_table']} LIMIT 0,1" );
			if ( $form_id !== null ) {
				parent::__construct( null, $form_id );
			}
		}
	}

	/*==========================================================================
	 * Wizard Functions
	 *========================================================================*/
	public function enqueue() {
		wp_enqueue_script( 'ipt-fsqm-exp-export-raw', plugins_url( '/static/admin/js/jquery.ipt-fsqm-exp-export-raw.js', IPT_FSQM_EXP_Loader::$abs_file ), array( 'jquery' ), IPT_FSQM_EXP_Loader::$version, true );
	}

	public function wizard( $ajax_action = 'ipt_fsqm_exp_raw_csv' ) {
		if ( $this->form_id == null ) {
			$this->ui->msg_error( __( 'You have not created any forms yet.', 'ipt_fsqm_exp' ) );
			return;
		}
		echo '<form method="post" action="">';
		if ( $_SERVER['REQUEST_METHOD'] == 'POST' ) {
			$settings = array(
				'form_id' => $this->post['form_id'],
				'custom_csv' => isset( $this->post['custom_csv'] ) && $this->post['custom_csv'] != '0' && $this->post['custom_csv'] != null ? true : false,
				'custom_csv_delimiter' => $this->post['custom_csv_delimiter'],
				'custom_csv_enclosure' => $this->post['custom_csv_enclosure'],
				'custom_csv_option_delimiter' => $this->post['custom_csv_option_delimiter'],
				'custom_csv_range_delimiter' => $this->post['custom_csv_range_delimiter'],
				'custom_csv_row_delimiter' => $this->post['custom_csv_row_delimiter'],
				'custom_date' => isset( $this->post['custom_date'] ) && $this->post['custom_date'] != '0' && $this->post['custom_date'] != null ? true : false,
				'custom_date_start' => $this->post['custom_date_start'],
				'custom_date_end' => $this->post['custom_date_end'],
				'load' => $this->post['load'],
			);
			if ( isset( $this->post['generate_csv'] ) ) {
				$mcq = isset( $this->post['mcq'] ) && is_array( $this->post['mcq'] ) ? $this->post['mcq'] : array();
				$freetype = isset( $this->post['freetype'] ) && is_array( $this->post['freetype'] ) ? $this->post['freetype'] : array();
				$pinfo = isset( $this->post['pinfo'] ) && is_array( $this->post['pinfo'] ) ? $this->post['pinfo'] : array();
				$this->wizard_generate_csv( $settings, $mcq, $freetype, $pinfo, $ajax_action );
			} else {
				$this->wizard_select_questions( $settings );
			}
		} else {
			$this->wizard_select_form();
		}

		echo '</form>';
	}

	public function wizard_select_form() {
		global $wpdb, $ipt_fsqm_info;

		$forms = $wpdb->get_results( "SELECT f.id id, f.name name, COUNT(d.id) subs FROM {$ipt_fsqm_info['form_table']} f LEFT JOIN {$ipt_fsqm_info['data_table']} d ON f.id = d.form_id GROUP BY f.id HAVING COUNT(d.id) > 0" );
		$today = current_time( 'mysql' );
		$least_date = $wpdb->get_var( "SELECT date FROM {$ipt_fsqm_info['data_table']} WHERE date != '0000-00-00 00:00:00' ORDER BY date ASC LIMIT 0,1" );
		$select_items = array();

		if ( !empty( $forms ) ) {
			foreach ( $forms as $form ) {
				$select_items[] = array(
					'label' => sprintf( __( '%1$s (Submissions: %2$d)', 'ipt_fsqm_exp' ), $form->name, $form->subs ),
					'value' => $form->id,
				);
			}
		}
		$this->ui->iconbox_head( __( 'Select the Form and Date Range', 'ipt_fsqm_exp' ), 'settings' );

		$server_loads = array(
			array(
				'label' => __( 'Light Load', 'ipt_fsqm_exp' ),
				'value' => '0',
			),
			array(
				'label' => __( 'Moderate Load (Recommended for Shared Hostings)', 'ipt_fsqm_exp' ),
				'value' => '1',
			),
			array(
				'label' => __( 'Heavy Load (Recommended for VPS or Dedicated Hostings)', 'ipt_fsqm_exp' ),
				'value' => '2',
			),
		);
		?>
<?php if ( empty( $forms ) ) : ?>
<?php $this->ui->msg_error( __( 'Not enough data for any of the forms to process the export.', 'ipt_fsqm_exp' ) ); ?>
<?php else : ?>
<table class="form-table">
	<tbody>
		<tr>
			<th><?php $this->ui->generate_label( 'form_id', __( 'Select Form', 'ipt_fsqm_exp' ) ); ?></th>
			<td>
				<?php $this->ui->select( 'form_id', $select_items, false ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'Please select the form whose report you want to generate.', 'ipt_fsqm_exp' ) ); ?>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<?php $this->ui->generate_label( 'load', __( 'Server Load', 'ipt_fsqm_exp' ) ); ?>
			</th>
			<td>
				<?php $this->ui->select( 'load', $server_loads, '1' ); ?>
			</td>
			<td>
				<?php $this->ui->help_head(); ?>
					<?php _e( 'Please select the calculation load for the queries.', 'ipt_fsqm_exp' ); ?>
					<ul class="ul-disc">
						<li><strong><?php _e( 'Light Load', 'ipt_fsqm_exp' ); ?></strong> : <?php _e( '15 queries per hit. Use this if you are experiencing problems.', 'ipt_fsqm_exp' ); ?></li>
						<li><strong><?php _e( 'Medium Load', 'ipt_fsqm_exp' ); ?></strong> : <?php _e( '30 queries per hit. Recommended for most of the shared hosting environments.', 'ipt_fsqm_exp' ); ?></li>
						<li><strong><?php _e( 'Heavy Load', 'ipt_fsqm_exp' ); ?></strong> : <?php _e( '50 queries per hit. Use only if you own a VPS or Dedicated Hosting.', 'ipt_fsqm_exp' ); ?></li>
					</ul>
					<?php _e( 'It is recommended to go with Medium Load for most of the shared servers.', 'ipt_fsqm_exp' ); ?>
				<?php $this->ui->help_tail(); ?>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<?php $this->ui->generate_label( 'custom_csv', __( 'Customize CSV Format', 'ipt_fsqm_exp' ) ) ?>
			</th>
			<td>
				<?php $this->ui->toggle( 'custom_csv', __( 'YES', 'ipt_fsqm_exp' ), __( 'NO', 'ipt_fsqm_exp' ), false, '1', false, true, array( 'condid' => 'csv_format_delimiter,csv_format_enclosure,csv_format_option_delimiter,csv_format_range_delimiter,csv_format_row_delimiter' ) ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'Tick to enter customize the CSV format for this export.', 'ipt_fsqm_exp' ) ); ?>
			</td>
		</tr>
		<tr id="csv_format_delimiter">
			<th scope="row">
				<?php $this->ui->generate_label( 'custom_csv_delimiter', __( 'CSV Field Delimiter', 'ipt_fsqm_exp' ) ); ?>
			</th>
			<td>
				<?php $this->ui->text( 'custom_csv_delimiter', $this->get_delimiter(), '', 'fit', 'normal', array( 'code' ) ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'Enter the CSV field delimiter. Only one character allowed. It will be stripped if given more than one character. Default is <code>,</code>', 'ipt_fsqm_exp' ) ); ?>
			</td>
		</tr>
		<tr id="csv_format_enclosure">
			<th scope="row">
				<?php $this->ui->generate_label( 'custom_csv_enclosure', __( 'CSV Field Enclosure', 'ipt_fsqm_exp' ) ); ?>
			</th>
			<td>
				<?php $this->ui->text( 'custom_csv_enclosure', $this->get_enclosure(), '', 'fit', 'normal', array( 'code' ) ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'Enter the CSV field Enclosure. Only one character allowed. It will be stripped if given more than one character. Default is <code>"</code>', 'ipt_fsqm_exp' ) ); ?>
			</td>
		</tr>
		<tr id="csv_format_option_delimiter">
			<th scope="row">
				<?php $this->ui->generate_label( 'custom_csv_option_delimiter', __( 'CSV Field Multiple Option Delimiter', 'ipt_fsqm_exp' ) ); ?>
			</th>
			<td>
				<?php $this->ui->text( 'custom_csv_option_delimiter', $this->get_option_delimiter(), '', 'fit', 'normal', array( 'code' ) ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'Enter the CSV field Multiple Option Delimiter (For questions like Multiple Options, Matrix etc). Default is <code>::</code>', 'ipt_fsqm_exp' ) ); ?>
			</td>
		</tr>
		<tr id="csv_format_range_delimiter">
			<th scope="row">
				<?php $this->ui->generate_label( 'custom_csv_range_delimiter', __( 'CSV Field Range Delimiter', 'ipt_fsqm_exp' ) ); ?>
			</th>
			<td>
				<?php $this->ui->text( 'custom_csv_range_delimiter', $this->get_range_delimiter(), '', 'fit', 'normal', array( 'code' ) ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'Enter the CSV field Range Delimiter (For questions like Grading, Ranges etc). Default is <code>#</code>', 'ipt_fsqm_exp' ) ); ?>
			</td>
		</tr>
		<tr id="csv_format_row_delimiter">
			<th scope="row">
				<?php $this->ui->generate_label( 'custom_csv_row_delimiter', __( 'CSV Field Multiple Row Delimiter', 'ipt_fsqm_exp' ) ); ?>
			</th>
			<td>
				<?php $this->ui->text( 'custom_csv_row_delimiter', $this->get_row_delimiter(), '', 'fit', 'normal', array( 'code' ) ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'Enter the CSV field Multiple Row Delimiter (For questions like Grading, Spinners, Matrix etc). Default is <code>~</code>', 'ipt_fsqm_exp' ) ); ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'custom_date', __( 'Custom Date Range', 'ipt_fsqm_exp' ) ); ?></th>
			<td>
				<?php $this->ui->toggle( 'custom_date', __( 'YES', 'ipt_fsqm_exp' ), __( 'NO', 'ipt_fsqm_exp' ), false, '1', false, true, array( 'condid' => 'ipt_fsqm_custom_date_start,ipt_fsqm_custom_date_end' ) ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'Tick to enter custom date range for the report.', 'ipt_fsqm_exp' ) ); ?>
			</td>
		</tr>
		<tr id="ipt_fsqm_custom_date_start">
			<th scope="row">
				<label for="custom_date_start"><?php _e( 'Start Date:', 'ipt_fsqm_exp' ) ?></label>
			</th>
			<td>
				<?php $this->ui->datetimepicker( 'custom_date_start', $least_date ); ?>
			</td>
			<td>
				<?php $this->ui->help_head(); ?>
					<?php _e( 'Please select the start date and time, inclusive', 'ipt_fsqm_exp' ); ?>
				<?php $this->ui->help_tail(); ?>
			</td>
		</tr>
		<tr id="ipt_fsqm_custom_date_end">
			<th scope="row">
				<label for="custom_date_end"><?php _e( 'End Date:', 'ipt_fsqm_exp' ) ?></label>
			</th>
			<td>
				<?php $this->ui->datetimepicker( 'custom_date_end', $today ); ?>
			</td>
			<td>
				<?php $this->ui->help_head(); ?>
					<?php _e( 'Please select the end date and time, inclusive', 'ipt_fsqm_exp' ); ?>
				<?php $this->ui->help_tail(); ?>
			</td>
		</tr>
	</tbody>
</table>
<?php endif; ?>
		<?php
		$this->ui->iconbox_tail();
		if ( !empty( $forms ) ) {
			$this->ui->button( __( 'Select Questions', 'ipt_fsqm_exp' ), 'select_questions', 'large', 'primary', 'normal', array(), 'submit' );
		}
	}

	public function wizard_select_questions( $settings ) {
		$settings = wp_parse_args( $settings, array(
			'form_id' => '0',
			'custom_csv' => false,
			'custom_csv_delimiter' => ',',
			'custom_csv_enclosure' => '"',
			'custom_csv_option_delimiter' => '::',
			'custom_csv_row_delimiter' => '~',
			'custom_date' => false,
			'custom_date_start' => '00-00-0000 00:00:00',
			'custom_date_end' => '00-00-0000 00:00:00',
			'load' => '1',
		) );
		$form_id = (int) $settings['form_id'];
		$this->init( null, $form_id );

		if ( null == $this->form_id ) {
			$this->ui->msg_error( __( 'Invalid form ID provided.', 'ipt_fsqm_exp' ) );
			return;
		}
		$this->ui->hiddens( $settings );
		$this->select_questions_by_mtype( 'mcq' );
		$this->select_questions_by_mtype( 'freetype' );
		$this->select_questions_by_mtype( 'pinfo' );

		$this->ui->button( __( 'Generate CSV', 'ipt_fsqm_exp' ), 'generate_csv', 'large', 'primary', 'normal', array(), 'submit' );
	}

	public function wizard_generate_csv( $settings, $mcq, $freetype, $pinfo, $ajax_action = 'ipt_fsqm_exp_raw_csv' ) {
		$this->enqueue();

		$settings = wp_parse_args( $settings, array(
			'form_id' => '0',
			'custom_csv' => false,
			'custom_csv_delimiter' => ',',
			'custom_csv_enclosure' => '"',
			'custom_csv_option_delimiter' => '::',
			'custom_csv_row_delimiter' => '~',
			'custom_date' => false,
			'custom_date_start' => '00-00-0000 00:00:00',
			'custom_date_end' => '00-00-0000 00:00:00',
			'load' => '1',
		) );

		extract( $settings );

		$this->init( null, $form_id );

		if ( null == $this->form_id ) {
			$this->ui->msg_error( __( 'Invalid form ID provided.', 'ipt_fsqm_exp' ) );
			return;
		}

		$total_data = $this->get_total_submissions();
		if ( $total_data == null || $total_data < 1 ) {
			$this->ui->msg_error( __( 'Not enough data to populate the CSV. Please be patient.', 'ipt_fsqm_exp' ) );
			return;
		}

		if ( empty( $mcq ) && empty( $freetype ) && empty( $pinfo ) ) {
			$this->ui->msg_error( __( 'No questions selected for export.', 'ipt_fsqm_exp' ) );
			return;
		}

		$buttons = array();
		$buttons[] = array(
			__( 'Download CSV', 'ipt_fsqm_exp' ),
			'ipt_fsqm_export_download_' . $this->form_id,
			'large',
			'secondary',
			'normal',
			array(),
			'anchor',
			array(),
			array(),
			'#',
			'file-excel',
		);
		?>
<div class="ipt_fsqm_exp_export_raw" id="ipt_fsqm_exp_raw_<?php echo $this->form_id; ?>">
<?php $this->ui->progressbar( '', '0', 'ipt_fsqm_exp_raw_progressbar' ); ?>
<?php $this->ui->clear(); ?>
<?php $this->ui->ajax_loader( false, '', array( 'done' => __( 'Successfully created the CSV file', 'ipt_fsqm_exp' ) ), true, __( 'Generating CSV', 'ipt_fsqm_exp' ), array( 'ipt_fsqm_exp_raw_ajax' ) ); ?>
<?php $this->ui->buttons( $buttons, 'ipt_fsqm_exp_raw_button_container_' . $this->form_id, array( 'center' ) ); ?>
<script type="text/javascript">
	window.addEventListener('load', function() {
		jQuery(document).ready(function($) {
			var mcq = <?php echo json_encode( $mcq ); ?>,
			freetype = <?php echo json_encode( $freetype ); ?>,
			pinfo = <?php echo json_encode( $pinfo ); ?>,
			settings = <?php echo json_encode( (object) $settings ); ?>,
			wpnonce = '<?php echo wp_create_nonce( 'ipt_fsqm_exp_raw_ajax_' . $this->form_id ); ?>',
			ajaxurl = '<?php echo admin_url( 'admin-ajax.php' ); ?>',
			action = '<?php echo $ajax_action; ?>';
			$('#ipt_fsqm_exp_raw_<?php echo $this->form_id; ?>').iptFSQMEXPRaw({
				mcq : mcq,
				freetype : freetype,
				pinfo : pinfo,
				settings : settings,
				wpnonce : wpnonce,
				ajaxurl : ajaxurl,
				action : action
			});
		});
	});
</script>
</div>
		<?php
	}

	protected function select_questions_by_mtype( $type = 'mcq' ) {
		$keys = $this->get_keys_from_layouts_by_m_type( $type, $this->layout );
		$items = array();
		if ( !empty( $keys ) ) {
			foreach ( $keys as $key ) {
				$label = isset( $this->{$type}[$key] ) ? $this->{$type}[$key]['title'] : null;

				if ( $label === null ) {
					continue;
				}

				$items[] = array(
					'label' => $label,
					'value' => $key,
				);
			}
		}

		$type_to_name = array(
			'mcq' => __( 'Multiple Choice Type Questions', 'ipt_fsqm_exp' ),
			'freetype' => __( 'Feedback Type Questions', 'ipt_fsqm_exp' ),
			'pinfo' => __( 'Other Elements', 'ipt_fsqm_exp' ),
		);

		ob_start();
		$this->ui->checkbox_toggler( 'ipt_fsqm_' . $type . '_toggler', __( 'Toggle All', 'ipt_fsqm_exp' ), '#ipt_fsqm_' . $type . '_select_questions input.ipt_uif_checkbox' );
		$toggler = ob_get_clean();
?>
<?php if ( $this->form_id == null ) : ?>
<?php $this->ui->msg_error( __( 'Invalid Form ID Supplied. Please press the back button and check again.', 'ipt_fsqm_exp' ) ); ?>
<?php elseif ( empty( $items ) ) : ?>
<?php $this->ui->msg_error( sprintf( __( 'No %s found on the form you have selected.', 'ipt_fsqm_exp' ), $type_to_name[$type] ) ); ?>
<?php else : ?>
<?php $this->ui->iconbox_head( $type_to_name[$type], 'checkbox-checked', $toggler ); ?>
<table class="form-table">
	<tbody>
		<tr>
			<td id="ipt_fsqm_<?php echo $type; ?>_select_questions">
				<?php $this->ui->checkboxes( $type . '[]', $items, false, false, false, '<div class="clear"></div>' ); ?>
			</td>
		</tr>
	</tbody>
</table>
<?php $this->ui->iconbox_tail(); ?>
<?php endif; ?>
		<?php
	}

	/*==========================================================================
	 * Make the CSV
	 *========================================================================*/

	public function make_csv($doing = 0, $load = '1') {
		global $wpdb, $ipt_fsqm_exp_info, $ipt_fsqm_info;
		if ( null == $this->form_id ) {
			return false;
		}

		// Set the insert heading parameter
		$insert_heading = false;
		if ( ! file_exists( $this->uniqpath ) ) {
			$insert_heading = true;
		}

		// Init the File
		$csv_file = fopen( $this->uniqpath, 'a' );

		// Put the Heading if necessary
		if ( $insert_heading ) {
			fputcsv( $csv_file, $this->head_row(), $this->get_delimiter(), $this->get_enclosure() );
		}

		// Now get the working data ids
		// First the perpage
		$per_page = 15;
		switch ( $load ) {
		case '1' :
			$per_page = 30;
			break;
		case '2' :
			$per_page = 50;
			break;
		}

		// Where
		$where = '';
		$where_arr = array();
		if ( $this->raw_data->start_date != '0000-00-00 00:00:00' ) {
			$where_arr[] = $wpdb->prepare( 'date >= %s', date( 'Y-m-d H:i:s', strtotime( $this->raw_data->start_date ) ) );
		}

		if ( $this->raw_data->end_date != '0000-00-00 00:00:00' ) {
			$where_arr[] = $wpdb->prepare( 'date <= %s', date( 'Y-m-d H:i:s', strtotime( $this->raw_data->end_date ) ) );
		}

		if ( !empty( $where_arr ) ) {
			$where .= ' AND ' . implode( ' AND ', $where_arr );
		}

		// Return
		$return = 0;

		$data_ids = $wpdb->get_col( $wpdb->prepare( "SELECT id FROM {$ipt_fsqm_info['data_table']} WHERE form_id = %d{$where} ORDER BY id ASC LIMIT %d,%d", $this->form_id, $doing * $per_page, $per_page ) );
		$total = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(id) FROM {$ipt_fsqm_info['data_table']} WHERE form_id = %d{$where}", $this->form_id ) );

		// Now loop through and populate the CSV
		if ( empty( $data_ids ) ) {
			$this->raw_mark_complete();
			$return = 100;
		} else {
			// Lets do the magic
			foreach ( $data_ids as $id ) {
				$this->init( $id );
				fputcsv( $csv_file, $this->data_row(), $this->get_delimiter(), $this->get_enclosure() );
			}

			// Calculate the done
			$done_till_now = $doing * $per_page + $per_page;
			if ( $done_till_now >= $total ) {
				$this->raw_mark_complete();
				$return = 100;
			} else {
				$return = (float) $done_till_now * 100 / $total;
			}
		}

		// Close the file handle
		fclose( $csv_file );

		// Return the done
		return $return;
	}

	/*==========================================================================
	 * File Managements
	 *========================================================================*/

	public function stream_csv() {
		if ( null == $this->form_id ) {
			wp_die( __( 'Export and/or Form Deleted.', 'ipt_fsqm_exp' ), __( 'Error - Exporter for FSQM Pro', 'ipt_fsqm_exp' ), array( 'back_link' => true ) );
		}

		if ( ! file_exists( $this->uniqpath ) ) {
			wp_die( __( 'CSV File deleted.', 'ipt_fsqm_exp' ), __( 'Error - Exporter for FSQM Pro', 'ipt_fsqm_exp' ), array( 'back_link' => true ) );
		}

		header( 'Content-Description: File Transfer' );
		header( 'Content-Type: application/octet-stream' );
		header( 'Content-Disposition: attachment; filename='.basename( $this->uniqpath ) );
		header( 'Content-Transfer-Encoding: binary' );
		header( 'Expires: 0' );
		header( 'Cache-Control: must-revalidate' );
		header( 'Pragma: public' );
		header( 'Content-Length: ' . filesize( $this->uniqpath ) );
		@ob_clean();
		readfile( $this->uniqpath );
		exit;
	}

	protected function set_paths() {
		// Create default directories and files if not present
		if ( $this->form_id == null ) {
			return false;
		}
		$wp_upload_dir = wp_upload_dir();
		$this->wpath = $wp_upload_dir['basedir'] . '/fsqm-exp-raw';

		if ( ! wp_mkdir_p( $this->wpath ) ) {
			$this->errors[] = sprintf( __( 'Permission Error: For creating directory under %s', 'ipt_fsqm_exp' ), $wp_upload_dir['basedir'] );
			return false;
		}

		if ( ! file_exists( $this->wpath . '/.htaccess' ) ) {
			file_put_contents( $this->wpath . '/.htaccess', "deny from all" );
		}

		// Set the unique path name for this raw_id
		$this->uniqpath = $this->wpath . '/raw-' . $this->form_id . '-' . $this->raw_id . '.csv';
	}


	/*==========================================================================
	 * Database Abstractions
	 *========================================================================*/

	protected function create_raw_database_entry( $start_date = '', $end_date = '', $mcq = array(), $freetype = array(), $pinfo = array() ) {
		if ( $start_date == '' ) {
			$start_date = '00-00-0000 00:00:00';
		}

		if ( $end_date == '' ) {
			$end_date = '00-00-0000 00:00:00';
		}
		global $wpdb, $ipt_fsqm_exp_info;

		$wpdb->insert($ipt_fsqm_exp_info['raw_table'], array(
			'form_id' => $this->form_id,
			'created' => current_time( 'mysql' ),
			'complete' => '0',
			'start_date' => $start_date,
			'end_date' => $end_date,
			'mcq' => maybe_serialize( $mcq ),
			'freetype' => maybe_serialize( $freetype ),
			'pinfo' => maybe_serialize( $pinfo ),
		));
		$this->raw_id = $wpdb->insert_id;
	}

	protected function validate_raw_database( $raw_id = null, $start_date = '', $end_date = '', $mcq = array(), $freetype = array(), $pinfo = array() ) {
		if ( null == $this->form_id ) {
			$this->errors[] = __( 'Invalid Form ID provided.', 'ipt_fsqm_exp' );
			return;
		}

		if ( $raw_id == null ) {
			$this->create_raw_database_entry( $start_date, $end_date, $mcq, $freetype, $pinfo );
		} else {
			// Check for integrity
			global $wpdb, $ipt_fsqm_exp_info;

			if ( $this->form_id != $wpdb->get_var( $wpdb->prepare( "SELECT form_id FROM {$ipt_fsqm_exp_info['raw_table']} WHERE id = %d", $raw_id ) ) ) {
				$this->errors[] = __( 'Invalid RAW ID provided.', 'ipt_fsqm_exp' );
				$this->raw_id = null;
				$this->form_id = null;
			} else {
				$this->raw_id = $raw_id;
			}
		}
	}

	protected function populate_raw_variables() {
		global $wpdb, $ipt_fsqm_exp_info;
		if ( null == $this->raw_id || null == $this->form_id ) {
			return;
		}
		$this->raw_data = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$ipt_fsqm_exp_info['raw_table']} WHERE id = %d", $this->raw_id ) );
		$this->raw_data->mcq = maybe_unserialize( $this->raw_data->mcq );
		$this->raw_data->freetype = maybe_unserialize( $this->raw_data->freetype );
		$this->raw_data->pinfo = maybe_unserialize( $this->raw_data->pinfo );
	}

	protected function raw_mark_complete() {
		global $wpdb, $ipt_fsqm_exp_info;
		$wpdb->update( $ipt_fsqm_exp_info['raw_table'], array(
			'complete' => 1,
		), array(
			'id' => $this->raw_id,
		), '%d', '%d' );
	}


	/*==========================================================================
	 * CSV Array functions
	 *========================================================================*/

	public function head_row() {
		if ( null == $this->form_id ) {
			return;
		}

		$headings = array();

		// Info
		$headings[] = __( 'Submission ID', 'ipt_fsqm_exp' );
		$headings[] = __( 'User ID', 'ipt_fsqm_exp' );
		$headings[] = __( 'Submission Date and Time', 'ipt_fsqm_exp' );
		$headings[] = __( 'First Name', 'ipt_fsqm_exp' );
		$headings[] = __( 'Last Name', 'ipt_fsqm_exp' );
		$headings[] = __( 'Email', 'ipt_fsqm_exp' );

		// Loop through the MCQ
		foreach ( $this->mcq as $m_key => $mcq ) {
			if ( in_array( (string) $m_key, $this->raw_data->mcq ) ) {
				$headings[] = $mcq['title'];
			}
		}

		// Loop through the Feedback
		foreach ( $this->freetype as $f_key => $freetype ) {
			if ( in_array( (string) $f_key, $this->raw_data->freetype ) ) {
				$headings[] = $freetype['title'];
			}
		}

		// Loop through the Pinfo
		foreach ( $this->pinfo as $p_key => $pinfo ) {
			if ( in_array( (string) $p_key, $this->raw_data->pinfo ) && ! in_array( $pinfo['type'], $this->db_maps ) ) {
				$headings[] = $pinfo['title'];
			}
		}

		// Others
		$headings[] = __( 'IP Address', 'ipt_fsqm_exp' );
		if ( $this->settings['general']['comment_title'] != '' ) {
			$headings[] = $this->settings['general']['comment_title'];
		}

		// Score
		$headings[] = __( 'Score', 'ipt_fsqm_exp' );
		$headings[] = __( 'Max Score', 'ipt_fsqm_exp' );

		// Link
		$headings[] = __( 'Link', 'ipt_fsqm_exp' );

		return $headings;
	}

	public function data_row() {
		if ( null == $this->form_id ) {
			return;
		}

		$data = array();

		// Info
		$data[] = $this->data_id;
		$data[] = $this->data->user_id;
		$data[] = $this->data->date;
		$data[] = $this->data->f_name;
		$data[] = $this->data->l_name;
		$data[] = $this->data->email;

		// Loop through mcq
		foreach ( $this->mcq as $m_key => $mcq ) {
			if ( in_array( (string) $m_key, $this->raw_data->mcq ) ) {
				if ( ! isset( $this->data->mcq[$m_key] ) ) {
					$data[] = '';
				} else {
					$data[] = $this->element_csv_array( $mcq, $m_key, $this->data->mcq[$m_key] );
				}
			}
		}

		// Loop through freetype
		foreach ( $this->freetype as $f_key => $freetype ) {
			if ( in_array( (string) $f_key, $this->raw_data->freetype ) ) {
				if ( ! isset( $this->data->freetype[$f_key] ) ) {
					$data[] = '';
				} else {
					$data[] = $this->element_csv_array( $freetype, $f_key, $this->data->freetype[$f_key] );
				}
			}
		}

		// Loop through pinfo
		foreach ( $this->pinfo as $p_key => $pinfo ) {
			if ( in_array( (string) $p_key, $this->raw_data->pinfo ) && ! in_array( $pinfo['type'], $this->db_maps ) ) {
				if ( ! isset( $this->data->pinfo[$p_key] ) ) {
					$data[] = '';
				} else {
					$data[] = $this->element_csv_array( $pinfo, $p_key, $this->data->pinfo[$p_key] );
				}
			}
		}

		// Others
		$data[] = $this->data->ip;
		if ( $this->settings['general']['comment_title'] != '' ) {
			$data[] = $this->data->comment;
		}

		// Score
		$data[] = $this->data->score;
		$data[] = $this->data->max_score;

		// Link
		$data[] = admin_url( 'admin.php?page=ipt_fsqm_view_submission&id=' . $this->data_id );

		return $data;
	}

	public function element_csv_array( $element, $e_key, $submission ) {
		// Check for integrity
		if ( ! is_array( $element ) || ! isset( $element['type'] ) || $element['type'] == '' ) {
			return __( 'Invalid Element. Type not set.', 'ipt_fsqm_exp' );
		}

		// Check for built ins first
		if ( method_exists( $this, 'csv_' . $element['type'] ) ) {
			return call_user_func( array( $this, 'csv_' . $element['type'] ), $element, $e_key, $submission );
		}

		// Not found, so check if external
		$definition = $this->get_element_definition( $element );
		if ( isset( $definition['callback_csv'] ) && is_callable( $definition['callback_csv'] ) ) {
			return call_user_func_array( $definition['callback_csv'], $element, $e_key, $submission, $this );
		} else {
			return sprintf( __( 'Invalid Callback for CSV Export: %s', 'ipt_fsqm_exp' ), $element['type'] );
		}
	}

	/*==========================================================================
	 * CSV Calculator for Elements
	 *========================================================================*/
	// MCQs
	public function csv_checkbox( $element, $e_key, $submission ) {
		return $this->csv_make_mcqs( $element, $e_key, $submission );
	}

	public function csv_radio( $element, $e_key, $submission ) {
		return $this->csv_make_mcqs( $element, $e_key, $submission );
	}

	public function csv_select( $element, $e_key, $submission ) {
		return $this->csv_make_mcqs( $element, $e_key, $submission );
	}

	public function csv_slider( $element, $e_key, $submission ) {
		return $submission['value'];
	}

	public function csv_range( $element, $e_key, $submission ) {
		return $submission['values']['min'] . $this->get_range_delimiter() . $submission['values']['max'];
	}

	public function csv_grading( $element, $e_key, $submission ) {
		$return = array();

		foreach ( $element['settings']['options'] as $o_key => $option ) {
			if ( ! isset ( $submission['options'][$o_key] ) ) {
				continue;
			}

			// Can be range or single
			if ( true == $element['settings']['range'] ) {
				$return[] = $option . $this->get_option_delimiter() . $submission['options'][$o_key]['min'] . $this->get_range_delimiter() . $submission['options'][$o_key]['max'];
			} else {
				$return[] = $option . $this->get_option_delimiter() . $submission['options'][$o_key];
			}
		}

		return implode( $this->get_row_delimiter(), $return );
	}

	public function csv_starrating( $element, $e_key, $submission ) {
		return $this->csv_make_numerics( $element, $e_key, $submission );
	}

	public function csv_scalerating( $element, $e_key, $submission ) {
		return $this->csv_make_numerics( $element, $e_key, $submission );
	}

	public function csv_spinners( $element, $e_key, $submission ) {
		return $this->csv_make_numerics( $element, $e_key, $submission );
	}

	public function csv_matrix( $element, $e_key, $submission ) {
		$return = array();

		foreach ( $element['settings']['rows'] as $r_key => $row ) {
			if ( ! isset( $submission['rows'][$r_key] ) ) {
				continue;
			}

			$sub = array();
			$sub[] = $row;

			foreach ( $element['settings']['columns'] as $c_key => $col ) {
				if ( in_array( (string) $c_key, $submission['rows'][$r_key] ) ) {
					$sub[] = $col;
				}
			}

			$return[] = implode( $this->get_option_delimiter(), $sub );
		}

		return implode( $this->get_row_delimiter(), $return );
	}

	public function csv_toggle( $element, $e_key, $submission ) {
		if ( $submission['value'] == false ) {
			return $element['settings']['off'];
		}
		return $element['settings']['on'];
	}

	public function csv_sorting( $element, $e_key, $submission ) {
		return $this->csv_make_sorting( $element, $e_key, $submission );
	}

	// Feedbacks
	public function csv_feedback_large( $element, $e_key, $submission ) {
		return $this->csv_make_text( $element, $e_key, $submission );
	}

	public function csv_feedback_small( $element, $e_key, $submission ) {
		return $this->csv_make_text( $element, $e_key, $submission );
	}

	public function csv_upload( $element, $e_key, $submission ) {
		$uploader = new IPT_FSQM_Form_Elements_Uploader( $this->form_id, $e_key );
		$uploads = $uploader->get_uploads( $this->data_id );

		if ( ! empty( $uploads ) ) {
			$return = array();
			foreach ( $uploads as $upload ) {
				if ( $upload['guid'] == '' ) {
					continue;
				}
				$return[] = $upload['name'] . $this->get_option_delimiter() . '(' . $upload['guid'] . ')';
			}
			return implode( $this->get_row_delimiter(), $return );
		} else {
			return __( 'No files uploaded.', 'ipt_fsqm_exp' );
		}
	}

	// Pinfos
	public function csv_f_name( $element, $e_key, $submission ) {
		return $this->csv_make_text( $element, $e_key, $submission );
	}

	public function csv_l_name( $element, $e_key, $submission ) {
		return $this->csv_make_text( $element, $e_key, $submission );
	}

	public function csv_email( $element, $e_key, $submission ) {
		return $this->csv_make_text( $element, $e_key, $submission );
	}

	public function csv_phone( $element, $e_key, $submission ) {
		return $this->csv_make_text( $element, $e_key, $submission );
	}

	public function csv_p_name( $element, $e_key, $submission ) {
		return $this->csv_make_text( $element, $e_key, $submission );
	}

	public function csv_p_email( $element, $e_key, $submission ) {
		return $this->csv_make_text( $element, $e_key, $submission );
	}

	public function csv_p_phone( $element, $e_key, $submission ) {
		return $this->csv_make_text( $element, $e_key, $submission );
	}

	public function csv_textinput( $element, $e_key, $submission ) {
		return $this->csv_make_text( $element, $e_key, $submission );
	}

	public function csv_textarea( $element, $e_key, $submission ) {
		return $this->csv_make_text( $element, $e_key, $submission );
	}

	public function csv_password( $element, $e_key, $submission ) {
		return $this->csv_make_text( $element, $e_key, $submission );
	}

	public function csv_keypad( $element, $e_key, $submission ) {
		return $this->csv_make_text( $element, $e_key, $submission );
	}

	public function csv_datetime( $element, $e_key, $submission ) {
		return $this->csv_make_text( $element, $e_key, $submission );
	}

	public function csv_p_checkbox( $element, $e_key, $submission ) {
		return $this->csv_make_mcqs( $element, $e_key, $submission );
	}

	public function csv_p_radio( $element, $e_key, $submission ) {
		return $this->csv_make_mcqs( $element, $e_key, $submission );
	}

	public function csv_p_select( $element, $e_key, $submission ) {
		return $this->csv_make_mcqs( $element, $e_key, $submission );
	}

	public function csv_s_checkbox( $element, $e_key, $submission ) {
		if ( $submission['value'] == false ) {
			return 0;
		}
		return 1;
	}

	public function csv_address( $element, $e_key, $submission ) {
		return implode( $this->get_row_delimiter(), $submission['values'] );
	}

	public function csv_p_sorting( $element, $e_key, $submission ) {
		return $this->csv_make_sorting( $element, $e_key, $submission );
	}

	/*==========================================================================
	 * Helpers for CSV Calculators
	 *========================================================================*/
	public function csv_make_mcqs( $element, $e_key, $submission ) {
		$return = array();
		foreach ( $element['settings']['options'] as $o_key => $op ) {
			if ( in_array( (string) $o_key, $submission['options'] ) ) {
				$return[] = $op['label'];
			}
		}
		if ( $element['settings']['others'] == true ) {
			if ( in_array( 'others', $submission['options'] ) ) {
				$return[] = $element['settings']['o_label'];
			}
		}

		$return = implode( $this->get_option_delimiter(), $return );
		if ( $element['settings']['others'] == true && in_array( 'others', $submission['options'] ) ) {
			$return .= $this->get_row_delimiter() . $submission['others'];
		}

		return $return;
	}

	public function csv_make_text( $element, $e_key, $submission ) {
		$return = str_replace( "\r", "", $submission['value'] );
		return str_replace( "\n\n" , "\n", $return );
	}

	public function csv_make_numerics( $element, $e_key, $submission ) {
		$return = array();

		foreach ( $element['settings']['options'] as $o_key => $option ) {
			if ( ! isset( $submission['options'][$o_key] ) ) {
				continue;
			}
			$return[] = $option . $this->get_option_delimiter() . $submission['options'][$o_key];
		}

		return implode( $this->get_row_delimiter(), $return );
	}

	public function csv_make_sorting( $element, $e_key, $submission ) {
		$return = array();
		foreach ( (array) $submission['order'] as $o_key ) {
			$return[] = $element['settings']['options'][$o_key]['label'];
		}

		return implode( $this->get_row_delimiter(), $return );
	}
}
