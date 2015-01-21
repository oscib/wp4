<?php
/**
 * IPT FSQM Export Admin Classes
 *
 * @package IPT FSQM Export
 * @subpackage Admin
 * @author Swashata Ghosh <swashata@intechgrity.com>
 */

class IPT_FSQM_EXP_Export_Report extends IPT_FSQM_Admin_Base {
	/**
	 * Form utilities object
	 *
	 * @var IPT_FSQM_Form_Elements_Utilities
	 */
	public $form_util;
	public function __construct() {
		$this->capability = 'manage_feedback';
		$this->action_nonce = 'ipt_fsqm_exp_report_nonce';
		parent::__construct();

		$this->icon = 'stats';

		$this->form_util = new IPT_FSQM_Form_Elements_Utilities();
	}

	public function admin_menu() {
		$this->pagehook = add_submenu_page( 'ipt_fsqm_dashboard', __( 'IPT FSQM Export Report', 'ipt_fsqm_exp' ), __( 'Export Report', 'ipt_fsqm_exp' ), $this->capability, 'ipt_fsqm_exp_export_report', array( $this, 'index' ) );
		parent::admin_menu();
	}

	public function index() {
		$this->index_head( __( 'Exporter for FSQM Pro <span class="icon-arrow-right-2"></span> Export Report', 'ipt_fsqm_exp' ), false );
		echo '<form method="post" action="">';

		if ( isset( $this->post['generate_report'] ) ) {
			$this->show_ajax_form();
		} elseif ( isset ( $this->post['select_questions'] ) ) {
			$this->form_util->report_select_questions();
		} else {
			$this->form_util->report_show_forms();
		}

		echo '</form>';
		$this->index_foot( false );
	}

	protected function show_ajax_form() {
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
		extract( $hiddens );

		$this->form_util->init( $form_id );

		if ( null == $this->form_util->form_id ) {
			$this->ui->msg_error( __( 'Invalid form ID Provided', 'ipt_fsqm_exp' ) );
			return;
		}

		$total_data = $this->form_util->get_total_submissions();

		if ( null == $total_data || $total_data < 1 ) {
			$this->ui->msg_error( __( 'Not enough data to populate report. Please be patient.', 'ipt_fsqm_exp' ) );
			return;
		}

		$survey = array();
		$feedback = array();

		ob_start();
		switch ( $report ) {
		case 'survey' :
			$survey = $this->form_util->survey_generate_report( $mcqs, true, '' );
			break;
		case 'feedback' :
			$feedback = $this->form_util->feedback_generate_report( $freetypes, true );
			break;
		case 'survey_feedback' :
			$survey = $this->form_util->survey_generate_report( $mcqs, true, '' );
			$feedback = $this->form_util->feedback_generate_report( $freetypes, true );
			break;
		default:
			$this->ui->msg_error( __( 'Invalid report type selected', 'ipt_fsqm_exp' ) );
			return;
			break;
		}
		ob_end_clean();

		if ( !empty( $survey ) ) {
			$survey['data'] = (object) $survey['data'];
			$survey['elements'] = (object) $survey['elements'];
		}
		if ( !empty( $feedback ) ) {
			$feedback['data'] = (object) $feedback['data'];
			$feedback['elements'] = (object) $feedback['elements'];
		}
?>
<div id="ipt_fsqm_<?php echo $this->form_util->form_id; ?>_exp_export_report" class="ipt_fsqm_exp_export_report">
	<?php $this->ui->progressbar( '', 0, 'ipt_fsqm_exp_export_report_progressbar' ); ?>
	<?php $this->ui->clear(); ?>
	<?php $this->ui->ajax_loader( false, '', array(
				'done' => __( 'Complete', 'ipt_fsqm_exp' ),
			), true, __( 'Please wait &hellip;', 'ipt_fsqm_exp' ), array( 'ipt_fsqm_exp_export_report_al' ) ); ?>
	<?php echo IPT_FSQM_EXP_Export_API::render_available_export_formats_buttons(); ?>
	<script type="text/javascript">
	window.addEventListener('load', function() {
		jQuery(document).ready(function($) {
			var survey = <?php echo json_encode( (object) $survey ); ?>;
			var feedback = <?php echo json_encode( (object) $feedback ); ?>;
			var settings = <?php echo json_encode( (object) $hiddens ); ?>;
			var ajaxurl = '<?php echo admin_url( 'admin-ajax.php' ); ?>';
			var wpnonce = '<?php echo wp_create_nonce( 'ipt_fsqm_exp_export_report_' . $this->form_util->form_id ); ?>';
			$('#ipt_fsqm_<?php echo $this->form_util->form_id; ?>_exp_export_report').iptFSQMEXPExportReport({
				settings : settings,
				survey : survey,
				feedback : feedback,
				wpnonce : wpnonce,
				ajaxurl : ajaxurl,
				form_id : <?php echo $this->form_util->form_id; ?>
			});
		});
	});
	</script>
</div>
		<?php
	}

	public function on_load_page() {
		wp_enqueue_script( 'ipt-fsqm-exp-export-report', plugins_url( '/static/admin/js/jquery.ipt-fsqm-exp-export-report.js', IPT_FSQM_EXP_Loader::$abs_file ), array( 'jquery' ), IPT_FSQM_EXP_Loader::$version );
		get_current_screen()->add_help_tab( array(
			'id' => 'overview',
			'title' => __( 'Overview', 'ipt_fsqm_exp' ),
			'content' =>
			'<p>' . __( 'This page provides a nice way to generate and export reports for a particular form.', 'ipt_fsqm_exp' ) . '</p>' .
			'<p>' . __( 'This part of Exporter for FSQM Pro works like a wizard which will guide you through the procedure.', 'ipt_fsqm_exp' ) . '</p>' .
			'<p>' . __( 'Please check other help items for more information.', 'ipt_fsqm_exp' ) . '</p>',
		) );
		get_current_screen()->add_help_tab( array(
			'id' => 'first_step',
			'title' => __( 'Selecting Form', 'ipt_fsqm_exp' ),
			'content' =>
			'<p>' . __( 'In this page you have the following options to get started.', 'ipt_fsqm_exp' ) . '</p>' .
			'<ul>' .
			'<li>' . __( '<strong>Select Form:</strong> Select the form for which you want to generate the report.', 'ipt_fsqm_exp' ) . '</li>' .
			'<li>' . __( '<strong>Report Type:</strong> Please select the type of the report.', 'ipt_fsqm_exp' ) . '</li>' .
			'<li>' . __( '<strong>Server Load:</strong> Select the load on your server. For shared hosts, Medium Load is recommended.', 'ipt_fsqm_exp' ) . '</li>' .
			'<li>' . __( '<strong>Custom Date Range:</strong> Tick and select a range of date.', 'ipt_fsqm_exp' ) . '</li>' .
			'</ul>' .
			'<p>' . __( 'Once done, simply click on the <strong>Select Questions</strong> button.', 'ipt_fsqm_exp' ) . '</p>'
		) );
		get_current_screen()->add_help_tab( array(
			'id' => 'second_step',
			'title' => __( 'Selecting Questions', 'ipt_fsqm_exp' ),
			'content' =>
			'<p>' . __( 'From this page, you will be able to select questions for which you want to generate the report.', 'ipt_fsqm_exp' ) . '</p>' .
			'<ul>' .
			'<li>' . __( '<strong>Select the Multiple Choice Type Questions:</strong> This will list down all the MCQs in your form in proper order. Select the one you like.', 'ipt_fsqm_exp' ) . '</li>' .
			'<li>' . __( '<strong>Select the Feedback Questions:</strong> This will list down all the feedbacks in your form in proper order. Select the one you like.', 'ipt_fsqm_exp' ) . '</li>' .
			'</ul>'

		) );
		get_current_screen()->add_help_tab( array(
			'id' => 'third_step',
			'title' => __( 'Generate Report', 'ipt_fsqm_exp' ),
			'content' =>
			'<p>' . __( 'Now all you have to do it wait until the progress bar reaches 100%. Once done, it will show you the download buttons using which you can download the report in desired format.', 'ipt_fsqm_exp' ) . '</p>',

		) );
		get_current_screen()->set_help_sidebar(
			'<p><strong>' . __( 'For more information:', 'ipt_fsqm_exp' ) . '</strong></p>' .
			'<p>' . sprintf( __( '<a href="%s" target="_blank">Documentation</a>', 'ipt_fsqm_exp' ), IPT_FSQM_EXP_Loader::$documentation ) . '</p>' .
			'<p>' . sprintf( __( '<a href="%s" target="_blank">Support Forums</a>', 'ipt_fsqm_exp' ), IPT_FSQM_EXP_Loader::$support_forum ) . '</p>'
		);
		parent::on_load_page();
	}
}

