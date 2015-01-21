<?php
/**
 * IPT FSQM Export API Class
 *
 * @package IPT FSQM Export
 * @subpackage API
 * @author Swashata Ghosh <swashata@intechgrity.com>
 */

class IPT_FSQM_EXP_Export_API {

	/**
	 * The User Interface instance for admin area from the mother plugin
	 *
	 * @var IPT_Plugin_UIF_Admin
	 */
	static $ui = null;

	/*==========================================================================
	 * Hooks and Filters Integration
	 *========================================================================*/

	public static function admin_load() {
		// Add the dashboard widget
		add_action( 'toplevel_page_ipt_fsqm_dashboard_page_after', array( __CLASS__, 'ipt_fsqm_dashboard' ), $priority = 10, 1 );
		// Exporter generator
		add_action( 'wp_ajax_ipt_fsqm_exp_export_report', array( __CLASS__, 'ipt_fsqm_exp_export_report_cb' ) );
		// Exporter create and stream file
		add_action( 'wp_ajax_ipt_fsqm_exp_export_report_to_file', array( __CLASS__, 'ipt_fsqm_exp_export_report_to_file_cb' ) );

		// Hook to FSQM Pro Form Deleted
		add_action( 'ipt_fsqm_form_deleted', array( __CLASS__, 'sync_form_delete' ) );
		add_action( 'ipt_fsqm_forms_deleted', array( __CLASS__, 'sync_form_delete' ) );

		// Add our item to the FSQM Shortcode Wizard
		add_action( 'ipt_fsqm_shortcode_wizard', array( __CLASS__, 'persistent_shortcode_wizard_cb' ) );
		add_action( 'wp_ajax_ipt_fsqm_exp_shortcode_insert_persistent_trends', array( __CLASS__, 'persistent_shortcode_generator_cb' ) );

		// Add the AJAX for CSV Export
		add_action( 'wp_ajax_ipt_fsqm_exp_raw_csv', array( __CLASS__, 'csv_export_ajax_cb' ) );
		add_action( 'wp_ajax_ipt_fsqm_exp_csv_download', array( __CLASS__, 'csv_download_ajax_cb' ) );
	}

	public static function common_init() {
		global $ipt_fsqm_exp_settings;
		// Persistent Report AJAX Callback
		add_action( 'wp_ajax_ipt_fsqm_exp_report', array( __CLASS__, 'persistent_report_callback_ajax' ) );
		add_action( 'wp_ajax_nopriv_ipt_fsqm_exp_report', array( __CLASS__, 'persistent_report_callback_ajax' ) );

		// Shortcode for Persistent Trends
		add_shortcode( 'ipt_fsqm_ptrends', array( __CLASS__, 'persistent_trends_cb' ) );

		if ( $ipt_fsqm_exp_settings['download_pdf'] == true || is_admin() ) {
			add_filter( 'ipt_fsqm_filter_static_report_print', array( __CLASS__, 'pdf_in_trackback' ), $priority = 10, 2 );
			add_action( 'wp_ajax_ipt_fsqm_exp_download_submission', array( __CLASS__, 'download_pdf_ajax_cb' ) );
			if ( $ipt_fsqm_exp_settings['download_pdf'] == true ) {
				add_filter( 'ipt_fsqm_up_filter_action_button', array( __CLASS__, 'pdf_in_trackback' ), $priority = 10, 2 );
				add_action( 'wp_ajax_nopriv_ipt_fsqm_exp_download_submission', array( __CLASS__, 'download_pdf_ajax_cb' ) );
			}
		}
	}

	/*==========================================================================
	 * CSV Download Function
	 *========================================================================*/
	public static function csv_download_ajax_cb() {
		if ( ! current_user_can( 'manage_feedback' ) ) {
			die( __( 'Cheatin&#8217; uh?' ) );
		}

		$raw_id = isset( $_REQUEST['id'] ) ? (int) $_REQUEST['id'] : 0;

		if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'ipt_fsqm_exp_csv_download_' . $raw_id ) ) {
			die( __( 'Cheatin&#8217; uh?' ) );
		}

		$csv_stream = new IPT_FSQM_EXP_Form_Elements_Export_RAW( $raw_id );
		$csv_stream->stream_csv();
	}


	/*==========================================================================
	 * CSV Export Functions
	 *========================================================================*/
	public static function csv_export_ajax_cb() {
		//Get the variables
		$mcq = isset( $_POST['mcq'] ) && is_array( $_POST['mcq'] ) ? $_POST['mcq'] : array();
		$freetype = isset( $_POST['freetype'] ) && is_array( $_POST['freetype'] ) ? $_POST['freetype'] : array();
		$pinfo = isset( $_POST['pinfo'] ) && is_array( $_POST['pinfo'] ) ? $_POST['pinfo'] : array();
		$settings = isset( $_POST['settings'] ) && is_array( $_POST['settings'] ) ? $_POST['settings'] : array();
		$raw_id = isset( $_POST['raw_id'] ) && !empty( $_POST['raw_id'] ) ? (int) $_POST['raw_id'] : null;
		$raw_nonce = isset( $_POST['raw_nonce'] ) && is_string( $_POST['raw_nonce'] ) ? $_POST['raw_nonce'] : '';
		$doing = isset( $_POST['doing'] ) ? (int) $_POST['doing'] : 0;

		// Check for authenticity
		if ( ! current_user_can( 'manage_feedback' ) ) {
			die( __( 'Cheatin&#8217; uh?' ) );
		}

		// Check for nonce
		if ( ! wp_verify_nonce( $_POST['wpnonce'], 'ipt_fsqm_exp_raw_ajax_' . $settings['form_id'] ) ) {
			die( __( 'Cheatin&#8217; uh?' ) );
		}

		if ( $raw_id !== null ) {
			if ( ! wp_verify_nonce( $raw_nonce, 'ipt_fsqm_exp_raw_update_ajax_' . $raw_id . $doing ) ) {
				die( __( 'Cheatin&#8217; uh?' ) );
			}
		}

		// All done, now prepare the stuff
		$load = $settings['load'];
		$start_date = '';
		$end_date = '';
		if ( $settings['custom_date'] == 'true' ) {
			$start_date = $settings['custom_date_start'];
			$end_date = $settings['custom_date_end'];
		}
		$csv_gen = new IPT_FSQM_EXP_Form_Elements_Export_RAW( $raw_id, $settings['form_id'], false, $start_date, $end_date, $mcq, $freetype, $pinfo );

		if ( $settings['custom_csv'] == 'true' ) {
			$csv_gen->set_delimiter( stripslashes( $settings['custom_csv_delimiter'] ) );
			$csv_gen->set_enclosure( stripslashes( $settings['custom_csv_enclosure'] ) );
			$csv_gen->set_option_delimiter( stripslashes( $settings['custom_csv_option_delimiter'] ) );
			$csv_gen->set_range_delimiter( stripslashes( $settings['custom_csv_range_delimiter'] ) );
			$csv_gen->set_row_delimiter( stripslashes( $settings['custom_csv_row_delimiter'] ) );
		}

		$return = array(
			'type' => 'success',
			'done' => 0,
			'html' => '',
			'raw_id' => $csv_gen->get_raw_id(),
			'raw_nonce' => wp_create_nonce( 'ipt_fsqm_exp_raw_update_ajax_' . $csv_gen->get_raw_id() . ( $doing + 1 ) ),
			'download_url' => admin_url( 'admin-ajax.php?action=ipt_fsqm_exp_csv_download&id=' . $csv_gen->get_raw_id() . '&_wpnonce=' . wp_create_nonce( 'ipt_fsqm_exp_csv_download_' . $csv_gen->get_raw_id() ) ),
		);

		// Check for errors first
		$errors = $csv_gen->get_errors();
		if ( empty( $errors ) ) {
			$return['done'] = $csv_gen->make_csv( $doing, $load );
		} else {
			self::$ui = IPT_Plugin_UIF_Admin::instance( 'ipt_fsqm' );
			$return['type'] = 'error';
			$return['html'] = self::$ui->msg_error( implode( '<br />', $csv_gen->get_errors() ), false );
		}

		@header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );
		echo json_encode( (object) $return );
		die();
	}

	/*==========================================================================
	 * Trackback / Full Preview Print Filter
	 *========================================================================*/

	public static function pdf_in_trackback( $buttons, $form ) {
		if ( !is_object( $form ) || ( ! $form instanceof IPT_FSQM_Form_Elements_Front && ! $form instanceof IPT_FSQM_Form_Elements_Data ) ) {
			return $buttons;
		}

		$size = 'auto';
		$text = __( 'Download', 'ipt_fsqm_exp' );

		if ( $form instanceof IPT_FSQM_Form_Elements_Front ) {
			$size = 'large';
			$text = __( 'Download PDF', 'ipt_fsqm_exp' );
		}

		$buttons[] = array(
			$text,
			'ipt_fsqm_report_download_' . $form->form_id . '_' . $form->data_id,
			$size,
			'none',
			'normal',
			array( 'ipt_fsqm_exp_download_submission' ),
			'button',
			array(),
			array('onclick' => 'javascript:window.location.href="' . wp_nonce_url( admin_url( 'admin-ajax.php?action=ipt_fsqm_exp_download_submission&id=' . $form->data_id ), 'ipt_fsqm_exp_download_sub_' . $form->data_id ) . '"'),
			'',
			'file-pdf',
			'before',
		);

		return $buttons;
	}

	public static function download_pdf_ajax_cb() {
		if ( ! isset( $_REQUEST['id'] ) ) {
			die( __( 'Cheatin&#8217; uh?' ) );
		}
		$data_id = (int) $_REQUEST['id'];

		if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'ipt_fsqm_exp_download_sub_' . $data_id ) ) {
			die( __( 'Cheatin&#8217; uh?' ) );
		}

		$data = new IPT_FSQM_Form_Elements_Data( $data_id );

		if ( $data->form_id == null ) {
			die( __( 'Cheatin&#8217; uh?' ) );
		}

		global $ipt_fsqm_exp_settings;

		include_once IPT_FSQM_EXP_Loader::$abs_path . '/lib/mPDF5.7.1/mpdf.php';
		ob_start();
		?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<title><?php echo $data->name; ?></title>
	<style type="text/css">
		html {
			margin: 20px;
			font-size: 11pt;
		}
		body {
			margin: 0;
			padding: 0;
		}
		table {
			text-align: left;
			vertical-align: middle;
			width: 100%;
			margin: 10px;
			border-collapse: collapse;
			page-break-before: always;
			overflow: wrap;
		}
		table td,
		table th {
			padding: 10px;
			border: 1px solid #888;
			text-align: left;
			border-collapse: collapse;
		}
		table thead tr th,
		table tfoot tr th,
		table tr.head th {
			background-color: #aaa;
			color: #fff;
			text-shadow: 0 0 2px #666;
		}
		table td.data {
			padding: 0;
		}
		table th {
			font-weight: bold;
		}
		.data table,
		.matrix table {
			height: 100%;
			margin: 0;
			width: 100%;
		}
		td.icons {
			width: 1cm;
			padding: 5px;
			overflow: wrap;
		}
		td.icons img {
			display: inline;
			border: 0 none;
		}
		.description {
			font-size: 10pt;
			text-transform: lowercase;
			font-style: italic;
			font-weight: normal;
		}
		<?php echo $ipt_fsqm_exp_settings['style']; ?>
	</style>
