<?php
/**
 * IPT FSQM Admin
 * The library of all the administration classes
 *
 * @author Swashata <swashata4u@gmail.com>
 * @package WP Feedback, Survey & Quiz Manager - Pro
 * @subpackage Admin Backend classes
 * @version 2.1.4
 */

/*==============================================================================
 * Admin Classes
 *============================================================================*/
/**
 * Dashboard class
 */
class IPT_FSQM_Dashboard extends IPT_FSQM_Admin_Base {
	public function __construct() {
		$this->capability = 'view_feedback';
		$this->action_nonce = 'ipt_fsqm_dashboard_nonce';

		parent::__construct();

		$this->icon = 'dashboard';
	}

	/*==========================================================================
	 * SYSTEM METHODS
	 *========================================================================*/

	public function admin_menu() {
		$this->pagehook = add_object_page( __( 'WP Feedback, Survey & Quiz Manager - Pro', 'ipt_fsqm' ), __( 'FSQM Pro', 'ipt_fsqm' ), $this->capability, 'ipt_fsqm_dashboard', array( &$this, 'index' ), 'div' );
		add_submenu_page( 'ipt_fsqm_dashboard', __( 'WP Feedback, Survey & Quiz Manager - Pro', 'ipt_fsqm' ), __( 'Dashboard', 'ipt_fsqm' ), $this->capability, 'ipt_fsqm_dashboard', array( &$this, 'index' ) );
		parent::admin_menu();
	}
	public function index() {
		$this->index_head( __( 'WP Feedback, Survey & Quiz Manager Pro <span class="ipt-icomoon-arrow-right2"></span> Dashboard', 'ipt_fsqm' ), false );
?>
<script type="text/javascript">
	jQuery(document).ready(function($) {
		var protocol = window.location.protocol;
		$.getScript(protocol + '//www.google.com/jsapi', function() {
			google.load('visualization', '1.0', {
				packages : ['corechart'],
				callback : function() {
					if ( typeof( drawLatestTen ) == 'function' ) {
						drawLatestTen();
					}
					if ( typeof( drawOverallPie ) == 'function' ) {
						drawOverallPie();
					}
				}
			});
		});
	});
</script>
<div class="ipt_uif_left_col"><div class="ipt_uif_col_inner">
	<?php $this->ui->iconbox( __( 'Latest Submission Statistics', 'ipt_fsqm' ), array( $this, 'meta_stat' ), 'stats' ); ?>
</div></div>
<div class="ipt_uif_right_col"><div class="ipt_uif_col_inner">
	<?php $this->ui->iconbox( __( 'Overall Submission Statistics', 'ipt_fsqm' ), array( $this, 'meta_overall' ), 'pie' ); ?>
</div></div>
<div class="clear"></div>
<?php $this->ui->iconbox( __( 'Latest 10 Submissions', 'ipt_fsqm' ), array( $this, 'meta_ten' ), 'list2' ); ?>
<div class="clear"></div>
<div class="ipt_uif_left_col"><div class="ipt_uif_col_inner">
	<?php $this->ui->iconbox( __( 'Thank You!', 'ipt_fsqm' ), array( $this, 'meta_thank_you' ), 'thumbs-up' ); ?>
</div></div>
<div class="ipt_uif_right_col"><div class="ipt_uif_col_inner">
	<?php $this->ui->iconbox( __( 'Stay Connected', 'ipt_fsqm' ), array( $this, 'meta_social' ), 'share' ); ?>
</div></div>
<div class="clear"></div>
<?php $this->ui->iconbox( __( 'Generate Embed Code for Standalone Forms', 'ipt_fsqm' ), array( $this, 'meta_embed_generator' ), 'embed' ); ?>
<div class="clear"></div>
		<?php
		$this->index_foot( false );
	}


	/*==========================================================================
	 * METABOX CB
	 *========================================================================*/

	public function meta_embed_generator() {
		$forms = IPT_FSQM_Form_Elements_Static::get_forms();
		if ( null == $forms || empty( $forms ) ) {
			$this->ui->msg_error( __( 'You have not created any forms yet.', 'ipt_fsqm' ) );
			return;
		}
		$default_permalink = IPT_FSQM_Form_Elements_Static::standalone_permalink_parts( $forms[0]->id );
		$default_code = '<iframe src="' . $default_permalink['url'] . '" width="960" height="480" style="width: 960px; height: 480px; border: 0 none; overflow-y: auto;" frameborder="0">&nbsp;</iframe>';
		$items = array();
		foreach ( $forms as $form ) {
			$items[] = array(
				'label' => $form->name,
				'value' => $form->id,
			);
		}
		?>
<table class="form-table" id="embed_generator_table">
	<tbody>
		<tr>
			<th>
				<?php $this->ui->generate_label( 'standalone_form_id', __( 'Select Form', 'ipt_fsqm' ) ); ?>
			</th>
			<td>
				<div class="ipt_uif_float_right" style="height: 68px; width: 200px;">
					<?php $this->ui->ajax_loader( true, 'ipt_fsqm_embed_generator_al', array(), true ); ?>
				</div>
				<?php $this->ui->select( 'standalone_form_id', $items, '' ); ?>
			</td>
			<td>
				<?php $this->ui->help_head( __( 'Embed Code', 'ipt_fsqm' ) ); ?>
				<p><?php _e( 'Embed codes are useful for embedding your forms on some external sites. Think of it as a YouTube share/embed code.', 'ipt_fsqm' ); ?></p>
				<p><?php _e( 'To use it simply select a form and select width and height. The system will generate the code automatically. Press <kbd>Ctrl</kbd> + <kbd>c</kbd> to copy. Paste it where you want.' ) ?></p>
				<p><?php _e( 'You can also use the URL to link to the standalone page.', 'ipt_fsqm' ); ?></p>
				<?php $this->ui->help_tail(); ?>
			</td>
		</tr>
		<tr>
			<th>
				<?php $this->ui->generate_label( 'standalone_width', __( 'Width', 'ipt_fsqm' ) ); ?>
			</th>
			<td colspan="2">
				<?php $this->ui->slider( 'standalone_width', '960', '320', '2560', '20' ); ?>
			</td>
		</tr>
		<tr>
			<th>
				<?php $this->ui->generate_label( 'standalone_height', __( 'Height', 'ipt_fsqm' ) ); ?>
			</th>
			<td colspan="2">
				<?php $this->ui->slider( 'standalone_height', '480', '320', '2560', '20' ); ?>
			</td>
		</tr>
		<tr>
			<th>
				<?php $this->ui->generate_label( 'standalone_permalink', __( 'Permalink', 'ipt_fsqm' ) ); ?>
			</th>
			<td colspan="2">
				<?php $this->ui->text( 'standalone_permalink', $default_permalink['url'], __( 'Adjust settings to update this', 'ipt_fsqm' ), 'large', 'normal', 'code' ) ?>
			</td>
		</tr>
		<tr>
			<th>
				<?php $this->ui->generate_label( 'standalone_shortlink', __( 'Short Link', 'ipt_fsqm' ) ); ?>
			</th>
			<td colspan="2">
				<?php $this->ui->text( 'standalone_shortlink', $default_permalink['shortlink'], __( 'Adjust settings to update this', 'ipt_fsqm' ), 'large', 'normal', 'code' ) ?>
			</td>
		</tr>
		<tr>
			<th>
				<?php $this->ui->generate_label( 'standalone_code', __( 'Embed Code', 'ipt_fsqm' ) ); ?>
			</th>
			<td colspan="2">
				<?php $this->ui->textarea( 'standalone_code', $default_code, __( 'Adjust settings to update this', 'ipt_fsqm' ), 'widefat', 'normal', 'code' ); ?>
			</td>
		</tr>
	</tbody>
</table>
<div class="clear"></div>
<script type="text/javascript">
	jQuery(document).ready(function($) {
		$('#standalone_code, #standalone_permalink, #standalone_shortlink').on('focus', function() {
			var $this = $(this);
			$this.select();

			$this.on('mouseup', function() {
				$this.off('mouseup');
				return false;
			});
		});
		$('#standalone_form_id').on('change keyup', function() {
			generate_embed();
		});

		$('#embed_generator_table').on('slidestop', function() {
			generate_embed();
		});

		var generate_embed = function() {
			var form_id = $('#standalone_form_id').val(),
			width = $('#standalone_width').val(),
			height = $('#standalone_height').val(),
			permalink = $('#standalone_permalink'),
			shortlink = $('#standalone_shortlink'),
			code = $('#standalone_code'),
			ajax_loader = $('#ipt_fsqm_embed_generator_al'),
			self = $(this);

			// Get the query parameters
			var data = {
				action : 'ipt_fsqm_standalone_embed_generate',
				form_id : form_id
			};

			ajax_loader.fadeIn('fast');

			// Query it
			$.get(ajaxurl, data, function(response) {
				if ( response == false || response === null ) {
					alert('Invalid Form Selected');
					return;
				}

				var embed_code = '<iframe src="' + response.url + '" width="' + width + '" height="' + height + '" style="width: ' + width + 'px; height: ' + height + 'px; border: 0 none; overflow-y: auto;" frameborder="0">&nbsp;</iframe>';
				code.text(embed_code);
				permalink.val(response.url);
				shortlink.val(response.shortlink);
				code.trigger('focus');
			}, 'json').fail(function() {
				alert('AJAX Error');
			}).always(function() {
				ajax_loader.fadeOut('fast');
			});
		}
	});
</script>
		<?php
	}

	public function meta_thank_you() {
		global $ipt_fsqm_info;
?>
<p>
	<?php _e( 'Thank you for Purchasing WP Feedback, Survey & Quiz Manager - Pro Plugin.', 'ipt_fsqm' ); ?>
</p>
<ul class="ipt_uif_ul_menu">
	<li><a href="http://ipanelthemes.com/fsqm-doc/#!/gettingstarted"><i class="ipt-icomoon-play"></i> <?php _e( 'Getting Started', 'ipt_fsqm' ) ?></a></li>
	<li><a href="http://ipanelthemes.com/fsqm-doc/"><i class="ipt-icomoon-file3"></i> <?php _e( 'Documentation', 'ipt_fsqm' ); ?></a></li>
	<li><a href="http://support.ipanelthemes.com/viewforum.php?f=5"><i class="ipt-icomoon-support"></i> <?php _e( 'Get Support', 'ipt_fsqm' ); ?></a></li>
</ul>
<?php $this->ui->help_head( __( 'Plugin Version', 'ipt_fsqm' ), true ); ?>
	<?php _e( 'If the Script version and DB version do not match, then deactivate the plugin and reactivate again. This should solve the problem. If the problem persists then contact the developer.', 'ipt_fsqm' ); ?>
<?php $this->ui->help_tail(); ?>
<p>
	<?php printf( __( '<strong>Plugin Version:</strong> <em>%s(Script)/%s(DB)</em>', 'ipt_fsqm' ), IPT_FSQM_Loader::$version, $ipt_fsqm_info['version'] ); ?> | <?php _e( 'Icons Used from: ', 'ipt_fsqm' ); ?> <a href="http://icomoon.io/" title="IcoMoon" target="_blank">IcoMoon</a>
</p>
<div class="clear"></div>
		<?php
	}

	public function meta_social() {
?>
<p>
	<?php _e( 'Stay connected to get our latest updates.', 'ipt_fsqm' ) ?>
</p>
<ul class="ipt_uif_ul_menu">
	<li><a href="http://twitter.com/iPanelThemes"><i class="ipt-icomoon-twitter"></i> @iPanelThemes</a></li>
	<li><a href="https://www.facebook.com/iPanelThemes"><i class="ipt-icomoon-facebook2"></i> /iPanelThemes</a></li>
	<li><a href="http://plus.google.com/111068628455450253377"><i class="ipt-icomoon-google-plus"></i> +iPanelThemes</a></li>
</ul>
<?php $this->ui->help_head( __( 'About Us!', 'ipt_fsqm' ), true ); ?>
	<?php _e( 'Currently we do not support customization or freelancing. Once we do, we shall update this. In the meanwhile, if you face any trouble you can always contact us using the forum link given before.', 'ipt_fsqm' ); ?>
<?php $this->ui->help_tail(); ?>
<p>
	<strong><?php _e( 'Lead Developer:', 'ipt_fsqm' ); ?></strong> <a href="mailto:swashata@intechgrity.com">Swashata</a> | <strong><?php _e( 'Lead Designer:', 'ipt_fsqm' ); ?></strong>  <a href="mailto:akash@intechgrity.com">Akash</a> | <strong><?php _e( 'Product By:', 'ipt_fsqm' ); ?></strong> <a href="http://ipanelthemes.com">iPanelThemes</a>
</p>
<div class="clear"></div>
		<?php
	}

	public function meta_stat() {
		global $wpdb, $ipt_fsqm_info;
		$today = current_time( 'timestamp' );
		$forms = $wpdb->get_results( "SELECT id, name FROM {$ipt_fsqm_info['form_table']} ORDER BY id ASC", ARRAY_A );
		$info = array();
		$valid_forms = array();
		for ( $i = 9; $i >= 0; $i-- ) {
			$thedate = date( 'Y-m-d', mktime( 0, 0, 0, date( 'm', $today ), date( 'd', $today ) - $i, date( 'Y', $today ) ) );
			$start_date = $thedate . ' 00:00:00';
			$end_date = $thedate . ' 24:00:00';
			//var_dump($thedate, $start_date, $end_date);
			$info[$thedate] = array();
			$total = 0;

			$counts = $wpdb->get_results( $wpdb->prepare( "SELECT COUNT(id) count, form_id FROM {$ipt_fsqm_info['data_table']} WHERE date <= %s AND date >= %s GROUP BY form_id HAVING count > 0", $end_date, $start_date ), ARRAY_A );

			//var_dump($counts);
			foreach ( (array) $counts as $count ) {
				$info[$thedate][$count['form_id']] = (int) $count['count'];
				$total += $count['count'];
				$valid_forms[] = $count['form_id'];
			}

			//ksort( $info[$thedate] );
			$info[$thedate]['total'] = $total;
		}
		if ( empty( $valid_forms ) ) {
			echo '<div style="height: 300px;">';
			$this->ui->msg_error( __( 'No submissions for past ten days. Please be patient.', 'ipt_fsqm' ) );
			echo '</div>';
			return;
		}
		$valid_forms = array_unique( $valid_forms );

		sort( $valid_forms );

		$json = array();
		$json[0] = array();
		$json[0][0] = __( 'Date', 'ipt_fsqm' );
		foreach ( $forms as $form ) {
			if ( !in_array( $form['id'], $valid_forms ) ) {
				continue;
			}
			$json[0][] = $form['name'];
		}
		$json[0][] = __( 'Total', 'ipt_fsqm' );
		$i = 1;
		foreach ( $info as $date => $count_data ) {
			$json[$i][0] = $date;
			foreach ( $valid_forms as $form ) {
				$json[$i][] = isset( $count_data[$form] ) ? $count_data[$form] : 0;
			}
			$json[$i][] = $count_data['total'];
			$i++;
		}

		//var_dump($json);
?>
<?php $this->ui->ajax_loader( false, 'ipt_fsqm_ten_stat', array(), true ); ?>
<script type="text/javascript">

function drawLatestTen() {
	var data = google.visualization.arrayToDataTable(<?php echo json_encode( $json ); ?>);

	var ac = new google.visualization.ComboChart(document.getElementById('ipt_fsqm_ten_stat'));
	ac.draw(data, {
		title : '<?php _e( 'Last 10 days form submission statistics', 'ipt_fsqm' ); ?>',
		height : 300,
		vAxis : {title : '<?php _e( 'Submission Hits', 'ipt_fsqm' ) ?>'},
		hAxis : {title : '<?php _e( 'Date', 'ipt_fsqm' ); ?>'},
		seriesType : 'bars',
		series : {<?php echo count( $json[0] ) - 2; ?> : {type : 'line'}},
		legend : {position : 'top'},
		tooltip : {isHTML : true}
	});
}

</script>
		<?php
	}

	public function meta_overall() {
		global $wpdb, $ipt_fsqm_info;
		$query = "SELECT f.name name, COUNT(d.id) subs FROM {$ipt_fsqm_info['form_table']} f LEFT JOIN {$ipt_fsqm_info['data_table']} d ON f.id = d.form_id GROUP BY f.id HAVING subs > 0";
		$json = array();
		$json[] = array( __( 'Form', 'ipt_fsqm' ), __( 'Submissions', 'ipt_fsqm' ) );
		$db_data = $wpdb->get_results( $query );

		if ( !empty( $db_data ) ) {
			foreach ( $db_data as $db ) {
				if ( $db->subs == 0 ) {
					continue;
				}
				$json[] = array( $db->name, (int) $db->subs );
			}
		} else {
			echo '<div style="height: 300px;">';
			$this->ui->msg_error( __( 'No submissions yet. Please be patient.', 'ipt_fsqm' ) );
			echo '</div>';
			return;
		}
?>
<?php $this->ui->ajax_loader( false, 'ipt_fsqm_pie_stat', array(), true ); ?>
<script type="text/javascript">

function drawOverallPie() {
	var data = google.visualization.arrayToDataTable(<?php echo json_encode( $json ); ?>);

	var ac = new google.visualization.PieChart(document.getElementById('ipt_fsqm_pie_stat'));
	ac.draw(data, {
		title : '<?php _e( 'Overall form submission statistics', 'ipt_fsqm' ); ?>',
		height : 300,
		is3D : true,
		legend : {position : 'right'},
		tooltip : {isHTML : true}
	});
}

</script>
		<?php
	}

	/**
	 *
	 *
	 * @global wpdb $wpdb
	 * @global array $ipt_fsqm_info
	 */
	public function meta_ten() {
		global $wpdb, $ipt_fsqm_info;
		$rows = $wpdb->get_results( "SELECT d.id id, d.f_name f_name, d.l_name l_name, d.email email, d.phone phone, d.ip ip, d.date date, d.star star, d.comment comment, f.name name, f.id form_id FROM {$ipt_fsqm_info['data_table']} d LEFT JOIN {$ipt_fsqm_info['form_table']} f ON d.form_id = f.id ORDER BY d.date DESC LIMIT 0,10", ARRAY_A );

?>
<table class="widefat">
	<thead>
		<tr>
			<th scope="col">
				<img src="<?php echo plugins_url( '/static/admin/images/star_on.png', IPT_FSQM_Loader::$abs_file ); ?>" />
			</th>
			<th scope="col">
				<?php _e( 'Name', 'ipt_fsqm' ); ?>
			</th>
			<th scope="col">
				<?php _e( 'Email', 'ipt_fsqm' ); ?>
			</th>
			<th scope="col">
				<?php _e( 'Phone', 'ipt_fsqm' ); ?>
			</th>
			<th scope="col">
				<?php _e( 'Date', 'ipt_fsqm' ); ?>
			</th>
			<th scope="col">
				<?php _e( 'IP Address', 'ipt_fsqm' ); ?>
			</th>
			<th scope="col">
				<?php _e( 'Form', 'ipt_fsqm' ); ?>
			</th>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<th scope="col">
				<img src="<?php echo plugins_url( '/static/admin/images/star_on.png', IPT_FSQM_Loader::$abs_file ); ?>" />
			</th>
			<th scope="col">
				<?php _e( 'Name', 'ipt_fsqm' ); ?>
			</th>
			<th scope="col">
				<?php _e( 'Email', 'ipt_fsqm' ); ?>
			</th>
			<th scope="col">
				<?php _e( 'Phone', 'ipt_fsqm' ); ?>
			</th>
			<th scope="col">
				<?php _e( 'Date', 'ipt_fsqm' ); ?>
			</th>
			<th scope="col">
				<?php _e( 'IP Address', 'ipt_fsqm' ); ?>
			</th>
			<th scope="col">
				<?php _e( 'Form', 'ipt_fsqm' ); ?>
			</th>
		</tr>
	</tfoot>
	<tbody>
		<?php if ( empty( $rows ) ) : ?>
		<tr>
			<td colspan="7"><?php _e( 'No submissions yet', 'ipt_fsqm' ); ?></td>
		</tr>
		<?php else : ?>
		<?php foreach ( $rows as $item ) : ?>
		<tr>
			<th scope="row"><img src="<?php echo plugins_url( $item['star'] == 1 ? '/static/admin/images/star_on.png' : '/static/admin/images/star_off.png', IPT_FSQM_Loader::$abs_file ) ?>" /></th>
			<td>
				<?php printf( '<strong><a class="thickbox" title="%s" href="admin-ajax.php?action=ipt_fsqm_quick_preview&id=' . $item['id'] . '&width=640&height=500">' . $item['f_name'] . ' ' . $item['l_name'] . '</a></strong>', sprintf( __( 'Submission of %s under %s', 'ipt_fsqm' ), $item['f_name'], $item['name'] ) ); ?>
			</td>
			<td>
				<?php if ( trim( $item['email'] ) !== '' ) : ?>
				<?php echo '<a href="mailto:' . $item['email'] . '">' . $item['email'] . '</a>'; ?>
				<?php else : ?>
				<?php _e( 'anonymous', 'ipt_fsqm' ); ?>
				<?php endif; ?>
			</td>
			<td>
				<?php echo $item['phone']; ?>
			</td>
			<td>
				<?php echo date_i18n( get_option( 'date_format' ) . __( ' \a\t ', 'ipt_fsqm' ) . get_option( 'time_format' ), strtotime( $item['date'] ) ); ?>
			</td>
			<td>
				<?php echo $item['ip']; ?>
			</td>
			<td>
			<?php if ( current_user_can( 'manage_feedback' ) ) : ?>
				<?php echo '<a href="admin.php?page=ipt_fsqm_view_all_submissions&form_id=' . $item['form_id'] . '">' . $item['name'] . '</a>'; ?>
			<?php else : ?>
				<?php echo $item['name']; ?>
			<?php endif; ?>
			</td>
		</tr>
		<?php endforeach; ?>
		<?php endif; ?>
	</tbody>
</table>
<div class="ipt_uif_button_container">
	<a href="admin.php?page=ipt_fsqm_view_all_submissions" class="ipt_uif_button secondary-button"><?php _e( 'View All', 'ipt_fsqm' ) ?></a>
</div>
		<?php
	}