class IPT_FSQM_EXP_View_All_Reports extends IPT_FSQM_Admin_Base {
	/**
	 * Export table class
	 *
	 * @var IPT_FSQM_EXP_Report_Table
	 */
	public $table_view;
	public function __construct() {
		$this->capability = 'manage_feedback';
		$this->action_nonce = 'ipt_fsqm_exp_view_report_nonce';
		parent::__construct();

		$this->icon = 'download-2';
		add_filter( 'set-screen-option', array( $this, 'table_set_option' ), 10, 3 );

		$this->post_result[4] = array(
			'type' => 'update',
			'msg' => __( 'Successfully deleted the reports', 'ipt_fsqm_exp' ),
		);
		$this->post_result[5] = array(
			'type' => 'error',
			'msg' => __( 'Please select an action', 'ipt_fsqm_exp' ),
		);
		$this->post_result[6] = array(
			'type' => 'update',
			'msg' => __( 'Successfully deleted the report', 'ipt_fsqm_exp' ),
		);
		$this->post_result[7] = array(
			'type' => 'error',
			'msg' => __( 'Please select some reports to perform the action', 'ipt_fsqm_exp' ),
		);
		$this->post_result[8] = array(
			'type' => 'error',
			'msg' => __( 'Could not delete the report.', 'ipt_fsqm_exp' ),
		);
	}

	public function admin_menu() {
		$this->pagehook = add_submenu_page( 'ipt_fsqm_dashboard', __( 'IPT FSQM View all Reports', 'ipt_fsqm_exp' ), __( 'View all Reports', 'ipt_fsqm_exp' ), $this->capability, 'ipt_fsqm_exp_view_all_reports', array( $this, 'index' ) );
		parent::admin_menu();
	}

	public function index() {
		if ( isset( $_GET['id'] ) ) {
			//Show the persistent report
			$this->index_head( __( 'Exporter for FSQM Pro <span class="icon-arrow-right-2"></span> Persistent Report', 'ipt_fsqm_exp' )  . '<a href="admin.php?page=ipt_fsqm_exp_view_all_reports" class="add-new-h2">' . __( 'Go Back', 'ipt_fsqm_exp' ) . '</a>', false );
			IPT_FSQM_EXP_Export_API::persistent_report( $_GET['id'], true );
			$this->index_foot( false );
		} else {
			$this->index_head( __( 'Exporter for FSQM Pro <span class="icon-arrow-right-2"></span> View all Reports', 'ipt_fsqm_exp' )  . '<a href="admin.php?page=ipt_fsqm_exp_export_report" class="add-new-h2">' . __( 'Add New', 'ipt_fsqm_exp' ) . '</a>', false );
			$this->table_view->prepare_items();
?>
<style type="text/css">
	.wp-list-table .column-downloads {
		width: 500px;
	}
</style>
<div class="ipt_uif_iconbox ipt_uif_shadow glowy">
	<div class="ipt_uif_box cyan">
		<h3><span class="icon-pencil"></span><?php _e( 'View and/or Delete Reports', 'ipt_fsqm_exp' ); ?></h3>
	</div>
	<div class="ipt_uif_iconbox_inner">
		<form action="" method="get">
			<?php foreach ( $_GET as $k => $v ) : if ( $k == 'order' || $k == 'orderby' || $k == 'page' ) : ?>
			<input type="hidden" name="<?php echo $k; ?>" value="<?php echo $v; ?>" />
			<?php endif; endforeach; ?>
			<?php $this->table_view->display(); ?>
		</form>
	</div>
</div>
			<?php
			$this->index_foot( false );
		}
	}