</head>
<body>
	<?php echo $ipt_fsqm_exp_settings['html_header']; ?>
	<?php $data->show_quick_preview(); ?>
	<?php echo $ipt_fsqm_exp_settings['html_footer']; ?>
</body>
</html>
		<?php
		$html = ob_get_clean();
		// echo $html;
		$pdf = new mPDF();
		$pdf->shrink_tables_to_fit = 1;
		$pdf->WriteHTML( $html, 0 );
		$filename = sanitize_file_name( $data->name . '-' . $data_id . '.pdf' );
		$pdf->Output( $filename, 'D' );
		die();
	}


	/*==========================================================================
	 * Shortcode Wizard Callbacks
	 *========================================================================*/
	public static function persistent_shortcode_generator_cb() {
		$exports = self::get_exports();
		?>
<table class="form-table" id="ipt_fsqm_exp_shortcode_table">
	<tbody>
		<tr>
			<th><label for="ipt_fsqm_exp_shortcode_export"><?php _e( 'Select the Report', 'ipt_fsqm_exp' ); ?></label></th>
			<td>
				<select id="ipt_fsqm_exp_shortcode_export">
					<?php if ( ! empty( $exports ) ) : ?>
					<?php foreach ( $exports as $export ) : ?>
					<option value="<?php echo $export->id; ?>"><?php echo $export->name . ' [' . date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $export->created ) ) . ']'; ?></option>
					<?php endforeach; ?>
					<?php else : ?>
					<option value="0"><?php _e( 'You have not generated any reports yet.', 'ipt_fsqm_exp' ); ?></option>
					<?php endif; ?>
				</select>
			</td>
		</tr>
	</tbody>
	<tfoot>
		<tr>
			<td colspan="2">
				<p class="submit">
					<input type="button" class="button-primary" value="<?php _e( 'Insert', 'ipt_fsqm_exp' ); ?>" id="ipt_fsqm_exp_shortcode_export_insert_button" />
					<a id="ipt_fsqm_shortcode_wizard_back" title="<?php _e( 'Insert Shortcodes for WP Feedback, Survey & Quiz Manager - Pro', 'ipt_fsqm' ); ?>" href="admin-ajax.php?action=ipt_fsqm_shortcode_insert" class="button-secondary"><?php _e( 'Back', 'ipt_fsqm' ); ?></a>
				</p>
			</td>
		</tr>
	</tfoot>
</table>