	public function on_load_page() {
		parent::on_load_page();

		get_current_screen()->add_help_tab( array(
			'id' => 'overview',
			'title' => __( 'Overview', 'ipt_fsqm' ),
			'content' =>
			'<p>' . __( 'Thank you for choosing WP Feedback, Survey & Quiz Manager Pro Plugin. This screen provides some basic information of the plugin and Latest Submission Statistics. The design is integrated from WordPress\' own framework. So you should feel like home!', 'ipt_fsqm' ) . '<p>' .
			'<p>' . __( 'The concept and working of the Plugin is very simple.', 'ipt_fsqm' ) . '</p>' .
			'<ul>' .
			'<li>' . __( 'You setup a form from the <a href="admin.php?page=ipt_fsqm_new_form">New Form</a>.', 'ipt_fsqm' ) . '</li>' .
			'<li>' . __( 'You use the Shortcodes (check the Shortcodes tab on this help screen) for displaying on your Site/Blog. Simply create a page and you will see a new button added to your editor from where you can put the shortcodes automatically. If you want to use the codes manually, then check the Shortcode section of this help.', 'ipt_fsqm' ) . '</li>' .
			'<li>' . __( 'Finally use the <a href="admin.php?page=ipt_fsqm_report">Report & Analysis</a> Or <a href="admin.php?page=ipt_fsqm_view_all_submissions">View all Submissions</a> pages to analyze the submissions.', 'ipt_fsqm' ) . '</li>' .
			'</ul>' .
			'<p>' . __( 'Sounds easy enough? Then get started by going to the <a href="admin.php?page=ipt_fsqm_new_form">New Form</a> now. You can always click on the <strong>HELP</strong> button above the screen to know more.', 'ipt_fsqm' ) . '</p>' .
			'<p>' . __( 'If you have any suggestions or have encountered any bug, please feel free to use the Linked support forum', 'ipt_fsqm' ) . '</p>',
		) );

		get_current_screen()->add_help_tab( array(
			'id' => 'shortcodes',
			'title' => __( 'Shortcodes', 'ipt_fsqm' ),
			'content' =>
			'<p>' . __( 'This plugin comes with three shortcodes. One for displaying the FORM and other for displaying the Trends (The same Latest 100 Survey Reports you see on this screen)', 'ipt_fsqm' ) . '</p>' .
			'<ul>' .
			'<li>' . __( '<code>[ipt_fsqm_form id="form_id"]</code> : Just use this inside a Post/Page and the form will start appearing.', 'ipt_fsqm' ) . '</li>' .
			'<li>' . __( '<code>[ipt_fsqm_trends form_id="form_id"]</code> : Use this to show the Trends based on all available MCQs. Just like the <strong>Report & Analysis</strong>.', 'ipt_fsqm' ) . '</li>' .
			'<li>' . __( '<code>[ipt_fsqm_trackback]</code> : A page from where your users can track their submission. If it is thre in the notification email, then the surveyee should receive a confirmation email with the link to the track page.', 'ipt_fsqm' ) . '</li>' .
			'<li>' . __( '<code>[ipt_fsqm_utrackback]</code> : A central page from where your registered users can track all their submissions. It integrates with your wordpress users and if they are not logged in, it will simply show a login form.', 'ipt_fsqm' ) . '</li>' .
			'</ul>' .
			'<p>' . __( 'If the output of the shortcodes look weird, then probably you have copied them from the list above with the <code>&lt;code&gt;</code> HTML markup. Please delete them and manually write the shortcode.', 'ipt_fsqm' ) . '</p>',
		) );

		get_current_screen()->add_help_tab( array(
			'id' => 'credits',
			'title' => __( 'Credits', 'ipt_fsqm' ),
			'content' =>
			'<p>' . __( 'This is a Pro version of the Free <a href="http://wordpress.org/extend/plugins/wp-feedback-survey-manager/">WP Feedback & Survey Manager</a> Plugin.', 'ipt_fsqm' ) . '</p>' .
			'<p>' . __( 'The plugin uses a few free and/or open source products, which are:', 'ipt_fsqm' ) .
			'<ul>' .
			'<li>' . __( '<strong><a href="http://www.google.com/webfonts/">Google WebFont</a></strong> : To make the form look better.', 'ipt_fsqm' ) . '</li>' .
			'<li>' . __( '<strong><a href="http://jqueryui.com/">jQuery UI</a></strong> : Renders many elements along with the "Tab Like" appearance of the form.', 'ipt_fsqm' ) . '</li>' .
			'<li>' . __( '<strong><a href="https://developers.google.com/chart/">Google Charts Tool</a></strong> : Renders the report charts on both backend as well as frontend.', 'ipt_fsqm' ) . '</li>' .
			'<li>' . __( '<strong><a href="https://github.com/posabsolute/jQuery-Validation-Engine">jQuery Validation Engine</a></strong> : Wonderful form validation plugin from Position-absolute.', 'ipt_fsqm' ) . '</li>' .
			'<li>' . __( '<strong>Icons</strong> : <a href="http://www.icomoon.io/" target="_blank">IcoMoon Icons</a> The wonderful and free collection of Font Icons.', 'ipt_fsqm' ) . '</li>' .
			'</ul>',
		) );

		get_current_screen()->set_help_sidebar(
			'<p><strong>' . __( 'For more information:', 'ipt_fsqm' ) . '</strong></p>' .
			'<p>' . sprintf( __( '<a href="%s" target="_blank">Documentation</a>', 'ipt_fsqm' ), IPT_FSQM_Loader::$documentation ) . '</p>' .
			'<p>' . sprintf( __( '<a href="%s" target="_blank">Support Forums</a>', 'ipt_fsqm' ), IPT_FSQM_Loader::$support_forum ) . '</p>'
		);
	}
}

/**
 * View all Forms Class
 */
class IPT_FSQM_All_Forms extends IPT_FSQM_Admin_Base {
	public $table_view;
	public $form_data;
	public $form_element_admin;

	public function __construct() {
		$this->capability = 'manage_feedback';
		$this->action_nonce = 'ipt_fsqm_form_view_all_nonce';

		parent::__construct();

		$this->icon = 'insert-template';
		add_filter( 'set-screen-option', array( &$this, 'table_set_option' ), 10, 3 );

		$this->post_result[4] = array(
			'type' => 'update',
			'msg' => __( 'Successfully deleted the forms', 'ipt_fsqm' ),
		);
		$this->post_result[5] = array(
			'type' => 'error',
			'msg' => __( 'Please select an action', 'ipt_fsqm' ),
		);
		$this->post_result[6] = array(
			'type' => 'update',
			'msg' => __( 'Successfully deleted the form', 'ipt_fsqm' ),
		);
		$this->post_result[7] = array(
			'type' => 'update',
			'msg' => __( 'Successfully added the form', 'ipt_fsqm' ),
		);
		$this->post_result[8] = array(
			'type' => 'error',
			'msg' => __( 'Could not delete the forms. Please contact developer if problem persists', 'ipt_fsqm' ),
		);
		$this->post_result[9] = array(
			'type' => 'error',
			'msg' => __( 'Could not delete the forms. Please contact developer if problem persists', 'ipt_fsqm' ),
		);
		$this->post_result[10] = array(
			'type' => 'update',
			'msg' => __( 'Successfully updated the form', 'ipt_fsqm' ),
		);
		$this->post_result[11] = array(
			'type' => 'update',
			'msg' => __( 'Successfully copied the form', 'ipt_fsqm' ),
		);

		if ( isset( $_GET['form_id'] ) ) {
			$this->form_element_admin = new IPT_FSQM_Form_Elements_Admin( (int) $_GET['form_id'] );
		} else {
			$this->form_element_admin = new IPT_FSQM_Form_Elements_Admin();
		}

		add_action( 'wp_ajax_' . $this->admin_post_action, array( $this->form_element_admin, 'ajax_save' ) );
	}

	public function admin_menu() {
		$page_title = __( 'View all Forms', 'ipt_fsqm' );
		if ( isset( $_GET['form_id'] ) ) {
			$page_title = __( 'Edit Form', 'ipt_fsqm' );
		}
		$this->pagehook = add_submenu_page( 'ipt_fsqm_dashboard', $page_title, __( 'View all Forms', 'ipt_fsqm' ), $this->capability, 'ipt_fsqm_all_forms', array( &$this, 'index' ) );
		parent::admin_menu();
	}

	public function index() {
		if ( isset( $_GET['form_id'] ) ) {
			$this->index_head( __( 'WP Feedback, Survey & Quiz Manager Pro <span class="ipt-icomoon-arrow-right2"></span> Update Form <a href="admin.php?page=ipt_fsqm_all_forms" class="add-new-h2">Go Back</a>', 'ipt_fsqm' ) );
			if ( $this->form_element_admin->form_id != $_GET['form_id'] ) {
				$this->ui->msg_error( __( 'Invalid form ID provided.', 'ipt_fsqm' ) );
			} else {
				$this->form_element_admin->show_form();
			}
			$this->index_foot( false );
		} else {
			$this->index_head( __( 'WP Feedback, Survey & Quiz Manager Pro <span class="ipt-icomoon-arrow-right2"></span> View all forms', 'ipt_fsqm' ) . '<a href="admin.php?page=ipt_fsqm_new_form" class="add-new-h2">' . __( 'Add New', 'ipt_fsqm' ) . '</a>', false );
			$this->table_view->prepare_items();
?>
<div class="ipt_uif_iconbox ipt_uif_shadow glowy">
	<div class="ipt_uif_box cyan">
		<h3><span class="ipt-icomoon-pencil"></span><?php _e( 'Modify and/or View Forms', 'ipt_fsqm' ); ?></h3>
	</div>
	<div class="ipt_uif_iconbox_inner">
		<form action="" method="get">
			<?php foreach ( $_GET as $k => $v ) : if ( $k == 'order' || $k == 'orderby' || $k == 'page' ) : ?>
			<input type="hidden" name="<?php echo $k; ?>" value="<?php echo $v; ?>" />
			<?php endif; endforeach; ?>
			<?php $this->table_view->search_box( __( 'Search Forms', 'ipt_fsqm' ), 'search_id' ); ?>
			<?php $this->table_view->display(); ?>
		</form>
	</div>
</div>
			<?php
			$this->index_foot();
		}
	}

	/**
	 *
	 *
	 * @global wpdb $wpdb
	 * @global array $ipt_fsqm_info
	 */
	public function save_post() {
		parent::save_post();
		$this->form_element_admin->process_save();
		wp_redirect( add_query_arg( array( 'post_result' => '10' ), $_POST['_wp_http_referer'] ) );
		die();
	}

	public function on_load_page() {
		global $wpdb, $ipt_fsqm_info;

		$this->table_view = new IPT_FSQM_Form_Table();
		$action = $this->table_view->current_action();
		if ( $action == 'delete' ) {
			if ( isset( $_GET['id'] ) ) {
				if ( !wp_verify_nonce( $_GET['_wpnonce'], 'ipt_fsqm_form_delete_' . $_GET['id'] ) ) {
					wp_die( __( 'Cheatin&#8217; uh?' ) );
				}
				if ( IPT_FSQM_Form_Elements_Static::delete_forms( $_GET['id'] ) ) {
					wp_redirect( add_query_arg( array( 'post_result' => '6' ), 'admin.php?page=ipt_fsqm_all_forms' ) );
				} else {
					wp_redirect( add_query_arg( array( 'post_result' => '9' ), 'admin.php?page=ipt_fsqm_all_forms' ) );
				}
			} else {
				if ( !wp_verify_nonce( $_GET['_wpnonce'], 'bulk-ipt_fsqm_form_items' ) ) {
					wp_die( __( 'Cheatin&#8217; uh?' ) );
				}

				if ( IPT_FSQM_Form_Elements_Static::delete_forms( $_GET['forms'] ) ) {
					wp_redirect( add_query_arg( array( 'post_result' => '4' ), $_GET['_wp_http_referer'] ) );
				} else {
					wp_redirect( add_query_arg( array( 'post_result' => '8' ), $_GET['_wp_http_referer'] ) );
				}
			}
			die();
		} else if ( $action == 'copy' ) {
				if ( !wp_verify_nonce( $_GET['_wpnonce'], 'ipt_fsqm_form_copy_' . $_GET['id'] ) ) {
					wp_die( __( 'Cheatin&#8217; uh?' ) );
				}

				IPT_FSQM_Form_Elements_Static::copy_form( $_GET['id'] );
				wp_redirect( add_query_arg( array( 'post_result' => '11' ), 'admin.php?page=ipt_fsqm_all_forms' ) );
				die();
			}

		if ( !empty( $_GET['_wp_http_referer'] ) ) {
			wp_redirect( remove_query_arg( array( '_wp_http_referer', '_wpnonce' ) ) );
			die();
		}

		$option = 'per_page';
		$args = array(
			'label' => __( 'Forms per page', 'ipt_fsqm' ),
			'default' => 20,
			'option' => 'feedback_forms_per_page',
		);
		add_screen_option( $option, $args );

		parent::on_load_page();

		if ( isset( $_GET['form_id'] ) ) {
			$this->form_element_admin->add_help();
		} else {
			get_current_screen()->add_help_tab( array(
					'id'  => 'overview',
					'title'  => __( 'Overview', 'ipt_fsqm' ),
					'content' =>
					'<p>' . __( 'This screen provides access to all of your forms. You can customize the display of this screen to suit your workflow.', 'ipt_fsqm' ) . '</p>' .
					'<p>' . __( 'By default, this screen will show all the forms. Please check the Screen Content for more information.', 'ipt_fsqm' ) . '</p>'
				) );
			get_current_screen()->add_help_tab( array(
					'id'  => 'screen-content',
					'title'  => __( 'Screen Content', 'ipt_fsqm' ),
					'content' =>
					'<p>' . __( 'You can customize the display of this screen&#8217;s contents in a number of ways:' ) . '</p>' .
					'<ul>' .
					'<li>' . __( 'You can sort forms based on total submissions or last updated.', 'ipt_fsqm' ) . '</li>' .
					'<li>' . __( 'You can hide/display columns based on your needs and decide how many forms to list per screen using the Screen Options tab.', 'ipt_fsqm' ) . '</li>' .
					'<li>' . __( 'You can search a particular form by using the Search Form. You can type in just the name.', 'ipt_fsqm' ) . '</li>' .
					'</ul>'
				) );
			get_current_screen()->add_help_tab( array(
					'id'  => 'action-links',
					'title'  => __( 'Available Actions', 'ipt_fsqm' ),
					'content' =>
					'<p>' . __( 'Hovering over a row in the posts list will display action links that allow you to manage your submissions. You can perform the following actions:', 'ipt_fsqm' ) . '</p>' .
					'<ul>' .
					'<li>' . __( '<strong>View Submissions</strong> will take you to a page from where you can see all the submissions under that form.', 'ipt_fsqm' ) . '</li>' .
					'<li>' . __( '<strong>Edit</strong> lets you recustomize the form.', 'ipt_fsqm' ) . '</li>' .
					'<li>' . __( '<strong>Delete</strong> removes your from this list as well as from the database along with all the submissions under it. You can not restore it back, so make sure you want to delete it before you do.', 'ipt_fsqm' ) . '</li>' .
					'<li>' . __( '<strong>Copy</strong> creates a copy of the form.', 'ipt_fsqm' ) . '</li>' .
					'</ul>'
				) );
			get_current_screen()->add_help_tab( array(
					'id'  => 'bulk-actions',
					'title'  => __( 'Bulk Actions', 'ipt_fsqm' ),
					'content' =>
					'<p>' . __( 'There are a number of bulk actions available. Here are the details.', 'ipt_fsqm' ) . '</p>' .
					'<ul>' .
					'<li>' . __( '<strong>Delete</strong>. This will permanently delete the ticked forms from the database along with all the submissions under it.', 'ipt_fsqm' ) . '</li>' .
					'</ul>'
				) );
		}

		get_current_screen()->set_help_sidebar(
			'<p><strong>' . __( 'For more information:' ) . '</strong></p>' .
			'<p>' . sprintf( __( '<a href="%s" target="_blank">Documentation</a>', 'ipt_fsqm' ), IPT_FSQM_Loader::$documentation ) . '</p>' .
			'<p>' . sprintf( __( '<a href="%s" target="_blank">Support Forums</a>', 'ipt_fsqm' ), IPT_FSQM_Loader::$support_forum ) . '</p>'
		);
	}

	public function table_set_option( $status, $option, $value ) {
		return $value;
	}
}

/**
 * New Form Class
 */
class IPT_FSQM_New_Form extends IPT_FSQM_Admin_Base {
	public $form_element_admin;
	public function __construct() {
		$this->capability = 'manage_feedback';
		$this->action_nonce = 'ipt_fsqm_forms_nonce';

		parent::__construct();

		$this->icon = 'insert-template';
		$this->is_metabox = false;
		$this->form_element_admin = new IPT_FSQM_Form_Elements_Admin();

		add_action( 'wp_ajax_' . $this->admin_post_action, array( $this->form_element_admin, 'ajax_save' ) );
	}

	/*==========================================================================
	 * SYSTEM METHODS
	 *========================================================================*/
	public function admin_menu() {
		$this->pagehook = add_submenu_page( 'ipt_fsqm_dashboard', __( 'New Form', 'ipt_fsqm' ), __( 'New Form', 'ipt_fsqm' ), $this->capability, 'ipt_fsqm_new_form', array( &$this, 'index' ) );
		parent::admin_menu();
	}

	public function index() {
		$this->index_head( __( 'WP Feedback, Survey & Quiz Manager - Pro <span class="ipt-icomoon-arrow-right2"></span> New Form', 'ipt_fsqm' ) );
		$this->form_element_admin->show_form();
		$this->index_foot( false );
	}

	/**
	 *
	 *
	 * @global wpdb $wpdb
	 */
	public function save_post() {
		parent::save_post();

		$this->form_element_admin->process_save();

		wp_redirect( add_query_arg( array( 'post_result' => '7' ), 'admin.php?page=ipt_fsqm_all_forms' ) );
		die();
	}

	public function on_load_page() {
		parent::on_load_page();

		$this->form_element_admin->add_help();

		get_current_screen()->set_help_sidebar(
			'<p><strong>' . __( 'For more information:' ) . '</strong></p>' .
			'<p>' . sprintf( __( '<a href="%s" target="_blank">Documentation</a>', 'ipt_fsqm' ), IPT_FSQM_Loader::$documentation ) . '</p>' .
			'<p>' . sprintf( __( '<a href="%s" target="_blank">Support Forums</a>', 'ipt_fsqm' ), IPT_FSQM_Loader::$support_forum ) . '</p>'
		);
	}
}

/**
 * Report & Analysis Class
 */
class IPT_FSQM_Report extends IPT_FSQM_Admin_Base {
	public $form_elements_utilities;
	public function __construct() {
		$this->capability = 'view_feedback';
		$this->action_nonce = 'ipt_fsqm_survey_report_nonce';
		parent::__construct();
		$this->icon = 'stats';
		$this->form_elements_utilities = new IPT_FSQM_Form_Elements_Utilities();

		//Add the ajax for Survey
		add_action( 'wp_ajax_ipt_fsqm_survey_report', array( $this->form_elements_utilities, 'report_ajax' ) );
	}

	public function admin_menu() {
		$this->pagehook = add_submenu_page( 'ipt_fsqm_dashboard', __( 'Generate Report for Forms', 'ipt_fsqm' ), __( 'Report &amp; Analysis', 'ipt_fsqm' ), $this->capability, 'ipt_fsqm_report', array( &$this, 'index' ) );
		parent::admin_menu();
	}
	public function index() {
		$this->index_head( __( 'WP Feedback, Survey & Quiz Manager - Pro <span class="ipt-icomoon-arrow-right2"></span> Report &amp; Analysis', 'ipt_fsqm' ), false );
		$this->form_elements_utilities->report_index();
		$this->index_foot( false );
	}

	public function on_load_page() {
		parent::on_load_page();
		get_current_screen()->add_help_tab( array(
				'id' => 'overview',
				'title' => __( 'Overview', 'ipt_fsqm' ),
				'content' =>
				'<p>' . __( 'This page provides a nice way to view all the survey reports from beginning to end. As this can be a bit database expensive, so reports are pulled 15/30/50 at a time, depending on the server load. You will need JavaScript to view this page.', 'ipt_fsqm' ) . '</p>' .
				'<p>' . __( 'This part of FSQM Pro works like a wizard which takes you through the steps necessary to generate just the part of the report you wish to see.', 'ipt_fsqm' ) . '</p>' .
				'<p>' . __( 'Please check out the other help items for more information.', 'ipt_fsqm' ) . '</p>'

			) );
		get_current_screen()->add_help_tab( array(
				'id' => 'first_step',
				'title' => __( 'Selecting Form', 'ipt_fsqm' ),
				'content' =>
				'<p>' . __( 'In this page you have the following options to get started.', 'ipt_fsqm' ) . '</p>' .
				'<ul>' .
				'<li>' . __( '<strong>Select Form:</strong> Select the form for which you want to generate the report.', 'ipt_fsqm' ) . '</li>' .
				'<li>' . __( '<strong>Report Type:</strong> Please select the type of the report.', 'ipt_fsqm' ) . '</li>' .
				'<li>' . __( '<strong>Server Load:</strong> Select the load on your server. For shared hosts, Medium Load is recommended.', 'ipt_fsqm' ) . '</li>' .
				'<li>' . __( '<strong>Custom Date Range:</strong> Tick and select a range of date.', 'ipt_fsqm' ) . '</li>' .
				'</ul>' .
				'<p>' . __( 'Once done, simply click on the <strong>Select Questions</strong> button.', 'ipt_fsqm' ) . '</p>'
			) );
		get_current_screen()->add_help_tab( array(
				'id' => 'second_step',
				'title' => __( 'Selecting Questions', 'ipt_fsqm' ),
				'content' =>
				'<p>' . __( 'From this page, you will be able to select questions for which you want to generate the report.', 'ipt_fsqm' ) . '</p>' .
				'<ul>' .
				'<li>' . __( '<strong>Select the Multiple Choice Type Questions:</strong> This will list down all the MCQs in your form in proper order. Select the one you like.', 'ipt_fsqm' ) . '</li>' .
				'<li>' . __( '<strong>Select the Feedback Questions:</strong> This will list down all the feedbacks in your form in proper order. Select the one you like.', 'ipt_fsqm' ) . '</li>' .
				'</ul>'

			) );
		get_current_screen()->add_help_tab( array(
				'id' => 'third_step',
				'title' => __( 'Generate Report', 'ipt_fsqm' ),
				'content' =>
				'<p>' . __( 'Now all you have to do it wait until the progress bar reaches 100%. Once done, it will show you the reports of all the questions you have selected in a tabular fashion with charts whenever applicable.', 'ipt_fsqm' ) . '</p>' .
				'<p>' . __( 'If you want to take a printout then scroll to the bottom of the page and click on the big print button.', 'ipt_fsqm' ) . '</p>' .
				'<p>' . __( 'If you wish to put something on this site, then simply use the <strong>Insert Trends</strong> from the FSQM Pro editor button.', 'ipt_fsqm' ) . '</p>'

			) );
		get_current_screen()->set_help_sidebar(
			'<p><strong>' . __( 'For more information:' ) . '</strong></p>' .
			'<p>' . sprintf( __( '<a href="%s" target="_blank">Documentation</a>', 'ipt_fsqm' ), IPT_FSQM_Loader::$documentation ) . '</p>' .
			'<p>' . sprintf( __( '<a href="%s" target="_blank">Support Forums</a>', 'ipt_fsqm' ), IPT_FSQM_Loader::$support_forum ) . '</p>'
		);
	}
}

/**
 * View a Submission Class
 */
class IPT_FSQM_View_Submission extends IPT_FSQM_Admin_Base {

	public function __construct() {
		$this->capability = 'view_feedback';
		$this->action_nonce = 'ipt_fsqm_view_nonce';
		parent::__construct();

		$this->icon = 'newspaper';
	}

	public function admin_menu() {
		$this->pagehook = add_submenu_page( 'ipt_fsqm_dashboard', __( 'View a Submission', 'ipt_fsqm' ), __( 'View a Submission', 'ipt_fsqm' ), $this->capability, 'ipt_fsqm_view_submission', array( &$this, 'index' ) );
		parent::admin_menu();
	}
	public function index() {
		$ui_state = 'back';
		if ( isset( $_GET['id'] ) || isset( $_GET['id2'] ) ) {
			$ui_state = 'none';
		}
		$this->index_head( __( 'WP Feedback, Survey & Quiz Manager - Pro <span class="ipt-icomoon-arrow-right2"></span> View a Submission', 'ipt_fsqm' ), false, $ui_state );
		if ( isset( $_GET['id'] ) || isset( $_GET['id2'] ) ) {
			$this->show_submission();
		} else {
			$this->show_form();
		}
		$this->index_foot();
	}

	/**
	 *
	 *
	 * @global wpdb $wpdb
	 * @global array $ipt_fsqm_info
	 */
	public function save_post() {
		parent::save_post();
		die();
	}