	public function on_load_page() {
		global $wpdb, $ipt_fsqm_exp_info;
		$this->table_view = new IPT_FSQM_EXP_Report_Table();

		$action = $this->table_view->current_action();

		if ( $action == 'delete' ) {
			if ( isset( $_GET['id'] ) ) {
				if ( !wp_verify_nonce( $_GET['_wpnonce'], 'ipt_fsqm_exp_report_delete_' . $_GET['id'] ) ) {
					wp_die( __( 'Cheatin&#8217; uh?' ) );
				}
				if ( IPT_FSQM_EXP_Export_API::delete_exp( $_GET['id'] ) ) {
					wp_redirect( add_query_arg( array( 'post_result' => '6' ), 'admin.php?page=ipt_fsqm_exp_view_all_reports' ) );
				} else {
					wp_redirect( add_query_arg( array( 'post_result' => '8' ), 'admin.php?page=ipt_fsqm_exp_view_all_reports' ) );
				}
			} else {
				if ( !wp_verify_nonce( $_GET['_wpnonce'], 'bulk-ipt_fsqm_exp_report_items' ) ) {
					wp_die( __( 'Cheatin&#8217; uh?' ) );
				}
				if ( !isset( $_GET['reports'] ) || empty( $_GET['reports'] ) ) {
					wp_redirect( add_query_arg( array( 'post_result' => '7' ), $_GET['_wp_http_referer'] ) );
				}

				if ( IPT_FSQM_EXP_Export_API::delete_exp( $_GET['reports'] ) ) {
					wp_redirect( add_query_arg( array( 'post_result' => '4' ), $_GET['_wp_http_referer'] ) );
				} else {
					wp_redirect( add_query_arg( array( 'post_result' => '8' ), $_GET['_wp_http_referer'] ) );
				}
			}
			die();
		}

		get_current_screen()->add_help_tab( array(
			'id'  => 'overview',
			'title'  => __( 'Overview', 'ipt_fsqm_exp' ),
			'content' =>
			'<p>' . __( 'This screen provides access to all of your reports. You can customize the display of this screen to suit your workflow.', 'ipt_fsqm_exp' ) . '</p>' .
			'<p>' . __( 'By default, this screen will show all the reports. Please check the Screen Content for more information.', 'ipt_fsqm_exp' ) . '</p>'
		) );
		get_current_screen()->add_help_tab( array(
			'id'  => 'screen-content',
			'title'  => __( 'Screen Content', 'ipt_fsqm_exp' ),
			'content' =>
			'<p>' . __( 'You can customize the display of this screen&#8217;s contents in a number of ways:' ) . '</p>' .
			'<ul>' .
			'<li>' . __( 'You can sort reports based on created, start date or end date.', 'ipt_fsqm_exp' ) . '</li>' .
			'<li>' . __( 'You can hide/display columns based on your needs and decide how many reports to list per screen using the Screen Options tab.', 'ipt_fsqm_exp' ) . '</li>' .
			'</ul>'
		) );
		get_current_screen()->add_help_tab( array(
			'id'  => 'action-links',
			'title'  => __( 'Available Actions', 'ipt_fsqm_exp' ),
			'content' =>
			'<p>' . __( 'Hovering over a row in the reports list will display action links that allow you to manage your reports. You can perform the following actions:', 'ipt_fsqm_exp' ) . '</p>' .
			'<ul>' .
			'<li>' . __( '<strong>View Persistent Report</strong> will take you to a page from where you can see the report in the same format as <strong>FSQM Pro > Report & Analysis</strong>.', 'ipt_fsqm_exp' ) . '</li>' .
			'<li>' . __( '<strong>Delete</strong> removes your report from this list as well as from the database along with all the downloadable files under it. You can not restore it back, so make sure you want to delete it before you do.', 'ipt_fsqm_exp' ) . '</li>' .
			'</ul>'
		) );
		get_current_screen()->add_help_tab( array(
			'id'  => 'bulk-actions',
			'title'  => __( 'Bulk Actions', 'ipt_fsqm_exp' ),
			'content' =>
			'<p>' . __( 'There are a number of bulk actions available. Here are the details.', 'ipt_fsqm_exp' ) . '</p>' .
			'<ul>' .
			'<li>' . __( '<strong>Delete</strong>. This will permanently delete the ticked reports from the database along with all the downloadable files under it.', 'ipt_fsqm_exp' ) . '</li>' .
			'</ul>'
		) );

		get_current_screen()->set_help_sidebar(
			'<p><strong>' . __( 'For more information:', 'ipt_fsqm_exp' ) . '</strong></p>' .
			'<p>' . sprintf( __( '<a href="%s" target="_blank">Documentation</a>', 'ipt_fsqm_exp' ), IPT_FSQM_EXP_Loader::$documentation ) . '</p>' .
			'<p>' . sprintf( __( '<a href="%s" target="_blank">Support Forums</a>', 'ipt_fsqm_exp' ), IPT_FSQM_EXP_Loader::$support_forum ) . '</p>'
		);

		get_current_screen()->add_help_tab( array(
			'id'  => 'downloads',
			'title'  => __( 'Downloads', 'ipt_fsqm_exp' ),
			'content' =>
			'<p>' . __( 'On the right side of the table, you will see a number of buttons for downloading the reports in various format. Clicking any of them will prompt you to save the report in the mentioned format.', 'ipt_fsqm_exp' ) . '</p>' .
			'<ul>' .
			'<li><strong>' . __( 'XLSX:', 'ipt_fsqm_exp' ) . '</strong> ' . __( 'The report will be converted into an XLSX file. Each of the questions will be created in a different worksheet. For all MCQs, a corresponding chart will be added.', 'ipt_fsqm_exp' ) . '</li>' .
			'<li><strong>' . __( 'PDF:', 'ipt_fsqm_exp' ) . '</strong> ' . __( 'The report will be converted into a PDF file. Charts will be converted into images and will be embedded accordingly.', 'ipt_fsqm_exp' ) . '</li>' .
			'<li><strong>' . __( 'XLS:', 'ipt_fsqm_exp' ) . '</strong> ' . __( 'The report will be converted into an XLS file. Each of the questions will be created in a different worksheet. For MCQs, charts will be converted into images and will be embedded accordingly.', 'ipt_fsqm_exp' ) . '</li>' .
			'<li><strong>' . __( 'HTML:', 'ipt_fsqm_exp' ) . '</strong> ' . __( 'The report will be converted into an HTML file. Charts will be converted into images and will be embedded accordingly.', 'ipt_fsqm_exp' ) . '</li>' .
			'</ul>' .
			'<p>' . __( 'No matter on what format you download, it will be zipped. You have to open the ZIP file using some compression utility in order to view the files inside.', 'ipt_fsqm_exp' ) . '</p>',
		) );

		if ( !empty( $_GET['_wp_http_referer'] ) ) {
			wp_redirect( remove_query_arg( array( '_wp_http_referer', '_wpnonce' ) ) );
			die();
		}

		$option = 'per_page';
		$args = array(
			'label' => __( 'Reports per page', 'ipt_fsqm_exp' ),
			'default' => 20,
			'option' => 'ipt_fsqm_exp_report_per_page',
		);
		add_screen_option( $option, $args );
		parent::on_load_page();
	}

	public function table_set_option( $status, $option, $value ) {
		return $value;
	}
}

class IPT_FSQM_EXP_Export_CSV extends IPT_FSQM_Admin_Base {
	/**
	 * Form utilities object
	 *
	 * @var IPT_FSQM_EXP_Form_Elements_Export_RAW
	 */
	public $export_raw;
	public function __construct() {
		$this->capability = 'manage_feedback';
		$this->action_nonce = 'ipt_fsqm_exp_raw_nonce';
		parent::__construct();

		$this->icon = 'file-excel';
	}

	public function admin_menu() {
		$this->pagehook = add_submenu_page( 'ipt_fsqm_dashboard', __( 'IPT FSQM Export CSV', 'ipt_fsqm_exp' ), __( 'Export to CSV', 'ipt_fsqm_exp' ), $this->capability, 'ipt_fsqm_exp_export_csv', array( $this, 'index' ) );
		parent::admin_menu();
	}

	public function index() {
		$this->export_raw = new IPT_FSQM_EXP_Form_Elements_Export_RAW( null, null, true );
		$this->index_head( __( 'Exporter for FSQM Pro <span class="icon-arrow-right-2"></span> Export to CSV', 'ipt_fsqm_exp' ), false );

		$this->export_raw->wizard();

		$this->index_foot( false );
	}

