<?php
/**
 * WP Feedback, Surver & Quiz Manager - Pro Form Elements Class
 * Utilities
 *
 * @package WP Feedback, Surver & Quiz Manager - Pro
 * @subpackage Form Elements
 * @author Swashata Ghosh <swashata@intechgrity.com>
 */
class IPT_FSQM_Form_Elements_Utilities extends IPT_FSQM_Form_Elements_Base {
	/*==========================================================================
	 * Internal Variables
	 *========================================================================*/
	/**
	 * UI Variable
	 *
	 * @var IPT_Plugin_UIF_Admin
	 */
	public $ui;

	/*==========================================================================
	 * Constructor
	 *========================================================================*/
	public function __construct( $form_id = null, $ui = null ) {
		if ( $ui == null ) {
			$this->ui = IPT_Plugin_UIF_Admin::instance( 'ipt_fsqm' );
		} else {
			$this->ui = $ui;
		}

		parent::__construct( $form_id );
	}

	public function enqueue() {
		$path =  plugins_url( '/lib/images/icomoon/333/PNG/', IPT_FSQM_Loader::$abs_file );
		wp_enqueue_script( 'ipt-fsqm-report', plugins_url( '/static/common/js/jquery.ipt-fsqm-report.js', IPT_FSQM_Loader::$abs_file ), array( 'jquery' ), IPT_FSQM_Loader::$version );
		wp_localize_script( 'ipt-fsqm-report', 'iptFSQMReport', apply_filters( 'ipt_fsqm_report_js', array(
			'range_text'       => __( ' to ', 'ipt_fsqm' ),
			'option'           => __( 'Option', 'ipt_fsqm' ),
			'avg_slider'       => __( 'Avg', 'ipt_fsqm' ),
			'avg_range'        => __( 'Avg', 'ipt_fsqm' ),
			'avg'              => __( 'based on', 'ipt_fsqm' ),
			'avg_count'        => __( 'submission(s)', 'ipt_fsqm' ),
			'rating_img_full'  => '<img height="16" width="16" alt="1" src="' . $path . 'star4.png" />',
			'rating_img_half'  => '<img height="16" width="16" alt="0.5" src="' . $path . 'star3.png" />',
			'rating_img_empty' => '<img height="16" width="16" alt="0" src="' . $path . 'star2.png" />',
			'sorting_img'      => '<img height="16" width="16" src="' . $path . 'point-right.png' . '" />',
			'rating'           => __( 'Rating', 'ipt_fsqm' ),
			'count'            => __( 'Count', 'ipt_fsqm' ),
			'grading'          => __( 'Grading', 'ipt_fsqm' ),
			'value'            => __( 'Value', 'ipt_fsqm' ),
			'noupload'         => __( 'No files uploaded.', 'ipt_fsqm' ),
			'g_data'           => array(
				'op_label'        => __( 'Option', 'ipt_fsqm' ),
				'ct_label'        => __( 'Count', 'ipt_fsqm' ),
				'sl_label'        => __( 'Value', 'ipt_fsqm' ),
				'rg_label'        => __( 'Range', 'ipt_fsqm' ),
				'sl_head_label_s' => __( 'entry', 'ipt_fsqm' ),
				'sl_head_label_p' => __( 'entries', 'ipt_fsqm' ),
				'avg'             => __( 'Average', 'ipt_fsqm' ),
				's_presets'       => __( 'Predefined/Correct Sorting', 'ipt_fsqm' ),
				's_others'        => __( 'Custom Sorting', 'ipt_fsqm' ),
				's_breakdown'     => __( 'Overall Sorting breakdown', 'ipt_fsqm' ),
				's_order'         => __( 'Sorting order', 'ipt_fsqm' ),
				's_order_custom'  => __( 'Customized order', 'ipt_fsqm' ),
			),
			'callbacks'        => array(),
			'gcallbacks'       => array(),
		) ) );
		do_action( 'ipt_fsqm_report_enqueue', $this );
	}


	/*==========================================================================
	 * Common Reports APIs
	 *========================================================================*/
	public function report_index() {
		echo '<form method="post" action="">';
		if ( isset( $this->post['generate_report'] ) ) {
			$form_id = (int) $this->post['form_id'];
			$hiddens = array(
				'form_id' => $form_id,
				'report' => $this->post['report'],
				'custom_date' => isset( $this->post['custom_date'] ) && $this->post['custom_date'] != '0' && $this->post['custom_date'] != null ? true : false,
				'custom_date_start' => $this->post['custom_date_start'],
				'custom_date_end' => $this->post['custom_date_end'],
				'load' => $this->post['load'],
			);
			$mcqs = isset( $this->post['mcqs'] ) ? $this->post['mcqs'] : array();
			$freetypes = isset( $this->post['freetypes'] ) ? $this->post['freetypes'] : array();
			$this->report_generate_report( $hiddens, $mcqs, '', true, $freetypes );
		} elseif ( isset( $this->post['select_questions'] ) ) {
			$this->report_select_questions();
		} else {
			$this->report_show_forms();
		}
		echo '</form>';
	}