	public function on_load_page() {
		parent::on_load_page();
		get_current_screen()->add_help_tab( array(
				'id' => 'overview',
				'title' => __( 'Overview', 'ipt_fsqm' ),
				'content' =>
				'<p>' . __( 'Using this page, you can view/edit a particular submission either by it\'s ID (which is mailed to the notification email when a submission is being submitted) Or select one from the latest 100.', 'ipt_fsqm' ) . '</p>',
			) );
		get_current_screen()->set_help_sidebar(
			'<p><strong>' . __( 'For more information:' ) . '</strong></p>' .
			'<p>' . sprintf( __( '<a href="%s" target="_blank">Documentation</a>', 'ipt_fsqm' ), IPT_FSQM_Loader::$documentation ) . '</p>' .
			'<p>' . sprintf( __( '<a href="%s" target="_blank">Support Forums</a>', 'ipt_fsqm' ), IPT_FSQM_Loader::$support_forum ) . '</p>'
		);
	}

	private function show_submission() {
		$id = !empty( $_GET['id'] ) ? (int) $_GET['id'] : $_GET['id2'];
		$edit = isset( $_GET['edit'] ) ? true : false;
		$form = new IPT_FSQM_Form_Elements_Front( $id );

		if ( $edit ) {
			if ( !current_user_can( 'manage_feedback' ) ) {
				wp_die( __( 'Cheatin&#8217; uh?' ) );
			}
			$form->show_form( true, true );
		} else {
			IPT_FSQM_Form_Elements_Static::ipt_fsqm_full_preview( $id );
		}

	}

	private function show_form() {
		global $wpdb, $ipt_fsqm_info;
		$s = array();
		$last100 = $wpdb->get_results( "SELECT f_name, l_name, id FROM {$ipt_fsqm_info['data_table']} ORDER BY `date` DESC LIMIT 0, 100" );
		if ( empty( $last100 ) ) {
			$this->ui->msg_error( __( 'There are no submissions in the database. Please be patient!', 'ipt_fsqm' ) );
			return;
		}

		foreach ( $last100 as $l ) {
			$s[$l->id] = $l->f_name . ' ' . $l->l_name;
		}
		$buttons = array(
			array( __( 'View', 'ipt_fsqm' ), 'view', 'medium', 'primary', 'normal', array(), 'submit' ),
		);
		if ( current_user_can( 'manage_feedback' ) ) {
			$buttons[] = array( __( 'Edit', 'ipt_fsqm' ), 'edit', 'medium', 'secondary', 'normal', array( 'equal-height' ), 'submit' );
		}
?>
<?php $this->print_p_update( __( 'Please either enter an ID or select one from the latest 100', 'ipt_fsqm' ) ); ?>
<div class="ipt_uif_iconbox ipt_uif_shadow glowy">
	<div class="ipt_uif_box cyan">
		<h3><span class="ipt-icomoon-menu"></span><?php _e( 'Select a Submission', 'ipt_fsqm' ); ?></h3>
	</div>
	<div class="ipt_uif_iconbox_inner">

		<form action="" method="get">
			<?php foreach ( $_GET as $k => $v ) : ?>
			<input type="hidden" name="<?php echo esc_attr( $k ); ?>" value="<?php echo esc_attr( $v ); ?>" />
			<?php endforeach; ?>
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row">
							<label for="id"><?php _e( 'Enter the ID', 'ipt_fsqm' ); ?></label>
						</th>
						<td>
							<?php $this->print_input_text( 'id', '', 'regular-text code' ); ?>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="id2"><?php _e( 'Or Select One', 'ipt_fsqm' ); ?></label>
						</th>
						<td>
							<select name="id2" id="id2" class="ipt_uif_select">
								<?php $this->print_select_op( $s, null, true ); ?>
							</select>
						</td>
					</tr>
				</tbody>
			</table>
			<?php $this->ui->buttons( $buttons ); ?>
		</form>
	</div>
</div>
		<?php
	}
}

/**
 * View all Submissions Class
 */
class IPT_FSQM_View_All_Submissions extends IPT_FSQM_Admin_Base {
	/**
	 * The feedback table class object
	 * Should be instantiated on-load
	 *
	 * @var IPT_FSQM_Data_Table
	 */
	public $table_view;
	public function __construct() {
		$this->capability = 'manage_feedback';
		$this->action_nonce = 'ipt_fsqm_view_all_nonce';

		parent::__construct();
		$this->icon = 'newspaper';
		add_filter( 'set-screen-option', array( $this, 'table_set_option' ), 10, 3 );

		$this->post_result[4] = array(
			'type' => 'update',
			'msg' => __( 'Successfully deleted the submissions', 'ipt_fsqm' ),
		);
		$this->post_result[5] = array(
			'type' => 'error',
			'msg' => __( 'Please select an action', 'ipt_fsqm' ),
		);
		$this->post_result[6] = array(
			'type' => 'update',
			'msg' => __( 'Successfully deleted the submission', 'ipt_fsqm' ),
		);

		$this->post_result[7] = array(
			'type' => 'update',
			'msg' => __( 'Successfully updated the submission', 'ipt_fsqm' ),
		);
		$this->post_result[8] = array(
			'type' => 'update',
			'msg' => __( 'An error has occured updating the submission. Either you haven\'t changed anything or something terrible has happened. Please contact the developer', 'ipt_fsqm' ),
		);
		$this->post_result[9] = array(
			'type' => 'update',
			'msg' => __( 'Successfully starred the submissions', 'ipt_fsqm' ),
		);
		$this->post_result[10] = array(
			'type' => 'update',
			'msg' => __( 'Successfully unstarred the submissions', 'ipt_fsqm' ),
		);
		$this->post_result[11] = array(
			'type' => 'error',
			'msg' => __( 'Please select some submissions to perform the action', 'ipt_fsqm' ),
		);

		add_action( 'wp_ajax_ipt_fsqm_star', array( &$this, 'ajax_star' ) );
	}

	public function admin_menu() {
		$this->pagehook = add_submenu_page( 'ipt_fsqm_dashboard', __( 'View all Submissions', 'ipt_fsqm' ), __( 'View all Submissions', 'ipt_fsqm' ), $this->capability, 'ipt_fsqm_view_all_submissions', array( &$this, 'index' ) );
		parent::admin_menu();
	}

	public function index() {
		$this->index_head( __( 'WP Feedback, Survey & Quiz Manager - Pro <span class="ipt-icomoon-arrow-right2"></span> View All Submissions', 'ipt_fsqm' ), false );
		$this->table_view->prepare_items();
?>
<style type="text/css">
	.column-star {
		width: 50px;
	}
	.column-title {
		width: 300px;
	}
</style>
<div class="ipt_uif_iconbox ipt_uif_shadow glowy">
	<div class="ipt_uif_box cyan">
		<h3><span class="ipt-icomoon-pencil"></span><?php _e( 'Modify and/or View Submissions', 'ipt_fsqm' ); ?></h3>
	</div>
	<div class="ipt_uif_iconbox_inner">
		<form action="" method="get">
			<?php foreach ( $_GET as $k => $v ) : if ( $k == 'order' || $k == 'orderby' || $k == 'page' ) : ?>
			<input type="hidden" name="<?php echo $k; ?>" value="<?php echo $v; ?>" />
			<?php endif; endforeach; ?>
			<?php $this->table_view->search_box( __( 'Search Submissions', 'ipt_fsqm' ), 'search_id' ); ?>
			<?php $this->table_view->display(); ?>
		</form>
	</div>
</div>
<script type="text/javascript">
(function($) {
	$(document).ready(function() {
		var _ipt_fsqm_nonce = '<?php echo wp_create_nonce( 'ipt_fsqm_star' ); ?>';
		$('a.ipt_fsqm_star').click(function(e) {
			e.preventDefault();
			var $this = this;
			$(this).html('<img src="<?php echo admin_url( '/images/wpspin_light.gif' ); ?>" />');
			var data_id = $(this).parent().siblings('th').find('input').attr('value');
			var data = {
				'id' : data_id,
				'action' : 'ipt_fsqm_star',
				'_wpnonce' : _ipt_fsqm_nonce
			};
			$.post(ajaxurl, data, function(response) {
				$($this).html(response.html);
				_ipt_fsqm_nonce = responce.nonce;
			}, 'json');
		});
	});
})(jQuery);
</script>
		<?php
		$this->index_foot();
	}

	public function save_post() {
		parent::save_post();
	}

	public function ajax_star() {
		global $wpdb, $ipt_fsqm_info;
		$id = $_REQUEST['id'];
		$nonce = $_REQUEST['_wpnonce'];
		if ( !wp_verify_nonce( $nonce, 'ipt_fsqm_star' ) ) {
			echo json_encode( array( 'html' => '<img title="Invalid Nonce. Cheating uh?" src="' . plugins_url( '/static/admin/images/error.png', IPT_FSQM_Loader::$abs_file ) . '" />', 'nonce' => 'boundtoFAIL' ) );
			die();
		}

		$data = $wpdb->get_var( $wpdb->prepare( "SELECT star FROM {$ipt_fsqm_info['data_table']} WHERE id = %d", $id ) );
		if ( null == $data ) {
			echo json_encode( array( 'html' => '<img title="Invalid ID associtated. Try Again?" src="' . plugins_url( '/static/admin/images/error.png', IPT_FSQM_Loader::$abs_file ) . '" />', 'nonce' => wp_create_nonce( 'ipt_fsqm_star' ) ) );
			die();
		}

		if ( 0 == $data ) {
			IPT_FSQM_Form_Elements_Static::star_submissions( $id );
			echo json_encode( array( 'html' => '<img title="' . __( 'Click to Unstar', 'ipt_fsqm' ) . '" src="' . plugins_url( '/static/admin/images/star_on.png', IPT_FSQM_Loader::$abs_file ) . '" />', 'nonce' => wp_create_nonce( 'ipt_fsqm_star' ) ) );
		} else {
			IPT_FSQM_Form_Elements_Static::unstar_submissions( $id );
			echo json_encode( array( 'html' => '<img title="' . __( 'Click to Star', 'ipt_fsqm' ) . '" src="' . plugins_url( '/static/admin/images/star_off.png', IPT_FSQM_Loader::$abs_file ) . '" />', 'nonce' => wp_create_nonce( 'ipt_fsqm_star' ) ) );
		}
		die();
	}

	public function on_load_page() {
		global $wpdb, $ipt_fsqm_info;

		$this->table_view = new IPT_FSQM_Data_Table();

		if ( $_SERVER['REQUEST_METHOD'] == 'POST' )
			$this->save_post ();

		$action = $this->table_view->current_action();

		if ( false !== $action ) {
			//check if single delete request
			if ( isset( $_GET['id'] ) ) {
				if ( wp_verify_nonce( $_GET['_wpnonce'], 'ipt_fsqm_delete_' . $_GET['id'] ) ) {
					IPT_FSQM_Form_Elements_Static::delete_submissions( $_GET['id'] );
					wp_redirect( add_query_arg( array( 'post_result' => 6 ), 'admin.php?page=' . $_GET['page'] ) );
				} else {
					wp_die( __( 'Cheatin&#8217; uh?' ) );
				}
				die();
			} else {
				//bulk actions
				if ( !wp_verify_nonce( $_GET['_wpnonce'], 'bulk-ipt_fsqm_table_items' ) ) {
					wp_die( __( 'Cheatin&#8217; uh?' ) );
				}

				if ( empty( $_GET['feedbacks'] ) ) {
					wp_redirect( add_query_arg( array( 'post_result' => 11 ), $_GET['_wp_http_referer'] ) );
					die();
				}


				switch ( $action ) {
				case 'delete' :
					if ( IPT_FSQM_Form_Elements_Static::delete_submissions( $_GET['feedbacks'] ) ) {
						wp_redirect( add_query_arg( array( 'post_result' => 4 ), $_GET['_wp_http_referer'] ) );
					} else {
						wp_redirect( add_query_arg( array( 'post_result' => 2 ), $_GET['_wp_http_referer'] ) );
					}
					break;
				case 'star' :
					if ( IPT_FSQM_Form_Elements_Static::star_submissions( $_GET['feedbacks'] ) ) {
						wp_redirect( add_query_arg( array( 'post_result' => 9 ), $_GET['_wp_http_referer'] ) );
					} else {
						wp_redirect( add_query_arg( array( 'post_result' => 2 ), $_GET['_wp_http_referer'] ) );
					}
					break;
				case 'unstar' :
					if ( IPT_FSQM_Form_Elements_Static::unstar_submissions( $_GET['feedbacks'] ) ) {
						wp_redirect( add_query_arg( array( 'post_result' => 10 ), $_GET['_wp_http_referer'] ) );
					} else {
						wp_redirect( add_query_arg( array( 'post_result' => 2 ), $_GET['_wp_http_referer'] ) );
					}
					break;
				default :
					wp_redirect( add_query_arg( array( 'post_result' => 5 ), $_GET['_wp_http_referer'] ) );
				}
				die();
			}
		}

		//clean up the URL
		if ( !empty( $_GET['_wp_http_referer'] ) ) {
			wp_redirect( remove_query_arg( array( '_wp_http_referer', '_wpnonce' ) ) );
			die();
		}

		$option = 'per_page';
		$args = array(
			'label' => __( 'Submissions per page', 'ipt_fsqm' ),
			'default' => 20,
			'option' => 'feedbacks_per_page',
		);
		add_screen_option( $option, $args );
		parent::on_load_page();

		get_current_screen()->add_help_tab( array(
				'id'  => 'overview',
				'title'  => __( 'Overview' ),
				'content' =>
				'<p>' . __( 'This screen provides access to all of your submissions & surveys. You can customize the display of this screen to suit your workflow.', 'ipt_fsqm' ) . '</p>' .
				'<p>' . __( 'By default, this screen will show all the submissions and submissions of all the available forms. Please check the Screen Content for more information.', 'ipt_fsqm' ) . '</p>'
			) );
		get_current_screen()->add_help_tab( array(
				'id'  => 'screen-content',
				'title'  => __( 'Screen Content' ),
				'content' =>
				'<p>' . __( 'You can customize the display of this screen&#8217;s contents in a number of ways:' ) . '</p>' .
				'<ul>' .
				'<li>' . __( 'You can select a particular form and filter submissions on that form only.', 'ipt_fsqm' ) . '</li>' .
				'<li>' . __( 'You can hide/display columns based on your needs and decide how many submissions to list per screen using the Screen Options tab.', 'ipt_fsqm' ) . '</li>' .
				'<li>' . __( 'You can search a particular submission by using the Search Form. You can type in just the first name or the last name or the email or the ID or even the IP Address.', 'ipt_fsqm' ) . '</li>' .
				'</ul>'
			) );
		get_current_screen()->add_help_tab( array(
				'id'  => 'action-links',
				'title'  => __( 'Available Actions' ),
				'content' =>
				'<p>' . __( 'Hovering over a row in the posts list will display action links that allow you to manage your submissions. You can perform the following actions:', 'ipt_fsqm' ) . '</p>' .
				'<ul>' .
				'<li>' . __( '<strong>Quick Preview</strong>: Pops up a modal window with the detailed preview of the particular submission. You can also print the submission if you wish to.', 'ipt_fsqm' ) . '</li>' .
				'<li>' . __( '<strong>Full View</strong>: Opens up a page where you can view the form along with the submission data.', 'ipt_fsqm' ) . '</li>' .
				'<li>' . __( '<strong>Edit Submission</strong>: Lets you edit all the aspects of the submission. Most importantly you can add administrator remarks which will be shown on the track page.', 'ipt_fsqm' ) . '</li>' .
				'<li>' . __( '<strong>Delete</strong> removes the submission from this list as well as from the database. You can not restore it back, so make sure you want to delete it before you do.', 'ipt_fsqm' ) . '</li>' .
				'<li>' . __( '<strong>Star Column</strong> lets you star/unstar a submission. Simply click on the star to toggle.', 'ipt_fsqm' ) . '</li>' .
				'</ul>'
			) );
		get_current_screen()->add_help_tab( array(
				'id'  => 'bulk-actions',
				'title'  => __( 'Bulk Actions' ),
				'content' =>
				'<p>' . __( 'There are a number of bulk actions available. Here are the details.', 'ipt_fsqm' ) . '</p>' .
				'<ul>' .
				'<li>' . __( '<strong>Delete</strong>. This will permanently delete the ticked submissions from the database.', 'ipt_fsqm' ) . '</li>' .
				'<li>' . __( '<strong>Mark Starred</strong>. This will mark the submissions starred.', 'ipt_fsqm' ) . '</li>' .
				'<li>' . __( '<strong>Mark Unstarred</strong>. This will mark the submissions unstarred.', 'ipt_fsqm' ) . '</li>' .
				'</ul>'
			) );

		get_current_screen()->set_help_sidebar(
			'<p><strong>' . __( 'For more information:' ) . '</strong></p>' .
			'<p>' . sprintf( __( '<a href="%s" target="_blank">Documentation</a>', 'ipt_fsqm' ), IPT_FSQM_Loader::$documentation ) . '</p>' .
			'<p>' . sprintf( __( '<a href="%s" target="_blank">Support Forums</a>', 'ipt_fsqm' ), IPT_FSQM_Loader::$support_forum ) . '</p>'
		);
	}

	public function table_set_option( $status, $option, $value ) {
		return $value;
	}
}

/**
 * Settings Class
 */
class IPT_FSQM_Settings extends IPT_FSQM_Admin_Base {
	public function __construct() {
		$this->capability = 'manage_feedback';
		$this->action_nonce = 'ipt_fsqm_settings_nonce';

		parent::__construct();

		$this->icon = 'settings';

		$this->post_result[4] = array(
			'type' => 'okay',
			'msg' => __( 'Successfully saved the options as well as created sample forms. You may now head to <a href="admin.php?page=ipt_fsqm_all_forms">View all Forms</a> to start editing them.', 'ipt_fsqm' ),
		);
	}

	/*==========================================================================
	 * SYSTEM METHODS
	 *========================================================================*/

	public function admin_menu() {
		$this->pagehook = add_submenu_page( 'ipt_fsqm_dashboard', __( 'FSQM Pro Settings', 'ipt_fsqm' ), __( 'Settings', 'ipt_fsqm' ), $this->capability, 'ipt_fsqm_settings', array( &$this, 'index' ) );
		parent::admin_menu();
	}

	public function index() {
		global $ipt_fsqm_settings;
		$ipt_fsqm_key = get_option( 'ipt_fsqm_key' );
		$this->index_head( __( 'WP Feedback, Survey & Quiz Manager - Pro <span class="ipt-icomoon-arrow-right2"></span> Settings', 'ipt_fsqm' ) );
?>
<div class="ipt_uif_iconbox ipt_uif_shadow glowy">
	<div class="ipt_uif_box cyan">
		<h3><span class="ipt-icomoon-cog"></span><?php _e( 'Modify Plugin Settings', 'ipt_fsqm' ); ?></h3>
	</div>
	<div class="ipt_uif_iconbox_inner">
		<table class="form-table">
			<tr>
				<th scope="row">
					<label for="global_email"><?php _e( 'Global Notification Email', 'ipt_fsqm' ); ?></label>
				</th>
				<td>
					<?php $this->print_input_text( 'global[email]', $ipt_fsqm_settings['email'], 'regular-text code' ); ?>
				</td>
				<td>
					<?php $this->ui->help_head(); ?>
						<?php _e( 'Enter the email where you want to send notifications for all the feedback forms.', 'ipt_fsqm' ); ?>
					<?php $this->ui->help_tail(); ?>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="global_track_page"><?php _e( 'Single Submission Trackback Page for Unregistered Users', 'ipt_fsqm' ); ?></label>
				</th>
				<td>
					<?php $this->ui->dropdown_pages( array(
						'name' => 'global[track_page]',
						'selected' => $ipt_fsqm_settings['track_page'],
						'show_option_none' => __( 'Please select a page', 'ipt_fsqm' ),
					) ); ?>
				</td>
				<td>
					<?php $this->ui->help_head(); ?>
						<?php _e( 'Select the page where you\'ve put the <code>[ipt_fsqm_trackback]</code> shortcode. The page will be linked throughout all the notification email.', 'ipt_fsqm' ); ?>
					<?php $this->ui->help_tail(); ?>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="global_utrack_page"><?php _e( 'Central Trackback page for Registered Users', 'ipt_fsqm' ); ?></label>
				</th>
				<td>
					<?php $this->ui->dropdown_pages( array(
						'name' => 'global[utrack_page]',
						'selected' => $ipt_fsqm_settings['utrack_page'],
						'show_option_none' => __( 'Please select a page', 'ipt_fsqm' ),
					) ); ?>
				</td>
				<td>
					<?php $this->ui->help_head(); ?>
						<?php _e( 'Select the page where you\'ve put the <code>[ipt_fsqm_utrackback]</code> shortcode. The page will be linked throughout all the notification email.', 'ipt_fsqm' ); ?>
					<?php $this->ui->help_tail(); ?>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php $this->ui->generate_label( 'global[backward_shortcode]', __( 'Backward Compatible Shortcode', 'ipt_fsqm' ) ); ?>
				</th>
				<td>
					<?php $this->ui->toggle( 'global[backward_shortcode]', __( 'yes', 'ipt_fsqm' ), 'no', $ipt_fsqm_settings['backward_shortcode'] ); ?>
				</td>
				<td>
					<?php $this->ui->help( __( 'If you are coming from older version (prior to version 2.x) then you need to leave it enabled in order to make the older format of shortcodes work. Since version 2.x, the shortcode format was changed to a more localized form.', 'ipt_fsqm' ) ); ?>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php $this->ui->generate_label( 'global[key]', __( 'Secret Encryption Key', 'ipt_fsqm' ) ); ?>
				</th>
				<td>
					<?php $this->ui->text( 'global[key]', $ipt_fsqm_key, __( 'Can not be empty', 'ipt_fsqm' ) ); ?>
				</td>
				<td>
					<?php $this->ui->help_head(); ?>
					<p><?php _e( 'This key is used to generate the trackback keys. If you change this, then all the trackback codes will get reset.', 'ipt_fsqm' ); ?></p>
					<p><?php _e( 'Use this with extreme caution and change only if necessary. The new trackback keys will not be sent to the users.', 'ipt_fsqm' ); ?></p>
					<?php $this->ui->help_tail(); ?>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php $this->ui->generate_label( 'global[delete_uninstall]', __( 'Delete all Data when uninstalling plugin', 'ipt_fsqm' ) ); ?>
				</th>
				<td>
					<?php $this->ui->toggle( 'global[delete_uninstall]', __( 'yes', 'ipt_fsqm' ), 'no', $ipt_fsqm_settings['delete_uninstall'] ); ?>
				</td>
				<td>
					<?php $this->ui->help( __( 'If you want to completely wipe out all data when uninstalling, then have this enabled. Keep it disabled, if you are planning to update the plugin by uninstalling and then reinstalling.', 'ipt_fsqm' ) ); ?>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php $this->ui->generate_label( 'sample_forms', __( 'Sample Forms', 'ipt_fsqm' ) ); ?>
				</th>
				<td>
					<?php $this->ui->checkbox( 'sample_forms', array(
						'label' => __( 'Create sample forms upon save', 'ipt_fsqm' ),
						'value' => '1',
					), false ); ?>
				</td>
				<td>
					<?php $this->ui->help( __( 'Sample forms are created when you first install the plugin. If you want them again, then tick this checkbox and hit Save Changes.', 'ipt_fsqm' ) ); ?>
				</td>
			</tr>
		</table>
	</div>
</div>
<div class="ipt_uif_iconbox ipt_uif_shadow glowy">
	<div class="ipt_uif_box cyan">
		<h3><span class="ipt-icomoon-file2"></span><?php _e( 'Modify Standalone Forms Settings', 'ipt_fsqm' ); ?></h3>
	</div>
	<div class="ipt_uif_iconbox_inner">
		<table class="form-table">
			<tbody>
				<tr>
				<th scope="row">
					<?php $this->ui->generate_label( 'global[standalone][base]', __( 'Permalink Base', 'ipt_fsqm' ) ); ?>
				</th>
				<td>
					<?php $this->ui->text( 'global[standalone][base]', $ipt_fsqm_settings['standalone']['base'], __( 'Can not be empty', 'ipt_fsqm' ), 'fit', 'normal', 'code' ); ?>
				</td>
				<td>
					<?php $this->ui->help_head(); ?>
					<p><?php _e( 'This will be the base of any permalink generated for your standalone forms.', 'ipt_fsqm' ); ?></p>
					<p><?php _e( 'If you want the links to be like <code>http://example.com/<strong>webforms</strong>/my-awesome-form/01/</code> then use <code>webforms</code> as the base.', 'ipt_fsqm' ); ?></p>
					<?php $this->ui->help_tail(); ?>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php $this->ui->generate_label( 'global[standalone][head]', __( 'HTML Head Section', 'ipt_fsqm' ) ); ?>
				</th>
				<td>
					<?php $this->ui->textarea( 'global[standalone][head]', $ipt_fsqm_settings['standalone']['head'], __( 'CSS or JS or Meta Tags', 'ipt_fsqm' ), 'widefat', 'normal', 'code' ); ?>
				</td>
				<td>
					<?php $this->ui->help_head(); ?>
					<p><?php _e( 'If you want to put any custom CSS code or other HTML tags inside the <code>&lt;head&gt;</code> section, then do it here.', 'ipt_fsqm' ); ?></p>
					<p><?php _e( 'Please note that, if a css file named fsqm-pro.css or fsqm-pro-{form_id}.css is present inside your current theme directory, then it will be included by default.', 'ipt_fsqm' ); ?></p>
					<?php $this->ui->help_tail(); ?>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php $this->ui->generate_label( 'global[standalone][before]', __( 'Before Form HTML', 'ipt_fsqm' ) ); ?>
				</th>
				<td>
					<?php $this->ui->wp_editor( 'global[standalone][before]', $ipt_fsqm_settings['standalone']['before'] ); ?>
				</td>
				<td>
					<?php $this->ui->help_head(); ?>
					<p><?php _e( 'This content will be appended before the output of the form.', 'ipt_fsqm' ); ?></p>
					<?php $this->ui->help_tail(); ?>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php $this->ui->generate_label( 'global[standalone][after]', __( 'After Form HTML', 'ipt_fsqm' ) ); ?>
				</th>
				<td>
					<?php $this->ui->wp_editor( 'global[standalone][after]', $ipt_fsqm_settings['standalone']['after'] ); ?>
				</td>
				<td>
					<?php $this->ui->help_head(); ?>
					<p><?php _e( 'This content will be appended after the output of the form.', 'ipt_fsqm' ); ?></p>
					<?php $this->ui->help_tail(); ?>
				</td>
			</tr>
			</tbody>
		</table>
	</div>
</div>
		<?php
		$this->index_foot();
	}