	public function on_load_page() {
		get_current_screen()->add_help_tab( array(
			'id' => 'overview',
			'title' => __( 'Overview', 'ipt_fsqm_exp' ),
			'content' =>
			'<p>' . __( 'This page provides a nice way to generate and export csv for a particular form.', 'ipt_fsqm_exp' ) . '</p>' .
			'<p>' . __( 'This part of Exporter for FSQM Pro works like a wizard which will guide you through the procedure.', 'ipt_fsqm_exp' ) . '</p>' .
			'<p>' . __( 'Please check other help items for more information.', 'ipt_fsqm_exp' ) . '</p>',
		) );
		get_current_screen()->add_help_tab( array(
			'id' => 'first_step',
			'title' => __( 'Selecting Form', 'ipt_fsqm_exp' ),
			'content' =>
			'<p>' . __( 'In this page you have the following options to get started.', 'ipt_fsqm_exp' ) . '</p>' .
			'<ul>' .
			'<li>' . __( '<strong>Select Form:</strong> Select the form for which you want to generate the report.', 'ipt_fsqm_exp' ) . '</li>' .
			'<li>' . __( '<strong>Server Load:</strong> Select the load on your server. For shared hosts, Medium Load is recommended.', 'ipt_fsqm_exp' ) . '</li>' .
			'<li>' . __( '<strong>Customize CSV Format:</strong> Here you can change the Field Delimiter, Filed Enclosure, Multiple Option Delimiter, Range Delimiter and Multiple Row Delimiter. For maximum compatibility we recommend leaving Field Delimiter and Field Enclosure the way it is.', 'ipt_fsqm_exp' ) . '</li>' .
			'<li>' . __( '<strong>Custom Date Range:</strong> Tick and select a range of date.', 'ipt_fsqm_exp' ) . '</li>' .
			'</ul>' .
			'<p>' . __( 'Once done, simply click on the <strong>Select Questions</strong> button.', 'ipt_fsqm_exp' ) . '</p>'
		) );
		get_current_screen()->add_help_tab( array(
			'id' => 'second_step',
			'title' => __( 'Selecting Questions', 'ipt_fsqm_exp' ),
			'content' =>
			'<p>' . __( 'From this page, you will be able to select questions for which you want to generate the report.', 'ipt_fsqm_exp' ) . '</p>' .
			'<ul>' .
			'<li>' . __( '<strong>Select the Multiple Choice Type Questions:</strong> This will list down all the MCQs in your form in proper order. Select the one you like.', 'ipt_fsqm_exp' ) . '</li>' .
			'<li>' . __( '<strong>Select the Freetype Questions:</strong> This will list down all the freetype questions in your form in proper order. Select the one you like.', 'ipt_fsqm_exp' ) . '</li>' .
			'<li>' . __( '<strong>Select Other Elements:</strong> This will list down all the other questions in your form in proper order. Select the one you like.', 'ipt_fsqm_exp' ) . '</li>' .
			'</ul>'
		) );
		get_current_screen()->add_help_tab( array(
			'id' => 'third_step',
			'title' => __( 'Generate CSV', 'ipt_fsqm_exp' ),
			'content' =>
			'<p>' . __( 'Now all you have to do it wait until the progress bar reaches 100%. Once done, it will show you the download buttons using which you can download the csv file.', 'ipt_fsqm_exp' ) . '</p>',
		) );
		get_current_screen()->set_help_sidebar(
			'<p><strong>' . __( 'For more information:', 'ipt_fsqm_exp' ) . '</strong></p>' .
			'<p>' . sprintf( __( '<a href="%s" target="_blank">Documentation</a>', 'ipt_fsqm_exp' ), IPT_FSQM_EXP_Loader::$documentation ) . '</p>' .
			'<p>' . sprintf( __( '<a href="%s" target="_blank">Support Forums</a>', 'ipt_fsqm_exp' ), IPT_FSQM_EXP_Loader::$support_forum ) . '</p>'
		);
		parent::on_load_page();
	}
}

class IPT_FSQM_EXP_View_All_CSV extends IPT_FSQM_Admin_Base {
	/**
	 * Export table class
	 *
	 * @var IPT_FSQM_EXP_Report_Table
	 */
	public $table_view;
	public function __construct() {
		$this->capability = 'manage_feedback';
		$this->action_nonce = 'ipt_fsqm_exp_view_csv_nonce';
		parent::__construct();

		$this->icon = 'download-2';
		add_filter( 'set-screen-option', array( $this, 'table_set_option' ), 10, 3 );

		$this->post_result[4] = array(
			'type' => 'update',
			'msg' => __( 'Successfully deleted the csv exports.', 'ipt_fsqm_exp' ),
		);
		$this->post_result[5] = array(
			'type' => 'error',
			'msg' => __( 'Please select an action.', 'ipt_fsqm_exp' ),
		);
		$this->post_result[6] = array(
			'type' => 'update',
			'msg' => __( 'Successfully deleted the csv export.', 'ipt_fsqm_exp' ),
		);
		$this->post_result[7] = array(
			'type' => 'error',
			'msg' => __( 'Please select some csv exports to perform the action.', 'ipt_fsqm_exp' ),
		);
		$this->post_result[8] = array(
			'type' => 'error',
			'msg' => __( 'Could not delete the csv export.', 'ipt_fsqm_exp' ),
		);
	}

	public function admin_menu() {
		$this->pagehook = add_submenu_page( 'ipt_fsqm_dashboard', __( 'IPT FSQM View all CSV Exports', 'ipt_fsqm_exp' ), __( 'View all CSV Exports', 'ipt_fsqm_exp' ), $this->capability, 'ipt_fsqm_exp_view_all_csv', array( $this, 'index' ) );
		parent::admin_menu();
	}

	public function index() {
		$this->index_head( __( 'Exporter for FSQM Pro <span class="icon-arrow-right-2"></span> View all CSV Exports', 'ipt_fsqm_exp' )  . '<a href="admin.php?page=ipt_fsqm_exp_export_csv" class="add-new-h2">' . __( 'Add New', 'ipt_fsqm_exp' ) . '</a>', false );
		$this->table_view->prepare_items();
?>
<style type="text/css">
	.wp-list-table .column-downloads {
		width: 300px;
	}
</style>
<div class="ipt_uif_iconbox ipt_uif_shadow glowy">
	<div class="ipt_uif_box cyan">
		<h3><span class="icon-pencil"></span><?php _e( 'Download and/or Delete CSV Exports', 'ipt_fsqm_exp' ); ?></h3>
	</div>
	<div class="ipt_uif_iconbox_inner">
		<form action="" method="get">
			<?php foreach ( $_GET as $k => $v ) : if ( $k == 'order' || $k == 'orderby' || $k == 'page' ) : ?>
			<input type="hidden" name="<?php echo $k; ?>" value="<?php echo $v; ?>" />
			<?php endif; endforeach; ?>
			<?php $this->table_view->display(); ?>
		</form>
	</div>
</div>
		<?php
		$this->index_foot( false );
	}