	public function report_generate_report( $settings, $mcqs, $visualization = '', $do_data = false, $freetypes = array(), $ajax_action = 'ipt_fsqm_report' ) {
		$this->enqueue();

		extract( $settings = wp_parse_args( $settings, array(
					'form_id' => 0,
					'report' => 'survey_feedback',
					'custom_date' => false,
					'custom_date_start' => '',
					'custom_date_end' => '',
					'load' => '1',
				) ) );

		$this->init( $form_id );

		if ( null == $this->form_id ) {
			$this->ui->msg_error( __( 'Invalid form ID Provided.', 'ipt_fsqm' ) );
			return;
		}

		//Buttons
		$buttons = array();
		$buttons[] = array(
			__( 'Print', 'ipt_fsqm' ),
			'ipt_fsqm_report_print_' . $this->form_id,
			'large',
			'ui',
			'normal',
			array( 'ipt_fsqm_report_print' ),
			'button',
			array(),
			array(),
			'',
			'print'
		);
		$buttons = apply_filters( 'ipt_fsqm_filter_utilities_report_print', $buttons, $this );

		//data check
		$total_data = $this->get_total_submissions();
		if ( null == $total_data || $total_data < 1 ) {
			$this->ui->msg_error( __( 'Not enough data to populate report. Please be patient.', 'ipt_fsqm' ), true, __( 'No data', 'ipt_fsqm' ) );
			return;
		}
		$survey = array();
		$feedback = array();
?>
<div class="ipt_fsqm_report" id="ipt_fsqm_<?php echo $this->form_id; ?>_report">
<?php $this->ui->progressbar( '', '0', 'ipt_fsqm_report_progressbar' ); ?>
<?php $this->ui->clear(); ?>
<?php $this->ui->ajax_loader( false, '', array(), true, null, array( 'ipt_fsqm_report_ajax' ) ); ?>
	<?php
		switch ( $report ) {
		case 'survey' :
			$survey = $this->survey_generate_report( $mcqs, $do_data, $visualization );
			break;
		case 'feedback' :
			$feedback = $this->feedback_generate_report( $freetypes, $do_data );
			break;
		case 'survey_feedback' :
			$survey = $this->survey_generate_report( $mcqs, $do_data, $visualization );
			$feedback = $this->feedback_generate_report( $freetypes, $do_data );
			break;
		default :
			$this->ui->msg_error( __( 'Invalid report type selected.', 'ipt_fsqm' ) );
			return;
		}
		if ( !empty( $survey ) ) {
			$survey['data'] = (object) $survey['data'];
			$survey['elements'] = (object) $survey['elements'];
		}
		if ( !empty( $feedback ) ) {
			$feedback['data'] = (object) $feedback['data'];
			$feedback['elements'] = (object) $feedback['elements'];
		}
?>
</div>
<?php $this->ui->buttons( $buttons, 'ipt_fsqm_report_button_container_' . $this->form_id, array( 'center' ) ); ?>
<script type="text/javascript">
	window.addEventListener('load', function() {
		jQuery(document).ready(function($) {
			var survey = <?php echo json_encode( (object) $survey ); ?>;
			var feedback = <?php echo json_encode( (object) $feedback ); ?>;
			var settings = <?php echo json_encode( (object) $settings ); ?>;
			var wpnonce = '<?php echo wp_create_nonce( 'ipt_fsqm_report_ajax_' . $this->form_id ); ?>';
			var ajaxurl = '<?php echo admin_url( 'admin-ajax.php' ); ?>';
			var do_data = <?php echo $do_data ? 'true' : 'false'; ?>;
			var do_data_nonce = '<?php echo $do_data ? wp_create_nonce( 'ipt_fsqm_report_ajax_do_data_' . $this->form_id ) : ''; ?>';
			var action = '<?php echo $ajax_action; ?>';
			$('#ipt_fsqm_<?php echo $this->form_id; ?>_report').iptFSQMReport({
				settings : settings,
				survey : survey,
				feedback : feedback,
				wpnonce : wpnonce,
				ajaxurl : ajaxurl,
				form_id : <?php echo $this->form_id; ?>,
				do_data : do_data,
				do_data_nonce : do_data_nonce,
				action : action
			});
		});
	}, false);
</script>
		<?php
	}

	public function report_select_questions() {
		$form_id = (int) $this->post['form_id'];
		$hiddens = array(
			'form_id' => $form_id,
			'report' => $this->post['report'],
			'custom_date' => isset( $this->post['custom_date'] ) && $this->post['custom_date'] != '0' && $this->post['custom_date'] != '0' ? true : false,
			'custom_date_start' => $this->post['custom_date_start'],
			'custom_date_end' => $this->post['custom_date_end'],
			'load' => $this->post['load'],
		);

		$this->init( $form_id );
		$this->ui->hiddens( $hiddens );

		if ( null == $this->form_id ) {
			$this->ui->msg_error( __( 'Invalid form ID Provided.', 'ipt_fsqm' ) );
			return;
		} else {
			switch ( $hiddens['report'] ) {
			case 'survey' :
				$this->survey_select_questions();
				break;
			case 'feedback' :
				$this->feedback_select_questions();
				break;
			case 'survey_feedback' :
				$this->survey_select_questions();
				$this->feedback_select_questions();
				break;
			default :
				$this->ui->msg_error( __( 'Invalid report type selected.', 'ipt_fsqm' ) );
				return;
			}

			$this->ui->button( __( 'Generate Report', 'ipt_fsqm' ), 'generate_report', 'large', 'primary', 'normal', array(), 'submit' );
		}
	}