	public function save_post() {
		parent::save_post();

		$settings = array(
			'email' => $this->post['global']['email'],
			'track_page' => $this->post['global']['track_page'],
			'utrack_page' => $this->post['global']['utrack_page'],
			'backward_shortcode' => isset( $this->post['global']['backward_shortcode'] ) && '' != $this->post['global']['backward_shortcode'] ? true : false,
			'delete_uninstall' => isset( $this->post['global']['delete_uninstall'] ) && '' != $this->post['global']['delete_uninstall'] ? true : false,
			'standalone' => array(
				'base' => $this->post['global']['standalone']['base'],
				'before' => $this->post['global']['standalone']['before'],
				'after' => $this->post['global']['standalone']['after'],
				'head' => $this->post['global']['standalone']['head'],
			),
		);

		if ( trim( $settings['standalone']['base'] ) == '' ) {
			$settings['standalone']['base'] = 'eforms';
		}

		$settings['standalone']['base'] = sanitize_title( $settings['standalone']['base'] );

		update_option( 'ipt_fsqm_settings', $settings );

		$key = $this->post['global']['key'];
		if ( trim( $key ) == '' ) {
			$key = NONCE_SALT;
		}
		update_option( 'ipt_fsqm_key', $key );

		if ( isset( $this->post['sample_forms'] ) ) {
			include_once IPT_FSQM_Loader::$abs_path . '/classes/class-ipt-fsqm-install.php';
			$install = new IPT_FSQM_Install();
			$install->create_sample_forms();
			wp_redirect( add_query_arg( array( 'post_result' => 4 ), $_POST['_wp_http_referer'] ) );
		} else {
			wp_redirect( add_query_arg( array( 'post_result' => 1 ), $_POST['_wp_http_referer'] ) );
		}
		die();
	}

	public function on_load_page() {
		flush_rewrite_rules();
		parent::on_load_page();
		get_current_screen()->add_help_tab( array(
				'id' => 'track',
				'title' => __( 'Settings', 'ipt_fsqm' ),
				'content' =>
				'<p>' . __( 'There are five settings which you can change.', 'ipt_fsqm' ) . '<p>' .
				'<ul>' .
				'<li>' . __( '<strong>Global Notification Email:</strong> Enter an email where the notification will be sent everytime a user submits any of the forms.', 'ipt_fsqm' ) . '</li>' .
				'<li>' . __( '<strong>Single Submission Trackback Page for Unregistered Users:</strong> Select the page where you\'ve put the <code>[ipt_fsqm_trackback]</code> shortcode. From this page users can see their submission and print if they want. The page will be linked throughout all the notification email.', 'ipt_fsqm' ) . '</li>' .
				'<li>' . __( '<strong>Central Trackback page for Registered Users:</strong> Select the page where you\'ve put the [ipt_fsqm_utrackback] shortcode. From this page, logged in users will be able to see all their submissions and also they will be getting a link to the trackback page. The page will be linked throughout all the trackbacks whenever applicable.', 'ipt_fsqm' ) . '</li>' .
				'<li>' . __( '<strong>Backward Compatible Shortcode:</strong> If you are coming from older version (prior to version 2.x) then you need to leave it enabled in order to make the older format of shortcodes work. Since version 2.x, the shortcode format was changed to a more localized form.', 'ipt_fsqm' ) . '</li>' .
				'<li>' . __( '<strong>Secret Encryption Key:</strong> This key is used to generate the trackback keys. If you change this, then all the trackback codes will get reset.', 'ipt_fsqm' ) . '</li>' .
				'</ul>' .
				'<p>' . __( 'Please set the settings up before going live with your forms.', 'ipt_fsqm' ) . '</p>',
			) );
		get_current_screen()->set_help_sidebar(
			'<p><strong>' . __( 'For more information:', 'ipt_fsqm' ) . '</strong></p>' .
			'<p>' . sprintf( __( '<a href="%s" target="_blank">Documentation</a>', 'ipt_fsqm' ), IPT_FSQM_Loader::$documentation ) . '</p>' .
			'<p>' . sprintf( __( '<a href="%s" target="_blank">Support Forums</a>', 'ipt_fsqm' ), IPT_FSQM_Loader::$support_forum ) . '</p>'
		);
	}
}

class IPT_FSQM_Import_Export extends IPT_FSQM_Admin_Base {
	public function __construct() {
		$this->capability = 'manage_feedback';
		$this->action_nonce = 'ipt_fsqm_import_export_nonce';

		parent::__construct();

		$this->icon = 'code';
		add_action( 'wp_ajax_ipt_fsqm_generate_export', array( $this, 'generate_export' ) );
		add_action( 'wp_ajax_ipt_fsqm_generate_import', array( $this, 'generate_import' ) );
	}

	/*==========================================================================
	 * SYSTEM METHODS
	 *========================================================================*/

	public function admin_menu() {
		$this->pagehook = add_submenu_page( 'ipt_fsqm_dashboard', __( 'Import & Export Forms - WP Feedback, Survey & Quiz Manager - Pro', 'ipt_fsqm' ), __( 'Import/Export Forms', 'ipt_fsqm' ), $this->capability, 'ipt_fsqm_import_export', array( $this, 'index' ) );
		parent::admin_menu();
	}

	public function index() {
		$this->index_head( __( 'WP Feedback, Survey & Quiz Manager Pro <span class="ipt-icomoon-arrow-right2"></span> Import/Export Forms', 'ipt_fsqm' ), false );
		wp_nonce_field( 'ipt_fsqm_import_export_nonce', 'ipt_fsqm_ie_nonce' );
		$this->ui->iconbox( __( 'Generate Export Code', 'ipt_fsqm' ), array( $this, 'export_code_html' ), 'copy' );
		$this->ui->iconbox( __( 'Import Form from Code', 'ipt_fsqm' ), array( $this, 'import_code_html' ), 'paste-2' );
		$this->index_foot( false );
	}

	public function on_load_page() {
		get_current_screen()->add_help_tab( array(
			'id' => 'overview',
			'title' => __( 'Overview', 'ipt_fsqm' ),
			'content' =>
			'<p>' . __( 'This screen provides tools to export and/or import forms among different sites of yours or friends.', 'ipt_fsqm' ) . '<p>' .
			'<p>' . __( 'Using the export code is pretty easy. You are presented with two options:', 'ipt_fsqm' ) . '</p>' .
			'<ul>' .
			'<li>' . __( '<strong>Generate Export Code:</strong> Simply select the form and hit Generate Code button. It will give you the export code of the form. Copy the code and keep it handy somewhere.', 'ipt_fsqm' ) . '</li>' .
			'<li>' . __( '<strong>Import Form from Code:</strong> Here you can insert previously generated code to recreate the form. Enter form name (if you wish to override the name) and the code in respected fields and hit the Import from Code button. It will automatically generate the form. It will also notify you should any problem is found.', 'ipt_fsqm' ) . '</li>' .
			'</ul>' .
			'<p>' . __( 'Also as a bonus, click on the help icon beside <strong>Enter Export Code</strong> and you will get an amazing form.', 'ipt_fsqm' ) . '</p>',
		) );

		get_current_screen()->set_help_sidebar(
			'<p><strong>' . __( 'For more information:', 'ipt_fsqm' ) . '</strong></p>' .
			'<p>' . sprintf( __( '<a href="%s" target="_blank">Documentation</a>', 'ipt_fsqm' ), IPT_FSQM_Loader::$documentation ) . '</p>' .
			'<p>' . sprintf( __( '<a href="%s" target="_blank">Support Forums</a>', 'ipt_fsqm' ), IPT_FSQM_Loader::$support_forum ) . '</p>'
		);
	}

	/*==========================================================================
	 * Form HTML
	 *========================================================================*/

	public function export_code_html() {
		global $wpdb, $ipt_fsqm_info;
		$forms = $wpdb->get_results( "SELECT id, name FROM {$ipt_fsqm_info['form_table']} ORDER BY id DESC" );
		$form_select = array();
		$form_select[] = array(
			'label' => __( '--Please select a form--', 'ipt_fsqm' ),
			'value' => '',
		);
		foreach ( $forms as $form ) {
			$form_select[] = array(
				'label' => $form->name,
				'value' => $form->id,
			);
		}
		?>
<script type="text/javascript">
	jQuery(document).ready(function($) {
		$('#ipt_fsqm_export_form').on('submit', function(e) {
			// Prevent form submission
			e.preventDefault();

			// Init the variables
			var self = $(this),
			button = self.find('#export_code_generate'),
			ajax_loader = self.find('#ipt_fsqm_export_code_generator_ajax'),
			textarea = self.find('#export_code'),
			tr_to_hide = self.find('.ipt_fsqm_tr_hide'),
			ajax_data = {
				form_id: self.find('#form_id').val(),
				_wpnonce: $('#ipt_fsqm_ie_nonce').val(),
				action: 'ipt_fsqm_generate_export'
			};

			// Hide things first
			tr_to_hide.fadeOut('fast');

			// Disable the submit button
			button.prop('disabled', true);

			// Show the ajax loader
			ajax_loader.fadeIn('fast');

			$.get(ajaxurl, ajax_data, function(data) {
				// Get the message box
				var msg_tr = self.find('.ipt_fsqm_tr_hide.msg_error'),
				// Get the textarea tr
				txt_tr = self.find('.ipt_fsqm_tr_hide.export_code');

				if ( data.error ) { // There is an error, so show the error
					msg_tr.find('.ipt_uif_box.red').html('<p><strong>Error</strong>: ' + data.code + ';</p>');
					msg_tr.fadeIn('fast');
				} else { // It was successful, so show the code
					textarea.val(data.code);
					txt_tr.fadeIn('fast');
				}
			}, 'json').always(function() {
				// Enable submit button
				button.prop('disabled', false);
				// Show the ajax loader
				ajax_loader.fadeOut('fast');
			}).fail(function(jqXHR, textStatus, errorThrown) {
				// Show the message
				var msg_tr = self.find('.ipt_fsqm_tr_hide.msg_error');
				msg_tr.find('.ipt_uif_box.red').html('<p><strong>Ajax Error</strong>: Status: ' + textStatus + '; Error: ' + errorThrown + ';</p>');
				msg_tr.fadeIn('fast');
			});
		});
	});
</script>
<form action="" method="get" id="ipt_fsqm_export_form">
	<table class="form-table">
		<tbody>
			<tr>
				<th><?php $this->ui->generate_label( 'form_id', __( 'Select a form', 'ipt_fsqm' ) ); ?></th>
				<td>
					<?php $this->ui->select( 'form_id', $form_select, '' ); ?>
				</td>
				<td>
					<?php $this->ui->help( __( 'Please select a form for which you want to generate the export code.', 'ipt_fsqm' ) ); ?>
				</td>
			</tr>
			<tr style="display: none" class="ipt_fsqm_tr_hide export_code">
				<td colspan="3">
					<?php $this->ui->msg_okay( __( 'Please copy the code below', 'ipt_fsqm' ) ); ?>
					<?php $this->ui->textarea( 'export_code', '', '', 'fit', 'normal', array( 'code' ), false, false, 10 ); ?>
				</td>
			</tr>
			<tr class="ipt_fsqm_tr_hide msg_error" style="display: none">
				<td colspan="3">
					<?php $this->ui->msg_error( '' ); ?>
				</td>
			</tr>
		</tbody>
	</table>
	<div class="ipt_uif_float_left">
		<?php $this->ui->button( __( 'Generate Code', 'ipt_fsqm' ), 'export_code_generate', 'large', 'primary', 'normal', array(), 'submit' ); ?>
	</div>
	<div class="ipt_uif_float_left">
		<?php $this->ui->ajax_loader( true, 'ipt_fsqm_export_code_generator_ajax', array(), true, __( 'Generating Code', 'ipt_fsqm' ) ); ?>
	</div>
	<?php $this->ui->clear(); ?>
</form>
		<?php
	}

	public function import_code_html() {
		?>
<script type="text/javascript">
	jQuery(document).ready(function($) {
		$('#ipt_fsqm_import_form').on('submit', function(e) {
			// Prevent the submit
			e.preventDefault();

			// Get all variables
			var self = $(this),
			button = self.find('#import_code_generate'),
			ajax_loader = self.find('#ipt_fsqm_import_code_generator_ajax'),
			divs_to_hide = self.find('.hide_div'),
			okay_box = self.find('#ipt_fsqm_import_result_okay'),
			error_box = self.find('#ipt_fsqm_import_result_error'),
			ajax_data = {
				form_name: self.find('#form_name').val(),
				form_code: self.find('#form_code').val(),
				_wpnonce: $('#ipt_fsqm_ie_nonce').val(),
				action: 'ipt_fsqm_generate_import'
			};

			// Hide things first
			divs_to_hide.fadeOut('fast');

			// Disable the submit button
			button.prop('disabled', true);

			// Show the ajax loader
			ajax_loader.fadeIn('fast');

			// Post the data
			$.post(ajaxurl, ajax_data, function( data ) {
				// Get the okay box
				if ( data.error ) {
					error_box.find('.ipt_uif_box').html('<p>' + data.code + '</p>');
					error_box.fadeIn('fast');
				} else {
					okay_box.find('.ipt_uif_box').html('<p>' + data.code + '</p>');
					okay_box.fadeIn('fast');
				}
			}).always(function() {
				// Enable submit button
				button.prop('disabled', false);
				// Hide the ajax loader
				ajax_loader.fadeOut('fast');
				// Reset the values
				self.find('#form_name').val('');
				self.find('#form_code').val('');
			}).fail(function(jqXHR, textStatus, errorThrown) {
				error_box.find('.ipt_uif_box').html('<p><strong>Ajax Error</strong>: Status: ' + textStatus + '; Error: ' + errorThrown + ';</p>');
				error_box.fadeIn('fast');
			});
		});
	});
</script>
<form action="" method="get" id="ipt_fsqm_import_form">
	<table class="form-table">
		<tbody>
			<tr>
				<th>
					<?php $this->ui->generate_label( 'form_name', __( 'Enter Form Name', 'ipt_fsqm' ) ); ?>
				</th>
				<td>
					<?php $this->ui->text( 'form_name', '', __( 'Leave empty to use from the code', 'ipt_fsqm' ), 'large' ); ?>
				</td>
				<td>
					<?php $this->ui->help( __( 'You can override the form name from the code. Leaving it empty will simply use the form name available on the import code.', 'ipt_fsqm' ) ); ?>
				</td>
			</tr>
			<tr>
				<th>
					<?php $this->ui->generate_label( 'form_code', __( 'Enter Export Code', 'ipt_fsqm' ) ); ?>
				</th>
				<td>
					<?php $this->ui->textarea( 'form_code', '', __( 'Paste the export code', 'ipt_fsqm' ), 'fit', 'normal', array( 'code' ), false, false, 10 ); ?>
				</td>
				<td>
					<?php $this->ui->help_head(); ?>
					<p><?php _e( 'Please copy paste the export code here. Try the following for fun:', 'ipt_fsqm' ); ?></p>
					<code style="display: block; height: 200px; overflow: auto;">
<pre><?php $this->print_sample_import_code(); ?></pre>
					</code>
					<?php $this->ui->help_tail(); ?>
				</td>
			</tr>
		</tbody>
	</table>
	<div class="ipt_uif_float_left">
		<?php $this->ui->button( __( 'Import from Code', 'ipt_fsqm' ), 'import_code_generate', 'large', 'primary', 'normal', array(), 'submit' ); ?>
	</div>
	<div class="ipt_uif_float_left">
		<?php $this->ui->ajax_loader( true, 'ipt_fsqm_import_code_generator_ajax', array(), true, __( 'Importing Form', 'ipt_fsqm' ) ); ?>
	</div>
	<?php $this->ui->clear(); ?>
	<div class="hide_div" style="display: none;" id="ipt_fsqm_import_result_okay">
		<?php $this->ui->msg_okay( '' ); ?>
	</div>
	<div class="hide_div" style="display: none;" id="ipt_fsqm_import_result_error">
		<?php $this->ui->msg_error( '' ); ?>
	</div>
</form>
		<?php
	}