	public function on_load_page() {
		global $wpdb, $ipt_fsqm_exp_info;
		$this->table_view = new IPT_FSQM_EXP_RAW_Table();

		$action = $this->table_view->current_action();

		if ( $action == 'delete' ) {
			if ( isset( $_GET['id'] ) ) {
				if ( !wp_verify_nonce( $_GET['_wpnonce'], 'ipt_fsqm_exp_csv_delete_' . $_GET['id'] ) ) {
					wp_die( __( 'Cheatin&#8217; uh?' ) );
				}
				if ( IPT_FSQM_EXP_Export_API::delete_raw( $_GET['id'] ) ) {
					wp_redirect( add_query_arg( array( 'post_result' => '6' ), 'admin.php?page=ipt_fsqm_exp_view_all_csv' ) );
				} else {
					wp_redirect( add_query_arg( array( 'post_result' => '8' ), 'admin.php?page=ipt_fsqm_exp_view_all_csv' ) );
				}
			} else {
				if ( !wp_verify_nonce( $_GET['_wpnonce'], 'bulk-ipt_fsqm_exp_csv_items' ) ) {
					wp_die( __( 'Cheatin&#8217; uh?' ) );
				}
				if ( !isset( $_GET['csvs'] ) || empty( $_GET['csvs'] ) ) {
					wp_redirect( add_query_arg( array( 'post_result' => '7' ), $_GET['_wp_http_referer'] ) );
				}

				if ( IPT_FSQM_EXP_Export_API::delete_raw( $_GET['csvs'] ) ) {
					wp_redirect( add_query_arg( array( 'post_result' => '4' ), $_GET['_wp_http_referer'] ) );
				} else {
					wp_redirect( add_query_arg( array( 'post_result' => '8' ), $_GET['_wp_http_referer'] ) );
				}
			}
			die();
		}

		if ( !empty( $_GET['_wp_http_referer'] ) ) {
			wp_redirect( remove_query_arg( array( '_wp_http_referer', '_wpnonce' ) ) );
			die();
		}

		$option = 'per_page';
		$args = array(
			'label' => __( 'CSV exports per page', 'ipt_fsqm_exp' ),
			'default' => 20,
			'option' => 'ipt_fsqm_exp_csv_per_page',
		);
		add_screen_option( $option, $args );

		get_current_screen()->add_help_tab( array(
			'id'  => 'overview',
			'title'  => __( 'Overview', 'ipt_fsqm_exp' ),
			'content' =>
			'<p>' . __( 'This screen provides access to all of your CSV Exports. You can customize the display of this screen to suit your workflow.', 'ipt_fsqm_exp' ) . '</p>' .
			'<p>' . __( 'By default, this screen will show all the CSV Exports. Please check the Screen Content for more information.', 'ipt_fsqm_exp' ) . '</p>'
		) );
		get_current_screen()->add_help_tab( array(
			'id'  => 'screen-content',
			'title'  => __( 'Screen Content', 'ipt_fsqm_exp' ),
			'content' =>
			'<p>' . __( 'You can customize the display of this screen&#8217;s contents in a number of ways:' ) . '</p>' .
			'<ul>' .
			'<li>' . __( 'You can sort CSV Exports based on created, start date or end date.', 'ipt_fsqm_exp' ) . '</li>' .
			'<li>' . __( 'You can hide/display columns based on your needs and decide how many CSV Exports to list per screen using the Screen Options tab.', 'ipt_fsqm_exp' ) . '</li>' .
			'</ul>'
		) );
		get_current_screen()->add_help_tab( array(
			'id'  => 'action-links',
			'title'  => __( 'Available Actions', 'ipt_fsqm_exp' ),
			'content' =>
			'<p>' . __( 'Hovering over a row in the CSV Exports list will display action links that allow you to manage your CSV Exports. You can perform the following actions:', 'ipt_fsqm_exp' ) . '</p>' .
			'<ul>' .
			'<li>' . __( '<strong>Delete</strong> removes your CSV Export from this list as well as from the database along with all the downloadable files under it. You can not restore it back, so make sure you want to delete it before you do.', 'ipt_fsqm_exp' ) . '</li>' .
			'</ul>'
		) );
		get_current_screen()->add_help_tab( array(
			'id'  => 'bulk-actions',
			'title'  => __( 'Bulk Actions', 'ipt_fsqm_exp' ),
			'content' =>
			'<p>' . __( 'There are a number of bulk actions available. Here are the details.', 'ipt_fsqm_exp' ) . '</p>' .
			'<ul>' .
			'<li>' . __( '<strong>Delete</strong>. This will permanently delete the ticked CSV Exports from the database along with all the downloadable files under it.', 'ipt_fsqm_exp' ) . '</li>' .
			'</ul>'
		) );

		get_current_screen()->add_help_tab( array(
			'id'  => 'downloads',
			'title'  => __( 'Download', 'ipt_fsqm_exp' ),
			'content' =>
			'<p>' . __( 'Simply click on the <strong>Download CSV</strong> button on the right side of the table. This will prompt you to save the csv file.', 'ipt_fsqm_exp' ) . '</p>',
		) );

		get_current_screen()->set_help_sidebar(
			'<p><strong>' . __( 'For more information:', 'ipt_fsqm_exp' ) . '</strong></p>' .
			'<p>' . sprintf( __( '<a href="%s" target="_blank">Documentation</a>', 'ipt_fsqm_exp' ), IPT_FSQM_EXP_Loader::$documentation ) . '</p>' .
			'<p>' . sprintf( __( '<a href="%s" target="_blank">Support Forums</a>', 'ipt_fsqm_exp' ), IPT_FSQM_EXP_Loader::$support_forum ) . '</p>'
		);
		parent::on_load_page();
	}

	public function table_set_option( $status, $option, $value ) {
		return $value;
	}
}

class IPT_FSQM_EXP_Settings extends IPT_FSQM_Admin_Base {
	public function __construct() {
		$this->capability = 'manage_feedback';
		$this->action_nonce = 'ipt_fsqm_exp_settings';
		parent::__construct();

		$this->icon = 'cog-2 ';
	}

	public function admin_menu() {
		$this->pagehook = add_submenu_page( 'ipt_fsqm_dashboard', __( 'Exporter for IPT FSQM - Settings', 'ipt_fsqm_exp' ), __( 'Exporter Settings', 'ipt_fsqm_exp' ), $this->capability, 'ipt_fsqm_exp_settings', array( $this, 'index' ) );
		parent::admin_menu();
	}