<script type="text/javascript">
jQuery(document).ready(function($) {
	$('#ipt_fsqm_exp_shortcode_export_insert_button').on('click', function(e) {
		e.preventDefault();
		var val = $('#ipt_fsqm_exp_shortcode_export').val(),
		shortcode = '<br />[ipt_fsqm_ptrends id="' + val + '"]<br />';
		if ( val == 0 || val == '' ) {
			shortcode = '';
		}
		$(document).trigger('ipt_fsqm_shortcode_insert', [function(ed) {
			ed.execCommand('mceInsertContent', 0, shortcode);
		}]);
	});
});
</script>
		<?php
		die();
	}
	public static function persistent_shortcode_wizard_cb() {
		?>
<tr>
	<td>
		<a title="<?php _e( 'Exporter for FSQM Pro - Insert Persistent Trends', 'ipt_fsqm_exp' ); ?>" class="button-secondary" href="admin-ajax.php?action=ipt_fsqm_exp_shortcode_insert_persistent_trends"><?php _e( 'Persistent Trends', 'ipt_fsqm_exp' ); ?></a>
	</td>
	<td>
		<span class="description">
			<?php _e( 'Recommended method to show trends, where instead of querying your server for the live data, one of your generated reports will be used. This saves a lot of server bandwidth and load. But please note that the report will only be as updated as your export.', 'ipt_fsqm_exp' ); ?>
		</span>
	</td>
</tr>
		<?php
	}


	/*==========================================================================
	 * Persistent Reports as Trends
	 *========================================================================*/

	public static function persistent_trends_cb( $atts, $content = null ) {
		extract(
			shortcode_atts( array(
				'id' => 0,
				'title' => __( 'Trends', 'ipt_fsqm_exp' ),
			), $atts )
		);

		$exp_data = self::get_exp_data( $id );

		$form_id = 0;

		if ( false !== $exp_data ) {
			$form_id = $exp_data->form_id;
		}

		$front = new IPT_FSQM_Form_Elements_Front( null, $form_id );

		ob_start();
		$front->container( array( array( __CLASS__, 'persistent_report' ), array( $id, false, IPT_Plugin_UIF_Front::instance( 'ipt_fsqm' ), $title ) ) );
		return ob_get_clean();
	}

	public static function persistent_report( $exp_id, $do_data = false, $ui = null, $visualization = '' ) {
		$exp_data = self::get_exp_data( $exp_id );

		if ( $ui === null ) {
			if ( class_exists( 'IPT_Plugin_UIF_Admin' ) ) {
				$ui = IPT_Plugin_UIF_Admin::instance( 'ipt_fsqm' );
			} elseif ( class_exists( 'IPT_Plugin_UIF_Front' ) ) {
				$ui = IPT_Plugin_UIF_Front::instance( 'ipt_fsqm' );
			} else {
				return __( 'Invalid scope to call this function', 'ipt_fsqm_exp' );
			}
		}

		if ( $exp_data === false ) {
			$ui->msg_error( __( 'Invalid Export ID', 'ipt_fsqm_exp' ) );
			return;
		}

		$util = new IPT_FSQM_Form_Elements_Utilities( $exp_data->form_id, $ui );

		// Init the keys
		$mcqs_raw = array_keys( $exp_data->survey );
		$freetypes_raw = array();

		if ( $do_data ) {
			$freetypes_raw = array_keys( $exp_data->feedback );
		}

		// Now rearrange the keys
		$mcqs = array();
		$freetypes = array();

		foreach ( $util->mcq as $m_key => $mcq ) {
			if ( in_array( $m_key, $mcqs_raw ) ) {
				$mcqs[] = $m_key;
			}
		}

		foreach ( $util->freetype as $f_key => $freetype ) {
			if ( in_array( $f_key, $freetypes_raw ) ) {
				$freetypes[] = $f_key;
			}
		}

		$settings = array(
			'form_id' => $exp_data->form_id,
			'report' => 'survey_feedback',
			'custom_date' => false,
			'custom_date_start' => '',
			'custom_date_end' => '',
			'load' => '1',
			'exp_id' => $exp_id,
		);

		if ( ! $do_data || empty( $freetypes ) ) {
			$settings['report'] = 'survey';
		} elseif ( empty( $mcqs ) && $do_data ) {
			$settings['report'] = 'feedback';
		}

		$util->report_generate_report( $settings, $mcqs, $visualization, $do_data, $freetypes, 'ipt_fsqm_exp_report' );
	}

	public static function persistent_report_callback_ajax() {
		global $wpdb, $ipt_fsqm_info;
		$settings = isset( $_POST['settings'] ) ? $_POST['settings'] : array();
		$survey = isset( $_POST['survey'] ) ? $_POST['survey'] : array();
		$feedback = isset( $_POST['feedback'] ) ? $_POST['feedback'] : array();
		$doing = isset( $_POST['doing'] ) ? (int) $_POST['doing'] : 0;
		$form_id = isset( $_POST['form_id'] ) ? (int) $_POST['form_id'] : 0;
		$do_data = isset( $_POST['do_data'] ) && $_POST['do_data'] == 'true' ? true : false;
		$exp_id = isset( $settings['exp_id'] ) ? $settings['exp_id'] : 0;

		if ( !wp_verify_nonce( $_POST['wpnonce'], 'ipt_fsqm_report_ajax_' . $form_id ) ) {
			die( __( 'Cheatin&#8217; uh?' ) );
		}
		if ( $do_data && !wp_verify_nonce( $_POST['do_data_nonce'], 'ipt_fsqm_report_ajax_do_data_' . $form_id ) ) {
			die( __( 'Cheatin&#8217; uh?' ) );
		}

		$return = array(
			'type' => 'success',
			'done' => '100',
			'survey' => array(),
			'feedback' => array(),
			'wpnonce' => wp_create_nonce( 'ipt_fsqm_report_ajax_' . $form_id ),
			'form_id' => $form_id,
			'do_data' => $do_data,
			'do_data_nonce' => $do_data ? wp_create_nonce( 'ipt_fsqm_report_ajax_do_data_' . $form_id ) : '',
		);

		//First test the form_id
		if ( null == $wpdb->get_var( $wpdb->prepare( "SELECT name FROM {$ipt_fsqm_info['form_table']} WHERE id = %d", $form_id ) ) ) {
			die( __( 'Cheatin&#8217; uh?' ) );
		}

		//Get the persistent data
		$exp_data = self::get_exp_data( $exp_id );

		//Test for consistency
		if ( false === $exp_data || $exp_data->form_id != $form_id ) {
			die( __( 'Cheatin&#8217; uh?' ) );
		}

		$util = new IPT_FSQM_Form_Elements_Utilities( $exp_data->form_id );

		// Do the surveys
		if ( is_array( $survey ) && !empty( $survey ) ) {
			foreach ( $survey as $m_key => $element ) {
				$m_key = (int) $m_key;

				if ( ! isset( $exp_data->survey["$m_key"] ) ) {
					continue;
				}

				if ( $element['type'] != $util->mcq[$m_key]['type'] ) {
					die( __( 'Cheatin&#8217; uh?' ) );
				}

				switch ( $element['type'] ) {
				default :
					$definition = $util->get_element_definition( $element );
					if ( isset( $definition['callback_exporter_persistent_sanitizer'] ) && is_callable( $definition['callback_exporter_persistent_sanitizer'] ) ) {
						$return['survey']["$m_key"] = call_user_func( $definition['callback_exporter_persistent_sanitizer'], $element, $exp_data->survey["$m_key"], $m_key, $do_data );
					} else {
						$return['survey']["$m_key"] = $exp_data->survey["$m_key"];
					}
					break;
				case 'radio' :
				case 'checkbox' :
				case 'select' :
					if ( ! $do_data && isset( $exp_data->survey["$m_key"]['others_data'] ) ) {
						unset( $exp_data->survey["$m_key"]['others_data'] );
					} else {
						foreach ( $exp_data->survey["$m_key"]['others_data'] as $o_key => $other ) {
							$exp_data->survey["$m_key"]['others_data'][$o_key]['value'] = wpautop( $other['value'] );
							$exp_data->survey["$m_key"]['others_data'][$o_key]['email'] = $other['email'] != __( 'anonymous', 'ipt_fsqm_exp' ) ? '<a href="mailto:' . $other['email'] . '">' . $other['email'] . '</a>' : $other['email'];
						}
					}
					$return['survey']["$m_key"] = $exp_data->survey["$m_key"];
					break;
				}
			}
		}

		// Do the feedbacks
		if ( is_array( $feedback ) && ! empty( $feedback ) && $do_data ) {
			foreach ( $feedback as $f_key => $element ) {
				if ( ! isset( $exp_data->feedback[$f_key] ) ) {
					continue;
				}
				if ( $element['type'] != $util->freetype[$f_key]['type'] ) {
					die( __( 'Cheatin&#8217; uh?' ) );
				}

				switch ( $element['type'] ) {
				default :
					$definition = $util->get_element_definition( $element );
					if ( isset( $definition['callback_exporter_persistent_sanitizer'] ) && is_callable( $definition['callback_exporter_persistent_sanitizer'] ) ) {
						$return['feedback']["$f_key"] = call_user_func( $definition['callback_exporter_persistent_sanitizer'], $element, $exp_data->feedback["$f_key"], $f_key, $do_data );
					} else {
						$return['feedback']["$f_key"] = $exp_data->feedback["$f_key"];
					}
					break;
				case 'upload' :
					$return['feedback']["$f_key"] = $exp_data->feedback[$f_key];
					break;
				case 'feedback_large' :
				case 'feedback_small' :
					foreach ( $exp_data->feedback["$f_key"] as $s_key => $sub ) {
						$exp_data->feedback["$f_key"][$s_key]['value'] = wpautop( $sub['value'] );
						$exp_data->feedback["$f_key"][$s_key]['email'] = $sub['email'] != __( 'anonymous', 'ipt_fsqm_exp' ) ? '<a href="mailto:' . $sub['email'] . '">' . $sub['email'] . '</a>' : $sub['email'];
					}
					$return['feedback']["$f_key"] = $exp_data->feedback[$f_key];
					break;
				}
			}
		}

		//Echo the json and exit
		$return['survey'] = (object) $return['survey'];
		$return['feedback'] = (object) $return['feedback'];
		@header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );

		echo json_encode( (object) $return );
		die();

	}

	/*==========================================================================
	 * Callback for Form Delete from FSQM Pro
	 *========================================================================*/
	public static function sync_form_delete( $form_ids ) {
		global $ipt_fsqm_exp_info, $wpdb;
		if ( ! is_array( $form_ids ) ) {
			$form_ids = (array) $form_ids;
		}

		if ( empty( $form_ids ) ) {
			return false;
		}

		$form_ids = array_map( 'intval', $form_ids );
		$form_ids_in = implode( ',', $form_ids );
		$report_ids = $wpdb->get_col( "SELECT id FROM {$ipt_fsqm_exp_info['exp_table']} WHERE form_id IN ({$form_ids_in})" );
		$raw_ids = $wpdb->get_col( "SELECT id FROM {$ipt_fsqm_exp_info['raw_table']} WHERE form_id IN ({$form_ids_in})" );

		if ( null == $report_ids || empty( $report_ids ) || ! is_array( $report_ids ) ) {
			$report_ids = array();
		}

		if ( null == $raw_ids || empty( $raw_ids ) || ! is_array( $raw_ids ) ) {
			$raw_ids = array();
		}

		self::delete_exp( $report_ids );
		self::delete_raw( $raw_ids );
	}

	/*==========================================================================
	 * Database Abstractions
	 *========================================================================*/

	public static function get_exports() {
		global $wpdb, $ipt_fsqm_exp_info, $ipt_fsqm_info;
		return $wpdb->get_results( "SELECT e.created created, f.name name, e.id id FROM {$ipt_fsqm_exp_info['exp_table']} e LEFT JOIN {$ipt_fsqm_info['form_table']} f ON e.form_id = f.id" );
	}

	public static function get_exp_data( $exp_id ) {
		global $wpdb, $ipt_fsqm_exp_info;
		$data = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$ipt_fsqm_exp_info['exp_table']} WHERE id = %d", $exp_id ) );
		if ( null == $data ) {
			return false;
		}
		$data->feedback = maybe_unserialize( $data->feedback );
		$data->survey = maybe_unserialize( $data->survey );
		return $data;
	}

	public static function delete_exp( $ids = array() ) {
		global $ipt_fsqm_exp_info, $wpdb;
		if ( ! is_array( $ids ) ) {
			$ids = (array) $ids;
		}

		if ( empty( $ids ) ) {
			return false;
		}

		do_action( 'ipt_fsqm_exp_exports_deleted', $ids );

		// Delete entries from database
		$ids = array_map( 'intval', $ids );
		$delete_ids = implode( ',', $ids );
		$return = $wpdb->query( "DELETE FROM {$ipt_fsqm_exp_info['exp_table']} WHERE id IN ({$delete_ids})" );

		// Now loop through and delete individual files
		$file_extensions = self::get_available_export_formats();
		$wp_path = wp_upload_dir();
		$path = $wp_path['basedir'] . '/fsqm-exp-reports';

		foreach ( $ids as $id ) {
			foreach ( $file_extensions as $type ) {
				$file_path = $path . '/ipt-fsqm-exp-' . $id . '.' . $type;

				// Delete the zip file
				if ( file_exists( $file_path . '.zip' ) ) {
					@unlink( $file_path . '.zip' );
				}

				// Delete the directory
				if ( file_exists( $file_path ) ) {
					if ( is_dir( $file_path ) ) {
						self::delTree( $file_path );
					} else {
						@unlink( $file_path );
					}
				}
			}
		}

		// And we are done
		return $return;
	}

	public static function get_raws() {
		global $wpdb, $ipt_fsqm_exp_info, $ipt_fsqm_info;
		return $wpdb->get_results( "SELECT r.id id, r.created created, r.start_date, start_date, r.end_date end_date, f.name name FROM {$ipt_fsqm_exp_info['raw_table']} r LEFT JOIN {$ipt_fsqm_info['form_table']} f ON r.form_id = f.id" );
	}

	public static function get_raw_data( $raw_id ) {
		global $wpdb, $ipt_fsqm_exp_info;
		$data = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$ipt_fsqm_exp_info['raw_table']} WHERE id = %d", $raw_id ) );
		if ( null == $data ) {
			return false;
		}
		$data->freetype = maybe_unserialize( $data->freetype );
		$data->mcq = maybe_unserialize( $data->mcq );
		$data->pinfo = maybe_unserialize( $data->pinfo );
		return $data;
	}

	public static function delete_raw( $ids = array() ) {
		global $wpdb, $ipt_fsqm_exp_info;
		if ( ! is_array( $ids ) ) {
			$ids = (array) $ids;
		}

		if ( empty( $ids ) ) {
			return false;
		}

		do_action( 'ipt_fsqm_exp_raws_deleted', $ids );

		// Prepare delete entries from database
		$ids = array_map( 'intval', $ids );
		$delete_ids = implode( ',', $ids );


		// Now loop through and delete individual files
		$entries = $wpdb->get_results( "SELECT id, form_id FROM {$ipt_fsqm_exp_info['raw_table']} WHERE id IN ({$delete_ids})" );
		$wp_path = wp_upload_dir();
		$path = $wp_path['basedir'] . '/fsqm-exp-raw';

		foreach ( $entries as $entry ) {
			$file_path = $path . '/raw-' . $entry->form_id . '-' . $entry->id . '.csv';

			// Delete the zip file
			if ( file_exists( $file_path ) ) {
				@unlink( $file_path );
			}
		}

		// Delete the entries
		$return = $wpdb->query( "DELETE FROM {$ipt_fsqm_exp_info['raw_table']} WHERE id IN ({$delete_ids})" );

		// And we are done
		return $return;
	}

	/*==========================================================================
	 * Exporter functions
	 *========================================================================*/

	public static function ipt_fsqm_exp_export_report_to_file_cb() {
		global $wpdb, $ipt_fsqm_exp_info;
		if ( !isset( $_REQUEST['id'] ) || !isset( $_REQUEST['_wpnonce'] ) ) {
			wp_die( __( 'Cheatin&#8217; uh?' ) );
		}

		if( !current_user_can( 'manage_feedback' ) ) {
			wp_die( __( 'Cheatin&#8217; uh?' ) );
		}

		$id = (int) $_REQUEST['id'];

		if ( !wp_verify_nonce( $_REQUEST['_wpnonce'], 'ipt_fsqm_exp_export_report_to_file_' . $id ) ) {
			wp_die( __( 'Cheatin&#8217; uh?' ) );
		}

		require_once IPT_FSQM_EXP_Loader::$abs_path . '/classes/class-ipt-fsqm-exp-form-elements-export-report.php';
		$report = new IPT_FSQM_EXP_Form_Elements_Export_Report( $id );
		$report->stream_export( $_REQUEST['type'] );
		die();
	}

	/**
	 * Export Report calculation
	 *
	 * @global  wpdb $wpdb
	 * @global array $ipt_fsqm_exp_info
	 * @global array $ipt_fsqm_info
	 * @return void
	 */
	public static function ipt_fsqm_exp_export_report_cb() {
		global $wpdb, $ipt_fsqm_exp_info, $ipt_fsqm_info;

		$settings = isset( $_POST['settings'] ) ? $_POST['settings'] : array();
		$survey = isset( $_POST['survey'] ) ? $_POST['survey'] : array();
		$feedback = isset( $_POST['feedback'] ) ? $_POST['feedback'] : array();
		$doing = isset( $_POST['doing'] ) ? (int) $_POST['doing'] : 0;
		$form_id = isset( $_POST['form_id'] ) ? (int) $_POST['form_id'] : 0;
		$exp_id = isset( $_POST['exp_id'] ) ? (int) $_POST['exp_id'] : 0;
		$do_data = true;

		if ( !wp_verify_nonce( $_POST['wpnonce'], 'ipt_fsqm_exp_export_report_' . $form_id ) ) {
			die( __( 'Cheatin&#8217; uh?' ) );
		}

		//First test the form_id
		if ( null == $wpdb->get_var( $wpdb->prepare( "SELECT name FROM {$ipt_fsqm_info['form_table']} WHERE id = %d", $form_id ) ) ) {
			die( __( 'Cheatin&#8217; uh?' ) );
		}

		if ( $doing == 0 && $exp_id == 0 ) {
			//Create a new entry
			$exp_data_new = array(
				'form_id' => $form_id,
				'created' => current_time( 'mysql' ),
				'complete' => 0,
				'start_date' => isset( $settings['custom_date'] ) && $settings['custom_date'] == 'true' && isset( $settings['custom_date_start'] ) && $settings['custom_date_start'] != '' ? $settings['custom_date_start'] : '0000-00-00 00:00:00',
				'end_date' => isset( $settings['custom_date'] ) && $settings['custom_date'] == 'true' && isset( $settings['custom_date_end'] ) && $settings['custom_date_end'] != '' ? $settings['custom_date_end'] : '0000-00-00 00:00:00',
				'survey' => maybe_serialize( array() ),
				'feedback' => maybe_serialize( array() ),
			);
			$wpdb->insert( $ipt_fsqm_exp_info['exp_table'], $exp_data_new, array(
					'%d', '%s', '%d', '%s', '%s', '%s', '%s',
				) );
			$exp_id = $wpdb->insert_id;
		} else {
			if ( !wp_verify_nonce( $_POST['exp_nonce'], 'ipt_fsqm_exp_export_report_' . $form_id . '_exp_' . $exp_id ) ) {
				die( __( 'Cheatin&#8217; uh?' ) );
			}
			if ( null == $wpdb->get_var( $wpdb->prepare( "SELECT form_id FROM {$ipt_fsqm_exp_info['exp_table']} WHERE id = %d", $exp_id ) ) ) {
				die( __( 'Cheatin&#8217; uh?' ) );
			}
		}

		$exp_row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$ipt_fsqm_exp_info['exp_table']} WHERE id = %d", $exp_id ) );
		$exp_data = array(
			'survey' => maybe_unserialize( $exp_row->survey ),
			'feedback' => maybe_unserialize( $exp_row->feedback ),
			'start_date' => $exp_row->start_date,
			'end_date' => $exp_row->end_date,
		);

		$return = array(
			'type' => 'success',
			'done' => 0,
			'wpnonce' => wp_create_nonce( 'ipt_fsqm_exp_export_report_' . $form_id ),
			'exp_nonce' => wp_create_nonce( 'ipt_fsqm_exp_export_report_' . $form_id . '_exp_' . $exp_id ),
			'exp_id' => $exp_id,
			'links' => array(),
		);
		$available_export_formats = self::get_available_export_formats();
		foreach ( $available_export_formats as $format ) {
			$return['links'][$format] = admin_url( 'admin-ajax.php?action=ipt_fsqm_exp_export_report_to_file&type=' . $format . '&id=' . $exp_id . '&_wpnonce=' . wp_create_nonce( 'ipt_fsqm_exp_export_report_to_file_' . $exp_id ) );
		}

		//Calculate the number of data to fetch
		$per_page = 15;
		if ( isset( $settings['load'] ) ) {
			switch ( $settings['load'] ) {
			case '1' :
				$per_page = 30;
				break;
			case '2' :
				$per_page = 50;
			}
		}

		@header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );

		$where = '';
		$where_arr = array();
		if ( $exp_data['start_date'] != '0000-00-00 00:00:00' ) {
			$where_arr[] = $wpdb->prepare( 'date >= %s', date( 'Y-m-d H:i:s', strtotime( $exp_data['start_date'] ) ) );
		}

		if ( $exp_data['end_date'] != '0000-00-00 00:00:00' ) {
			$where_arr[] = $wpdb->prepare( 'date <= %s', date( 'Y-m-d H:i:s', strtotime( $exp_data['end_date'] ) ) );
		}

		if ( !empty( $where_arr ) ) {
			$where .= ' AND ' . implode( ' AND ', $where_arr );
		}

		$data_ids = $wpdb->get_col( $wpdb->prepare( "SELECT id FROM {$ipt_fsqm_info['data_table']} WHERE form_id = %d{$where} ORDER BY id ASC LIMIT %d,%d", $form_id, $doing * $per_page, $per_page ) );
		$total = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(id) FROM {$ipt_fsqm_info['data_table']} WHERE form_id = %d{$where}", $form_id ) );

		if ( empty( $data_ids ) ) {
			$return['done'] = 100;
			$wpdb->update( $ipt_fsqm_exp_info['exp_table'], array(
					'complete' => 1,
				), array(
					'id' => $exp_id,
				), '%d', '%d' );
			echo json_encode( (object) $return );
			die();
		}

		foreach ( $data_ids as $data_id ) {
			$data = new IPT_FSQM_Form_Elements_Data( $data_id );

			//Do the survey
			if ( is_array( $survey ) && !empty( $survey ) ) {
				foreach ( $survey as $m_key => $element ) {
					$m_key = (int) $m_key;
					if ( !isset( $data->mcq[$m_key] ) || !isset( $data->data->mcq[$m_key] ) ) {
						continue;
					}
					if ( $element['type'] != $data->mcq[$m_key]['type'] ) {
						die( __( 'Cheatin&#8217; uh?' ) );
					}

					switch ( $data->mcq[$m_key]['type'] ) {
					default :
						$definition = $data->get_element_definition($element);
						if ( isset( $definition['callback_report_calculator'] ) && is_callable( $definition['callback_report_calculator'] ) ) {
							if ( ! isset($exp_data['survey']["$m_key"] ) ) {
								$exp_data['survey']["$m_key"] = array();
							}
							$exp_data['survey']["$m_key"] = call_user_func( $definition['callback_report_calculator'], $element, $data->data->mcq[$m_key], $m_key, $do_data, $exp_data['survey']["$m_key"]  );
						}
						break;
					case 'radio' :
					case 'checkbox' :
					case 'select' :
						if ( !isset( $exp_data['survey']["$m_key"] ) ) {
							$exp_data['survey']["$m_key"] = array();
							$exp_data['survey']["$m_key"]['others_data'] = array();
						}
						if ( empty( $data->data->mcq[$m_key]['options'] ) ) {
							continue 2;
						}
						foreach ( $data->data->mcq[$m_key]['options'] as $o_key ) {
							$exp_data['survey']["$m_key"]["$o_key"] = isset( $exp_data['survey']["$m_key"]["$o_key"] ) ? $exp_data['survey']["$m_key"]["$o_key"] + 1 : 1;
						}
						if ( !empty( $data->data->mcq[$m_key]['others'] ) && $do_data ) {
							$exp_data['survey']["$m_key"]['others_data'][] = array(
								'value' => esc_textarea( $data->data->mcq[$m_key]['others'] ),
								'name' => $data->data->f_name . ' ' . $data->data->l_name,
								'email' => $data->data->email == '' ? __( 'anonymous', 'ipt_fsqm_exp' ) : $data->data->email,
								'id' => $data->data_id,
							);
						}
						break;
					case 'slider' :
						if ( !isset( $exp_data['survey']["$m_key"] ) ) {
							$exp_data['survey']["$m_key"] = array();
						}
						if ( '' == $data->data->mcq[$m_key]['value'] ) {
							continue 2;
						}
						$exp_data['survey']["$m_key"]["{$data->data->mcq[$m_key]['value']}"] = isset( $exp_data['survey']["$m_key"]["{$data->data->mcq[$m_key]['value']}"] ) ? $exp_data['survey']["$m_key"]["{$data->data->mcq[$m_key]['value']}"] + 1 : 1;
						break;
					case 'range' :
						if ( !isset( $exp_data['survey']["$m_key"] ) ) {
							$exp_data['survey']["$m_key"] = array();
						}
						if ( empty( $data->data->mcq[$m_key]['values'] ) ) {
							continue;
						}
						$key = "{$data->data->mcq[$m_key]['values']['min']},{$data->data->mcq[$m_key]['values']['max']}";
						$exp_data['survey']["$m_key"][$key] = isset( $exp_data['survey']["$m_key"][$key] ) ? $exp_data['survey']["$m_key"][$key] + 1 : 1;
						break;
					case 'spinners' :
					case 'grading' :
					case 'starrating' :
					case 'scalerating' :
						if ( !isset( $exp_data['survey']["$m_key"] ) ) {
							$exp_data['survey']["$m_key"] = array();
						}
						if ( empty( $data->data->mcq[$m_key]['options'] ) ) {
							continue 2;
						}

						foreach ( $data->mcq[$m_key]['settings']['options'] as $o_key => $o_val ) {
							if ( !isset( $exp_data['survey']["$m_key"]["$o_key"] ) ) {
								$exp_data['survey']["$m_key"]["$o_key"] = array();
							}
							if ( !isset( $data->data->mcq[$m_key]['options'][$o_key] ) ) {
								continue;
							}
							if ( is_array( $data->data->mcq[$m_key]['options'][$o_key] ) ) {
								$key = $data->data->mcq[$m_key]['options'][$o_key]['min'] . ',' . $data->data->mcq[$m_key]['options'][$o_key]['max'];
							} else {
								$key = (string) $data->data->mcq[$m_key]['options'][$o_key];
							}

							if ( $key == '' ) {
								continue;
							}

							$exp_data['survey']["$m_key"]["$o_key"][$key] = isset( $exp_data['survey']["$m_key"]["$o_key"][$key] ) ? $exp_data['survey']["$m_key"]["$o_key"][$key] + 1 : 1;
						}
						break;
					case 'matrix' :
						if ( !isset( $exp_data['survey']["$m_key"] ) ) {
							$exp_data['survey']["$m_key"] = array();
						}
						if ( empty( $data->data->mcq[$m_key]['rows'] ) ) {
							continue 2;
						}
						foreach ( $data->data->mcq[$m_key]['rows'] as $r_key => $columns ) {
							if ( !isset( $exp_data['survey']["$m_key"]["$r_key"] ) ) {
								$exp_data['survey']["$m_key"]["$r_key"] = array();
							}
							foreach ( $columns as $c_key ) {
								$exp_data['survey']["$m_key"]["$r_key"]["$c_key"] = isset( $exp_data['survey']["$m_key"]["$r_key"]["$c_key"] ) ? $exp_data['survey']["$m_key"]["$r_key"]["$c_key"] + 1 : 1;
							}
						}
						break;
					case 'toggle' :
						if ( !isset( $exp_data['survey']["$m_key"] ) ) {
							$exp_data['survey']["$m_key"] = array(
								'on' => 0,
								'off' => 0,
							);
						}
						if ( $data->data->mcq[$m_key]['value'] == false ) {
							$exp_data['survey']["$m_key"]['off']++;
						} else {
							$exp_data['survey']["$m_key"]['on']++;
						}
						break;
					case 'sorting' :
						if ( !isset( $exp_data['survey']["$m_key"] ) ) {
							$exp_data['survey']["$m_key"] = array(
								'preset' => 0,
								'other' => 0,
								'orders' => array(),
							);
						}
						if ( empty( $data->data->mcq[$m_key]['order'] ) ) {
							continue 2;
						}
						$correct_order = implode( '-', array_keys( $data->mcq[$m_key]['settings']['options'] ) );
						$user_order = implode( '-', $data->data->mcq[$m_key]['order'] );
						if ( $correct_order == $user_order ) {
							$exp_data['survey']["$m_key"]['preset']++;
						} else {
							$exp_data['survey']["$m_key"]['other']++;
						}
						$exp_data['survey']["$m_key"]['orders'][$user_order] = isset( $exp_data['survey']["$m_key"]['orders'][$user_order] ) ? $exp_data['survey']["$m_key"]['orders'][$user_order] + 1 : 1;
					}
				}
			}

			//Do the Feedback
			if ( is_array( $feedback ) && !empty( $feedback ) && $do_data ) {
				foreach ( $feedback as $f_key => $element ) {
					if ( !isset( $data->freetype[$f_key] ) || !isset( $data->data->freetype[$f_key] ) ) {
						continue;
					}
					if ( $element['type'] != $data->freetype[$f_key]['type'] ) {
						die( __( 'Cheatin&#8217; uh?' ) );
					}

					switch ( $element['type'] ) {
					default :
						$definition = $data->get_element_definition($element);
						if ( isset( $definition['callback_report_calculator'] ) && is_callable( $definition['callback_report_calculator'] ) ) {
							if ( isset( $exp_data['feedback']["$f_key"] ) ) {
								$exp_data['feedback']["$f_key"] = array();
							}
							$exp_data['feedback']["$f_key"] = call_user_func( $definition['callback_report_calculator'], $element, $data->data->freetype[$f_key], $f_key, $do_data, $exp_data['feedback']["$f_key"] );
						}
						break;
					case 'upload' :
						if ( !isset( $exp_data['feedback']["$f_key"] ) ) {
							$exp_data['feedback']["$f_key"] = array();
						}

						// Init the uploader class
						$uploader = new IPT_FSQM_Form_Elements_Uploader( $data->form_id, $f_key );
						$uploads = $uploader->get_uploads( $data->data_id );

						// Loop through all uploads and save the meta
						$upload_array = array();

						if ( ! empty( $uploads ) ) {
							foreach ( $uploads as $upload ) {
								if ( '' == $upload['guid'] ) {
									continue;
								}
								$upload_array[] = array(
									'guid'      => $upload['guid'],
									'thumb_url' => $upload['thumb_url'],
									'name'      => $upload['name'] . ' (' . $upload['mime_type'] . ' )',
									'filename'  => $upload['filename'],
								);
							}
						}

						$exp_data['feedback']["$f_key"][] = array(
							'id'      => $data->data_id,
							'name'    => $data->data->f_name . ' ' . $data->data->l_name,
							'date'    => date_i18n( get_option( 'date_format' ) . __(' \a\t ', 'ipt_fsqm') . get_option( 'time_format' ), strtotime( $data->data->date ) ),
							'uploads' => $upload_array,
						);
						break;

					case 'feedback_large' :
					case 'feedback_small' :
						if ( empty( $data->data->freetype[$f_key]['value'] ) ) {
							continue 2;
						}
						if ( !isset( $exp_data['feedback']["$f_key"] ) ) {
							$exp_data['feedback']["$f_key"] = array();
						}
						$exp_data['feedback']["$f_key"][] = array(
							'value' => esc_textarea( $data->data->freetype["$f_key"]['value'] ),
							'name'  => $data->data->f_name . ' ' . $data->data->l_name,
							'email' => $data->data->email == '' ? __( 'anonymous', 'ipt_fsqm' ) : $data->data->email,
							'phone' => $data->data->phone,
							'date'  => $data->data->date,
							'id'    => $data->data_id,
						);
						break;
					}
				}
			}
		}

		//Calculate the done
		$done_till_now = $doing * $per_page + $per_page;
		if ( $done_till_now >= $total ) {
			$return['done'] = 100;
		} else {
			$return['done'] = (float) $done_till_now * 100 / $total;
		}

		//Update the database
		$wpdb->update( $ipt_fsqm_exp_info['exp_table'], array(
				'survey' => maybe_serialize( $exp_data['survey'] ),
				'feedback' => maybe_serialize( $exp_data['feedback'] ),
				'complete' => $return['done'] == 100 ? 1 : 0,
			), array(
				'id' => $exp_id,
			), '%s', '%d' );

		echo json_encode( (object) $return );
		die();
	}

	/*==========================================================================
	 * Dashboard Widget Callback
	 *========================================================================*/

	/**
	 * Hooks to FSQM Pro Dashboard to show Exporter
	 *
	 * Works only with FSQM Pro version 2.1.1+
	 *
	 * @param IPT_FSQM_Admin $admin The instance of the admin class
	 * @return void
	 */
	public static function ipt_fsqm_dashboard( $admin ) {
		$admin->ui->iconbox( __( 'Exporter for FSQM Pro', 'ipt_fsqm_exp' ), array( __CLASS__, 'notify_about_available_features' ), 'download-2' );
	}

	/*==========================================================================
	 * Managing PHP Resources & Export Types
	 *========================================================================*/

	public static function extend_resources($ignore_user_abort = false) {
		global $ipt_fsqm_exp_settings;
		$memory_limit = (int) $ipt_fsqm_exp_settings['memory'];
		$execution_time = (int) $ipt_fsqm_exp_settings['execution_time'];

		if ( $memory_limit < 128 ) {
			$memory_limit = 128;
		}

		if ( $execution_time < 100 ) {
			$execution_time = 100;
		}

		@ini_set( 'memory_limit', $memory_limit . 'M' );
		@ini_set( 'max_execution_time', $execution_time );
		@ignore_user_abort( $ignore_user_abort );
	}

	public static function notify_about_available_features() {
		global $ipt_fsqm_exp_info;
		$errors = array();
		$okays = array();

		IPT_FSQM_EXP_Export_API::extend_resources();

		//Check PHP Version
		if ( defined( 'PHP_VERSION' ) ) {
			$okays[] = sprintf( __( 'Current PHP version %s, required version 5.2.0.', 'ipt_fsqm_exp' ), PHP_VERSION );
		} else {
			$errors[] = __( 'Your server does not give PHP version information.', 'ipt_fsqm_exp' );
		}

		//Check LIBXML
		if ( defined( 'LIBXML_DOTTED_VERSION' ) ) {
			$okays[] = sprintf( __( 'XML Support is loaded and current LIBXML version is %s.', 'ipt_fsqm_exp' ), LIBXML_DOTTED_VERSION );
		} else {
			if ( defined( 'LIBXML_VERSION' ) ) {
				$okays[] = sprintf( __( 'XML Support is loaded and current LIBXML version is %s.', 'ipt_fsqm_exp' ), LIBXML_VERSION );
			} else {
				$okays[] = __( 'XML Support is loaded.', 'ipt_fsqm_exp' );
			}
		}

		//Check ZIP Extension
		if ( !extension_loaded( 'zip' ) ) {
			$errors[] = __( 'PHP ZIP extension is not loaded. Export Support Disabled.', 'ipt_fsqm_exp' );
		} else {
			$okays[] = __( 'PHP ZIP extension is loaded. Export support enabled.', 'ipt_fsqm_exp' );
		}

		//Check PHP GD
		if ( self::gd_version() < 2 ) {
			$errors[] = __( 'PHP GD2 is not loaded. XLS, PDF and HTML support disabled.', 'ipt_fsqm_exp' );
		} else {
			$okays[] = __( 'PHP GD2 is loaded. XLS, PDF and HTML support enabled.', 'ipt_fsqm_exp' );
		}

		//Check PHP Memory Limit
		if ( self::memory_limit() !== false ) {
			$memory_limit = self::memory_limit() / 1024 / 1024;
			if ( $memory_limit >= 1024 ) {
				$okays[] = sprintf( __( 'Server status: Excellent. PHP memory limit %dMB.', 'ipt_fsqm_exp' ), $memory_limit );
			} else if ( $memory_limit >= 512 ) {
					$okays[] = sprintf( __( 'Server status: Good. PHP memory limit %dMB.', 'ipt_fsqm_exp' ), $memory_limit );
				} else if ( $memory_limit >= 256 ) {
					$okays[] = sprintf( __( 'Server status: Fair. PHP memory limit %dMB.', 'ipt_fsqm_exp' ), $memory_limit );
				} else if ( $memory_limit >= 128 ) {
					$errors[] = sprintf( __( 'Server status: Average. PHP memory limit %dMB.', 'ipt_fsqm_exp' ), $memory_limit );
				} else {
				$errors[] = sprintf( __( 'Server status: Poor. PHP memory limit %dMB.', 'ipt_fsqm_exp' ), $memory_limit );
			}
		} else {
			$errors[] = __( 'Could not determine PHP memory limit.', 'ipt_fsqm_exp' );
		}

		//Check PHP Max Execution Time
		$max_execution = @ini_get( 'max_execution_time' );
		if ( $max_execution ) {
			if ( $max_execution >= 200 ) {
				$okays[] = sprintf( __( 'PHP execution time limit %dSeconds, which is excellent.', 'ipt_fsqm_exp' ), $max_execution );
			} else if ( $max_execution >= 100 ) {
					$okays[] = sprintf( __( 'PHP execution time limit %dSeconds, which is good.', 'ipt_fsqm_exp' ), $max_execution );
				} else if ( $max_execution >= 50 ) {
					$errors[] = sprintf( __( 'PHP execution time limit %dSeconds, may cause problem for PDFs.', 'ipt_fsqm_exp' ), $max_execution );
				} else {
				$errors[] = sprintf( __( 'PHP execution time limit %dSeconds, may cause problem for downloads.', 'ipt_fsqm_exp' ), $max_execution );
			}
		} else {
			$errors[] = __( 'Could not determine PHP execution time limit.', 'ipt_fsqm_exp' );
		}

		self::$ui = IPT_Plugin_UIF_Admin::instance( 'ipt_fsqm' );
		$buttons = array(
			'xlsx' => 'javascript:;',
			'pdf' => 'javascript:;',
			'xls' => 'javascript:;',
			'html' => 'javascript:;',
		);

		self::$ui->help_head( __( 'Plugin Version', 'ipt_fsqm_exp' ) );
		_e( 'If the Script version and DB version do not match, then deactivate the plugin and reactivate again. This should solve the problem. If the problem persists then contact the developer.', 'ipt_fsqm_exp' );
		self::$ui->help_tail();
?>
<p>
	<?php _e( 'Thank you for purchasing Exporter for FSQM Pro extension.', 'ipt_fsqm_exp' ); ?> |
	<?php printf( __( '<strong>Plugin Version:</strong> <em>%s(Script)/%s(DB)</em>', 'ipt_fsqm_exp' ), IPT_FSQM_EXP_Loader::$version, $ipt_fsqm_exp_info['version'] ); ?> |
	<strong><?php _e( 'Server Status', 'ipt_fsqm_exp' ); ?></strong> <span style="vertical-align: middle;" class="icon-arrow-down-2"></span>
</p>
		<?php
		self::$ui->clear();
		$i = 0;
		if ( !empty( $okays ) ) {
			foreach ( $okays as $okay ) {
				echo '<div class="' . ( $i % 2 == 0 ? 'ipt_uif_left_col' : 'ipt_uif_right_col' ) . '"><div class="ipt_uif_col_inner">';
				self::$ui->msg_okay( $okay );
				echo '</div></div>';
				$i++;
			}
		}
		if ( !empty( $errors ) ) {
			foreach ( $errors as $error ) {
				echo '<div class="' . ( $i % 2 == 0 ? 'ipt_uif_left_col' : 'ipt_uif_right_col' ) . '"><div class="ipt_uif_col_inner">';
				self::$ui->msg_error( $error );
				echo '</div></div>';
				$i++;
			}
		}
		self::$ui->clear();
?>
<div class="ipt_uif_float_left">
	<p><strong><?php _e( 'Available Export Formats', 'ipt_fsqm_exp' ); ?></strong></p>
</div>
<div class="ipt_uif_float_right">
	<?php echo self::render_available_export_formats_buttons( $buttons ); ?>
</div>
		<?php
		self::$ui->clear();
	}

	/**
	 * Get available export formats
	 * Depending on the installed libraries
	 *
	 *
	 * @return array list of all file extensions in terms of formats (xlsx, pdf, html, xls)
	 */
	public static function get_available_export_formats() {
		$formats = array();
		$formats[] = 'xlsx';
		if ( self::gd_version() >= 2 ) {
			$formats[] = 'pdf';
			$formats[] = 'html';
			$formats[] = 'xls';
		}
		return $formats;
	}

	/**
	 * Render available export format buttons
	 * Depending on the installed libraries
	 *
	 *
	 * @param array   $links Default anchor links to the corresponding buttons
	 * @return string        The HTML of the buttons
	 */
	public static function render_available_export_formats_buttons( $links = array() ) {
		$formats = self::get_available_export_formats();
		$button_class = array(
			'xlsx' => 'file-openoffice',
			'pdf' => 'file-pdf',
			'xls' => 'file-excel',
			'html' => 'html5',
		);
		$button_text = array(
			'xlsx' => __( 'Open Office XML or Excel 2007 and above', 'ipt_fsqm_exp' ),
			'pdf' => __( 'Portable Document Format', 'ipt_fsqm_exp' ),
			'xls' => __( 'Excel 95 and above', 'ipt_fsqm_exp' ),
			'html' => __( 'HTML File', 'ipt_fsqm_exp' ),
		);
		ob_start();
?>
<div class="ipt_uif_button_container center">
	<?php foreach ( $button_class as $format => $class ) : ?>
	<?php if ( in_array( $format, $formats, true ) ) : ?>
	<a title="<?php echo esc_attr( $button_text[$format] ); ?>" style="display: inline-block; float: none;<?php if ( $format == 'xlsx' || $format == 'pdf' ) echo ' width: 100px;'; ?>" href="<?php echo isset( $links[$format] ) ? $links[$format] : '#'; ?>" class="ipt_uif_button secondary-button ipt_fsqm_exp_export_button_<?php echo esc_attr( $format ); ?>"><span class="icon-<?php echo $class; ?>"></span> <?php echo strtoupper( $format ); ?></a>
	<?php endif; ?>
	<?php endforeach; ?>
</div>
		<?php
		return ob_get_clean();
	}

	/*==========================================================================
	 * Some Internal methods
	 *========================================================================*/

	public function delTree( $dir ) {
		$files = array_diff( scandir( $dir ), array( '.', '..' ) );
		foreach ( $files as $file ) {
			( is_dir( "$dir/$file" ) ) ? self::delTree( "$dir/$file" ) : @unlink( "$dir/$file" );
		}
		return @rmdir( $dir );
	}

	public static function memory_limit( $override = false ) {
		$memory_limit = @ini_get( 'memory_limit' );
		static $memory_byte = null;
		if ( !$memory_limit ) {
			return false;
		}

		if ( $memory_byte !== null && $override !== true ) {
			return $memory_byte;
		}

		if ( preg_match( '/^(\d+)(.)$/', $memory_limit, $matches ) ) {
			$memory_byte = $matches[1];
			switch ( $matches[2] ) {
			case 'G' :
				$memory_byte = $memory_byte * 1024 * 1024 * 1024;
				break;
			case 'M':
				$memory_byte = $memory_byte * 1024 * 1024;
				break;
			case 'K':
				$memory_byte = $memory_byte * 1024;
				break;
			default :
				//According to @link http://www.php.net/manual/en/faq.using.php#faq.using.shorthandbytes
				//anything else will mean it is set in bytes
				//So we do not have to do anything
			}
		} else {
			if ( is_numeric( $memory_limit ) ) {
				$memory_byte = $memory_limit;
			} else {
				//Revert to default
				if ( version_compare( '5.2.0', PHP_VERSION, '>' ) ) {
					$memory_byte = 128 * 1024 * 1024;
				} else {
					$memory_byte = 16 * 1024 * 1024;
				}
			}
		}
		return $memory_byte;
	}

	/**
	 * Get version of GD
	 *
	 * @link http://www.php.net/manual/en/function.gd-info.php#52481
	 *
	 * @param integer $user_ver Specify version, do this only if specification is 1
	 * @return integer            Version number of GD library (0 if not installed, otherwise 1 or 2)
	 */
	public static function gd_version( $user_ver = 0 ) {
		if ( ! extension_loaded( 'gd' ) ) { return; }
		static $gd_ver = 0;
		// Just accept the specified setting if it's 1.
		if ( $user_ver == 1 ) { $gd_ver = 1; return 1; }
		// Use the static variable if function was called previously.
		if ( $user_ver !=2 && $gd_ver > 0 ) { return $gd_ver; }
		// Use the gd_info() function if possible.
		if ( function_exists( 'gd_info' ) ) {
			$ver_info = gd_info();
			preg_match( '/\d/', $ver_info['GD Version'], $match );
			$gd_ver = $match[0];
			return $match[0];
		}
		// If phpinfo() is disabled use a specified / fail-safe choice...
		if ( preg_match( '/phpinfo/', ini_get( 'disable_functions' ) ) ) {
			if ( $user_ver == 2 ) {
				$gd_ver = 2;
				return 2;
			} else {
				$gd_ver = 1;
				return 1;
			}
		}
		// ...otherwise use phpinfo().
		ob_start();
		phpinfo( 8 );
		$info = ob_get_contents();
		ob_end_clean();
		$info = stristr( $info, 'gd version' );
		preg_match( '/\d/', $info, $match );
		$gd_ver = $match[0];
		if ( $gd_ver === null ) {
			$gd_ver = 0;
		}
		return $gd_ver;
	}
}