	public function print_sample_import_code() {
		?>
YToxMDp7czoyOiJpZCI7czoyOiI3NSI7czo0OiJuYW1lIjtzOjExOiJSZXN1bWUgRm9ybSI7czo4
OiJzZXR0aW5ncyI7czoyMTkxOiJhOjEwOntzOjc6ImdlbmVyYWwiO2E6Njp7czoxMDoidGVybXNf
cGFnZSI7czoxOiIwIjtzOjEyOiJ0ZXJtc19waHJhc2UiO3M6MTgwOiJCeSBzdWJtaXR0aW5nIHRo
aXMgZm9ybSwgeW91IGhlcmVieSBhZ3JlZSB0byBhY2NlcHQgb3VyIDxhIGhyZWY9IiUxJHMiIHRh
cmdldD0iX2JsYW5rIj5UZXJtcyAmIENvbmRpdGlvbnM8L2E+LiBZb3VyIElQIGFkZHJlc3MgPHN0
cm9uZz4lMiRzPC9zdHJvbmc+IHdpbGwgYmUgc3RvcmVkIGluIG91ciBkYXRhYmFzZS4iO3M6MTM6
ImNvbW1lbnRfdGl0bGUiO3M6MjE6IkFkbWluaXN0cmF0b3IgUmVtYXJrcyI7czoxNToiZGVmYXVs
dF9jb21tZW50IjtzOjEwOiJQcm9jZXNzaW5nIjtzOjg6ImNhbl9lZGl0IjtiOjE7czo5OiJlZGl0
X3RpbWUiO3M6MDoiIjt9czo0OiJ1c2VyIjthOjY6e3M6MTY6Im5vdGlmaWNhdGlvbl9zdWIiO3M6
MjU6IldlIGhhdmUgZ290IHlvdXIgYW5zd2Vycy4iO3M6MTY6Im5vdGlmaWNhdGlvbl9tc2ciO3M6
MTk1OiJUaGFuayB5b3UgJU5BTUUlIGZvciB0YWtpbmcgdGhlIHF1aXovc3VydmV5L2ZlZWRiYWNr
Lg0KV2UgaGF2ZSByZWNlaXZlZCB5b3VyIGFuc3dlcnMuIFlvdSBjYW4gdmlldyBpdCBhbnl0aW1l
IGZyb20gdGhpcyBsaW5rIGJlbG93Og0KJVRSQUNLX0xJTkslDQpIZXJlIGlzIGEgY29weSBvZiB5
b3VyIHN1Ym1pc3Npb246DQolU1VCTUlTU0lPTiUiO3M6MTc6Im5vdGlmaWNhdGlvbl9mcm9tIjtz
OjM0OiJpUGFuZWxUaGVtZXMgTG9jYWxob3N0IERldmVsb3BtZW50IjtzOjE4OiJub3RpZmljYXRp
b25fZW1haWwiO3M6MjQ6InN3YXNoYXRhQGxvY2FsaG9zdC5sb2NhbCI7czo0OiJzbXRwIjtiOjA7
czoxMToic210cF9jb25maWciO2E6NTp7czo4OiJlbmNfdHlwZSI7czozOiJzc2wiO3M6NDoiaG9z
dCI7czoxNDoic210cC5nbWFpbC5jb20iO3M6NDoicG9ydCI7czozOiI0NjUiO3M6ODoidXNlcm5h
bWUiO3M6NToiYWRtaW4iO3M6ODoicGFzc3dvcmQiO3M6NDQ6Im04aFJnY1ZTcEpzMHdGbXlXbmpM
TTFGbHRHT0hIdGd3OVJnLzBnMDlXS0U9Ijt9fXM6NToiYWRtaW4iO2E6Mzp7czo1OiJlbWFpbCI7
czoyNDoic3dhc2hhdGFAbG9jYWxob3N0LmxvY2FsIjtzOjE1OiJtYWlsX3N1Ym1pc3Npb24iO2I6
MDtzOjE0OiJzZW5kX2Zyb21fdXNlciI7YjowO31zOjEwOiJsaW1pdGF0aW9uIjthOjM6e3M6MTE6
ImVtYWlsX2xpbWl0IjtzOjE6IjAiO3M6ODoiaXBfbGltaXQiO3M6MToiMCI7czoxMDoidXNlcl9s
aW1pdCI7czoxOiIwIjt9czoxMzoidHlwZV9zcGVjaWZpYyI7YTozOntzOjEwOiJwYWdpbmF0aW9u
IjthOjE6e3M6MTc6InNob3dfcHJvZ3Jlc3NfYmFyIjtiOjE7fXM6MzoidGFiIjthOjE6e3M6MTI6
ImNhbl9wcmV2aW91cyI7YjoxO31zOjY6Im5vcm1hbCI7YToxOntzOjc6IndyYXBwZXIiO2I6MDt9
fXM6NzoiYnV0dG9ucyI7YTozOntzOjQ6Im5leHQiO3M6NDoiTmV4dCI7czo0OiJwcmV2IjtzOjg6
IlByZXZpb3VzIjtzOjY6InN1Ym1pdCI7czo2OiJTdWJtaXQiO31zOjEwOiJzdWJtaXNzaW9uIjth
OjM6e3M6MTM6InByb2Nlc3NfdGl0bGUiO3M6MjI6IlByb2Nlc3NpbmcgeW91IHJlcXVlc3QiO3M6
MTM6InN1Y2Nlc3NfdGl0bGUiO3M6Mjg6IllvdXIgZm9ybSBoYXMgYmVlbiBzdWJtaXR0ZWQiO3M6
MTU6InN1Y2Nlc3NfbWVzc2FnZSI7czozMzoiVGhhbmsgeW91IGZvciBnaXZpbmcgeW91ciBhbnN3
ZXJzIjt9czoxMToicmVkaXJlY3Rpb24iO2E6NTp7czo0OiJ0eXBlIjtzOjQ6Im5vbmUiO3M6NToi
ZGVsYXkiO3M6NDoiMTAwMCI7czozOiJ0b3AiO2I6MDtzOjM6InVybCI7czoxMToiJVRSQUNLQkFD
SyUiO3M6NToic2NvcmUiO2E6MDp7fX1zOjc6InJhbmtpbmciO2E6Mzp7czo3OiJlbmFibGVkIjti
OjA7czo1OiJ0aXRsZSI7czoxMToiRGVzaWduYXRpb24iO3M6NToicmFua3MiO2E6MDp7fX1zOjU6
InRoZW1lIjthOjQ6e3M6ODoidGVtcGxhdGUiO3M6NzoiZGVmYXVsdCI7czo0OiJsb2dvIjtzOjA6
IiI7czoxMjoiY3VzdG9tX3N0eWxlIjtiOjA7czo1OiJzdHlsZSI7YTo1OntzOjk6ImhlYWRfZm9u
dCI7czo2OiJvc3dhbGQiO3M6OToiYm9keV9mb250IjtzOjY6InJvYm90byI7czoxNDoiYmFzZV9m
b250X3NpemUiO3M6MjoiMTIiO3M6MTQ6ImhlYWRfZm9udF90eXBvIjthOjI6e3M6NDoiYm9sZCI7
YjowO3M6NjoiaXRhbGljIjtiOjA7fXM6NjoiY3VzdG9tIjtzOjA6IiI7fX19IjtzOjY6ImxheW91
dCI7czoyMDQ5OiJhOjI6e2k6MDthOjc6e3M6NDoidHlwZSI7czozOiJ0YWIiO3M6NToidGl0bGUi
O3M6ODoiSWRlbnRpZnkiO3M6ODoic3VidGl0bGUiO3M6ODoieW91cnNlbGYiO3M6MTE6ImRlc2Ny
aXB0aW9uIjtzOjA6IiI7czo2OiJtX3R5cGUiO3M6NjoibGF5b3V0IjtzOjg6ImVsZW1lbnRzIjth
OjE4OntpOjA7YTozOntzOjY6Im1fdHlwZSI7czo2OiJkZXNpZ24iO3M6NDoidHlwZSI7czo4OiJj
b2xfaGFsZiI7czozOiJrZXkiO3M6MToiMCI7fWk6MTthOjM6e3M6NjoibV90eXBlIjtzOjY6ImRl
c2lnbiI7czo0OiJ0eXBlIjtzOjg6ImNvbF9oYWxmIjtzOjM6ImtleSI7czoxOiIxIjt9aToyO2E6
Mzp7czo2OiJtX3R5cGUiO3M6MzoibWNxIjtzOjQ6InR5cGUiO3M6ODoiY2hlY2tib3giO3M6Mzoi
a2V5IjtzOjE6IjEiO31pOjM7YTozOntzOjY6Im1fdHlwZSI7czozOiJtY3EiO3M6NDoidHlwZSI7
czo2OiJzbGlkZXIiO3M6Mzoia2V5IjtzOjE6IjIiO31pOjQ7YTozOntzOjY6Im1fdHlwZSI7czoz
OiJtY3EiO3M6NDoidHlwZSI7czo2OiJzbGlkZXIiO3M6Mzoia2V5IjtzOjE6IjMiO31pOjU7YToz
OntzOjY6Im1fdHlwZSI7czozOiJtY3EiO3M6NDoidHlwZSI7czo2OiJ0b2dnbGUiO3M6Mzoia2V5
IjtzOjI6IjExIjt9aTo2O2E6Mzp7czo2OiJtX3R5cGUiO3M6MzoibWNxIjtzOjQ6InR5cGUiO3M6
MTA6InN0YXJyYXRpbmciO3M6Mzoia2V5IjtzOjE6IjQiO31pOjc7YTozOntzOjY6Im1fdHlwZSI7
czozOiJtY3EiO3M6NDoidHlwZSI7czo2OiJ0b2dnbGUiO3M6Mzoia2V5IjtzOjI6IjEyIjt9aTo4
O2E6Mzp7czo2OiJtX3R5cGUiO3M6MzoibWNxIjtzOjQ6InR5cGUiO3M6NjoidG9nZ2xlIjtzOjM6
ImtleSI7czoxOiI1Ijt9aTo5O2E6Mzp7czo2OiJtX3R5cGUiO3M6ODoiZnJlZXR5cGUiO3M6NDoi
dHlwZSI7czoxNDoiZmVlZGJhY2tfc21hbGwiO3M6Mzoia2V5IjtzOjE6IjMiO31pOjEwO2E6Mzp7
czo2OiJtX3R5cGUiO3M6ODoiZnJlZXR5cGUiO3M6NDoidHlwZSI7czoxNDoiZmVlZGJhY2tfc21h
bGwiO3M6Mzoia2V5IjtzOjE6IjQiO31pOjExO2E6Mzp7czo2OiJtX3R5cGUiO3M6ODoiZnJlZXR5
cGUiO3M6NDoidHlwZSI7czoxNDoiZmVlZGJhY2tfc21hbGwiO3M6Mzoia2V5IjtzOjE6IjUiO31p
OjEyO2E6Mzp7czo2OiJtX3R5cGUiO3M6ODoiZnJlZXR5cGUiO3M6NDoidHlwZSI7czoxNDoiZmVl
ZGJhY2tfbGFyZ2UiO3M6Mzoia2V5IjtzOjE6IjYiO31pOjEzO2E6Mzp7czo2OiJtX3R5cGUiO3M6
MzoibWNxIjtzOjQ6InR5cGUiO3M6NjoibWF0cml4IjtzOjM6ImtleSI7czoxOiI2Ijt9aToxNDth
OjM6e3M6NjoibV90eXBlIjtzOjM6Im1jcSI7czo0OiJ0eXBlIjtzOjU6InJhbmdlIjtzOjM6Imtl
eSI7czoxOiI4Ijt9aToxNTthOjM6e3M6NjoibV90eXBlIjtzOjM6Im1jcSI7czo0OiJ0eXBlIjtz
OjU6InJhbmdlIjtzOjM6ImtleSI7czoxOiI5Ijt9aToxNjthOjM6e3M6NjoibV90eXBlIjtzOjM6
Im1jcSI7czo0OiJ0eXBlIjtzOjY6InRvZ2dsZSI7czozOiJrZXkiO3M6MjoiMTMiO31pOjE3O2E6
Mzp7czo2OiJtX3R5cGUiO3M6MzoibWNxIjtzOjQ6InR5cGUiO3M6NToicmFuZ2UiO3M6Mzoia2V5
IjtzOjI6IjEwIjt9fXM6NDoiaWNvbiI7czo1OiI1NzUwNCI7fWk6MTthOjc6e3M6NDoidHlwZSI7
czozOiJ0YWIiO3M6NToidGl0bGUiO3M6NjoiVXBsb2FkIjtzOjg6InN1YnRpdGxlIjtzOjExOiJ5
b3VyIHJlc3VtZSI7czoxMToiZGVzY3JpcHRpb24iO3M6MDoiIjtzOjY6Im1fdHlwZSI7czo2OiJs
YXlvdXQiO3M6ODoiZWxlbWVudHMiO2E6Mzp7aTowO2E6Mzp7czo2OiJtX3R5cGUiO3M6ODoiZnJl
ZXR5cGUiO3M6NDoidHlwZSI7czo2OiJ1cGxvYWQiO3M6Mzoia2V5IjtzOjE6IjAiO31pOjE7YToz
OntzOjY6Im1fdHlwZSI7czo4OiJmcmVldHlwZSI7czo0OiJ0eXBlIjtzOjY6InVwbG9hZCI7czoz
OiJrZXkiO3M6MToiMSI7fWk6MjthOjM6e3M6NjoibV90eXBlIjtzOjg6ImZyZWV0eXBlIjtzOjQ6
InR5cGUiO3M6NjoidXBsb2FkIjtzOjM6ImtleSI7czoxOiIyIjt9fXM6NDoiaWNvbiI7czo1OiI1
NzQyOCI7fX0iO3M6NjoiZGVzaWduIjtzOjYxMToiYToyOntpOjA7YTo2OntzOjQ6InR5cGUiO3M6
ODoiY29sX2hhbGYiO3M6NToidGl0bGUiO3M6MDoiIjtzOjg6InN1YnRpdGxlIjtzOjA6IiI7czox
MToiZGVzY3JpcHRpb24iO3M6MDoiIjtzOjY6Im1fdHlwZSI7czo2OiJkZXNpZ24iO3M6ODoiZWxl
bWVudHMiO2E6Mjp7aTowO2E6Mzp7czo2OiJtX3R5cGUiO3M6NToicGluZm8iO3M6NDoidHlwZSI7
czo2OiJmX25hbWUiO3M6Mzoia2V5IjtzOjE6IjAiO31pOjE7YTozOntzOjY6Im1fdHlwZSI7czo1
OiJwaW5mbyI7czo0OiJ0eXBlIjtzOjY6ImxfbmFtZSI7czozOiJrZXkiO3M6MToiMSI7fX19aTox
O2E6Njp7czo0OiJ0eXBlIjtzOjg6ImNvbF9oYWxmIjtzOjU6InRpdGxlIjtzOjA6IiI7czo4OiJz
dWJ0aXRsZSI7czowOiIiO3M6MTE6ImRlc2NyaXB0aW9uIjtzOjA6IiI7czo2OiJtX3R5cGUiO3M6
NjoiZGVzaWduIjtzOjg6ImVsZW1lbnRzIjthOjI6e2k6MDthOjM6e3M6NjoibV90eXBlIjtzOjU6
InBpbmZvIjtzOjQ6InR5cGUiO3M6NToiZW1haWwiO3M6Mzoia2V5IjtzOjE6IjIiO31pOjE7YToz
OntzOjY6Im1fdHlwZSI7czozOiJtY3EiO3M6NDoidHlwZSI7czo2OiJzbGlkZXIiO3M6Mzoia2V5
IjtzOjE6IjAiO319fX0iO3M6MzoibWNxIjtzOjc4ODU6ImE6MTM6e2k6MDthOjg6e3M6NDoidHlw
ZSI7czo2OiJzbGlkZXIiO3M6NToidGl0bGUiO3M6MzoiQWdlIjtzOjEwOiJ2YWxpZGF0aW9uIjth
OjA6e31zOjg6InN1YnRpdGxlIjtzOjc6ImluIHllYXIiO3M6MTE6ImRlc2NyaXB0aW9uIjtzOjA6
IiI7czoxMToiY29uZGl0aW9uYWwiO2E6NDp7czo2OiJhY3RpdmUiO2I6MDtzOjY6InN0YXR1cyI7
YjowO3M6NjoiY2hhbmdlIjtiOjA7czo1OiJsb2dpYyI7YTowOnt9fXM6NjoibV90eXBlIjtzOjM6
Im1jcSI7czo4OiJzZXR0aW5ncyI7YTo0OntzOjM6Im1pbiI7czoyOiIxOCI7czozOiJtYXgiO3M6
MjoiNjAiO3M6NDoic3RlcCI7aToxO3M6MTA6InNob3dfY291bnQiO2I6MTt9fWk6MTthOjg6e3M6
NDoidHlwZSI7czo4OiJjaGVja2JveCI7czo1OiJ0aXRsZSI7czoyMToiU2VsZWN0IGFsbCB0aGF0
IGFwcGx5IjtzOjEwOiJ2YWxpZGF0aW9uIjthOjI6e3M6ODoicmVxdWlyZWQiO2I6MTtzOjc6ImZp
bHRlcnMiO2E6Mjp7czoxMToibWluQ2hlY2tib3giO3M6MDoiIjtzOjExOiJtYXhDaGVja2JveCI7
czowOiIiO319czo4OiJzdWJ0aXRsZSI7czoyMToiYnV0IGRvIG5vdCBleGFnZ2VyYXRlIjtzOjEx
OiJkZXNjcmlwdGlvbiI7czowOiIiO3M6MTE6ImNvbmRpdGlvbmFsIjthOjQ6e3M6NjoiYWN0aXZl
IjtiOjA7czo2OiJzdGF0dXMiO2I6MDtzOjY6ImNoYW5nZSI7YjoxO3M6NToibG9naWMiO2E6Mjp7
aTowO2E6Njp7czo2OiJtX3R5cGUiO3M6ODoiZnJlZXR5cGUiO3M6Mzoia2V5IjtzOjE6IjAiO3M6
NToiY2hlY2siO3M6MzoibGVuIjtzOjg6Im9wZXJhdG9yIjtzOjI6ImVxIjtzOjU6InZhbHVlIjtz
OjE6IjIiO3M6MzoicmVsIjtzOjM6ImFuZCI7fWk6MTthOjY6e3M6NjoibV90eXBlIjtzOjg6ImZy
ZWV0eXBlIjtzOjM6ImtleSI7czoxOiIxIjtzOjU6ImNoZWNrIjtzOjM6ImxlbiI7czo4OiJvcGVy
YXRvciI7czozOiJuZXEiO3M6NToidmFsdWUiO3M6MToiMSI7czozOiJyZWwiO3M6MzoiYW5kIjt9
fX1zOjY6Im1fdHlwZSI7czozOiJtY3EiO3M6ODoic2V0dGluZ3MiO2E6NDp7czo3OiJvcHRpb25z
IjthOjQ6e2k6MDthOjI6e3M6NToibGFiZWwiO3M6MTY6IkkgYW0gYSBQSFAgTmluamEiO3M6NToi
c2NvcmUiO3M6MDoiIjt9aToxO2E6Mjp7czo1OiJsYWJlbCI7czoxNjoiSSBsb3ZlIFdvcmRQcmVz
cyI7czo1OiJzY29yZSI7czowOiIiO31pOjI7YToyOntzOjU6ImxhYmVsIjtzOjU4OiJDU1MzIGFu
ZCBqUXVlcnkgaXMgd2hhdCBJIHVzZSB0byBwZXJzb25pZnkgbXkgaW1hZ2luYXRpb25zIjtzOjU6
InNjb3JlIjtzOjA6IiI7fWk6MzthOjI6e3M6NToibGFiZWwiO3M6NDA6Ik15U1FMIHNpbXBseSBt
ZWFucyBhIHNwYWNlIHRvIHN0b3JlIGRhdGEiO3M6NToic2NvcmUiO3M6MDoiIjt9fXM6NzoiY29s
dW1ucyI7czoxOiIxIjtzOjY6Im90aGVycyI7YjoxO3M6Nzoib19sYWJlbCI7czo2OiJPdGhlcnMi
O319aToyO2E6ODp7czo0OiJ0eXBlIjtzOjY6InNsaWRlciI7czo1OiJ0aXRsZSI7czozNToiWWVh
cnMgb2YgUEhQIGRldmVsb3BtZW50IGV4cGVyaWVuY2UiO3M6MTA6InZhbGlkYXRpb24iO2E6MDp7
fXM6ODoic3VidGl0bGUiO3M6MDoiIjtzOjExOiJkZXNjcmlwdGlvbiI7czowOiIiO3M6MTE6ImNv
bmRpdGlvbmFsIjthOjQ6e3M6NjoiYWN0aXZlIjtiOjE7czo2OiJzdGF0dXMiO2I6MDtzOjY6ImNo
YW5nZSI7YjoxO3M6NToibG9naWMiO2E6MTp7aTowO2E6Njp7czo2OiJtX3R5cGUiO3M6MzoibWNx
IjtzOjM6ImtleSI7czoxOiIxIjtzOjU6ImNoZWNrIjtzOjM6InZhbCI7czo4OiJvcGVyYXRvciI7
czoyOiJjdCI7czo1OiJ2YWx1ZSI7czozOiJQSFAiO3M6MzoicmVsIjtzOjM6ImFuZCI7fX19czo2
OiJtX3R5cGUiO3M6MzoibWNxIjtzOjg6InNldHRpbmdzIjthOjQ6e3M6MzoibWluIjtzOjE6IjAi
O3M6MzoibWF4IjtzOjI6IjUwIjtzOjQ6InN0ZXAiO2k6MTtzOjEwOiJzaG93X2NvdW50IjtiOjE7
fX1pOjM7YTo4OntzOjQ6InR5cGUiO3M6Njoic2xpZGVyIjtzOjU6InRpdGxlIjtzOjI5OiJZZWFy
cyBvZiBXb3JkUHJlc3MgZXhwZXJpZW5jZSI7czoxMDoidmFsaWRhdGlvbiI7YTowOnt9czo4OiJz
dWJ0aXRsZSI7czowOiIiO3M6MTE6ImRlc2NyaXB0aW9uIjtzOjA6IiI7czoxMToiY29uZGl0aW9u
YWwiO2E6NDp7czo2OiJhY3RpdmUiO2I6MTtzOjY6InN0YXR1cyI7YjowO3M6NjoiY2hhbmdlIjti
OjE7czo1OiJsb2dpYyI7YToxOntpOjA7YTo2OntzOjY6Im1fdHlwZSI7czozOiJtY3EiO3M6Mzoi
a2V5IjtzOjE6IjEiO3M6NToiY2hlY2siO3M6MzoidmFsIjtzOjg6Im9wZXJhdG9yIjtzOjI6ImN0
IjtzOjU6InZhbHVlIjtzOjk6IldvcmRQcmVzcyI7czozOiJyZWwiO3M6MzoiYW5kIjt9fX1zOjY6
Im1fdHlwZSI7czozOiJtY3EiO3M6ODoic2V0dGluZ3MiO2E6NDp7czozOiJtaW4iO3M6MToiMCI7
czozOiJtYXgiO3M6MjoiNTAiO3M6NDoic3RlcCI7aToxO3M6MTA6InNob3dfY291bnQiO2I6MTt9
fWk6MTE7YTo4OntzOjQ6InR5cGUiO3M6NjoidG9nZ2xlIjtzOjU6InRpdGxlIjtzOjIxOiJZb3Ug
YSBXb3JkUHJlc3MgTmluamEiO3M6MTA6InZhbGlkYXRpb24iO2E6MDp7fXM6ODoic3VidGl0bGUi
O3M6MTc6Im5vdyBkb24ndCBiZSBzaHkhIjtzOjExOiJkZXNjcmlwdGlvbiI7czowOiIiO3M6MTE6
ImNvbmRpdGlvbmFsIjthOjQ6e3M6NjoiYWN0aXZlIjtiOjE7czo2OiJzdGF0dXMiO2I6MDtzOjY6
ImNoYW5nZSI7YjoxO3M6NToibG9naWMiO2E6MTp7aTowO2E6Njp7czo2OiJtX3R5cGUiO3M6Mzoi
bWNxIjtzOjM6ImtleSI7czoxOiIzIjtzOjU6ImNoZWNrIjtzOjM6InZhbCI7czo4OiJvcGVyYXRv
ciI7czoyOiJndCI7czo1OiJ2YWx1ZSI7czoyOiI0MCI7czozOiJyZWwiO3M6MzoiYW5kIjt9fX1z
OjY6Im1fdHlwZSI7czozOiJtY3EiO3M6ODoic2V0dGluZ3MiO2E6Mzp7czoyOiJvbiI7czo0OiJZ
ZWFwIjtzOjM6Im9mZiI7czo0OiJOb3BlIjtzOjc6ImNoZWNrZWQiO2I6MDt9fWk6NDthOjg6e3M6
NDoidHlwZSI7czoxMDoic3RhcnJhdGluZyI7czo1OiJ0aXRsZSI7czo3OiJSYXRlIGl0IjtzOjEw
OiJ2YWxpZGF0aW9uIjthOjE6e3M6ODoicmVxdWlyZWQiO2I6MTt9czo4OiJzdWJ0aXRsZSI7czox
NjoidGhlIHdheSB5b3Ugd2FudCI7czoxMToiZGVzY3JpcHRpb24iO3M6MDoiIjtzOjExOiJjb25k
aXRpb25hbCI7YTo0OntzOjY6ImFjdGl2ZSI7YjowO3M6Njoic3RhdHVzIjtiOjA7czo2OiJjaGFu
Z2UiO2I6MTtzOjU6ImxvZ2ljIjthOjA6e319czo2OiJtX3R5cGUiO3M6MzoibWNxIjtzOjg6InNl
dHRpbmdzIjthOjI6e3M6Nzoib3B0aW9ucyI7YToyOntpOjA7czoxNDoiVXNlciBJbnRlcmZhY2Ui
O2k6MTtzOjg6Ik5pY2VuZXNzIjt9czozOiJtYXgiO3M6MjoiMTAiO319aToxMjthOjg6e3M6NDoi
dHlwZSI7czo2OiJ0b2dnbGUiO3M6NToidGl0bGUiO3M6MTU6IlNvIHlvdSBsaWtlIHVzPyI7czox
MDoidmFsaWRhdGlvbiI7YTowOnt9czo4OiJzdWJ0aXRsZSI7czowOiIiO3M6MTE6ImRlc2NyaXB0
aW9uIjtzOjA6IiI7czoxMToiY29uZGl0aW9uYWwiO2E6NDp7czo2OiJhY3RpdmUiO2I6MTtzOjY6
InN0YXR1cyI7YjowO3M6NjoiY2hhbmdlIjtiOjE7czo1OiJsb2dpYyI7YToxOntpOjA7YTo2Ontz
OjY6Im1fdHlwZSI7czozOiJtY3EiO3M6Mzoia2V5IjtzOjE6IjQiO3M6NToiY2hlY2siO3M6Mzoi
dmFsIjtzOjg6Im9wZXJhdG9yIjtzOjI6Imd0IjtzOjU6InZhbHVlIjtzOjE6IjYiO3M6MzoicmVs
IjtzOjM6ImFuZCI7fX19czo2OiJtX3R5cGUiO3M6MzoibWNxIjtzOjg6InNldHRpbmdzIjthOjM6
e3M6Mjoib24iO3M6NDoiWWVhaCI7czozOiJvZmYiO3M6NDoiTm9wZSI7czo3OiJjaGVja2VkIjti
OjA7fX1pOjU7YTo4OntzOjQ6InR5cGUiO3M6NjoidG9nZ2xlIjtzOjU6InRpdGxlIjtzOjM0OiJX
YW5uYSBhbnN3ZXIgYSBmZXcgbW9yZSBxdWVzdGlvbnM/IjtzOjEwOiJ2YWxpZGF0aW9uIjthOjA6
e31zOjg6InN1YnRpdGxlIjtzOjI0OiJjb21tb24gdGhhdCB3aWxsIGJlIGZ1biEiO3M6MTE6ImRl
c2NyaXB0aW9uIjtzOjA6IiI7czoxMToiY29uZGl0aW9uYWwiO2E6NDp7czo2OiJhY3RpdmUiO2I6
MDtzOjY6InN0YXR1cyI7YjowO3M6NjoiY2hhbmdlIjtiOjE7czo1OiJsb2dpYyI7YTowOnt9fXM6
NjoibV90eXBlIjtzOjM6Im1jcSI7czo4OiJzZXR0aW5ncyI7YTozOntzOjI6Im9uIjtzOjQ6Illl
YWgiO3M6Mzoib2ZmIjtzOjQ6Ik5vcGUiO3M6NzoiY2hlY2tlZCI7YjowO319aTo2O2E6ODp7czo0
OiJ0eXBlIjtzOjY6Im1hdHJpeCI7czo1OiJ0aXRsZSI7czoxMzoiWW91ciBzdWJqZWN0cyI7czox
MDoidmFsaWRhdGlvbiI7YToxOntzOjg6InJlcXVpcmVkIjtiOjE7fXM6ODoic3VidGl0bGUiO3M6
MjY6ImZvciBkaWZmZXJlbnQgaW5zdGl0dXRpb25zIjtzOjExOiJkZXNjcmlwdGlvbiI7czowOiIi
O3M6MTE6ImNvbmRpdGlvbmFsIjthOjQ6e3M6NjoiYWN0aXZlIjtiOjE7czo2OiJzdGF0dXMiO2I6
MDtzOjY6ImNoYW5nZSI7YjoxO3M6NToibG9naWMiO2E6MTp7aTowO2E6Njp7czo2OiJtX3R5cGUi
O3M6MzoibWNxIjtzOjM6ImtleSI7czoxOiI1IjtzOjU6ImNoZWNrIjtzOjM6InZhbCI7czo4OiJv
cGVyYXRvciI7czoyOiJlcSI7czo1OiJ2YWx1ZSI7czoxOiIxIjtzOjM6InJlbCI7czozOiJhbmQi
O319fXM6NjoibV90eXBlIjtzOjM6Im1jcSI7czo4OiJzZXR0aW5ncyI7YTo0OntzOjQ6InJvd3Mi
O2E6Mzp7aTowO3M6MTE6IkhpZ2ggU2Nob29sIjtpOjE7czo3OiJDb2xsZWdlIjtpOjI7czoxMDoi
VW5pdmVyc2l0eSI7fXM6NzoiY29sdW1ucyI7YTozOntpOjA7czo3OiJQaHlzaWNzIjtpOjE7czox
MToiTWF0aGVtYXRpY3MiO2k6MjtzOjk6IkNoZW1pc3RyeSI7fXM6Njoic2NvcmVzIjthOjM6e2k6
MDtzOjA6IiI7aToxO3M6MDoiIjtpOjI7czowOiIiO31zOjg6Im11bHRpcGxlIjtiOjE7fX1pOjg7
YTo4OntzOjQ6InR5cGUiO3M6NToicmFuZ2UiO3M6NToidGl0bGUiO3M6MTk6IlBoeXNpY3MgU2Nv
cmUgUmFuZ2UiO3M6MTA6InZhbGlkYXRpb24iO2E6MDp7fXM6ODoic3VidGl0bGUiO3M6MzQ6Im1p
bmltdW0gdG8gbWF4aW11bSAoaW4gcGVyY2VudGFnZSkiO3M6MTE6ImRlc2NyaXB0aW9uIjtzOjA6
IiI7czoxMToiY29uZGl0aW9uYWwiO2E6NDp7czo2OiJhY3RpdmUiO2I6MTtzOjY6InN0YXR1cyI7
YjowO3M6NjoiY2hhbmdlIjtiOjE7czo1OiJsb2dpYyI7YToyOntpOjA7YTo2OntzOjY6Im1fdHlw
ZSI7czozOiJtY3EiO3M6Mzoia2V5IjtzOjE6IjYiO3M6NToiY2hlY2siO3M6MzoidmFsIjtzOjg6
Im9wZXJhdG9yIjtzOjI6ImN0IjtzOjU6InZhbHVlIjtzOjc6InBoeXNpY3MiO3M6MzoicmVsIjtz
OjM6ImFuZCI7fWk6MTthOjY6e3M6NjoibV90eXBlIjtzOjM6Im1jcSI7czozOiJrZXkiO3M6MToi
NSI7czo1OiJjaGVjayI7czozOiJ2YWwiO3M6ODoib3BlcmF0b3IiO3M6MjoiZXEiO3M6NToidmFs
dWUiO3M6MToiMSI7czozOiJyZWwiO3M6MzoiYW5kIjt9fX1zOjY6Im1fdHlwZSI7czozOiJtY3Ei
O3M6ODoic2V0dGluZ3MiO2E6NDp7czozOiJtaW4iO3M6MToiMCI7czozOiJtYXgiO3M6MzoiMTAw
IjtzOjQ6InN0ZXAiO2k6MTtzOjEwOiJzaG93X2NvdW50IjtiOjE7fX1pOjk7YTo4OntzOjQ6InR5
cGUiO3M6NToicmFuZ2UiO3M6NToidGl0bGUiO3M6MjM6Ik1hdGhlbWF0aWNzIFNjb3JlIFJhbmdl
IjtzOjEwOiJ2YWxpZGF0aW9uIjthOjA6e31zOjg6InN1YnRpdGxlIjtzOjM0OiJtaW5pbXVtIHRv
IG1heGltdW0gKGluIHBlcmNlbnRhZ2UpIjtzOjExOiJkZXNjcmlwdGlvbiI7czowOiIiO3M6MTE6
ImNvbmRpdGlvbmFsIjthOjQ6e3M6NjoiYWN0aXZlIjtiOjE7czo2OiJzdGF0dXMiO2I6MDtzOjY6
ImNoYW5nZSI7YjoxO3M6NToibG9naWMiO2E6Mjp7aTowO2E6Njp7czo2OiJtX3R5cGUiO3M6Mzoi
bWNxIjtzOjM6ImtleSI7czoxOiI2IjtzOjU6ImNoZWNrIjtzOjM6InZhbCI7czo4OiJvcGVyYXRv
ciI7czoyOiJjdCI7czo1OiJ2YWx1ZSI7czo0OiJtYXRoIjtzOjM6InJlbCI7czozOiJhbmQiO31p
OjE7YTo2OntzOjY6Im1fdHlwZSI7czozOiJtY3EiO3M6Mzoia2V5IjtzOjE6IjUiO3M6NToiY2hl
Y2siO3M6MzoidmFsIjtzOjg6Im9wZXJhdG9yIjtzOjI6ImVxIjtzOjU6InZhbHVlIjtzOjE6IjEi
O3M6MzoicmVsIjtzOjM6ImFuZCI7fX19czo2OiJtX3R5cGUiO3M6MzoibWNxIjtzOjg6InNldHRp
bmdzIjthOjQ6e3M6MzoibWluIjtzOjE6IjAiO3M6MzoibWF4IjtzOjM6IjEwMCI7czo0OiJzdGVw
IjtpOjE7czoxMDoic2hvd19jb3VudCI7YjoxO319aToxMzthOjg6e3M6NDoidHlwZSI7czo2OiJ0
b2dnbGUiO3M6NToidGl0bGUiO3M6MzQ6IkRvIHlvdSBrbm93IGRpZmZlcmVudGlhbCBjYWxjdWx1
cz8iO3M6MTA6InZhbGlkYXRpb24iO2E6MDp7fXM6ODoic3VidGl0bGUiO3M6MzE6IkF0IHlvdXIg
c2NvcmUgaXQgc2hvdWxkIGJlIGVhc3kiO3M6MTE6ImRlc2NyaXB0aW9uIjtzOjA6IiI7czoxMToi
Y29uZGl0aW9uYWwiO2E6NDp7czo2OiJhY3RpdmUiO2I6MTtzOjY6InN0YXR1cyI7YjowO3M6Njoi
Y2hhbmdlIjtiOjE7czo1OiJsb2dpYyI7YTozOntpOjA7YTo2OntzOjY6Im1fdHlwZSI7czozOiJt
Y3EiO3M6Mzoia2V5IjtzOjE6IjkiO3M6NToiY2hlY2siO3M6MzoidmFsIjtzOjg6Im9wZXJhdG9y
IjtzOjI6Imd0IjtzOjU6InZhbHVlIjtzOjI6IjY5IjtzOjM6InJlbCI7czozOiJhbmQiO31pOjE7
YTo2OntzOjY6Im1fdHlwZSI7czozOiJtY3EiO3M6Mzoia2V5IjtzOjE6IjYiO3M6NToiY2hlY2si
O3M6MzoidmFsIjtzOjg6Im9wZXJhdG9yIjtzOjI6ImN0IjtzOjU6InZhbHVlIjtzOjQ6Im1hdGgi
O3M6MzoicmVsIjtzOjM6ImFuZCI7fWk6MjthOjY6e3M6NjoibV90eXBlIjtzOjM6Im1jcSI7czoz
OiJrZXkiO3M6MToiNSI7czo1OiJjaGVjayI7czozOiJ2YWwiO3M6ODoib3BlcmF0b3IiO3M6Mjoi
ZXEiO3M6NToidmFsdWUiO3M6MToiMSI7czozOiJyZWwiO3M6MzoiYW5kIjt9fX1zOjY6Im1fdHlw
ZSI7czozOiJtY3EiO3M6ODoic2V0dGluZ3MiO2E6Mzp7czoyOiJvbiI7czo0OiJZZWFoIjtzOjM6
Im9mZiI7czo0OiJOb3BlIjtzOjc6ImNoZWNrZWQiO2I6MDt9fWk6MTA7YTo4OntzOjQ6InR5cGUi
O3M6NToicmFuZ2UiO3M6NToidGl0bGUiO3M6MjE6IkNoZW1pc3RyeSBTY29yZSBSYW5nZSI7czox
MDoidmFsaWRhdGlvbiI7YTowOnt9czo4OiJzdWJ0aXRsZSI7czozNDoibWluaW11bSB0byBtYXhp
bXVtIChpbiBwZXJjZW50YWdlKSI7czoxMToiZGVzY3JpcHRpb24iO3M6MDoiIjtzOjExOiJjb25k
aXRpb25hbCI7YTo0OntzOjY6ImFjdGl2ZSI7YjoxO3M6Njoic3RhdHVzIjtiOjA7czo2OiJjaGFu
Z2UiO2I6MTtzOjU6ImxvZ2ljIjthOjM6e2k6MDthOjY6e3M6NjoibV90eXBlIjtzOjM6Im1jcSI7
czozOiJrZXkiO3M6MToiNiI7czo1OiJjaGVjayI7czozOiJ2YWwiO3M6ODoib3BlcmF0b3IiO3M6
MjoiY3QiO3M6NToidmFsdWUiO3M6OToiY2hlbWlzdHJ5IjtzOjM6InJlbCI7czozOiJhbmQiO31p
OjE7YTo2OntzOjY6Im1fdHlwZSI7czozOiJtY3EiO3M6Mzoia2V5IjtzOjE6IjUiO3M6NToiY2hl
Y2siO3M6MzoidmFsIjtzOjg6Im9wZXJhdG9yIjtzOjI6ImVxIjtzOjU6InZhbHVlIjtzOjE6IjEi
O3M6MzoicmVsIjtzOjM6ImFuZCI7fWk6MjthOjY6e3M6NjoibV90eXBlIjtzOjM6Im1jcSI7czoz
OiJrZXkiO3M6MToiMCI7czo1OiJjaGVjayI7czozOiJ2YWwiO3M6ODoib3BlcmF0b3IiO3M6Mjoi
ZXEiO3M6NToidmFsdWUiO3M6MDoiIjtzOjM6InJlbCI7czozOiJhbmQiO319fXM6NjoibV90eXBl
IjtzOjM6Im1jcSI7czo4OiJzZXR0aW5ncyI7YTo0OntzOjM6Im1pbiI7czoxOiIwIjtzOjM6Im1h
eCI7czozOiIxMDAiO3M6NDoic3RlcCI7aToxO3M6MTA6InNob3dfY291bnQiO2I6MTt9fX0iO3M6
ODoiZnJlZXR5cGUiO3M6NTc1MDoiYTo3OntpOjM7YTo4OntzOjQ6InR5cGUiO3M6MTQ6ImZlZWRi
YWNrX3NtYWxsIjtzOjU6InRpdGxlIjtzOjE4OiJXaGVyZSBkbyB5b3UgbGl2ZT8iO3M6MTA6InZh
bGlkYXRpb24iO2E6Mjp7czo4OiJyZXF1aXJlZCI7YjoxO3M6NzoiZmlsdGVycyI7YTo1OntzOjQ6
InR5cGUiO3M6MzoiYWxsIjtzOjM6Im1pbiI7czowOiIiO3M6MzoibWF4IjtzOjA6IiI7czo3OiJt
aW5TaXplIjtzOjA6IiI7czo3OiJtYXhTaXplIjtzOjA6IiI7fX1zOjg6InN1YnRpdGxlIjtzOjMw
OiJqdXN0IHRoZSBjb3VudHJ5IHdvdWxkIGJlIGZpbmUiO3M6MTE6ImRlc2NyaXB0aW9uIjtzOjA6
IiI7czoxMToiY29uZGl0aW9uYWwiO2E6NDp7czo2OiJhY3RpdmUiO2I6MTtzOjY6InN0YXR1cyI7
YjowO3M6NjoiY2hhbmdlIjtiOjE7czo1OiJsb2dpYyI7YToxOntpOjA7YTo2OntzOjY6Im1fdHlw
ZSI7czozOiJtY3EiO3M6Mzoia2V5IjtzOjE6IjUiO3M6NToiY2hlY2siO3M6MzoidmFsIjtzOjg6
Im9wZXJhdG9yIjtzOjI6ImVxIjtzOjU6InZhbHVlIjtzOjE6IjEiO3M6MzoicmVsIjtzOjM6ImFu
ZCI7fX19czo2OiJtX3R5cGUiO3M6ODoiZnJlZXR5cGUiO3M6ODoic2V0dGluZ3MiO2E6NDp7czo1
OiJlbWFpbCI7czowOiIiO3M6NDoiaWNvbiI7czo1OiI1NzM0NSI7czoxMToicGxhY2Vob2xkZXIi
O3M6MTA6IldyaXRlIGhlcmUiO3M6NToic2NvcmUiO3M6MDoiIjt9fWk6NDthOjg6e3M6NDoidHlw
ZSI7czoxNDoiZmVlZGJhY2tfc21hbGwiO3M6NToidGl0bGUiO3M6MzY6IkluZGlhPyBUaGF0J3Mg
Z3JlYXQhIEluIHdoaWNoIHN0YXRlPyI7czoxMDoidmFsaWRhdGlvbiI7YToyOntzOjg6InJlcXVp
cmVkIjtiOjE7czo3OiJmaWx0ZXJzIjthOjU6e3M6NDoidHlwZSI7czozOiJhbGwiO3M6MzoibWlu
IjtzOjA6IiI7czozOiJtYXgiO3M6MDoiIjtzOjc6Im1pblNpemUiO3M6MDoiIjtzOjc6Im1heFNp
emUiO3M6MDoiIjt9fXM6ODoic3VidGl0bGUiO3M6MjI6IldlIGxvdmUgSW5kaWEgZG9uJ3Qgd2Ui
O3M6MTE6ImRlc2NyaXB0aW9uIjtzOjA6IiI7czoxMToiY29uZGl0aW9uYWwiO2E6NDp7czo2OiJh
Y3RpdmUiO2I6MTtzOjY6InN0YXR1cyI7YjowO3M6NjoiY2hhbmdlIjtiOjE7czo1OiJsb2dpYyI7
YToyOntpOjA7YTo2OntzOjY6Im1fdHlwZSI7czo4OiJmcmVldHlwZSI7czozOiJrZXkiO3M6MToi
MyI7czo1OiJjaGVjayI7czozOiJ2YWwiO3M6ODoib3BlcmF0b3IiO3M6MjoiZXEiO3M6NToidmFs
dWUiO3M6NToiaW5kaWEiO3M6MzoicmVsIjtzOjM6ImFuZCI7fWk6MTthOjY6e3M6NjoibV90eXBl
IjtzOjM6Im1jcSI7czozOiJrZXkiO3M6MToiNSI7czo1OiJjaGVjayI7czozOiJ2YWwiO3M6ODoi
b3BlcmF0b3IiO3M6MjoiZXEiO3M6NToidmFsdWUiO3M6MToiMSI7czozOiJyZWwiO3M6MzoiYW5k
Ijt9fX1zOjY6Im1fdHlwZSI7czo4OiJmcmVldHlwZSI7czo4OiJzZXR0aW5ncyI7YTo0OntzOjU6
ImVtYWlsIjtzOjA6IiI7czo0OiJpY29uIjtzOjU6IjU3MzQ1IjtzOjExOiJwbGFjZWhvbGRlciI7
czoxMDoiV3JpdGUgaGVyZSI7czo1OiJzY29yZSI7czowOiIiO319aTo1O2E6ODp7czo0OiJ0eXBl
IjtzOjE0OiJmZWVkYmFja19zbWFsbCI7czo1OiJ0aXRsZSI7czozMzoiUGxlYXNlIGFsc28gbGV0
IHVzIGtub3cgeW91ciBjaXR5IjtzOjEwOiJ2YWxpZGF0aW9uIjthOjI6e3M6ODoicmVxdWlyZWQi
O2I6MTtzOjc6ImZpbHRlcnMiO2E6NTp7czo0OiJ0eXBlIjtzOjM6ImFsbCI7czozOiJtaW4iO3M6
MDoiIjtzOjM6Im1heCI7czowOiIiO3M6NzoibWluU2l6ZSI7czowOiIiO3M6NzoibWF4U2l6ZSI7
czowOiIiO319czo4OiJzdWJ0aXRsZSI7czozMDoiQ2F1c2Ugd2UnZCBhbHdheXMgbGlrZSB0byBr
bm93IjtzOjExOiJkZXNjcmlwdGlvbiI7czowOiIiO3M6MTE6ImNvbmRpdGlvbmFsIjthOjQ6e3M6
NjoiYWN0aXZlIjtiOjE7czo2OiJzdGF0dXMiO2I6MDtzOjY6ImNoYW5nZSI7YjoxO3M6NToibG9n
aWMiO2E6Mzp7aTowO2E6Njp7czo2OiJtX3R5cGUiO3M6ODoiZnJlZXR5cGUiO3M6Mzoia2V5Ijtz
OjE6IjQiO3M6NToiY2hlY2siO3M6MzoibGVuIjtzOjg6Im9wZXJhdG9yIjtzOjI6Imd0IjtzOjU6
InZhbHVlIjtzOjE6IjEiO3M6MzoicmVsIjtzOjM6ImFuZCI7fWk6MTthOjY6e3M6NjoibV90eXBl
IjtzOjM6Im1jcSI7czozOiJrZXkiO3M6MToiNSI7czo1OiJjaGVjayI7czozOiJ2YWwiO3M6ODoi
b3BlcmF0b3IiO3M6MjoiZXEiO3M6NToidmFsdWUiO3M6MToiMSI7czozOiJyZWwiO3M6MzoiYW5k
Ijt9aToyO2E6Njp7czo2OiJtX3R5cGUiO3M6ODoiZnJlZXR5cGUiO3M6Mzoia2V5IjtzOjE6IjMi
O3M6NToiY2hlY2siO3M6MzoidmFsIjtzOjg6Im9wZXJhdG9yIjtzOjI6ImVxIjtzOjU6InZhbHVl
IjtzOjU6ImluZGlhIjtzOjM6InJlbCI7czozOiJhbmQiO319fXM6NjoibV90eXBlIjtzOjg6ImZy
ZWV0eXBlIjtzOjg6InNldHRpbmdzIjthOjQ6e3M6NToiZW1haWwiO3M6MDoiIjtzOjQ6Imljb24i
O3M6NToiNTczNDUiO3M6MTE6InBsYWNlaG9sZGVyIjtzOjEwOiJXcml0ZSBoZXJlIjtzOjU6InNj
b3JlIjtzOjA6IiI7fX1pOjY7YTo4OntzOjQ6InR5cGUiO3M6MTQ6ImZlZWRiYWNrX2xhcmdlIjtz
OjU6InRpdGxlIjtzOjE4OiJHaXZlIHlvdXIgYWRkcmVzcz8iO3M6MTA6InZhbGlkYXRpb24iO2E6
MTp7czo4OiJyZXF1aXJlZCI7YjowO31zOjg6InN1YnRpdGxlIjtzOjIyOiJ3ZSBsaXZlIGF0IGtv
bGthdGEgdG9vIjtzOjExOiJkZXNjcmlwdGlvbiI7czowOiIiO3M6MTE6ImNvbmRpdGlvbmFsIjth
OjQ6e3M6NjoiYWN0aXZlIjtiOjE7czo2OiJzdGF0dXMiO2I6MDtzOjY6ImNoYW5nZSI7YjoxO3M6
NToibG9naWMiO2E6NDp7aTowO2E6Njp7czo2OiJtX3R5cGUiO3M6MzoibWNxIjtzOjM6ImtleSI7
czoxOiI1IjtzOjU6ImNoZWNrIjtzOjM6InZhbCI7czo4OiJvcGVyYXRvciI7czoyOiJlcSI7czo1
OiJ2YWx1ZSI7czoxOiIxIjtzOjM6InJlbCI7czozOiJhbmQiO31pOjE7YTo2OntzOjY6Im1fdHlw
ZSI7czo4OiJmcmVldHlwZSI7czozOiJrZXkiO3M6MToiMyI7czo1OiJjaGVjayI7czozOiJ2YWwi
O3M6ODoib3BlcmF0b3IiO3M6MjoiZXEiO3M6NToidmFsdWUiO3M6NToiaW5kaWEiO3M6MzoicmVs
IjtzOjM6ImFuZCI7fWk6MjthOjY6e3M6NjoibV90eXBlIjtzOjg6ImZyZWV0eXBlIjtzOjM6Imtl
eSI7czoxOiI0IjtzOjU6ImNoZWNrIjtzOjM6ImxlbiI7czo4OiJvcGVyYXRvciI7czoyOiJndCI7
czo1OiJ2YWx1ZSI7czoxOiIxIjtzOjM6InJlbCI7czozOiJhbmQiO31pOjM7YTo2OntzOjY6Im1f
dHlwZSI7czo4OiJmcmVldHlwZSI7czozOiJrZXkiO3M6MToiNSI7czo1OiJjaGVjayI7czozOiJ2
YWwiO3M6ODoib3BlcmF0b3IiO3M6MjoiZXEiO3M6NToidmFsdWUiO3M6Nzoia29sa2F0YSI7czoz
OiJyZWwiO3M6MzoiYW5kIjt9fX1zOjY6Im1fdHlwZSI7czo4OiJmcmVldHlwZSI7czo4OiJzZXR0
aW5ncyI7YTozOntzOjU6ImVtYWlsIjtzOjA6IiI7czoxMToicGxhY2Vob2xkZXIiO3M6MTA6Ildy
aXRlIGhlcmUiO3M6NToic2NvcmUiO3M6MDoiIjt9fWk6MDthOjg6e3M6NDoidHlwZSI7czo2OiJ1
cGxvYWQiO3M6NToidGl0bGUiO3M6MjU6IlBsZWFzZSB1cGxvYWQgeW91ciByZXN1bWUiO3M6MTA6
InZhbGlkYXRpb24iO2E6MTp7czo4OiJyZXF1aXJlZCI7YjoxO31zOjg6InN1YnRpdGxlIjtzOjA6
IiI7czoxMToiZGVzY3JpcHRpb24iO3M6NTQ6IkRvY3VtZW50cyBvbmx5LiBTaG91bGQgY29udGFp
biB5b3VyIHNjYW5uZWQgc2lnbmF0dXJlLiI7czoxMToiY29uZGl0aW9uYWwiO2E6NDp7czo2OiJh
Y3RpdmUiO2I6MDtzOjY6InN0YXR1cyI7YjowO3M6NjoiY2hhbmdlIjtiOjA7czo1OiJsb2dpYyI7
YTowOnt9fXM6NjoibV90eXBlIjtzOjg6ImZyZWV0eXBlIjtzOjg6InNldHRpbmdzIjthOjEyOntz
OjQ6Imljb24iO3M6NToiNTc3ODciO3M6MTc6ImFjY2VwdF9maWxlX3R5cGVzIjtzOjEyOiJkb2Ms
ZG9jeCxwZGYiO3M6MTk6Im1heF9udW1iZXJfb2ZfZmlsZXMiO3M6MToiMiI7czoxOToibWluX251
bWJlcl9vZl9maWxlcyI7czowOiIiO3M6MTM6Im1heF9maWxlX3NpemUiO3M6NzoiODM4ODYwOCI7
czoxMzoibWluX2ZpbGVfc2l6ZSI7czoxOiIxIjtzOjIwOiJ3cF9tZWRpYV9pbnRlZ3JhdGlvbiI7
YjowO3M6MTE6ImF1dG9fdXBsb2FkIjtiOjA7czoxMToiZHJhZ19uX2Ryb3AiO2I6MTtzOjEyOiJw
cm9ncmVzc19iYXIiO2I6MTtzOjEzOiJwcmV2aWV3X21lZGlhIjtiOjE7czoxMDoiY2FuX2RlbGV0
ZSI7YjoxO319aToxO2E6ODp7czo0OiJ0eXBlIjtzOjY6InVwbG9hZCI7czo1OiJ0aXRsZSI7czox
NzoiVXBsb2FkIHlvdXIgcGhvdG8iO3M6MTA6InZhbGlkYXRpb24iO2E6MTp7czo4OiJyZXF1aXJl
ZCI7YjoxO31zOjg6InN1YnRpdGxlIjtzOjA6IiI7czoxMToiZGVzY3JpcHRpb24iO3M6NDk6Iklt
YWdlIG9ubHkuIFNob3VsZCBiZSBhdCBsZWFzdCA2MDBYNjAwcHggaW4gc2l6ZS4iO3M6MTE6ImNv
bmRpdGlvbmFsIjthOjQ6e3M6NjoiYWN0aXZlIjtiOjA7czo2OiJzdGF0dXMiO2I6MDtzOjY6ImNo
YW5nZSI7YjowO3M6NToibG9naWMiO2E6MDp7fX1zOjY6Im1fdHlwZSI7czo4OiJmcmVldHlwZSI7
czo4OiJzZXR0aW5ncyI7YToxMjp7czo0OiJpY29uIjtzOjU6IjU3MzQ2IjtzOjE3OiJhY2NlcHRf
ZmlsZV90eXBlcyI7czoxNjoiZ2lmLGpwZWcscG5nLGpwZyI7czoxOToibWF4X251bWJlcl9vZl9m
aWxlcyI7czoxOiIyIjtzOjE5OiJtaW5fbnVtYmVyX29mX2ZpbGVzIjtzOjE6IjIiO3M6MTM6Im1h
eF9maWxlX3NpemUiO3M6NzoiODM4ODYwOCI7czoxMzoibWluX2ZpbGVfc2l6ZSI7czoxOiIxIjtz
OjIwOiJ3cF9tZWRpYV9pbnRlZ3JhdGlvbiI7YjoxO3M6MTE6ImF1dG9fdXBsb2FkIjtiOjA7czox
MToiZHJhZ19uX2Ryb3AiO2I6MTtzOjEyOiJwcm9ncmVzc19iYXIiO2I6MTtzOjEzOiJwcmV2aWV3
X21lZGlhIjtiOjE7czoxMDoiY2FuX2RlbGV0ZSI7YjoxO319aToyO2E6ODp7czo0OiJ0eXBlIjtz
OjY6InVwbG9hZCI7czo1OiJ0aXRsZSI7czoyODoiVXBsb2FkIHJlY29tbWVuZGF0aW9uIGxldHRl
ciI7czoxMDoidmFsaWRhdGlvbiI7YToxOntzOjg6InJlcXVpcmVkIjtiOjA7fXM6ODoic3VidGl0
bGUiO3M6MDoiIjtzOjExOiJkZXNjcmlwdGlvbiI7czo4MjoiVGhpcyBpcyBvcHRpb25hbC4gQSBy
ZWNvbW1lbmRhdGlvbiB3aWxsIGFsd2F5cyBoZWxwIHlvdSBmaW5kIGEgYmV0dGVyIGluIG91ciBm
aXJtLiI7czoxMToiY29uZGl0aW9uYWwiO2E6NDp7czo2OiJhY3RpdmUiO2I6MDtzOjY6InN0YXR1
cyI7YjowO3M6NjoiY2hhbmdlIjtiOjA7czo1OiJsb2dpYyI7YTowOnt9fXM6NjoibV90eXBlIjtz
Ojg6ImZyZWV0eXBlIjtzOjg6InNldHRpbmdzIjthOjEyOntzOjQ6Imljb24iO3M6NToiNTc3MjIi
O3M6MTc6ImFjY2VwdF9maWxlX3R5cGVzIjtzOjM3OiJkb2MsZG9jeCxqcGcsanBlZyxnaWYscG5n
LHBkZixtcDQsbXAzIjtzOjE5OiJtYXhfbnVtYmVyX29mX2ZpbGVzIjtzOjE6IjIiO3M6MTk6Im1p
bl9udW1iZXJfb2ZfZmlsZXMiO3M6MDoiIjtzOjEzOiJtYXhfZmlsZV9zaXplIjtzOjc6IjEwMDAw
MDAiO3M6MTM6Im1pbl9maWxlX3NpemUiO3M6MToiMSI7czoyMDoid3BfbWVkaWFfaW50ZWdyYXRp
b24iO2I6MDtzOjExOiJhdXRvX3VwbG9hZCI7YjoxO3M6MTE6ImRyYWdfbl9kcm9wIjtiOjE7czox
MjoicHJvZ3Jlc3NfYmFyIjtiOjE7czoxMzoicHJldmlld19tZWRpYSI7YjoxO3M6MTA6ImNhbl9k
ZWxldGUiO2I6MTt9fX0iO3M6NToicGluZm8iO3M6OTkzOiJhOjM6e2k6MDthOjg6e3M6NDoidHlw
ZSI7czo2OiJmX25hbWUiO3M6NToidGl0bGUiO3M6MTA6IkZpcnN0IE5hbWUiO3M6MTA6InZhbGlk
YXRpb24iO2E6MTp7czo4OiJyZXF1aXJlZCI7YjoxO31zOjg6InN1YnRpdGxlIjtzOjA6IiI7czox
MToiZGVzY3JpcHRpb24iO3M6MDoiIjtzOjExOiJjb25kaXRpb25hbCI7YTo0OntzOjY6ImFjdGl2
ZSI7YjowO3M6Njoic3RhdHVzIjtiOjA7czo2OiJjaGFuZ2UiO2I6MDtzOjU6ImxvZ2ljIjthOjA6
e319czo2OiJtX3R5cGUiO3M6NToicGluZm8iO3M6ODoic2V0dGluZ3MiO2E6MTp7czoxMToicGxh
Y2Vob2xkZXIiO3M6MTA6IldyaXRlIGhlcmUiO319aToxO2E6ODp7czo0OiJ0eXBlIjtzOjY6Imxf
bmFtZSI7czo1OiJ0aXRsZSI7czo5OiJMYXN0IE5hbWUiO3M6MTA6InZhbGlkYXRpb24iO2E6MTp7
czo4OiJyZXF1aXJlZCI7YjoxO31zOjg6InN1YnRpdGxlIjtzOjA6IiI7czoxMToiZGVzY3JpcHRp
b24iO3M6MDoiIjtzOjExOiJjb25kaXRpb25hbCI7YTo0OntzOjY6ImFjdGl2ZSI7YjowO3M6Njoi
c3RhdHVzIjtiOjA7czo2OiJjaGFuZ2UiO2I6MDtzOjU6ImxvZ2ljIjthOjA6e319czo2OiJtX3R5
cGUiO3M6NToicGluZm8iO3M6ODoic2V0dGluZ3MiO2E6MTp7czoxMToicGxhY2Vob2xkZXIiO3M6
MTA6IldyaXRlIGhlcmUiO319aToyO2E6ODp7czo0OiJ0eXBlIjtzOjU6ImVtYWlsIjtzOjU6InRp
dGxlIjtzOjU6IkVtYWlsIjtzOjEwOiJ2YWxpZGF0aW9uIjthOjE6e3M6ODoicmVxdWlyZWQiO2I6
MTt9czo4OiJzdWJ0aXRsZSI7czowOiIiO3M6MTE6ImRlc2NyaXB0aW9uIjtzOjA6IiI7czoxMToi
Y29uZGl0aW9uYWwiO2E6NDp7czo2OiJhY3RpdmUiO2I6MDtzOjY6InN0YXR1cyI7YjowO3M6Njoi
Y2hhbmdlIjtiOjA7czo1OiJsb2dpYyI7YTowOnt9fXM6NjoibV90eXBlIjtzOjU6InBpbmZvIjtz
Ojg6InNldHRpbmdzIjthOjE6e3M6MTE6InBsYWNlaG9sZGVyIjtzOjEwOiJXcml0ZSBoZXJlIjt9
fX0iO3M6NDoidHlwZSI7czoxOiIxIjtzOjc6InVwZGF0ZWQiO3M6MTk6IjIwMTQtMDQtMTkgMTU6
MzY6MDkiO30=
		<?php
	}