	public function index() {
		global $ipt_fsqm_exp_settings;
		$this->index_head( __( 'Exporter for FSQM Pro <span class="icon-arrow-right-2"></span> Settings', 'ipt_fsqm_exp' ), true );
?>
<style type="text/css">
	textarea.widefat {
		height: 300px;
	}
</style>
<div class="ipt_uif_iconbox ipt_uif_shadow glowy">
	<div class="ipt_uif_box cyan">
		<h3><span class="icon-settings"></span><?php _e( 'Modify Exporter Settings', 'ipt_fsqm_exp' ); ?></h3>
	</div>
	<div class="ipt_uif_iconbox_inner">
		<table class="form-table">
			<tbody>
				<tr>
					<th><?php $this->ui->generate_label( 'settings[html_header]', __( 'PDF/HTML Header', 'ipt_fsqm_exp' ) ); ?></th>
					<td><?php wp_editor( $ipt_fsqm_exp_settings['html_header'], 'settings_html_header', array(
						'textarea_name' => 'settings[html_header]',
					) ); ?></td>
					<td><?php $this->ui->help( __( 'You can insert HTML to the header of your PDFs or HTMLs the exporter generates.', 'ipt_fsqm_exp' ) ); ?></td>
				</tr>
				<tr>
					<th><?php $this->ui->generate_label( 'settings[html_footer]', __( 'PDF/HTML Footer', 'ipt_fsqm_exp' ) ); ?></th>
					<td><?php wp_editor( $ipt_fsqm_exp_settings['html_footer'], 'settings_html_footer', array(
						'textarea_name' => 'settings[html_footer]',
					) ); ?></td>
					<td><?php $this->ui->help( __( 'You can insert HTML to the footer of your PDFs or HTMLs the exporter generates.', 'ipt_fsqm_exp' ) ); ?></td>
				</tr>
				<tr>
					<th><?php $this->ui->generate_label( 'settings[style]', __( 'CSS Style', 'ipt_fsqm_exp' ) ); ?></th>
					<td><?php $this->ui->textarea( 'settings[style]', $ipt_fsqm_exp_settings['style'], __( 'Please fill in some style', 'ipt_fsqm_exp' ), 'widefat', 'normal', array( 'code' ) ); ?></td>
					<td>
						<?php $this->ui->help_head(); ?>
						<p><?php _e( 'If you know about CSS then you can style your HTML or PDF exports here. The default code is as follows:', 'ipt_fsqm_exp' ); ?></p>
						<pre style="max-height: 300px; overflow: auto;"><code>/* Main Page */
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
}</code></pre>
						<?php $this->ui->help_tail(); ?>
					</td>
				</tr>
				<tr>
					<th><?php $this->ui->generate_label( 'settings[memory]', __( 'Set PHP Memory (in Megabytes)', 'ipt_fsqm_exp' ) ); ?></th>
					<td><?php $this->ui->spinner( 'settings[memory]', $ipt_fsqm_exp_settings['memory'], __( 'Disabled', 'ipt_fsqm_exp' ), '128', '', '16' ); ?></td>
					<td><?php $this->ui->help( __( 'Use this to increase your PHP memory on the fly. Please make sure you have more or at least same physical memory before setting it to a higher value. It may not work at all for shared hosts. For more, please consult the documentation.', 'ipt_fsqm_exp' ) ); ?></td>
				</tr>
				<tr>
					<th><?php $this->ui->generate_label( 'settings[execution_time]', __( 'Set PHP Execution time (in Seconds)', 'ipt_fsqm_exp' ) ); ?></th>
					<td><?php $this->ui->spinner( 'settings[execution_time]', $ipt_fsqm_exp_settings['execution_time'], __( 'Disabled', 'ipt_fsqm_exp' ), '100', '', '10' ); ?></td>
					<td><?php $this->ui->help( __( 'Use this to increase your PHP execution_time on the fly. For more, please consult the documentation.', 'ipt_fsqm_exp' ) ); ?></td>
				</tr>
				<tr>
					<th><?php $this->ui->generate_label( 'settings[download_pdf]', __( 'Show Download as PDF on Trackback Pages', 'ipt_fsqm_exp' ) ); ?></th>
					<td><?php $this->ui->toggle( 'settings[download_pdf]', __( 'Yes', 'ipt_fsqm_exp' ), __( 'No', 'ipt_fsqm_exp' ), $ipt_fsqm_exp_settings['download_pdf'] ); ?></td>
					<td><?php $this->ui->help( __( 'If you want your users to download PDFs from the trackback pages, then turn this feature on. Please note that only the summarized format is downloadable, not the whole form.', 'ipt_fsqm_exp' ) ); ?></td>
				</tr>
				<tr>
					<th><?php $this->ui->generate_label( 'settings[delete_uninstall]', __( 'Delete all Data when uninstalling plugin', 'ipt_fsqm_exp' ) ); ?></th>
					<td><?php $this->ui->toggle( 'settings[delete_uninstall]', __( 'Yes', 'ipt_fsqm_exp' ), __( 'No', 'ipt_fsqm_exp' ), $ipt_fsqm_exp_settings['delete_uninstall'] ); ?></td>
					<td><?php $this->ui->help( __( 'If you want to completely wipe out all data when uninstalling, then have this enabled. Keep it disabled, if you are planning to update the plugin by uninstalling and then reinstalling.', 'ipt_fsqm_exp' ) ); ?></td>
				</tr>
			</tbody>
		</table>
	</div>
</div>
		<?php
		$this->index_foot( true, __( 'Save Changes', 'ipt_fsqm_exp' ), __( 'Reset', 'ipt_fsqm_exp' ) );
	}

	public function save_post() {
		parent::save_post();

		$settings = $this->post['settings'];

		$settings['memory'] = (int) $settings['memory'];
		$settings['execution_time'] = (int) $settings['execution_time'];
		$settings['download_pdf'] = isset( $settings['download_pdf'] ) ? true : false;
		$settings['delete_uninstall'] = isset( $settings['delete_uninstall'] ) ? true : false;

		if ( $settings['memory'] < 128 ) {
			$settings['memory'] = 128;
		}

		if ( $settings['execution_time'] < 100 ) {
			$settings['execution_time'] = 100;
		}

		update_option( 'ipt_fsqm_exp_settings', $settings );

		wp_redirect( add_query_arg( 'post_result', '1', $_POST['_wp_http_referer'] ) );

		die();
	}

	public function on_load_page() {
		get_current_screen()->add_help_tab( array(
			'id'  => 'overview',
			'title'  => __( 'Overview', 'ipt_fsqm_exp' ),
			'content' =>
			'<p>' . __( 'This screen provides customization options for the plugin.', 'ipt_fsqm_exp' ) . '</p>' .
			'<p>' . __( 'You can modify the PDF/HTML headers, footers and styles. Also, you can manage PHP resources easily through this page.', 'ipt_fsqm_exp' ) . '</p>'
		) );
		get_current_screen()->add_help_tab( array(
			'id'  => 'credits',
			'title'  => __( 'Credits', 'ipt_fsqm_exp' ),
			'content' =>
			'<p>' . __( ' Thank you for purchasing Exporter for FSQM Pro. The following Tools were used during the development of this project.', 'ipt_fsqm_exp' ) . '</p>' .
			'<ul>' .
			'<li>' . __( '<a href="http://phpexcel.codeplex.com/">PHPExcel</a>: We forked PHPExcel to provide the XLSX, XLS, PDF and HTML downloads.', 'ipt_fsqm_exp' ) . '</li>' .
			'<li>' . __( '<a href="http://www.mpdf1.com/mpdf/index.php">mPDF</a>: For generating all PDFs.', 'ipt_fsqm_exp' ) . '</li>' .
			'<li>' . __( '<a href="http://jpgraph.net/">jpGraph</a>: For rendering all static charts.', 'ipt_fsqm_exp' ) . '</li>' .
			'<li>' . __( '<a href="http://wordpress.org/">WordPress</a>: The best platform ever.', 'ipt_fsqm_exp' ) . '</li>' .
			'</ul>'
		) );
		get_current_screen()->set_help_sidebar(
			'<p><strong>' . __( 'For more information:', 'ipt_fsqm_exp' ) . '</strong></p>' .
			'<p>' . sprintf( __( '<a href="%s" target="_blank">Documentation</a>', 'ipt_fsqm_exp' ), IPT_FSQM_EXP_Loader::$documentation ) . '</p>' .
			'<p>' . sprintf( __( '<a href="%s" target="_blank">Support Forums</a>', 'ipt_fsqm_exp' ), IPT_FSQM_EXP_Loader::$support_forum ) . '</p>'
		);
		parent::on_load_page();
	}
}

/*==============================================================================
 * List Tables
 *============================================================================*/