	/**
	 * Show the First Form
	 *
	 * @global wpdb $wpdb
	 * @global array $ipt_fsqm_info
	 */
	public function report_show_forms() {
		global $wpdb, $ipt_fsqm_info;

		$forms = $wpdb->get_results( "SELECT f.id id, f.name name, COUNT(d.id) subs FROM {$ipt_fsqm_info['form_table']} f LEFT JOIN {$ipt_fsqm_info['data_table']} d ON f.id = d.form_id GROUP BY f.id HAVING COUNT(d.id) > 0" );
		$today = current_time( 'mysql' );
		$least_date = $wpdb->get_var( "SELECT date FROM {$ipt_fsqm_info['data_table']} WHERE date != '0000-00-00 00:00:00' ORDER BY date ASC LIMIT 0,1" );
		$select_items = array();

		if ( !empty( $forms ) ) {
			foreach ( $forms as $form ) {
				$select_items[] = array(
					'label' => sprintf( __( '%1$s (Submissions %2$d)', 'ipt_fsqm' ), $form->name, $form->subs ),
					'value' => $form->id,
				);
			}
		}
		$this->ui->iconbox_head( __( 'Select the Form and Date Range', 'ipt_fsqm' ), 'settings' );

		$server_loads = array(
			array(
				'label' => __( 'Light Load', 'ipt_fsqm' ),
				'value' => '0',
			),
			array(
				'label' => __( 'Moderate Load (Recommended for Shared Hostings)', 'ipt_fsqm' ),
				'value' => '1',
			),
			array(
				'label' => __( 'Heavy Load (Recommended for VPS or Dedicated Hostings)', 'ipt_fsqm' ),
				'value' => '2',
			),
		);

		$report_type = array(
			array(
				'label' => __( 'Survey Only', 'ipt_fsqm' ),
				'value' => 'survey',
			),
			array(
				'label' => __( 'Feedback Only', 'ipt_fsqm' ),
				'value' => 'feedback',
			),
			array(
				'label' => __( 'Survey & Feedback', 'ipt_fsqm' ),
				'value' => 'survey_feedback',
			),
		);
?>
<?php if ( empty( $forms ) ) : ?>
<?php $this->ui->msg_error( __( 'Not enough data for any of the forms to populate report.', 'ipt_fsqm' ) ); ?>
<?php else : ?>
<table class="form-table">
	<tbody>
		<tr>
			<th><?php $this->ui->generate_label( 'form_id', __( 'Select Form', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->select( 'form_id', $select_items, false ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'Please select the form whose report you want to generate.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr>
			<th scope="col">
				<?php $this->ui->generate_label( 'report', __( 'Report Type', 'ipt_fsqm' ) ); ?>
			</th>
			<td>
				<?php $this->ui->select( 'report', $report_type, 'survey_feedback' ); ?>
			</td>
			<td>
				<?php $this->ui->help_head(); ?>
				<p><?php _e( 'Please select the type of the report.', 'ipt_fsqm' ) ?></p>
				<ul class="ul-disc">
					<li><strong><?php _e( 'Survey Only', 'ipt_fsqm' ); ?>:</strong> <?php _e( 'Shows only survey or multiple choice type questions.', 'ipt_fsqm' ); ?></li>
					<li><strong><?php _e( 'Feedback Only', 'ipt_fsqm' ); ?>:</strong> <?php _e( 'Shows only feedback type questions.', 'ipt_fsqm' ); ?></li>
					<li><strong><?php _e( 'Survey & Feedback', 'ipt_fsqm' ); ?>:</strong> <?php _e( 'Shows both.', 'ipt_fsqm' ); ?></li>
				</ul>
				<?php $this->ui->help_tail(); ?>
			</td>
		</tr>
		<tr>
			<th scope="col">
				<?php $this->ui->generate_label( 'load', __( 'Server Load:', 'ipt_fsqm' ) ); ?>
			</th>
			<td>
				<?php $this->ui->select( 'load', $server_loads, '1' ); ?>
			</td>
			<td>
				<?php $this->ui->help_head(); ?>
					<?php _e( 'Please select the calculation load for the queries.', 'ipt_fsqm' ); ?>
					<ul class="ul-disc">
						<li><strong><?php _e( 'Light Load', 'ipt_fsqm' ); ?></strong> : <?php _e( '15 queries per hit. Use this if you are experiencing problems.', 'ipt_fsqm' ); ?></li>
						<li><strong><?php _e( 'Medium Load', 'ipt_fsqm' ); ?></strong> : <?php _e( '30 queries per hit. Recommended for most of the shared hosting environments.', 'ipt_fsqm' ); ?></li>
						<li><strong><?php _e( 'Heavy Load', 'ipt_fsqm' ); ?></strong> : <?php _e( '50 queries per hit. Use only if you own a VPS or Dedicated Hosting.', 'ipt_fsqm' ); ?></li>
					</ul>
					<?php _e( 'It is recommended to go with Medium Load for most of the shared servers.', 'ipt_fsqm' ); ?>
				<?php $this->ui->help_tail(); ?>
			</td>
		</tr>
		<tr>
			<th><?php $this->ui->generate_label( 'custom_date', __( 'Custom Date Range', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->toggle( 'custom_date', __( 'YES', 'ipt_fsqm' ), __( 'NO', 'ipt_fsqm' ), false, '1', false, true, array( 'condid' => 'ipt_fsqm_custom_date_start,ipt_fsqm_custom_date_end' ) ); ?>
			</td>
			<td>
				<?php $this->ui->help( __( 'Tick to enter custom date range for the report.', 'ipt_fsqm' ) ); ?>
			</td>
		</tr>
		<tr id="ipt_fsqm_custom_date_start">
			<th scope="col">
				<label for="custom_date_start"><?php _e( 'Start Date:', 'ipt_fsqm' ) ?></label>
			</th>
			<td>
				<?php $this->ui->datetimepicker( 'custom_date_start', $least_date ); ?>
			</td>
			<td>
				<?php $this->ui->help_head(); ?>
					<?php _e( 'Please select the start date and time, inclusive', 'ipt_fsqm' ); ?>
				<?php $this->ui->help_tail(); ?>
			</td>
		</tr>
		<tr id="ipt_fsqm_custom_date_end">
			<th scope="col">
				<label for="custom_date_end"><?php _e( 'End Date:', 'ipt_fsqm' ) ?></label>
			</th>
			<td>
				<?php $this->ui->datetimepicker( 'custom_date_end', $today ); ?>
			</td>
			<td>
				<?php $this->ui->help_head(); ?>
					<?php _e( 'Please select the end date and time, inclusive', 'ipt_fsqm' ); ?>
				<?php $this->ui->help_tail(); ?>
			</td>
		</tr>
	</tbody>
</table>
<?php endif; ?>
		<?php
		$this->ui->iconbox_tail();
		if ( !empty( $forms ) ) {
			$this->ui->button( __( 'Select Questions', 'ipt_fsqm' ), 'select_questions', 'large', 'primary', 'normal', array(), 'submit' );
		}
	}

	/*==========================================================================
	 * Survey Reports APIs
	 *========================================================================*/

	public function survey_select_questions() {
		$keys = $this->get_keys_from_layouts_by_m_type( 'mcq', $this->layout );
		$items = array();
		if ( !empty( $keys ) ) {
			foreach ( $keys as $key ) {
				$label = isset( $this->mcq[$key] ) ? $this->mcq[$key]['title'] : null;

				if ( $label === null ) {
					continue;
				}

				$items[] = array(
					'label' => $label,
					'value' => $key,
				);
			}
		}

		ob_start();
		$this->ui->checkbox_toggler( 'ipt_fsqm_survey_toggler', __( 'Toggle All', 'ipt_fsqm' ), '#ipt_fsqm_survey_select_questions input.ipt_uif_checkbox' );
		$toggler = ob_get_clean();
?>
<?php if ( $this->form_id == null ) : ?>
<?php $this->ui->msg_error( __( 'Invalid Form ID Supplied. Please press the back button and check again.', 'ipt_fsqm' ) ); ?>
<?php elseif ( empty( $items ) ) : ?>
<?php $this->ui->msg_error( __( 'No Survey Questions found in the form you have selected.', 'ipt_fsqm' ) ); ?>
<?php else : ?>
<?php $this->ui->iconbox_head( __( 'Select the Multiple Choice Type Questions', 'ipt_fsqm' ), 'checkbox-checked', $toggler ); ?>
<table class="form-table">
	<tbody>
		<tr>
			<td id="ipt_fsqm_survey_select_questions">
				<?php $this->ui->checkboxes( 'mcqs[]', $items, false, false, false, '<div class="clear"></div>' ); ?>
			</td>
		</tr>
	</tbody>
</table>
<?php $this->ui->iconbox_tail(); ?>
<?php endif; ?>
		<?php
	}

	public function survey_generate_report( $mcqs, $do_data, $visualization = '' ) {
		if ( $this->form_id === null ) {
			$this->ui->msg_error( __( 'Invalid form ID supplied.', 'ipt_fsqm' ) );
			return;
		}

		if ( !is_array( $mcqs ) || empty( $mcqs ) ) {
			$this->ui->msg_error( __( 'No multiple choice type questions selected and/or found in the form.', 'ipt_fsqm' ) );
			return;
		}
		if ( $visualization == '' ) {
			$visualization = __( 'Graphical Representation', 'ipt_fsqm' );
		}
		$elements = array();
		$data = array();
?>
<?php foreach ( $mcqs as $mcq ) : ?>
<div class="ipt_fsqm_report_survey_<?php echo $mcq; ?> ipt_fsqm_report_container" style="display: none;">
	<?php $this->ui->iconbox_head( $this->mcq[$mcq]['title'] . ( $this->mcq[$mcq]['subtitle'] != '' ? '<span class="subtitle">' . $this->mcq[$mcq]['subtitle'] . '</span>' : '' ), 'pie' ); ?>
	<?php $elements["$mcq"] = $this->mcq[$mcq]; ?>
	<?php if ( $this->mcq[$mcq]['description'] != '' ) : ?>
	<?php echo apply_filters( 'ipt_uif_richtext', $this->mcq[$mcq]['description'] ); ?>
	<?php endif; ?>
	<?php $data["$mcq"] = $this->survey_generate_report_container( $visualization, $this->mcq[$mcq], $do_data ); ?>
	<?php $this->ui->iconbox_tail(); ?>
</div>
<?php endforeach; ?>
		<?php
		return array(
			'elements' => $elements,
			'data' => $data,
		);
	}

	public function survey_generate_report_container( $visualization, $mcq, $do_data ) {
		$data = array();
		switch ( $mcq['type'] ) {
		default :
			$this->ui->msg_update( __( 'Can generate report only for built in elements.', 'ipt_fsqm' ) );
			break;
		case 'radio' :
		case 'checkbox' :
		case 'select' :
			foreach ( $mcq['settings']['options'] as $o_key => $o_val ) {
				$data["$o_key"] = 0;
			}
			$data['others'] = 0;

?>
<table class="ipt_fsqm_preview table_to_update">
	<thead>
		<tr>
			<th style="width: 50%"><?php echo $visualization; ?></th>
			<th style="width: 50%"><?php _e( 'Data', 'ipt_fsqm' ); ?></th>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<th style="width: 50%"><?php echo $visualization; ?></th>
			<th style="width: 50%"><?php _e( 'Data', 'ipt_fsqm' ); ?></th>
		</tr>
	</tfoot>
	<tbody>
		<tr>
			<td style="width: 50%" class="visualization"><!-- Pie --></td>
			<td style="width: 50%" class="data">
				<table class="ipt_fsqm_preview">
					<thead>
						<tr>
							<th style="width: 80%"><?php _e( 'Options', 'ipt_fsqm' ); ?></th>
							<th style="width: 20%"><?php _e( 'Count', 'ipt_fsqm' ); ?></th>
						</tr>
					</thead>
					<tfoot>
						<tr>
							<th style="width: 80%"><?php _e( 'Options', 'ipt_fsqm' ); ?></th>
							<th style="width: 20%"><?php _e( 'Count', 'ipt_fsqm' ); ?></th>
						</tr>
					</tfoot>
					<tbody>
						<?php foreach ( $mcq['settings']['options'] as $o_key => $o_val ) : ?>
						<tr>
							<th><?php echo $o_val['label']; ?> <?php if ( $o_val['score'] != '' ) echo '<br /><span class="description">Score: ' . $o_val['score'] . '</span>'; ?></th>
							<td class="data_op_<?php echo $o_key; ?>">0</td>
						</tr>
						<?php endforeach; ?>
						<?php if ( $mcq['settings']['others'] == true ) : ?>
						<tr>
							<th><?php echo $mcq['settings']['o_label']; ?></th>
							<td class="data_op_others">0</td>
						</tr>
						<?php endif; ?>
					</tbody>
				</table>
			</td>
		</tr>
	</tbody>
</table>
<?php if ( $mcq['settings']['others'] == true && $do_data ) : ?>
<?php $this->ui->collapsible_head( $mcq['settings']['o_label'] ); ?>
<table class="ipt_fsqm_preview others">
	<thead>
		<tr>
			<th style="width: 60%"><?php _e( 'Entry', 'ipt_fsqm' ); ?></th>
			<th style="width: 20%"><?php _e( 'Name', 'ipt_fsqm' ); ?></th>
			<th style="width: 10%"><?php _e( 'Email', 'ipt_fsqm' ); ?></th>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<th style="width: 60%"><?php _e( 'Entry', 'ipt_fsqm' ); ?></th>
			<th style="width: 20%"><?php _e( 'Name', 'ipt_fsqm' ); ?></th>
			<th style="width: 10%"><?php _e( 'Email', 'ipt_fsqm' ); ?></th>
		</tr>
	</tfoot>
	<tbody>
		<tr class="empty">
			<td colspan="3"><?php _e( 'No data yet!', 'ipt_fsqm' ); ?></td>
		</tr>
	</tbody>
</table>
<?php $this->ui->collapsible_tail(); ?>
<?php endif; ?>
				<?php
			break;
		case 'slider' :
		case 'range' :
			$mcq = $this->sanitize_min_max_step( $mcq );
			$data = array();
?>
<table class="ipt_fsqm_preview table_to_update">
	<thead>
		<tr>
			<th style="width: 50%"><?php echo $visualization; ?></th>
			<th style="width: 50%"><?php _e( 'Data', 'ipt_fsqm' ); ?></th>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<th style="width: 50%"><?php echo $visualization; ?></th>
			<th style="width: 50%"><?php _e( 'Data', 'ipt_fsqm' ); ?></th>
		</tr>
	</tfoot>
	<tbody>
		<tr>
			<td style="width: 50%" class="visualization"><!-- Bar --></td>
			<td style="width: 50%" class="data">
				<table class="ipt_fsqm_preview">
					<thead>
						<tr>
							<th style="width: 60%"><?php _e( 'Value', 'ipt_fsqm' ); ?></th>
							<th style="width: 40%"><?php _e( 'Count', 'ipt_fsqm' ); ?></th>
						</tr>
					</thead>
					<tfoot>
						<tr>
							<th style="width: 60%"><span class="avg"><?php _e( 'N/A', 'ipt_fsqm' ); ?></span> <?php _e( 'average based on', 'ipt_fsqm' ); ?> <span class="avg_count"><?php _e( 'N/A', 'ipt_fsqm' ); ?></span> <?php _e( 'submission(s)', 'ipt_fsqm' ); ?></th>
							<th style="width: 40%"><?php _e( 'Average', 'ipt_fsqm' ); ?></th>
						</tr>
					</tfoot>
					<tbody>

					</tbody>
				</table>
			</td>
		</tr>
	</tbody>
</table>
				<?php
			break;
		case 'spinners' :
		case 'grading' :
			$mcq = $this->sanitize_min_max_step( $mcq );
			foreach ( $mcq['settings']['options'] as $o_key => $o_val ) {
				$data["$o_key"] = array();
			}
?>
<table class="ipt_fsqm_preview table_to_update">
	<thead>
		<tr>
			<th style="width: 50%;"><?php echo $visualization; ?></th>
			<th style="width: 30%"><?php _e( 'Option', 'ipt_fsqm' ); ?></th>
			<th style="width: 20%" colspan="2"><?php _e( 'Data', 'ipt_fsqm' ); ?></th>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<th style="width: 50%;"><?php echo $visualization; ?></th>
			<th style="width: 30%"><?php _e( 'Option', 'ipt_fsqm' ); ?></th>
			<th style="width: 30%" colspan="2"><?php _e( 'Data', 'ipt_fsqm' ); ?></th>
		</tr>
	</tfoot>
	<tbody>

	</tbody>
</table>
				<?php
			break;
		case 'starrating' :
		case 'scalerating' :
			foreach ( $mcq['settings']['options'] as $o_key => $o_val ) {
				$data["$o_key"] = array();
			}
?>
<table class="ipt_fsqm_preview table_to_update">
	<thead>
		<tr>
			<th style="width: 50%;"><?php echo $visualization; ?></th>
			<th style="width: 18%"><?php _e( 'Option', 'ipt_fsqm' ); ?></th>
			<th style="width: 32%" colspan="2"><?php _e( 'Data', 'ipt_fsqm' ); ?></th>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<th style="width: 50%;"><?php echo $visualization; ?></th>
			<th style="width: 18%"><?php _e( 'Option', 'ipt_fsqm' ); ?></th>
			<th style="width: 32%" colspan="2"><?php _e( 'Data', 'ipt_fsqm' ); ?></th>
		</tr>
	</tfoot>
	<tbody>

	</tbody>
</table>
				<?php
			break;
		case 'matrix' :
			foreach ( $mcq['settings']['rows'] as $r_key => $row ) {
				$data["$r_key"] = array();
				foreach ( $mcq['settings']['columns'] as $c_key => $column ) {
					$data["$r_key"]["$c_key"] = 0;
				}
			}
?>
<table class="ipt_fsqm_preview table_to_update">
	<thead>
		<tr>
			<th style="width: 50%"><?php echo $visualization; ?></th>
			<th style="width: 50%"><?php _e( 'Data', 'ipt_fsqm' ); ?></th>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<th style="width: 50%"><?php echo $visualization; ?></th>
			<th style="width: 50%"><?php _e( 'Data', 'ipt_fsqm' ); ?></th>
		</tr>
	</tfoot>
	<tbody>
		<tr>
			<td style="width: 50%" class="visualization"><!-- Combo --></td>
			<td style="width: 50%" class="data matrix">
				<table class="ipt_fsqm_preview">
					<thead>
						<tr>
							<th></th>
							<?php foreach ( $mcq['settings']['columns'] as $c_key => $column ) : ?>
							<th><?php echo $column; ?></th>
							<?php endforeach; ?>
						</tr>
					</thead>
					<tfoot>
						<tr>
							<th></th>
							<?php foreach ( $mcq['settings']['columns'] as $c_key => $column ) : ?>
							<th><?php echo $column; ?></th>
							<?php endforeach; ?>
						</tr>
					</tfoot>
					<tbody>
						<?php foreach ( $mcq['settings']['rows'] as $r_key => $row ) : ?>
						<tr>
							<th><?php echo $row; ?></th>
							<?php foreach ( $mcq['settings']['columns'] as $c_key => $column ) : ?>
							<td class="row_<?php echo $r_key; ?>_col_<?php echo $c_key; ?>">0</td>
							<?php endforeach; ?>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</td>
		</tr>
	</tbody>
</table>
				<?php
			break;
		case 'toggle' :
			$data['on'] = 0;
			$data['off'] = 0;
?>
<table class="ipt_fsqm_preview table_to_update">
	<thead>
		<tr>
			<th style="width: 50%"><?php echo $visualization; ?></th>
			<th style="width: 50%"><?php _e( 'Data', 'ipt_fsqm' ); ?></th>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<th style="width: 50%"><?php echo $visualization; ?></th>
			<th style="width: 50%"><?php _e( 'Data', 'ipt_fsqm' ); ?></th>
		</tr>
	</tfoot>
	<tbody>
		<tr>
			<td style="width: 50%" class="visualization"><!-- Pie --></td>
			<td style="width: 50%" class="data">
				<table class="ipt_fsqm_preview">
					<thead>
						<tr>
							<th style="width: 80%"><?php _e( 'Options', 'ipt_fsqm' ); ?></th>
							<th style="width: 20%"><?php _e( 'Count', 'ipt_fsqm' ); ?></th>
						</tr>
					</thead>
					<tfoot>
						<tr>
							<th style="width: 80%"><?php _e( 'Options', 'ipt_fsqm' ); ?></th>
							<th style="width: 20%"><?php _e( 'Count', 'ipt_fsqm' ); ?></th>
						</tr>
					</tfoot>
					<tbody>
						<?php foreach ( array( 'on', 'off' ) as $o_val ) : ?>
						<tr>
							<th><?php echo $mcq['settings'][$o_val]; ?></th>
							<td class="data_op_<?php echo $o_val; ?>">0</td>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</td>
		</tr>
	</tbody>
</table>
				<?php
			break;
		case 'sorting' :
			//Too many to permute, just leave it here and choose depending on the user submissions
			//But we can just make the preset order
			$data['preset'] = 0;
			$data['other'] = 0;
			$data['orders'] = array();
?>
<table class="ipt_fsqm_preview table_to_update">
	<thead>
		<tr>
			<th style="width: 50%"><?php echo $visualization; ?></th>
			<th style="width: 50%"><?php _e( 'Sortings', 'ipt_fsqm' ); ?></th>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<th style="width: 50%"><?php echo $visualization; ?></th>
			<th style="width: 50%"><?php _e( 'Sortings', 'ipt_fsqm' ); ?></th>
		</tr>
	</tfoot>
	<tbody>
		<tr>
			<td style="width: 50%" class="visualization"><!-- Pie --></td>
			<td style="width: 50%" class="data">
				<table class="ipt_fsqm_preview">
					<thead>
						<tr>
							<th colspan="2"><?php _e( 'Sorting', 'ipt_fsqm' ); ?></th>
							<th style="width: 50px"><?php _e( 'Count', 'ipt_fsqm' ); ?></th>
						</tr>
					</thead>
					<tfoot>
						<tr>
							<th colspan="2"><?php _e( 'Sorting', 'ipt_fsqm' ); ?></th>
							<th style="width: 50px"><?php _e( 'Count', 'ipt_fsqm' ); ?></th>
						</tr>
					</tfoot>
					<tbody>

					</tbody>
				</table>
			</td>
		</tr>
	</tbody>
</table>
				<?php
			break;
			default :
				$definition = $this->get_element_definition( $mcq );
				if( isset( $definition['callback_report'] ) && is_callable( $definition['callback_report'] ) ) {
					$data = call_user_func( $definition['callback_report'], $do_data );
				}
		}
		return $data;
	}


	/*==========================================================================
	 * FeedBack Reports APIs
	 *========================================================================*/
	public function feedback_select_questions() {
		$keys = $this->get_keys_from_layouts_by_m_type( 'freetype', $this->layout );
		$items = array();
		if ( !empty( $keys ) ) {
			foreach ( $keys as $key ) {
				$label = isset( $this->freetype[$key] ) ? $this->freetype[$key]['title'] : null;

				if ( $label === null ) {
					continue;
				}

				$items[] = array(
					'label' => $label,
					'value' => $key,
				);
			}
		}

		ob_start();
		$this->ui->checkbox_toggler( 'ipt_fsqm_feedback_toggler', __( 'Toggle All', 'ipt_fsqm' ), '#ipt_fsqm_feedback_select_questions input.ipt_uif_checkbox' );
		$toggler = ob_get_clean();
?>
<?php if ( $this->form_id == null ) : ?>
<?php $this->ui->msg_error( __( 'Invalid Form ID Supplied. Please press the back button and check again.', 'ipt_fsqm' ) ); ?>
<?php elseif ( empty( $items ) ) : ?>
<?php $this->ui->msg_error( __( 'No Feedback Questions found in the form you have selected.', 'ipt_fsqm' ) ); ?>
<?php else : ?>
<?php $this->ui->iconbox_head( __( 'Select the Feedback Type Questions', 'ipt_fsqm' ), 'checkbox-checked', $toggler ); ?>
<table class="form-table">
	<tbody>
		<tr>
			<td id="ipt_fsqm_feedback_select_questions">
				<?php $this->ui->checkboxes( 'freetypes[]', $items, false, false, false, '<div class="clear"></div>' ); ?>
			</td>
		</tr>
	</tbody>
</table>
<?php $this->ui->iconbox_tail(); ?>
<?php endif; ?>
		<?php
	}

	public function feedback_generate_report( $freetypes ) {
		if ( $this->form_id === null ) {
			$this->ui->msg_error( __( 'Invalid form ID supplied.', 'ipt_fsqm' ) );
			return;
		}

		if ( !is_array( $freetypes ) || empty( $freetypes ) ) {
			$this->ui->msg_error( __( 'No feedback type questions selected and/or found in the form.', 'ipt_fsqm' ) );
			return;
		}
		$elements = array();
		$data = array();
?>
<?php foreach ( $freetypes as $freetype ) : ?>
<div class="ipt_fsqm_report_feedback_<?php echo $freetype; ?> ipt_fsqm_report_container" style="display: none;">
	<?php $this->ui->iconbox_head( $this->freetype[$freetype]['title'] . ( $this->freetype[$freetype]['subtitle'] != '' ? '<span class="subtitle">' . $this->freetype[$freetype]['subtitle'] . '</span>' : '' ), 'bubbles2' ); ?>
	<?php $elements["$freetype"] = $this->freetype[$freetype]; ?>
	<?php if ( $this->freetype[$freetype]['description'] != '' ) : ?>
	<?php echo apply_filters( 'ipt_uif_richtext', $this->freetype[$freetype]['description'] ); ?>
	<?php endif; ?>
	<?php $data["$freetype"] = $this->feedback_generate_report_container( $this->freetype[$freetype] ); ?>
	<?php $this->ui->iconbox_tail(); ?>
</div>
<?php endforeach; ?>
		<?php
		return array(
			'elements' => $elements,
			'data' => $data,
		);
	}

	public function feedback_generate_report_container( $freetype ) {
		$data = array();
		$pinfo_titles = array(
			'name' => __( 'Name', 'ipt_fsqm' ),
			'email' => __( 'Email', 'ipt_fsqm' ),
			'phone' => __( 'Phone', 'ipt_fsqm' ),
		);
		foreach ( $this->pinfo as $pinfo ) {
			if ( in_array( $pinfo['type'], array_keys( $pinfo_titles ) ) ) {
				$pinfo_titles[$pinfo['type']] = $pinfo['title'];
			}
		}

		switch( $freetype['type'] ) {
			case 'feedback_large' :
			case 'feedback_small' :
				?>
<table class="ipt_fsqm_preview">
	<thead>
		<tr>
			<th style="width: 40%;"><?php _e( 'Feedback', 'ipt_fsqm' ); ?></th>
			<?php foreach ( $pinfo_titles as $p_val ) : ?>
			<th><?php echo $p_val; ?></th>
			<?php endforeach; ?>
			<th><?php _e( 'Date', 'ipt_fsqm' ); ?></th>
		</tr>
	</thead>
	<tbody>
		<tr class="empty">
			<td colspan="5"><?php _e( 'No data yet!', 'ipt_fsqm' ); ?></td>
		</tr>
	</tbody>
	<tfoot>
		<tr>
			<th style="width: 40%;"><?php _e( 'Feedback', 'ipt_fsqm' ); ?></th>
			<?php foreach ( $pinfo_titles as $p_val ) : ?>
			<th><?php echo $p_val; ?></th>
			<?php endforeach; ?>
			<th><?php _e( 'Date', 'ipt_fsqm' ); ?></th>
		</tr>
	</tfoot>
</table>
				<?php
				break;
			case 'upload' :
				?>
<table class="ipt_fsqm_preview">
	<thead>
		<tr>
			<th style="width: 30%;"><?php echo $pinfo_titles['name']; ?></th>
			<th><?php _e( 'Date', 'ipt_fsqm' ); ?></th>
			<th><?php _e( 'Uploads', 'ipt_fsqm' ); ?></th>
		</tr>
	</thead>
	<tbody>
		<tr class="empty">
			<td colspan="5"><?php _e( 'No data yet!', 'ipt_fsqm' ); ?></td>
		</tr>
	</tbody>
	<tfoot>
		<tr>
			<th style="width: 30%;"><?php echo $pinfo_titles['name']; ?></th>
			<th><?php _e( 'Date', 'ipt_fsqm' ); ?></th>
			<th><?php _e( 'Uploads', 'ipt_fsqm' ); ?></th>
		</tr>
	</tfoot>
</table>
				<?php
				break;
			default :
				$definition = $this->get_element_definition( $mcq );
				if( isset( $definition['callback_report'] ) && is_callable( $definition['callback_report'] ) ) {
					$data = call_user_func( $definition['callback_report'] );
				}
				break;
		}

		return $data;
	}
}