	/*==========================================================================
	 * AJAX Methods
	 *========================================================================*/
	public function generate_import() {
		// First set the JSON header
		@header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );

		// Init the global arrays
		global $wpdb, $ipt_fsqm_info;

		// Get the variables
		$form_name = @$this->post['form_name'];
		$form_code = @$this->post['form_code'];
		$nonce = @$this->post['_wpnonce'];

		// Init the return
		$return = array(
			'error' => false,
			'code' => '',
		);

		// First check the nonce
		if ( ! wp_verify_nonce( $nonce, 'ipt_fsqm_import_export_nonce' ) || ! current_user_can( 'manage_feedback' ) ) {
			$return['error'] = true;
			$return['code'] = __( 'Cheatin&#8217; uh?' );
			die( json_encode( (object) $return ) );
		}

		// Decode the form
		$form = maybe_unserialize( base64_decode( $form_code ) );

		// Check it's integrity
		if ( ! is_array( $form ) ) {
			$return['error'] = true;
			$return['code'] = __( 'Invalid import code', 'ipt_fsqm' );
			die( json_encode( (object) $return ) );
		}

		// So it is an array, now check for required fields
		$required_fields = array(
			'id', 'name', 'settings', 'layout', 'design', 'mcq', 'freetype', 'pinfo', 'type',
		);
		foreach ( $required_fields as $field_key ) {
			if ( ! isset( $form[$field_key] ) ) {
				$return['error'] = true;
				$return['code'] = __( 'Import code missing required argument: ', 'ipt_fsqm' ) . $field_key;
				die( json_encode( (object) $return ) );
			}
		}