/**
 * Get the WP_List_Table for populating our table
 */
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class IPT_FSQM_EXP_Report_Table extends WP_List_Table {
	public function __construct() {
		parent::__construct( array(
				'singular' => 'ipt_fsqm_exp_report_item',
				'plural' => 'ipt_fsqm_exp_report_items',
				'ajax' => false,
			) );
	}

	public function get_columns() {
		$columns = array(
			'cb' => '<input type="checkbox" />',
			'title' => __( 'Name', 'ipt_fsqm_exp' ),
			'created' => __( 'Created', 'ipt_fsqm_exp' ),
			'start_date' => __( 'Start Date', 'ipt_fsqm_exp' ),
			'end_date' => __( 'End Date', 'ipt_fsqm_exp' ),
			'downloads' => __( 'Downloads', 'ipt_fsqm_exp' ),
		);
		return $columns;
	}

	public function get_sortable_columns() {
		$sortable = array(
			'title' => array( 'f.name', false ),
			'created' => array( 'r.created', false ),
			'start_date' => array( 'r.start_date', true ),
			'end_date' => array( 'r.end_date', true ),
		);
		return $sortable;
	}

	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
		case 'title' :
			$actions = array(
				'view' => sprintf( '<a href="admin.php?page=ipt_fsqm_exp_view_all_reports&id=%d">%s</a>', (int) $item['id'], __( 'View Persistent Report', 'ipt_fsqm_exp' ) ),
				'delete' => '<a class="delete" href="' . wp_nonce_url( '?page=' . $_REQUEST['page'] . '&action=delete&id=' . $item['id'], 'ipt_fsqm_exp_report_delete_' . $item['id'] ) . '">' . __( 'Delete', 'ipt_fsqm_exp' ) . '</a>',
			);
			return sprintf( '%1$s %2$s', '<strong><a href="admin.php?page=ipt_fsqm_exp_view_all_reports&id=' . $item['id'] . '">' . $item['name'] . '</a></strong>', $this->row_actions( $actions ) );
			break;
		case 'created' :
			return date_i18n( get_option( 'date_format' ) . __( ', ', 'ipt_fsqm_exp' ) . get_option( 'time_format' ), strtotime( $item['created'] ) );
			break;
		case 'start_date' :
			if ( $item['start_date'] == '0000-00-00 00:00:00' ) {
				return __( 'N/A', 'ipt_fsqm_exp' );
			} else {
				return date_i18n( get_option( 'date_format' ) . __( ', ', 'ipt_fsqm_exp' ) . get_option( 'time_format' ), strtotime( $item['start_date'] ) );
			}
			break;
		case 'end_date' :
			if ( $item['end_date'] == '0000-00-00 00:00:00' ) {
				return __( 'N/A', 'ipt_fsqm_exp' );
			} else {
				return date_i18n( get_option( 'date_format' ) . __( ', ', 'ipt_fsqm_exp' ) . get_option( 'time_format' ), strtotime( $item['end_date'] ) );
			}
			break;
		case 'downloads' :
			$nonce = wp_create_nonce( 'ipt_fsqm_exp_export_report_to_file_' . $item['id'] );
			$links = array();
			$available_export_formats = IPT_FSQM_EXP_Export_API::get_available_export_formats();
			foreach ( $available_export_formats as $format ) {
				$links[$format] = admin_url( 'admin-ajax.php?action=ipt_fsqm_exp_export_report_to_file&type=' . $format . '&id=' . $item['id'] . '&_wpnonce=' . $nonce );
			}
			return IPT_FSQM_EXP_Export_API::render_available_export_formats_buttons( $links );
			break;
		default :
			print_r( $item );
		}
	}

	public function column_cb( $item ) {
		return sprintf( '<input type="checkbox" name="reports[]" value="%s" />', $item['id'] );
	}

	public function get_bulk_actions() {
		$actions = array(
			'delete' => __( 'Delete' ),
		);
		return $actions;
	}

	public function prepare_items() {
		global $wpdb, $ipt_fsqm_info, $ipt_fsqm_exp_info;

		//First delete any underlying incomplete report
		$wpdb->query( "DELETE FROM {$ipt_fsqm_exp_info['exp_table']} WHERE complete != 1" );

		//prepare the query
		$query = "SELECT r.id id, f.name name, r.created created, r.start_date start_date, r.end_date end_date FROM {$ipt_fsqm_exp_info['exp_table']} r LEFT JOIN {$ipt_fsqm_info['form_table']} f ON r.form_id = f.id";

		$orderby = !empty( $_GET['orderby'] ) ? esc_sql( $_GET['orderby'] ) : 'r.created';
		$order = !empty( $_GET['order'] ) ? esc_sql( $_GET['order'] ) : 'desc';
		$where = '';
		$wheres = array();

		if ( isset( $_GET['form_id'] ) && !empty( $_GET['form_id'] ) ) {
			$wheres[] = $wpdb->prepare( "form_id = %d", $_GET['form_id'] );
		}

		if ( !empty( $wheres ) ) {
			$where .= ' WHERE ' . implode( ' AND ', $wheres );
		}

		$query .= $where;

		//pagination
		$totalitems = $wpdb->get_var( "SELECT COUNT(id) FROM {$ipt_fsqm_exp_info['exp_table']} r{$where}" );
		$perpage = $this->get_items_per_page( 'ipt_fsqm_exp_report_per_page', 20 );
		$totalpages = ceil( $totalitems/$perpage );

		$this->set_pagination_args( array(
				'total_items' => $totalitems,
				'total_pages' => $totalpages,
				'per_page' => $perpage,
			) );

		$current_page = $this->get_pagenum();

		//pur pagination and order on the query
		$query .= ' ORDER BY ' . $orderby .  ' ' . $order . ' LIMIT ' . ( ( $current_page - 1 ) * $perpage ) . ',' . (int) $perpage;

		//register the columns
		$this->_column_headers = $this->get_column_info();

		//fetch the items
		$this->items = $wpdb->get_results( $query, ARRAY_A );
	}

	public function no_items() {
		_e( 'You have not generated any reports yet.', 'ipt_fsqm_exp' );
	}

	public function extra_tablenav( $which ) {
		global $wpdb, $ipt_fsqm_info;
		$forms = $wpdb->get_results( "SELECT id, name FROM {$ipt_fsqm_info['form_table']}" );

		switch ( $which ) {
		case 'top' :
?>
<div class="alignleft actions">
	<select name="form_id">
		<option value=""<?php if ( !isset( $_GET['form_id'] ) || empty( $_GET['form_id'] ) ) echo ' selected="selected"'; ?>><?php _e( 'Show all forms', 'ipt_fsqm_exp' ); ?></option>
		<?php if ( null != $forms ) : ?>
		<?php foreach ( $forms as $form ) : ?>
		<option value="<?php echo $form->id; ?>"<?php if ( isset( $_GET['form_id'] ) && $_GET['form_id'] == $form->id ) echo ' selected="selected"'; ?>><?php echo $form->name; ?></option>
		<?php endforeach; ?>
		<?php else : ?>
		<option value=""><?php _e( 'No Forms in the database', 'ipt_fsqm_exp' ); ?></option>
		<?php endif; ?>
	</select>
	<?php submit_button( __( 'Filter' ), 'secondary', false, false, array( 'id' => 'form-query-submit' ) ); ?>
</div>
				<?php
			break;
		case 'bottom' :
			echo '<div class="alignleft"><p>';
			_e( 'You can also use these reports to show persistent trends on your site. The FSQM shortcode generator button on the editor will guide you through the process.', 'ipt_fsqm_exp' );
			echo '</p></div>';
		}
	}
}