		// Override the name
		if ( $form_name != '' ) {
			$form['name'] = $form_name;
		}

		// Sanitize the name
		if ( $form['name'] == '' ) {
			$form['name'] = __( 'Untitled', 'ipt_fsqm' );
		} else {
			$form['name'] = strip_tags( $form['name'] );
		}

		// All set, now import it
		$wpdb->insert( $ipt_fsqm_info['form_table'], array(
			'name'     => $form['name'],
			'settings' => $form['settings'],
			'layout'   => $form['layout'],
			'design'   => $form['design'],
			'mcq'      => $form['mcq'],
			'freetype' => $form['freetype'],
			'pinfo'    => $form['pinfo'],
			'type'     => $form['type'],
		), '%s' );

		$new_form_id = $wpdb->insert_id;

		$return['code'] = sprintf( __( 'Form successfully imported. <a href="%1$s">Click here to edit: %2$s</a>', 'ipt_fsqm' ), admin_url( 'admin.php?page=ipt_fsqm_all_forms&action=edit&form_id=' . $new_form_id ), $form['name'] );
		die( json_encode( (object) $return ) );
	}

	public function generate_export() {
		// First set the JSON header
		@header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );

		// Init the global arrays
		global $wpdb, $ipt_fsqm_info;

		// Get the variables
		$form_id = (int) @$_GET['form_id'];
		$nonce = @$_GET['_wpnonce'];

		// Init the return
		$return = array(
			'error' => false,
			'code' => '',
		);

		// First check the nonce
		if ( ! wp_verify_nonce( $nonce, 'ipt_fsqm_import_export_nonce' ) || ! current_user_can( 'manage_feedback' ) ) {
			$return['error'] = true;
			$return['code'] = __( 'Cheatin&#8217; uh?' );
			die( json_encode( (object) $return ) );
		}

		// Now get the form
		$form = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$ipt_fsqm_info['form_table']} WHERE id = %d", $form_id ), ARRAY_A );

		// If it is invalid
		if ( null == $form ) {
			$return['error'] = true;
			$return['code'] = __( 'Invalid Form', 'ipt_fsqm' );
			die( json_encode( (object) $return ) );
		}

		// Now prepare the export
		$export = base64_encode( maybe_serialize( $form ) );
		$return['code'] = chunk_split( $export );
		die( json_encode( (object) $return ) );
	}
}

/**
 * The base admin class
 *
 * @abstract
 */
abstract class IPT_FSQM_Admin_Base {
	/**
	 * Duplicates the $_POST content and properly process it
	 * Holds the typecasted (converted int and floats properly and escaped html) value after the constructor has been called
	 *
	 * @var array
	 */
	public $post = array();

	/**
	 * Holds the hook of this page
	 *
	 * @var string Pagehook
	 * Should be set during the construction
	 */
	public $pagehook;

	/**
	 * The nonce for admin-post.php
	 * Should be set the by extending class
	 *
	 * @var string
	 */
	public $action_nonce;

	/**
	 * The class of the admin page icon
	 * Should be set by the extending class
	 *
	 * @var string
	 */
	public $icon;

	/**
	 * This gets passed directly to current_user_can
	 * Used for security and should be set by the extending class
	 *
	 * @var string
	 */
	public $capability;

	/**
	 * Holds the URL of the static directories
	 * Just the /static/admin/ URL and sub directories under it
	 * access it like $url['js'], ['images'], ['css'], ['root'] etc
	 *
	 * @var array
	 */
	public $url = array();

	/**
	 * Set this to true if you are going to use the WordPress Metabox appearance
	 * This will enqueue all the scripts and will also set the screenlayout option
	 *
	 * @var bool False by default
	 */
	public $is_metabox = false;

	/**
	 * Default number of columns on metabox
	 *
	 * @var int
	 */
	public $metabox_col = 2;

	/**
	 * Holds the post result message string
	 * Each entry is an associative array with the following options
	 *
	 * $key : The code of the post_result value =>
	 *
	 *      'type' => 'update' : The class of the message div update | error
	 *
	 *      'msg' => '' : The message to be displayed
	 *
	 * @var array
	 */
	public $post_result = array();

	/**
	 * The action value to be used for admin-post.php
	 * This is generated automatically by appending _post_action to the action_nonce variable
	 *
	 * @var string
	 */
	public $admin_post_action;

	/**
	 * Whether or not to print form on the admin wrap page
	 * Mainly for manually printing the form
	 *
	 * @var bool
	 */
	public $print_form;

	/**
	 * The USER INTERFACE Object
	 *
	 * @var IPT_Plugin_UIF_Admin
	 */
	public $ui;

	/**
	 * The constructor function
	 * 1. Properly copies the $_POST to $this->post on POST request
	 * 2. Calls the admin_menu() function
	 * You should have parent::__construct() for all these to happen
	 *
	 * @param boolean $gets_hooked Should be true if you wish to actually put this inside an admin menu. False otherwise
	 * It basically hooks into admin_menu and admin_post_ if true
	 */
	public function __construct( $gets_hooked = true ) {
		if ( $_SERVER['REQUEST_METHOD'] == 'POST' ) {
			//$this->post = $_POST;

			//we do not need to check on magic quotes
			//as wordpress always adds magic quotes
			//@link http://codex.wordpress.org/Function_Reference/stripslashes_deep
			$this->post = array_map( 'stripslashes_deep', $_POST );

			//convert html to special characters
			//array_walk_recursive ($this->post, array($this, 'htmlspecialchar_ify'));
		}

		$this->ui = IPT_Plugin_UIF_Admin::instance( 'ipt_fsqm' );

		$plugin = IPT_FSQM_Loader::$abs_file;

		$this->url = array(
			'root' => plugins_url( '/static/admin/', $plugin ),
			'js' => plugins_url( '/static/admin/js/', $plugin ),
			'images' => plugins_url( '/static/admin/images/', $plugin ),
			'css' => plugins_url( '/static/admin/css/', $plugin ),
		);

		$this->post_result = array(
			1 => array(
				'type' => 'update',
				'msg' => __( 'Successfully saved the options.', 'ipt_fsqm' ),
			),
			2 => array(
				'type' => 'error',
				'msg' => __( 'Either you have not changed anything or some error has occured. Please contact the developer.', 'ipt_fsqm' ),
			),
			3 => array(
				'type' => 'okay',
				'msg' => __( 'The Master Reset was successful.', 'ipt_fsqm' ),
			),
		);

		$this->admin_post_action = $this->action_nonce . '_post_action';

		if ( $gets_hooked ) {
			//register admin_menu hook
			add_action( 'admin_menu', array( &$this, 'admin_menu' ) );

			//register admin-post.php hook
			add_action( 'admin_post_' . $this->admin_post_action, array( &$this, 'save_post' ) );
		}
	}

	/*==========================================================================
	 * SYSTEM METHODS
	 *========================================================================*/


	/**
	 * Hook to the admin menu
	 * Should be overriden and also the hook should be saved in the $this->pagehook
	 * In the end, the parent::admin_menu() should be called for load to hooked properly
	 */
	public function admin_menu() {
		add_action( 'load-' . $this->pagehook, array( &$this, 'on_load_page' ) );
		//$this->pagehook = add_submenu_page($parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function);
		//do the above or similar in the overriden callback function
	}

	/**
	 * Use this to generate the admin page
	 * always call parent::index() so the save post is called
	 * also call $this->index_foot() after the generation of page (the last line of this function)
	 * to give some compatibility (mainly with the metaboxes)
	 *
	 * @access public
	 */
	abstract public function index();

	protected function index_head( $title = '', $print_form = true, $ui_state = 'back' ) {
		$this->print_form = $print_form;
		$ui_class = 'ipt_uif';

		switch ( $ui_state ) {
		case 'back' :
			$ui_class = 'ipt_uif';
			break;
		case 'front' :
			$ui_class = 'ipt_uif_front';
			break;
		default :
		case 'none' :
			$ui_class = 'ipt_uif_common';
		}
?>
<style type="text/css">
	<?php echo '#' . $this->pagehook; ?>-widgets .meta-box-sortables {
		margin: 0 8px;
	}
</style>
<div class="wrap ipt_uif_common <?php echo $ui_class; ?>" id="<?php echo $this->pagehook; ?>_widgets">
	<div class="icon32">
		<span class="ipt-icomoon-<?php echo $this->icon; ?>"></span>
	</div>
	<h2><?php echo $title; ?></h2>
	<?php $this->ui->clear(); ?>
	<?php
		if ( isset( $_GET['post_result'] ) ) {
			$msg = $this->post_result[(int) $_GET['post_result']];
			if ( !empty( $msg ) ) {
				if ( $msg['type'] == 'update' || $msg['type'] == 'updated' ) {
					$this->print_update( $msg['msg'] );
				} else if ( $msg['type'] == 'okay' ) {
						$this->print_p_okay( $msg['msg'] );
					} else {
					$this->print_error( $msg['msg'] );
				}
			}
		}
?>
	<?php if ( $this->print_form ) : ?>
	<form method="post" action="admin-post.php" id="<?php echo $this->pagehook; ?>_form_primary">
		<input type="hidden" name="action" value="<?php echo $this->admin_post_action; ?>" />
		<?php wp_nonce_field( $this->action_nonce, $this->action_nonce ); ?>
		<?php if ( $this->is_metabox ) : ?>
		<?php wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false ); ?>
		<?php wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false ); ?>
		<?php endif; ?>
	<?php endif; ?>
	<?php do_action( $this->pagehook . '_page_before', $this ); ?>
		<?php
	}

	/**
	 * Include this to the end of index function so that metaboxes work
	 */
	protected function index_foot( $submit = true, $save = 'Save Changes', $reset = 'Reset' ) {
		$buttons = array(
			array( $save, '', 'large', 'primary', 'normal', array(), 'submit' ),
			array( $reset, '', 'small', 'secondary', 'normal', array(), 'reset' ),
		);
?>
	<?php if ( $this->print_form ) : ?>
		<?php if ( true == $submit ) : ?>
		<div class="clear"></div>
		<?php $this->ui->buttons( $buttons ); ?>
		<?php endif; ?>
	</form>
	<?php endif; ?>
	<div class="clear"></div>
	<?php do_action( $this->pagehook . '_page_after', $this ); ?>
</div>
<?php if ( $this->is_metabox ) : ?>
<script type="text/javascript">
//<![CDATA[
jQuery(document).ready( function($) {
	if(postboxes) {
		// close postboxes that should be closed
		$('.if-js-closed').removeClass('if-js-closed').addClass('closed');
		// postboxes setup
		postboxes.add_postbox_toggles('<?php echo $this->pagehook; ?>');
	}
});
//]]>
</script>
<?php endif; ?>
		<?php
	}

	/**
	 * Override to manage the save_post
	 * This should be written by all the classes extending this
	 *
	 *
	 * * General Template
	 *
	 * //process here your on $_POST validation and / or option saving
	 *
	 * //lets redirect the post request into get request (you may add additional params at the url, if you need to show save results
	 * wp_redirect(add_query_arg(array(), $_POST['_wp_http_referer']));
	 *
	 *
	 */
	public function save_post( $check_referer = true ) {
		//user permission check
		if ( !current_user_can( $this->capability ) )
			wp_die( __( 'Cheatin&#8217; uh?' ) );
		//check nonce
		if ( $check_referer ) {
			if ( !wp_verify_nonce( $_POST[$this->action_nonce], $this->action_nonce ) )
				wp_die( __( 'Cheatin&#8217; uh?' ) );
		}

		//process here your on $_POST validation and / or option saving

		//lets redirect the post request into get request (you may add additional params at the url, if you need to show save results
		//wp_redirect(add_query_arg(array(), $_POST['_wp_http_referer']));
		//The above should be done by the extending after calling parent::save_post and processing post
	}

	/**
	 * Hook to the load plugin page
	 * This should be overriden
	 * Also call parent::on_load_page() for screenoptions
	 *
	 * @uses add_meta_box
	 */
	public function on_load_page() {

	}

	/**
	 * Get the pagehook of this class
	 *
	 * @return string
	 */
	public function get_pagehook() {
		return $this->pagehook;
	}

	/**
	 * Prints the metaboxes of a custom context
	 * Should atleast pass the $context, others are optional
	 *
	 * The screen defaults to the $this->pagehook so make sure it is set before using
	 * This should be the return value given by add_admin_menu or similar function
	 *
	 * The function automatically checks the screen layout columns and prints the normal/side columns accordingly
	 * If screen layout column is 1 then even if you pass with context side, it will be hidden
	 * Also if screen layout is 1 and you pass with context normal, it will get full width
	 *
	 * @param string  $context           The context of the metaboxes. Depending on this HTML ids are generated. Valid options normal | side
	 * @param string  $container_classes (Optional) The HTML class attribute of the container
	 * @param string  $container_style   (Optional) The RAW inline CSS style of the container
	 */
	public function print_metabox_containers( $context = 'normal', $container_classes = '', $container_style = '' ) {
		global $screen_layout_columns;
		$style = 'width: 50%;';

		//check to see if only one column has to be shown

		if ( isset( $screen_layout_columns ) && $screen_layout_columns == 1 ) {
			//normal?
			if ( 'normal' == $context ) {
				$style = 'width: 100%;';
			} else if ( 'side' == $context ) {
					$style = 'display: none;';
				}
		}

		//override for the special debug area (1 column)
		if ( 'debug' == $context ) {
			$style = 'width: 100%;';
			$container_classes .= ' debug-metabox';
		}
?>
<div class="postbox-container <?php echo $container_classes; ?>" style="<?php echo $style . $container_style; ?>" id="<?php echo ( 'normal' == $context )? 'postbox-container-1' : 'postbox-container-2'; ?>">
	<?php do_meta_boxes( $this->pagehook, $context, '' ); ?>
</div>
		<?php
	}


	/*==========================================================================
	 * INTERNAL METHODS
	 *========================================================================*/

	/**
	 * Prints error msg in WP style
	 *
	 * @param string  $msg
	 */
	protected function print_error( $msg = '', $echo = true ) {
		return $this->ui->msg_error( $msg, $echo );
	}

	protected function print_update( $msg = '', $echo = true ) {
		return $this->ui->msg_update( $msg, $echo );
	}

	protected function print_p_error( $msg = '', $echo = true ) {
		return $this->ui->msg_error( $msg, $echo );
	}

	protected function print_p_update( $msg = '', $echo = true ) {
		return $this->ui->msg_update( $msg, $echo );
	}

	protected function print_p_okay( $msg = '', $echo = true ) {
		return $this->ui->msg_okay( $msg, $echo );
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

	/*==========================================================================
	 * SHORTCUT HTML METHODS
	 *========================================================================*/


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
		return $this->ui->shorten_string( $text, $char, $cont );
	}

	/**
	 * Get the first image from a string
	 *
	 * @param string  $html
	 * @return mixed string|bool The src value on success or boolean false if no src found
	 */
	public function get_first_image( $html ) {
		return $this->ui->get_first_image( $html );
	}

	/**
	 * Wrap a RAW JS inside <script> tag
	 *
	 * @param String  $string The JS
	 * @return String The wrapped JS to be used under HTMl document
	 */
	public function js_wrap( $string ) {
		return $this->ui->js_wrap( $string );
	}

	/**
	 * Wrap a RAW CSS inside <style> tag
	 *
	 * @param String  $string The CSS
	 * @return String The wrapped CSS to be used under HTMl document
	 */
	public function css_wrap( $string ) {
		return $this->ui->css_wrap( $string );
	}

	public function print_datetimepicker( $name, $value, $dateonly = false ) {
		if ( $dateonly ) {
			$this->ui->datepicker( $name, $value );
		} else {
			$this->ui->datetimepicker( $name, $value );
		}
	}

	/**
	 * Prints options of a selectbox
	 *
	 * @param array   $ops Should pass either an array of string ('label1', 'label2') or associative array like array('val' => 'val1', 'label' => 'label1'),...
	 * @param string  $key The key in the haystack, if matched a selected="selected" will be printed
	 */
	public function print_select_op( $ops, $key, $inner = false ) {
		$items = $this->ui->convert_old_items( $ops, $inner );
		$this->ui->select( '', $items, $key, false, false, false, false );
	}

	/**
	 * Prints a set of checkboxes for a single HTML name
	 *
	 * @param string  $name    The HTML name of the checkboxes
	 * @param array   $items   The associative array of items array('val' => 'value', 'label' => 'label'),...
	 * @param array   $checked The array of checked items. It matches with the 'val' of the haystack array
	 * @param string  $sep     (Optional) The seperator, HTML non-breaking-space (&nbsp;) by default. Can be <br /> or anything
	 */
	public function print_checkboxes( $name, $items, $checked, $sep = '&nbsp;&nbsp;' ) {
		$items = $this->ui->convert_old_items( $items );
		$this->ui->checkboxes( $name, $items, $checked, false, false, $sep );
	}

	/**
	 * Prints a set of radioboxes for a single HTML name
	 *
	 * @param string  $name    The HTML name of the checkboxes
	 * @param array   $items   The associative array of items array('val' => 'value', 'label' => 'label'),...
	 * @param string  $checked The value of checked radiobox. It matches with the val of the haystack
	 * @param string  $sep     (Optional) The seperator, two HTML non-breaking-space (&nbsp;) by default. Can be <br /> or anything
	 */
	public function print_radioboxes( $name, $items, $checked, $sep = '&nbsp;&nbsp;' ) {
		$items = $this->ui->convert_old_items( $items );
		$this->ui->radios( $name, $items, $checked, false, false, $sep );
	}

	/**
	 * Print a single checkbox
	 * Useful for printing a single checkbox like for enable/disable type
	 *
	 * @param string  $name  The HTML name
	 * @param string  $value The value attribute
	 * @param mixed   (string|bool) $checked Can be true or can be equal to the $value for adding checked attribute. Anything else and it will not be added.
	 */
	public function print_checkbox( $name, $value, $checked ) {
		if ( $value === $checked || true === $checked ) {
			$checked = true;
		}
		$this->ui->toggle( $name, '', $value, $checked );
	}

	/**
	 * Prints a input[type="text"]
	 * All attributes are escaped except the value
	 *
	 * @param string  $name  The HTML name attribute
	 * @param string  $value The value of the textbox
	 * @param string  $class (Optional) The css class defaults to regular-text
	 */
	public function print_input_text( $name, $value, $class = 'regular-text' ) {
		$this->ui->text( $name, $value, '', $class );
	}

	/**
	 * Prints a <textarea> with custom attributes
	 * All attributes are escaped except the value
	 *
	 * @param string  $name  The HTML name attribute
	 * @param string  $value The value of the textbox
	 * @param string  $class (Optional) The css class defaults to regular-text
	 * @param int     $rows  (Optional) The number of rows in the rows attribute
	 * @param int     $cols  (Optional) The number of columns in the cols attribute
	 */
	public function print_textarea( $name, $value, $class = 'regular-text', $rows = 3, $cols = 20 ) {
		$this->ui->textarea( $name, $value, '', $class );
	}


	/**
	 * Displays a jQuery UI Slider to the page
	 *
	 * @param string  $name  The HTML name of the input box
	 * @param int     $value The initial/saved value of the input box
	 * @param int     $max   The maximum of the range
	 * @param int     $min   The minimum of the range
	 * @param int     $step  The step value
	 */
	public function print_ui_slider( $name, $value, $max = 100, $min = 0, $step = 1 ) {
		$this->ui->slider( $name, $value, $min, $max, $step );
	}

	/**
	 * Prints a ColorPicker
	 *
	 * @param string  $name  The HTML name of the input box
	 * @param string  $value The HEX color code
	 */
	public function print_cpicker( $name, $value ) {
		$this->ui->colorpicker( $name, $value );
	}

	/**
	 * Prints a input box with an attached upload button
	 *
	 * @param string  $name  The HTML name of the input box
	 * @param string  $value The value of the input box
	 */
	public function print_uploadbutton( $name, $value ) {
		$this->ui->upload( $name, $value );
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

/**
 * View all Forms Data Table Class
 */
class IPT_FSQM_Form_Table extends WP_List_Table {
	public function __construct() {
		parent::__construct( array(
				'singular' => 'ipt_fsqm_form_item',
				'plural' => 'ipt_fsqm_form_items',
				'ajax' => false,
			) );
	}

	public function get_columns() {
		$columns = array(
			'cb' => '<input type="checkbox" />',
			'title' => __( 'Name', 'ipt_fsqm' ),
			'shortcode' => __( 'Shortcode', 'ipt_fsqm' ),
			'submission' => __( 'Submissions', 'ipt_fsqm' ),
			'updated' => __( 'Last Updated', 'ipt_fsqm' ),
		);
		return $columns;
	}

	public function get_sortable_columns() {
		$sortable = array(
			'title' => array( 'f.name', false ),
			'submission' => array( 'sub', false ),
			'updated' => array( 'f.updated', true ),
		);

		return $sortable;
	}

	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
		case 'title' :
			$permalinks = IPT_FSQM_Form_Elements_Static::standalone_permalink_parts( $item['id'] );
			$actions = array(
				'permalink' => sprintf( '<a class="view" title="%3$s" href="%1$s" target="_blank">%2$s</a>', $permalinks['url'], __( 'Preview', 'ipt_fsqm' ), __( 'Preview the form or copy the permalink', 'ipt_fsqm' ) ),
				'view'      => sprintf( '<a class="view" href="admin.php?page=ipt_fsqm_view_all_submissions&form_id=%d">%s</a>', $item['id'], __( 'View Submissions', 'ipt_fsqm' ) ),
				'edit'      => sprintf( '<a class="edit" href="admin.php?page=ipt_fsqm_all_forms&action=edit&form_id=%d">%s</a>', $item['id'], __( 'Edit', 'ipt_fsqm' ) ),
				'copy'      => sprintf( '<a class="copy" href="%s">%s</a>', wp_nonce_url( '?page=' . $_REQUEST['page'] . '&action=copy&id=' . $item['id'], 'ipt_fsqm_form_copy_' . $item['id'] ), __( 'Copy', 'ipt_fsqm' ) ),
				'delete'    => sprintf( '<a class="delete" href="%s">%s</a>', wp_nonce_url( '?page=' . $_REQUEST['page'] . '&action=delete&id=' . $item['id'], 'ipt_fsqm_form_delete_' . $item['id'] ), __( 'Delete', 'ipt_fsqm' ) ),
			);
			return sprintf( '%1$s %2$s', '<strong><a title="' . __( 'View all submissions under this form', 'ipt_fsqm' ) . '" href="admin.php?page=ipt_fsqm_view_all_submissions&form_id=' . $item['id'] . '">' . $item['name'] . '</a></strong>', $this->row_actions( $actions ) );
			break;
		case 'shortcode' :
			return '[ipt_fsqm_form id="' . $item['id'] . '"]';
			break;
		case 'submission' :
			return $item['sub'];
			break;
		case 'updated' :
			if ( 0 == $item['sub'] )
				return __( 'N/A', 'ipt_fsqm' );
			else
				return date_i18n( get_option( 'date_format' ) . __(' \a\t ', 'ipt_fsqm') . get_option( 'time_format' ), strtotime( $item[$column_name] ) );
			break;
		default :
			print_r( $item );
		}
	}

	public function column_cb( $item ) {
		return sprintf( '<input type="checkbox" name="forms[]" value="%s" />', $item['id'] );
	}

	public function get_bulk_actions() {
		$actions = array(
			'delete' => __( 'Delete' ),
		);
		return $actions;
	}

	/**
	 *
	 *
	 * @global wpdb $wpdb
	 * @global array $ipt_fsqm_info
	 */
	public function prepare_items() {
		global $wpdb, $ipt_fsqm_info;

		//prepare our query
		$query = "SELECT f.id id, f.name name, f.updated updated, COUNT(d.id) sub FROM {$ipt_fsqm_info['form_table']} f LEFT JOIN {$ipt_fsqm_info['data_table']} d ON f.id = d.form_id";
		$orderby = !empty( $_GET['orderby'] ) ? esc_sql( $_GET['orderby'] ) : 'f.id';
		$order = !empty( $_GET['order'] ) ? esc_sql( $_GET['order'] ) : 'desc';
		$where = '';

		if ( !empty( $_GET['s'] ) ) {
			$search = '%' . $_GET['s'] . '%';

			$where = $wpdb->prepare( " WHERE name LIKE %s", $search );
		}
		$query .= $where;

		//pagination
		$totalitems = $wpdb->get_var( "SELECT COUNT(id) FROM {$ipt_fsqm_info['form_table']}{$where}" );
		$perpage = $this->get_items_per_page( 'feedback_forms_per_page', 20 );
		$totalpages = ceil( $totalitems/$perpage );

		$this->set_pagination_args( array(
				'total_items' => $totalitems,
				'total_pages' => $totalpages,
				'per_page' => $perpage,
			) );
		$current_page = $this->get_pagenum();

		//put pagination and order on the query
		$query .= ' GROUP BY f.id ORDER BY ' . $orderby . ' ' . $order . ' LIMIT ' . ( ( $current_page - 1 ) * $perpage ) . ',' . (int) $perpage;

		//register the columns
		$this->_column_headers = $this->get_column_info();

		//fetch the items
		$this->items = $wpdb->get_results( $query, ARRAY_A );
	}

	public function extra_tablenav( $which ) {
		if ( isset( $_GET['s'] ) && !empty( $_GET['s'] ) && 'top' == $which ) {
?>
<div class="actions alignleft">
	<?php printf( __( 'Showing search results for "%s"', 'ipt_fsqm' ), $_GET['s'] ); ?>
</div>
			<?php
		}
	}
}

/**
 * View all Submission Data Table Class
 */
class IPT_FSQM_Data_Table extends WP_List_Table {
	public $feedback;

	public function __construct() {
		$this->feedback = get_option( 'ipt_fsqm_feedback' );

		parent::__construct( array(
				'singular' => 'ipt_fsqm_table_item',
				'plural' => 'ipt_fsqm_table_items',
				'ajax' => true,
			) );
	}

	public function get_columns() {
		$columns = array(
			'cb' => '<input type="checkbox" />',
			'star' => '<img alt="Important" src="' . plugins_url( '/static/admin/images/star_on.png', IPT_FSQM_Loader::$abs_file ) . '" />',
			'title' => __( 'Name', 'ipt_fsqm' ),
			'email' => __( 'Email', 'ipt_fsqm' ),
			'phone' => __( 'Phone', 'ipt_fsqm' ),
			'date' => __( 'Date', 'ipt_fsqm' ),
			'ip' => __( 'IP Address', 'ipt_fsqm' ),
			'score' => __( 'Score', 'ipt_fsqm' ),
			'user' => __( 'Account', 'ipt_fsqm' ),
			'form' => __( 'Form', 'ipt_fsqm' ),
		);
		return $columns;
	}

	public function get_sortable_columns() {
		$sortable = array(
			'star' => array( 'star', true ),
			'title' => array( 'f_name', false ),
			'date' => array( 'date', true ),
			'email' => array( 'email', false ),
			'phone' => array( 'phone', false ),
			'score' => array( 'score', true ),
			'user' => array( 'user_id', true ),
			'ip' => array( 'ip', false ),
			'form' => array( 'form_id', false ),
		);
		return $sortable;
	}

	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
		case 'star' :
			return '<a href="javascript: void(null)" class="ipt_fsqm_star"><img title="' . ( $item['star'] == 1 ? __( 'Click to Unstar', 'ipt_fsqm' ) : __( 'Click to Star', 'ipt_fsqm' ) ) . '" src="' . plugins_url( ( $item['star'] == 1 ? '/static/admin/images/star_on.png' : '/static/admin/images/star_off.png' ), IPT_FSQM_Loader::$abs_file ) . '" /></a>';
		case 'title' :
			$actions = array(
				'qview' => sprintf( '<a class="thickbox" title="%s" href="admin-ajax.php?action=ipt_fsqm_quick_preview&id=%d&width=640&height=500">%s</a>', esc_attr( sprintf( __( 'Submission of %s under %s', 'ipt_fsqm' ), $item['f_name'] . ' ' . $item['l_name'], $item['name'] ) ), $item['id'], __( 'Quick Preview', 'ipt_fsqm' ) ),
				'view' => sprintf( '<a href="admin.php?page=ipt_fsqm_view_submission&id=%d">%s</a>', (int) $item['id'], __( 'Full View', 'ipt_fsqm' ) ),
				'edit' => '<a class="edit" href="admin.php?page=ipt_fsqm_view_submission&id=' . $item['id'] . '&edit=Edit">' . __( 'Edit Submission', 'ipt_fsqm' ) . '</a>',
				'delete' => '<a class="delete" href="' . wp_nonce_url( '?page=' . $_REQUEST['page'] . '&action=delete&id=' . $item['id'], 'ipt_fsqm_delete_' . $item['id'] ) . '">' . __( 'Delete', 'ipt_fsqm' ) . '</a>',
			);

			return sprintf( '%1$s %2$s', '<strong><a class="thickbox" title="' . esc_attr( sprintf( __( 'Submission of %s under %s', 'ipt_fsqm' ), $item['f_name'] . ' ' . $item['l_name'], $item['name'] ) ) . '" href="admin-ajax.php?action=ipt_fsqm_quick_preview&id=' . $item['id'] . '&width=640&height=500">' . $item['f_name'] . ' ' . $item['l_name'] . '</a></strong>', $this->row_actions( $actions ) );
			break;
		case 'email' :
			return '<a href="mailto:' . $item[$column_name] . '">' . $item[$column_name] . '</a>';
			break;
		case 'phone' :
		case 'ip' :
			return $item[$column_name];
			break;
		case 'date' :
			return date_i18n( get_option( 'date_format' ) . __(' \a\t ', 'ipt_fsqm') . get_option( 'time_format' ), strtotime( $item[$column_name] ) );
			break;
		case 'form' :
			return '<a href="admin.php?page=ipt_fsqm_view_all_submissions&form_id=' . $item['form_id'] . '">' . $item['name'] . '</a>';
			break;
		case 'score' :
			$score = __( 'N/A', 'ipt_fsqm' );
			if ( $item['max_score'] != 0 ) {
				$percent = number_format_i18n( $item['score'] * 100 / $item['max_score'], 2 );
				$score = $item['score'] . '/' . $item['max_score'] . ' <code>(' . $percent . '%)</code>';
			}
			return $score;
			break;
		case 'user' :
			$return = __( 'Guest', 'ipt_fsqm' );
			if ( $item['user_id'] != 0 ) {
				$user = get_user_by( 'id', $item['user_id'] );
				if ( $user instanceof WP_User ) {
					$return = '<a title="' . __( 'Edit user', 'ipt_fsqm' ) . '" href="user-edit.php?user_id=' . $user->ID . '">' . $user->display_name . '</a>';
				}
			}
			return $return;
		default :
			return print_r( $item, true );
		}
	}

	public function column_cb( $item ) {
		return sprintf( '<input type="checkbox" name="feedbacks[]" value="%s" />', $item['id'] );
	}

	public function get_bulk_actions() {
		$actions = array(
			'delete' => __( 'Delete' ),
			'star' => __( 'Mark Starred', 'ipt_fsqm' ),
			'unstar' => __( 'Mark Unstarred', 'ipt_fsqm' ),
		);
		return $actions;
	}

	/**
	 *
	 *
	 * @global wpdb $wpdb
	 * @global type $_wp_column_headers
	 * @global type $ipt_fsqm_info
	 */
	public function prepare_items() {
		global $wpdb, $ipt_fsqm_info;

		//prepare our query
		$query = "SELECT d.id id, d.f_name f_name, d.l_name l_name, d.email email, d.phone phone, d.ip ip, d.date date, d.star star, d.score score, d.max_score max_score, d.user_id user_id, f.name name, f.id form_id FROM {$ipt_fsqm_info['data_table']} d LEFT JOIN {$ipt_fsqm_info['form_table']} f ON d.form_id = f.id";
		$orderby = !empty( $_GET['orderby'] ) ? esc_sql( $_GET['orderby'] ) : 'date';
		$order = !empty( $_GET['order'] ) ? esc_sql( $_GET['order'] ) : 'desc';
		$where = '';
		$wheres = array();

		if ( isset( $_GET['form_id'] ) && !empty( $_GET['form_id'] ) ) {
			$wheres[] = $wpdb->prepare( "d.form_id = %d", $_GET['form_id'] );
		}
		if ( isset( $_GET['user_id'] ) && '' != $_GET['user_id'] ) {
			$wheres[] = $wpdb->prepare( "d.user_id = %d", $_GET['user_id'] );
		}

		if ( isset( $_GET['s'] ) && !empty( $_GET['s'] ) ) {
			$search = '%' . $_GET['s'] . '%';
			$wheres[] = $wpdb->prepare( "(f_name LIKE %s OR l_name LIKE %s OR email LIKE %s OR phone LIKE %s OR ip LIKE %s)", $search, $search, $search, $search, $search );
		}

		if ( !empty( $wheres ) ) {
			$where .= ' WHERE ' . implode( ' AND ', $wheres );
		}

		$query .= $where;

		//pagination
		$totalitems = $wpdb->get_var( "SELECT COUNT(id) FROM {$ipt_fsqm_info['data_table']} d{$where}" );
		$perpage = $this->get_items_per_page( 'feedbacks_per_page', 20 );
		$totalpages = ceil( $totalitems/$perpage );

		$this->set_pagination_args( array(
				'total_items' => $totalitems,
				'total_pages' => $totalpages,
				'per_page' => $perpage,
			) );
		$current_page = $this->get_pagenum();

		//put pagination and order on the query
		$query .= ' ORDER BY ' . $orderby . ' ' . $order . ' LIMIT ' . ( ( $current_page - 1 ) * $perpage ) . ',' . (int) $perpage;
		//print_r($query);

		//register the columns
		$this->_column_headers = $this->get_column_info();

		//fetch the items
		$this->items = $wpdb->get_results( $query, ARRAY_A );

		//var_dump($this->items);
	}

	public function no_items() {
		_e( 'No Feedbacks/Surveys/Quiz Results yet! Please be patient.', 'ipt_fsqm' );
	}

	public function extra_tablenav( $which ) {
		global $wpdb, $ipt_fsqm_info;
		$forms = $wpdb->get_results( "SELECT id, name FROM {$ipt_fsqm_info['form_table']}" );
		$users = $wpdb->get_col( "SELECT distinct user_id FROM {$ipt_fsqm_info['data_table']}" );
		switch ( $which ) {
		case 'top' :
?>
<div class="alignleft actions">
	<select name="form_id">
		<option value=""<?php if ( !isset( $_GET['form_id'] ) || empty( $_GET['form_id'] ) ) echo ' selected="selected"'; ?>><?php _e( 'Show all forms', 'ipt_fsqm' ); ?></option>
		<?php if ( null != $forms ) : ?>
		<?php foreach ( $forms as $form ) : ?>
		<option value="<?php echo $form->id; ?>"<?php if ( isset( $_GET['form_id'] ) && $_GET['form_id'] == $form->id ) echo ' selected="selected"'; ?>><?php echo $form->name; ?></option>
		<?php endforeach; ?>
		<?php else : ?>
		<option value=""><?php _e( 'No Forms in the database', 'ipt_fsqm' ); ?></option>
		<?php endif; ?>
	</select>

	<select name="user_id">
		<option value=""<?php if ( !isset( $_GET['user_id'] ) || '' == $_GET['user_id'] ) echo ' selected="selected"'; ?>><?php _e( 'Show all users', 'ipt_fsqm' ); ?></option>
		<?php if ( null != $users ) : ?>
		<?php foreach ( $users as $user_id ) : ?>
		<?php if ( $user_id == 0 ) : ?>
		<option value="0"<?php if ( isset( $_GET['user_id'] ) && '0' == $_GET['user_id'] ) echo ' selected="selected"'; ?>><?php _e( 'Guests (Unregistered)', 'ipt_fsqm' ); ?></option>
		<?php else : ?>
		<?php $user = get_user_by( 'id', $user_id ); ?>
		<option value="<?php echo $user_id; ?>"<?php if ( isset( $_GET['user_id'] ) && (string) $user_id == $_GET['user_id'] ) echo ' selected="selected"'; ?>><?php echo $user->display_name; ?></option>
		<?php endif; ?>
		<?php endforeach; ?>
		<?php endif; ?>
	</select>

	<?php submit_button( __( 'Filter' ), 'secondary', false, false, array( 'id' => 'form-query-submit' ) ); ?>
</div>
				<?php
			break;
		case 'bottom' :
			echo '<div class="alignleft"><p>';
			_e( 'You can also print a submission. Just select Quick Preview from the list and click on the print button.', 'ipt_fsqm' );
			echo '</p></div>';
		}
	}
}