class IPT_FSQM_EXP_RAW_Table extends WP_List_Table {
	public function __construct() {
		parent::__construct( array(
				'singular' => 'ipt_fsqm_exp_csv_item',
				'plural' => 'ipt_fsqm_exp_csv_items',
				'ajax' => false,
			) );
	}

	public function get_columns() {
		$columns = array(
			'cb' => '<input type="checkbox" />',
			'title' => __( 'Name', 'ipt_fsqm_exp' ),
			'created' => __( 'Created', 'ipt_fsqm_exp' ),
			'start_date' => __( 'Start Date', 'ipt_fsqm_exp' ),
			'end_date' => __( 'End Date', 'ipt_fsqm_exp' ),
			'downloads' => __( 'Download', 'ipt_fsqm_exp' ),
		);
		return $columns;
	}

	public function get_sortable_columns() {
		$sortable = array(
			'title' => array( 'f.name', false ),
			'created' => array( 'c.created', false ),
			'start_date' => array( 'c.start_date', true ),
			'end_date' => array( 'c.end_date', true ),
		);
		return $sortable;
	}

	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
		case 'title' :
			$actions = array(
				'delete' => '<a class="delete" href="' . wp_nonce_url( '?page=' . $_REQUEST['page'] . '&action=delete&id=' . $item['id'], 'ipt_fsqm_exp_csv_delete_' . $item['id'] ) . '">' . __( 'Delete', 'ipt_fsqm_exp' ) . '</a>',
			);
			return sprintf( '%1$s %2$s', '<strong><a href="' . wp_nonce_url( 'admin-ajax.php?action=ipt_fsqm_exp_csv_download&id=' . $item['id'], 'ipt_fsqm_exp_csv_download_' . $item['id'] ) . '">' . $item['name'] . '</a></strong>', $this->row_actions( $actions ) );
			break;
		case 'created' :
			return date_i18n( get_option( 'date_format' ) . __( ', ', 'ipt_fsqm_exp' ) . get_option( 'time_format' ), strtotime( $item['created'] ) );
			break;
		case 'start_date' :
			if ( $item['start_date'] == '0000-00-00 00:00:00' ) {
				return __( 'N/A', 'ipt_fsqm_exp' );
			} else {
				return date_i18n( get_option( 'date_format' ) . __( ', ', 'ipt_fsqm_exp' ) . get_option( 'time_format' ), strtotime( $item['start_date'] ) );
			}
			break;
		case 'end_date' :
			if ( $item['end_date'] == '0000-00-00 00:00:00' ) {
				return __( 'N/A', 'ipt_fsqm_exp' );
			} else {
				return date_i18n( get_option( 'date_format' ) . __( ', ', 'ipt_fsqm_exp' ) . get_option( 'time_format' ), strtotime( $item['end_date'] ) );
			}
			break;
		case 'downloads' :
			$ui = IPT_Plugin_UIF_Admin::instance( 'ipt_fsqm_exp' );
			$buttons = array();
			$buttons[] = array(
				__( 'Download CSV', 'ipt_fsqm_exp' ),
				'ipt_fsqm_export_download_' . $item['id'],
				'small',
				'secondary',
				'normal',
				array(),
				'anchor',
				array(),
				array(),
				wp_nonce_url( 'admin-ajax.php?action=ipt_fsqm_exp_csv_download&id=' . $item['id'], 'ipt_fsqm_exp_csv_download_' . $item['id'] ),
				'file-excel',
			);
			$ui->buttons( $buttons );
			break;
		default :
			print_r( $item );
		}
	}

	public function column_cb( $item ) {
		return sprintf( '<input type="checkbox" name="csvs[]" value="%s" />', $item['id'] );
	}

	public function get_bulk_actions() {
		$actions = array(
			'delete' => __( 'Delete' ),
		);
		return $actions;
	}

	public function prepare_items() {
		global $wpdb, $ipt_fsqm_info, $ipt_fsqm_exp_info;

		//First delete any underlying incomplete csv
		IPT_FSQM_EXP_Export_API::delete_raw( $wpdb->get_col( "SELECT id FROM {$ipt_fsqm_exp_info['raw_table']} WHERE complete != 1" ) );

		//prepare the query
		$query = "SELECT c.id id, f.name name, c.created created, c.start_date start_date, c.end_date end_date FROM {$ipt_fsqm_exp_info['raw_table']} c LEFT JOIN {$ipt_fsqm_info['form_table']} f ON c.form_id = f.id";

		$orderby = !empty( $_GET['orderby'] ) ? esc_sql( $_GET['orderby'] ) : 'c.created';
		$order = !empty( $_GET['order'] ) ? esc_sql( $_GET['order'] ) : 'desc';
		$where = '';
		$wheres = array();

		if ( isset( $_GET['form_id'] ) && !empty( $_GET['form_id'] ) ) {
			$wheres[] = $wpdb->prepare( "form_id = %d", $_GET['form_id'] );
		}

		if ( !empty( $wheres ) ) {
			$where .= ' WHERE ' . implode( ' AND ', $wheres );
		}

		$query .= $where;

		//pagination
		$totalitems = $wpdb->get_var( "SELECT COUNT(id) FROM {$ipt_fsqm_exp_info['raw_table']} c{$where}" );
		$perpage = $this->get_items_per_page( 'ipt_fsqm_exp_csv_per_page', 20 );
		$totalpages = ceil( $totalitems/$perpage );

		$this->set_pagination_args( array(
				'total_items' => $totalitems,
				'total_pages' => $totalpages,
				'per_page' => $perpage,
			) );

		$current_page = $this->get_pagenum();

		//pur pagination and order on the query
		$query .= ' ORDER BY ' . $orderby .  ' ' . $order . ' LIMIT ' . ( ( $current_page - 1 ) * $perpage ) . ',' . (int) $perpage;

		//register the columns
		$this->_column_headers = $this->get_column_info();

		//fetch the items
		$this->items = $wpdb->get_results( $query, ARRAY_A );
	}

	public function no_items() {
		_e( 'You have not created any csv export yet.', 'ipt_fsqm_exp' );
	}

	public function extra_tablenav( $which ) {
		global $wpdb, $ipt_fsqm_info;
		$forms = $wpdb->get_results( "SELECT id, name FROM {$ipt_fsqm_info['form_table']}" );

		switch ( $which ) {
		case 'top' :
?>
<div class="alignleft actions">
	<select name="form_id">
		<option value=""<?php if ( !isset( $_GET['form_id'] ) || empty( $_GET['form_id'] ) ) echo ' selected="selected"'; ?>><?php _e( 'Show all forms', 'ipt_fsqm_exp' ); ?></option>
		<?php if ( null != $forms ) : ?>
		<?php foreach ( $forms as $form ) : ?>
		<option value="<?php echo $form->id; ?>"<?php if ( isset( $_GET['form_id'] ) && $_GET['form_id'] == $form->id ) echo ' selected="selected"'; ?>><?php echo $form->name; ?></option>
		<?php endforeach; ?>
		<?php else : ?>
		<option value=""><?php _e( 'No Forms in the database', 'ipt_fsqm_exp' ); ?></option>
		<?php endif; ?>
	</select>
	<?php submit_button( __( 'Filter' ), 'secondary', false, false, array( 'id' => 'form-query-submit' ) ); ?>
</div>
				<?php
			break;
		}
	}
}
