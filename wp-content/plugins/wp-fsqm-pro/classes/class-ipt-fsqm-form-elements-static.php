<?php
/**
 * WP Feedback, Surver & Quiz Manager - Pro Form Elements Class
 * Static APIs
 *
 * @package WP Feedback, Surver & Quiz Manager - Pro
 * @subpackage Form Elements
 * @author Swashata Ghosh <swashata@intechgrity.com>
 */
class IPT_FSQM_Form_Elements_Static {
	/*==========================================================================
	 * SYSTEM APIs - Needs to be called
	 *========================================================================*/
	/**
	 * Admin Ajax Init
	 * Call inside the loader with (is_admin()) context
	 */
	public static function admin_init() {
		self::shortcode_to_wp_editor_api_init();
		self::ipt_fsqm_quick_preview();
		add_action( 'init', array( __CLASS__, 'admin_init_hook' ) );
	}

	public static function admin_init_hook() {
		add_filter( 'mce_external_plugins', array( __CLASS__, 'mce_external_plugins' ) );
		add_filter( 'mce_buttons', array( __CLASS__, 'mce_buttons' ) );
	}

	public static function common_init() {
		self::ipt_fsqm_report();
		self::ipt_fsqm_shortcodes_init();
		self::ipt_fsqm_save_form();
		self::standalone_form_init();
		self::user_portal_init();
		self::uploader_ajax_init();
		self::richtext_init();
	}

	/*==========================================================================
	 * RichText Init - Adds the filters
	 *========================================================================*/
	public static function richtext_init() {
		add_filter( 'ipt_uif_richtext', array( __CLASS__, 'richtext_filter' ), 8 );
	}

	public static function richtext_filter( $content ) {
		global $shortcode_tags, $ipt_fsqm_settings;
		$original_shortcode_tags = $shortcode_tags;
		$shortcodes_to_remove = array();
		$shortcodes_to_remove = array(
			'ipt_fsqm_form', 'ipt_fsqm_trackback', 'ipt_fsqm_utrackback', 'ipt_fsqm_trends',
		);
		if ( $ipt_fsqm_settings['backward_shortcode'] == true ) {
			$shortcodes_to_remove = array_merge( $shortcodes_to_remove, array(
				'feedback', 'feedback_trend', 'feedback_track',
			) );
		}
		$shortcode_tags = array();
		foreach ( $shortcodes_to_remove as $key ) {
			$shortcode_tags[$key] = 1;
		}
		$content = strip_shortcodes( $content );
		$shortcode_tags = $original_shortcode_tags;
		return $content;
	}


	/*==========================================================================
	 * Uploader Callbacks
	 *========================================================================*/
	public static function uploader_ajax_init() {
		add_action( 'wp_ajax_ipt_fsqm_fu_upload', array( __CLASS__, 'uploader_ajax_upload' ) );
		add_action( 'wp_ajax_ipt_fsqm_fu_download', array( __CLASS__, 'uploader_ajax_download' ) );
		add_action( 'wp_ajax_ipt_fsqm_fu_delete', array( __CLASS__, 'uploader_ajax_delete' ) );

		add_action( 'wp_ajax_nopriv_ipt_fsqm_fu_upload', array( __CLASS__, 'uploader_ajax_upload' ) );
		add_action( 'wp_ajax_nopriv_ipt_fsqm_fu_download', array( __CLASS__, 'uploader_ajax_download' ) );
		add_action( 'wp_ajax_nopriv_ipt_fsqm_fu_delete', array( __CLASS__, 'uploader_ajax_delete' ) );
	}
	public static function uploader_ajax_upload() {
		@header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );
		$nonce = @$_POST['nonce'];

		$form_id = (int) @$_POST['form_id'];
		$element_id = (int) @$_POST['element_key'];
		$data_id = ( 'null' == @$_POST['data_id'] || '' == @$_POST['data_id'] || 0 == @$_POST['data_id'] ) ? null : (int) @$_POST['data_id'];
		$files_key = @$_POST['files_key'];

		if ( ! wp_verify_nonce( $nonce, 'ipt_fsqm_upload_' . $form_id . '_' . $data_id . '_' . $element_id ) ) {
			$return = array(
				'files' => array(
					array(
						'name' => __( 'Invalid', 'ipt_fsqm' ),
						'size' => 0,
						'error' => __( 'Invalid nonce.', 'ipt_fsqm' ),
					),
				),
			);
			echo json_encode( (object) $return );
			die();
		}

		$upload_handler = new IPT_FSQM_Form_Elements_Uploader( $form_id, $element_id );
		$return = array(
			'files' => $upload_handler->process_file_uploads( $files_key ),
		);
		echo json_encode( (object) $return );

		wp_create_nonce( 'ipt_fsqm_upload_' . $form_id . '_' . $data_id . '_' . $element_id );

		die();
	}
	public static function uploader_ajax_download() {
		$nonce = @$_GET['download_nonce'];
		$data_id = (int) @$_GET['data_id'];
		$form_id = (int) @$_GET['form_id'];
		$element_id = (int) @$_GET['element_key'];

		if ( ! wp_verify_nonce( $nonce, 'ipt_fsqm_download_' . $form_id . '_' . $data_id . '_' . $element_id ) ) {
			die( __( 'Cheatin&#8217; uh?' ) );
		}

		@header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );
		$upload_handler = new IPT_FSQM_Form_Elements_Uploader( $form_id, $element_id );
		$uploads = $upload_handler->get_uploads( $data_id, true );
		$formatted_uploads = array();
		foreach ( (array) $uploads as $upload ) {
			$valid_audio = in_array( strtolower( $upload['ext'] ), array( 'mp3', 'wav', 'ogg' ) ) ? true : false;
			$valid_video = strtolower( $upload['ext'] ) == 'mp4' ? true : false;
			$formatted_uploads[] = array(
				'id' => $upload['id'],
				'name' => $upload['filename'],
				'size' => $upload['size'],
				'url' => $upload['guid'],
				'thumbnailUrl' => $upload['thumb_url'],
				'deleteUrl' => $upload['delete'],
				'deleteType' => 'DELETE',
				'validAudio' => $valid_audio,
				'validVideo' => $valid_video,
				'type' => $upload['mime_type'],
			);
		}
		$return = array(
			'files' => $formatted_uploads,
		);
		echo json_encode( (object) $return );
		die();
	}

	public static function uploader_ajax_delete() {
		if ( $_SERVER['REQUEST_METHOD'] !== 'DELETE' ) {
			wp_die( __( 'Cheatin&#8217; uh?' ) );
		}
		$file_id = (int) @$_GET['file_id'];
		$element_id = (int) @$_GET['element_id'];
		$form_id = (int) @$_GET['form_id'];
		$wpnonce = @$_GET['_wpnonce'];
		$file = @$_GET['file'];

		if ( ! wp_verify_nonce( $wpnonce, 'ipt_fsqm_fu_delete_file_' . $file_id ) ) {
			wp_die( __( 'Cheatin&#8217; uh?' ) );
		}

		$upload_handler = new IPT_FSQM_Form_Elements_Uploader( $form_id, $element_id );

		$return = array(
			$file => $upload_handler->delete_file( $file_id ),
		);

		@header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );
		echo json_encode( (object) $return );
		die();
	}


	/*==========================================================================
	 * Shortcode to WP Editor APIs
	 *========================================================================*/
	public static function mce_external_plugins( $plugins ) {
		$plugins['iptFSQM'] = plugins_url( '/static/admin/js/ipt-fsqm-editor-plugin.js', IPT_FSQM_Loader::$abs_file );
		return $plugins;
	}
	public static function mce_buttons( $buttons ) {
		array_push( $buttons, 'ipt_fsqm_shortcode_insert' );
		return $buttons;
	}


	protected static function shortcode_to_wp_editor_api_init() {
		add_action( 'wp_ajax_ipt_fsqm_shortcode_insert', array( __CLASS__, 'shortcode_insert' ) );
		add_action( 'wp_ajax_ipt_fsqm_shortcode_insert_form', array( __CLASS__, 'shortcode_insert_form' ) );
		add_action( 'wp_ajax_ipt_fsqm_shortcode_insert_system', array( __CLASS__, 'shortcode_insert_system' ) );
		add_action( 'wp_ajax_ipt_fsqm_shortcode_insert_trends', array( __CLASS__, 'shortcode_insert_trends' ) );
		add_action( 'wp_ajax_ipt_fsqm_shortcode_get_mcqs', array( __CLASS__, 'shortcode_insert_trends_helper' ) );
	}

	public static function shortcode_insert_trends() {
		global $wpdb, $ipt_fsqm_info;
		$forms = $wpdb->get_results( "SELECT id, name FROM {$ipt_fsqm_info['form_table']} ORDER BY id DESC" );
?>
<table class="form-table">
	<tbody>
		<tr>
			<th><label for="ipt_fsqm_shortcode_insert_title"><?php _e( 'Title of the Visualization Column', 'ipt_fsqm' ); ?></label></th>
			<td>
				<input type="text" class="regular-text" id="ipt_fsqm_shortcode_insert_title" value="<?php _e( 'Trends', 'ipt_fsqm' ); ?>" />
			</td>
		</tr>
		<tr>
			<th><label for="ipt_fsqm_shortcode_insert_load"><?php _e( 'Server Load', 'ipt_fsqm' ); ?></label></th>
			<td>
				<select id="ipt_fsqm_shortcode_insert_load">
					<option value="0"><?php _e( 'Light Load: 15 queries per hit', 'ipt_fsqm' ); ?></option>
					<option value="1" selected="selected"><?php _e( 'Medium Load: 30 queries per hit (Recommended)', 'ipt_fsqm' ); ?></option>
					<option value="2"><?php _e( 'Heavy Load: 50 queries per hit', 'ipt_fsqm' ); ?></option>
				</select>
			</td>
		</tr>
		<tr>
			<th><label for="ipt_fsqm_shortcode_insert_form"><?php _e( 'Select the form', 'ipt_fsqm' ); ?></label></th>
			<td>
				<select id="ipt_fsqm_shortcode_insert_form">
					<option value="" selected="selected"><?php _e( 'Please select a form', 'ipt_fsqm' ); ?></option>
					<?php foreach ( $forms as $form ) : ?>
					<option value="<?php echo $form->id; ?>"><?php echo $form->name; ?></option>
					<?php endforeach; ?>
				</select><img style="display: none;" src="<?php echo admin_url( '/images/wpspin_light.gif' ); ?>" />
			</td>
		</tr>

		<tr id="ipt_fsqm_shortcode_insert_mcqs">
			<td colspan="2">

			</td>
		</tr>
	</tbody>
</table>
<p class="submit">
	<input type="button" class="button-primary" value="<?php _e( 'Insert', 'ipt_fsqm' ); ?>" disabled="disabled" id="ipt_fsqm_shortcode_insert_trends_submit" />
	<a id="ipt_fsqm_shortcode_wizard_back" title="<?php _e( 'Insert Shortcodes for WP Feedback, Survey & Quiz Manager - Pro', 'ipt_fsqm' ); ?>" href="admin-ajax.php?action=ipt_fsqm_shortcode_insert" class="button-secondary"><?php _e( 'Back', 'ipt_fsqm' ); ?></a>
</p>
<script type="text/javascript">
jQuery(document).ready(function($) {
	var wpnonce = '<?php echo wp_create_nonce( 'ipt_fsqm_shortcode_get_mcqs' ); ?>';
	$('#ipt_fsqm_shortcode_insert_form').on('change keyup', function() {
		if ( $(this).val() == '' ) {
			$('#ipt_fsqm_shortcode_insert_trends_submit').attr('disabled', true);
			$('#ipt_fsqm_shortcode_insert_mcqs td').html('Please select a valid form.')
			return;
		}
		$(this).next('img').show();
		$('#ipt_fsqm_shortcode_insert_mcqs td').html('Please Wait...');
		$('#ipt_fsqm_shortcode_insert_trends_submit').attr('disabled', true);
		var form_id = $(this).val();
		var data = {
			'form_id' : form_id,
			'action' : 'ipt_fsqm_shortcode_get_mcqs',
			'wpnonce' : wpnonce
		};
		$.get(ajaxurl, data, function(response) {
			var toAppend = $('<div />').html(response.html);
			$('#ipt_fsqm_shortcode_insert_mcqs td').html('');
			$('#ipt_fsqm_shortcode_insert_mcqs td').append(toAppend);
			var checkAll = toAppend.find('.ipt_fsqm_shortcode_mcq_all'),
			mcqChecks = toAppend.find('.ipt_fsqm_shortcode_mcqs');
			mcqChecks.on('change', function() {
				checkAll.attr('checked', false);
			});
			checkAll.on('change', function() {
				if($(this).is(':checked')) {
					mcqChecks.attr('checked', true);
				} else {
					mcqChecks.attr('checked', false);
				}
			});
			wpnonce = response.wpnonce;
			// Fix the height of the TB_ajaxContent
			$('#TB_ajaxContent').height($('#TB_window').height() - 50);
			$('#ipt_fsqm_shortcode_insert_trends_submit').attr('disabled', false);
		}, 'json').fail(function() {
			alert('HTTP error has occured');
		}).always(function() {
			$('#ipt_fsqm_shortcode_insert_form').next('img').hide();
		});
	});

	$('#ipt_fsqm_shortcode_insert_trends_submit').on('click', function(e) {
		e.preventDefault();
		var shortcode = '[ipt_fsqm_trends form_id="' + $('#ipt_fsqm_shortcode_insert_form').val() + '" title="' + $('#ipt_fsqm_shortcode_insert_title').val()  + '" load="' + $('#ipt_fsqm_shortcode_insert_load').val() + '" mcq_ids="',
		mcq_ids,
		all_checkbox = $('#ipt_fsqm_shortcode_insert_mcqs td').find('.ipt_fsqm_shortcode_mcq_all');
		if(all_checkbox.length && all_checkbox.is(':checked')) {
			mcq_ids = 'all';
		} else {
			mcq_ids = $('.ipt_fsqm_shortcode_mcqs:checked').map(function() {
				return $(this).val();
			}).get();
		}
		if('' == mcq_ids) {
			mcq_ids = 'all';
		}
		shortcode += mcq_ids + '"]';
		if(!all_checkbox.length) {
			shortcode = '';
		}
		$(document).trigger('ipt_fsqm_shortcode_insert', [function(ed) {
			ed.execCommand('mceInsertContent', 0, '<br />' + shortcode + '<br />');
		}]);
	});
});
</script>
		<?php
		die();
	}

	public static function shortcode_insert_trends_helper() {
		$form_id = (int) $_REQUEST['form_id'];
		@header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );
		if ( !wp_verify_nonce( $_REQUEST['wpnonce'], 'ipt_fsqm_shortcode_get_mcqs' ) ) {
			$return = array(
				'wpnonce' => '',
				'html' => __( 'Cheatin&#8217; uh?' ),
			);
			echo json_encode( (object) $return );
			die();
		}
		$wpnonce = wp_create_nonce( 'ipt_fsqm_shortcode_get_mcqs' );
		$form_element = new IPT_FSQM_Form_Elements_Utilities( $form_id );
		if ( null == $form_element->form_id ) {
			$return = array(
				'wpnonce' => $wpnonce,
				'html' => __( 'Please select a form and try again.', 'ipt_fsqm' ),
			);
			echo json_encode( (object) $return );
			die();
		}
		$mcqs = $form_element->get_keys_from_layouts_by_m_type( 'mcq', $form_element->layout );
		if ( empty( $mcqs ) ) {
			$return = array(
				'wpnonce' => $wpnonce,
				'html' => __( 'No survey or multiple choice type questions found for this form.', 'ipt_fsqm' ),
			);
			echo json_encode( (object) $return );
			die();
		}
		ob_start();
?>
<input type="checkbox" class="ipt_fsqm_shortcode_mcq_all" value="all" checked="checked" id="ipt_fsqm_shortcode_mcq_all" />
<label for="ipt_fsqm_shortcode_mcq_all"><?php _e( 'Show All', 'ipt_fsqm' ); ?></label>
		<?php foreach ( $mcqs as $mcq ) : ?>
<br />
<input type="checkbox" class="ipt_fsqm_shortcode_mcqs" checked="checked" value="<?php echo $mcq; ?>" id="ipt_fsqm_shortcode_mcq_<?php echo $mcq; ?>" />
<label for="ipt_fsqm_shortcode_mcq_<?php echo $mcq; ?>"><?php echo esc_attr( $form_element->mcq[$mcq]['title'] ); ?></label>
		<?php endforeach; ?>
		<?php
		$html = ob_get_clean();
		$return = array(
			'wpnonce' => $wpnonce,
			'html' => $html,
		);
		echo json_encode( (object) $return );
		die();
	}

	public static function shortcode_insert_system() {
		$utrack_attrs = array(
			'login_attr' => array(
				'login' => __( 'Message to logged out users', 'ipt_fsqm' ),
				'show_register' => __( 'Show the registration button', 'ipt_fsqm' ),
				'show_forgot' => __( 'Show password recovery link', 'ipt_fsqm' ),
			),
			'portal_attr' => array(
				'title' => __( 'Welcome Title', 'ipt_fsqm' ),
				'content' => __( 'Welcome message', 'ipt_fsqm' ),
				'nosubmission' => __( 'No submissions message', 'ipt_fsqm' ),
				'formlabel' => __( 'Form Heading Label', 'ipt_fsqm' ),
				'datelabel' => __( 'Date Heading Label', 'ipt_fsqm' ),
				'showscore' => __( 'Show Score Column', 'ipt_fsqm' ),
				'scorelabel' => __( 'Score Heading Label', 'ipt_fsqm' ),
				'mscorelabel' => __( 'Max Score Heading Label', 'ipt_fsqm' ),
				'pscorelabel' => __( 'Percentage Score Heading Label', 'ipt_fsqm' ),
				'actionlabel' => __( 'Action Column Heading Label', 'ipt_fsqm' ),
				'linklabel' => __( 'Trackback Button Label', 'ipt_fsqm' ),
				'editlabel' => __( 'Edit Button Label', 'ipt_fsqm' ),
				'avatar' => __( 'Avatar Size', 'ipt_fsqm' ),
				'theme' => __( 'Page Theme', 'ipt_fsqm' ),
			),
		);
		$utrack_labels = array(
			'login_attr' => __( 'Login Page Modifications', 'ipt_fsqm' ),
			'portal_attr' => __( 'Portal Page Modifications', 'ipt_fsqm' ),
		);
		$utrack_defaults = array(
			'content' => __( 'Welcome %NAME%. Below is the list of all submissions you have made.', 'ipt_fsqm' ),
			'nosubmission' => __( 'No submissions yet.', 'ipt_fsqm' ),
			'login' => __( 'You need to login in order to view your submissions.', 'ipt_fsqm' ),
			'show_register' => '1',
			'show_forgot' => '1',
			'formlabel' => __( 'Form', 'ipt_fsqm' ),
			'datelabel' => __( 'Date', 'ipt_fsqm' ),
			'showscore' => '1',
			'scorelabel' => __( 'Score', 'ipt_fsqm' ),
			'mscorelabel' => __( 'Max', 'ipt_fsqm' ),
			'pscorelabel' => __( '%-age', 'ipt_fsqm' ),
			'linklabel' => __( 'View', 'ipt_fsqm' ),
			'actionlabel' => __( 'Action', 'ipt_fsqm' ),
			'editlabel' => __( 'Edit', 'ipt_fsqm' ),
			'avatar' => '96',
			'theme' => 'default',
			'title' => __( 'FSQM Pro User Portal', 'ipt_fsqm' ),
		);
		$form_element = new IPT_FSQM_Form_Elements_Base();
		$themes = $form_element->get_available_themes();
?>
<table class="form-table">
	<tbody>
		<tr>
			<th><label for="ipt_fsqm_shortcode_insert_system"><?php _e( 'System Shortcode Type', 'ipt_fsqm' ); ?></label></th>
			<td>
				<select id="ipt_fsqm_shortcode_insert_system">
					<option value="ipt_fsqm_trackback"><?php _e( 'Single Submission Trackback Page for Unregistered Users', 'ipt_fsqm' ); ?></option>
					<option value="ipt_fsqm_utrackback"><?php _e( 'User Portal - Central Trackback page for Registered Users', 'ipt_fsqm' ); ?></option>
				</select>
			</td>
		</tr>
	</tbody>
</table>
<div id="ipt_fsqm_shortcode_trackback_wrap">
	<table class="form-table">
		<tr id="ipt_fsqm_system_shortcode_trackback_label">
			<th>
				<label for="ipt_fsqm_system_shortcode_tb_label"><?php _e( 'Form Label', 'ipt_fsqm' ); ?></label>
			</th>
			<td>
				<input type="text" id="ipt_fsqm_system_shortcode_tb_label" value="<?php _e( 'Track Code', 'ipt_fsqm' ); ?>" /><br />
				<span class="description"><?php _e( 'Enter the label of the text input where the surveyee will need to paste his/her trackback code.', 'ipt_fsqm' ); ?></span>
			</td>
		</tr>
		<tr id="ipt_fsqm_system_shortcode_trackback_submit">
			<th>
				<label for="ipt_fsqm_system_shortcode_tb_submit"><?php _e( 'Submit Button Text', 'ipt_fsqm' ); ?></label>
			</th>
			<td>
				<input type="text" id="ipt_fsqm_system_shortcode_tb_submit" value="<?php _e( 'Submit', 'ipt_fsqm' ); ?>" />
			</td>
		</tr>
	</table>
</div>
<div id="ipt_fsqm_shortcode_utrackback_wrap">
	<?php foreach ( $utrack_attrs as $attr => $labels ) : ?>
	<h3><?php echo $utrack_labels[$attr]; ?></h3>
	<table class="form-table">
		<?php foreach ( $labels as $key => $label ) : ?>
		<tr>
			<th>
				<label for="ipt_fsqm_system_shortcode_utb_<?php echo $key; ?>"><?php echo $label; ?></label>
			</th>
			<td>
				<?php if ( $key == 'content' ) : ?>
				<textarea id="ipt_fsqm_system_shortcode_utb_<?php echo $key; ?>" class="widefat" rows="5"><?php echo esc_textarea( $utrack_defaults[$key] ); ?></textarea>
				<?php elseif ( $key == 'theme' ) : ?>
				<select id="ipt_fsqm_system_shortcode_utb_<?php echo $key; ?>">
					<?php foreach ( $themes as $theme_grp ) : ?>
					<optgroup label="<?php echo $theme_grp['label']; ?>">
						<?php foreach ( $theme_grp['themes'] as $theme_key => $theme ) : ?>
						<option value="<?php echo $theme_key; ?>"<?php if ( $utrack_defaults[$key] == $theme_key ) echo ' selected="selected"'; ?>><?php echo $theme['label']; ?></option>
						<?php endforeach; ?>
					</optgroup>
					<?php endforeach; ?>
				</select>
				<?php elseif ( is_numeric( $utrack_defaults[$key] ) && $utrack_defaults[$key] <= 1 ) : ?>
				<input type="checkbox" value="1" id="ipt_fsqm_system_shortcode_utb_<?php echo $key; ?>"<?php if ( $utrack_defaults[$key] == '1' ) echo ' checked="checked"'; ?> />
				<?php else : ?>
				<input type="text" class="large-text" value="<?php echo esc_html( $utrack_defaults[$key] ); ?>" id="ipt_fsqm_system_shortcode_utb_<?php echo $key; ?>" />
				<?php endif; ?>
			</td>
		</tr>
		<?php endforeach; ?>
	</table>
	<?php endforeach; ?>
</div>
<p class="submit">
	<input type="button" class="button-primary" value="<?php _e( 'Insert', 'ipt_fsqm' ); ?>" id="ipt_fsqm_shortcode_insert_system_submit" />
	<a id="ipt_fsqm_shortcode_wizard_back" title="<?php _e( 'Insert Shortcodes for WP Feedback, Survey & Quiz Manager - Pro', 'ipt_fsqm' ); ?>" href="admin-ajax.php?action=ipt_fsqm_shortcode_insert" class="button-secondary"><?php _e( 'Back', 'ipt_fsqm' ); ?></a>
</p>
<script type="text/javascript">
jQuery(document).ready(function($) {
	var utrack_ids = <?php echo json_encode( array_keys( $utrack_defaults ) ); ?>;
	$('#ipt_fsqm_shortcode_insert_system_submit').on('click', function(e) {
		e.preventDefault();
		var val = $('#ipt_fsqm_shortcode_insert_system').val();
		var shortcode = '[' + val;
		if(val == 'ipt_fsqm_trackback') {
			shortcode += ' label="' + $('#ipt_fsqm_system_shortcode_tb_label').val() + '" submit="' + $('#ipt_fsqm_system_shortcode_tb_submit').val() + '"';
		} else {
			for(var id in utrack_ids) {
				var elem = $('#ipt_fsqm_system_shortcode_utb_' + utrack_ids[id]);
				if ( elem.is('textarea') ) {
					continue;
				}
				shortcode += ' ' + utrack_ids[id] + '="';
				if ( elem.is('input[type="checkbox"]') ) {
					shortcode += elem.is(':checked') ? '1' : '0';
				} else {
					shortcode += elem.val();
				}
				shortcode += '"';
			}
			shortcode += ']' + $('#ipt_fsqm_system_shortcode_utb_content').val() + '[/ipt_fsqm_utrackback';
		}
		shortcode += ']';
		$(document).trigger('ipt_fsqm_shortcode_insert', [function(ed) {
			ed.execCommand('mceInsertContent', 0, "\n" + '<br />' + shortcode + '<br />' + "\n");
		}]);
	});

	var check_tb = function() {
		if($('#ipt_fsqm_shortcode_insert_system').val() == 'ipt_fsqm_trackback') {
			$('#ipt_fsqm_shortcode_trackback_wrap').show();
			$('#ipt_fsqm_shortcode_utrackback_wrap').hide();
		} else {
			$('#ipt_fsqm_shortcode_utrackback_wrap').show();
			$('#ipt_fsqm_shortcode_trackback_wrap').hide();
		}
		// Fix the height of the TB_ajaxContent
		$('#TB_ajaxContent').height($('#TB_window').height() - 50);
	};
	check_tb();
	$('#ipt_fsqm_shortcode_insert_system').on('change keyup', check_tb);
});
</script>
		<?php
		die();
	}

	public static function shortcode_insert_form() {
		global $wpdb, $ipt_fsqm_info;
		$forms = $wpdb->get_results( "SELECT id, name FROM {$ipt_fsqm_info['form_table']} ORDER BY id DESC" );
?>
<table class="form-table">
	<tbody>
		<tr>
			<th><label for="ipt_fsqm_shortcode_insert_form"><?php _e( 'Select the form', 'ipt_fsqm' ); ?></label></th>
			<td>
				<select id="ipt_fsqm_shortcode_insert_form">
					<?php foreach ( $forms as $form ) : ?>
					<option value="<?php echo $form->id; ?>"><?php echo $form->name; ?></option>
					<?php endforeach; ?>
				</select>
			</td>
		</tr>
	</tbody>
</table>
<p class="submit">
	<input type="button" class="button-primary" value="<?php _e( 'Insert', 'ipt_fsqm' ); ?>" id="ipt_fsqm_shortcode_insert_form_submit" />
	<a id="ipt_fsqm_shortcode_wizard_back" title="<?php _e( 'Insert Shortcodes for WP Feedback, Survey & Quiz Manager - Pro', 'ipt_fsqm' ); ?>" href="admin-ajax.php?action=ipt_fsqm_shortcode_insert" class="button-secondary"><?php _e( 'Back', 'ipt_fsqm' ); ?></a>
</p>
<script type="text/javascript">
jQuery(document).ready(function($) {
	$('#ipt_fsqm_shortcode_insert_form_submit').on('click', function(e) {
		e.preventDefault();
		var val = $('#ipt_fsqm_shortcode_insert_form').val();
		$(document).trigger('ipt_fsqm_shortcode_insert', [function(ed) {
			ed.execCommand('mceInsertContent', 0, '<br />[ipt_fsqm_form id="' + val + '"]<br />');
		}]);
	});
});
</script>
		<?php
		die();
	}

	public static function shortcode_insert() {
?>
<h3><?php _e( 'Please select what you wish to do', 'ipt_fsqm' ); ?></h3>
<table class="widefat" id="ipt_fsqm_shortcode_actions">
	<thead>
		<tr>
			<th><?php _e( 'Action', 'ipt_fsqm' ); ?></th>
			<th><?php _e( 'Consequence', 'ipt_fsqm' ); ?></th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td>
				<a title="<?php _e( 'WP Feedback, Survey & Quiz Manager - Pro - System Shortcodes', 'ipt_fsqm' ); ?>" class="button-secondary" href="admin-ajax.php?action=ipt_fsqm_shortcode_insert_system"><?php _e( 'System Shortcodes', 'ipt_fsqm' ); ?></a>
			</td>
			<td>
				<span class="description">
					<?php _e( 'Helps you insert system shortcodes for proper functionality of WP Feedback, Survey & Quiz Manager Pro Plugin. Once you insert any system shortcode, make sure to go to the <strong>Plugin Settings</strong> page and select the pages where you have put the shortcodes.', 'ipt_fsqm' ) ?>
				</span>
			</td>
		</tr>
		<tr>
			<td>
				<a title="<?php _e( 'WP Feedback, Survey & Quiz Manager - Pro - Insert Form', 'ipt_fsqm' ); ?>" class="button-secondary" href="admin-ajax.php?action=ipt_fsqm_shortcode_insert_form"><?php _e( 'Insert Form', 'ipt_fsqm' ); ?></a>
			</td>
			<td>
				<span class="description">
					<?php _e( 'Helps you insert forms you have created using shortcodes.', 'ipt_fsqm' ); ?>
				</span>
			</td>
		</tr>
		<tr>
			<td>
				<a title="<?php _e( 'WP Feedback, Survey & Quiz Manager - Pro - Insert Trends', 'ipt_fsqm' ); ?>" class="button-secondary" href="admin-ajax.php?action=ipt_fsqm_shortcode_insert_trends"><?php _e( 'Insert Trends', 'ipt_fsqm' ); ?></a>
			</td>
			<td>
				<span class="description">
					<?php _e( 'Helps you insert Helps you insert shortcodes to show trends of selected or all survey type questions of a form.', 'ipt_fsqm' ) ?>
				</span>
			</td>
		</tr>
		<?php do_action( 'ipt_fsqm_shortcode_wizard' ); ?>
	</tbody>
</table>
<script type="text/javascript">
jQuery(document).ready(function($) {
	$('#ipt_fsqm_shortcode_actions').find('a.button-secondary').on('click', function(e) {
		e.preventDefault();
		var href = $(this).attr('href');
		var title = $(this).attr('title');
		//tb_remove();
		tb_show(title, href);
	});
})
</script>
		<?php
		die();
	}

	/*==========================================================================
	 * Standalone Form APIs
	 *========================================================================*/
	protected static function standalone_form_init() {
		// Add the ajax for the Form Builder Page
		add_action( 'wp_ajax_ipt_fsqm_preview_form', array( __CLASS__, 'standalone_form_output' ) );
		add_action( 'wp_ajax_ipt_fsqm_standalone_embed_generate', array( __CLASS__, 'standalone_embed_generate' ) );

		add_action( 'init', array( __CLASS__, 'standalone_rewrite' ) );
		add_action( 'template_redirect', array( __CLASS__, 'standalone_frontend' ) );
	}

	public static function standalone_embed_generate() {
		@header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );
		$form_id = isset( $_REQUEST['form_id'] ) ? (int) $_REQUEST['form_id'] : 0;
		$permalink = self::standalone_permalink_parts( $form_id );

		echo json_encode( $permalink );
		die();
	}

	public static function standalone_base() {
		global $ipt_fsqm_settings;
		$base = ( ! isset( $ipt_fsqm_settings['standalone'] ) || ! isset( $ipt_fsqm_settings['standalone']['base'] ) || '' == $ipt_fsqm_settings['standalone']['base'] ) ? false : $ipt_fsqm_settings['standalone']['base'];
		if ( false !== $base ) {
			$base = sanitize_title( $base, 'eforms' );
		}
		return apply_filters( 'ipt_fsqm_standalone_base', $base );
	}

	public static function standalone_permalink_parts( $form_id ) {
		global $wpdb, $ipt_fsqm_info;

		$form_title = $wpdb->get_var( $wpdb->prepare( "SELECT name FROM {$ipt_fsqm_info['form_table']} WHERE id = %d", $form_id ) );

		if ( null == $form_title ) {
			return false;
		}

		$base = self::standalone_base();

		if ( false === $base ) {
			return false;
		}

		$slug = sanitize_title( $form_title );

		$return = array(
			'base' => $base,
			'slug' => $slug,
			'id' => $form_id,
			'url' => home_url( '/' ) . $base . '/' . $slug . '/' . $form_id . '/',
			'shortlink' => home_url( '/' ) . $base . '/' . $form_id . '/',
		);

		return apply_filters( 'ipt_fsqm_standalone_permalink', $return );
	}

	public static function standalone_canonical( $url = '' ) {
		$form_id = get_query_var( 'ipt_fsqm_form_id' );
		$permalink = self::standalone_permalink_parts( $form_id );

		if ( false === $permalink || ! isset( $permalink['url'] ) ) {
			return $url;
		} else {
			return $permalink['url'];
		}
	}

	public static function standalone_title( $title, $sep = '|' ) {
		$form_id = get_query_var( 'ipt_fsqm_form_id' );
		$form = self::get_form( $form_id );

		if ( $form == null ) {
			return $title;
		}

		return sprintf( '%1$s %2$s %3$s', $form->name, $sep, get_bloginfo( 'name' ) );
	}

	public static function standalone_rewrite() {
		global $wp;
		// Now the rewrite rule magic
		// First some sanity check
		$base = self::standalone_base();
		if ( false === $base ) {
			return;
		}
		// Add our query vars
		$wp->add_query_var( 'ipt_fsqm_rewrite' );
		$wp->add_query_var( 'ipt_fsqm_form_id' );

		// Prepare the regex and redirect depending on the base
		$reg_ex = '^' . $base . '/?([^/]*)/([0-9]+)';
		$redirect = 'index.php?ipt_fsqm_rewrite=$matches[1]&ipt_fsqm_form_id=$matches[2]';

		// Add the rewrite rule
		add_rewrite_rule( $reg_ex, $redirect, 'top' );

		// Flush the rewrite rule if necessary
		// Expected is for plugin update or new installation
		if ( get_option( 'ipt_fsqm_flush_rewrite', false ) ) {
			flush_rewrite_rules( true );
			update_option( 'ipt_fsqm_flush_rewrite', false );
		}
	}

	public static function standalone_frontend() {
		$ipt_fsqm_rewrite = get_query_var( 'ipt_fsqm_rewrite' );
		$ipt_fsqm_form_id = get_query_var( 'ipt_fsqm_form_id' );
		if ( '' == $ipt_fsqm_form_id ) {
			return;
		}

		$permalink = self::standalone_permalink_parts( $ipt_fsqm_form_id );

		// Check if it is present
		if ( $permalink === false ) {
			// 404 it
			global $wp_query;
			$wp_query->set_404();
			status_header( 404 );
			return;
		}

		// Redirect to proper page for SEO
		if ( $permalink['slug'] != $ipt_fsqm_rewrite ) {
			wp_redirect( $permalink['url'], 301 );
			die();
		}

		// Add the canonical filters
		add_filter( 'aioseop_canonical_url', array( __CLASS__, 'standalone_canonical' ) );
		add_filter( 'wpseo_canonical', array( __CLASS__, 'standalone_canonical' ) );

		// Add the title filter
		add_filter( 'wp_title', array( __CLASS__, 'standalone_title' ), 20, 2 );
		add_filter( 'wpseo_title', array( __CLASS__, 'standalone_title' ), 10, 2 );
		add_filter( 'aioseop_title_single', array( __CLASS__, 'standalone_title' ), 10, 2 );
		add_filter( 'aioseop_title_page', array( __CLASS__, 'standalone_title' ), 10, 2 );

		// Output the form
		self::standalone_form_output( (int) $ipt_fsqm_form_id );
	}

	public static function standalone_form_output( $form_id = false ) {
		global $ipt_fsqm_settings;
		if ( $form_id == false ) {
			$form_id = @$_REQUEST['form_id'];
		}
		$form = new IPT_FSQM_Form_Elements_Front( null, $form_id );
		$theme_dir = get_stylesheet_directory();
		$theme_uri = get_stylesheet_directory_uri();
		$base_css = '';
		$form_css = '';
		if ( file_exists( $theme_dir . '/fsqm-pro.css' ) ) {
			$base_css = '<link href="' . esc_url( $theme_uri . '/fsqm-pro.css' ) . '" media="all" rel="stylesheet" type="text/css" />';
		}
		if ( file_exists( $theme_dir . '/fsqm-pro-' . $form_id . '.css' ) ) {
			$form_css = '<link href="' . esc_url( $theme_uri . '/fsqm-pro-' . $form_id . '.css' ) . '" media="all" rel="stylesheet" type="text/css" />';
		}
		add_filter( 'show_admin_bar', '__return_false' );

		// Set the dynamic theme
		if ( isset( $_GET['fsqm_theme'] ) ) {
			$theme = $form->get_theme_by_id( strip_tags( $_GET['fsqm_theme'] ) );
			if ( ! empty( $theme['src'] ) || $_GET['fsqm_theme'] == 'default' ) {
				$form->settings['theme']['template'] = strip_tags( $_GET['fsqm_theme'] );
			}
		}

		// Set the bg color
		$bg_color = 'ffffff';
		if ( isset( $_GET['bg'] ) ) {
			$bg_color = strip_tags( $_GET['bg'] );
			if ( ! ctype_xdigit( $bg_color ) && $bg_color !== 'transparent' ) {
				$bg_color = 'ffffff';
			}
		}

		// Prepare variables for JSAPI
		$js_api = array(
			'id' => $form_id,
			'name' => $form->name,
			'theme' => $form->settings['theme']['template'],
			'bg_color' => $bg_color,
			'type' => $form->type,
			'product' => __( 'WP Feedback Survey & Quiz Manager Pro', 'ipt_fsqm' ),
			'version' => IPT_FSQM_Loader::$version,
		);
		?>
<!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js">
<head>
	<title><?php wp_title( '|', true ); ?></title>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />

	<style type="text/css">
	@import url("//fonts.googleapis.com/css?family=Oswald|Roboto:400,700,400italic,700italic");
	/* =Reset
	-------------------------------------------------------------- */

	html, body, div, span, applet, object, iframe, h1, h2, h3, h4, h5, h6, p, blockquote, pre, a, abbr, acronym, address, big, cite, code, del, dfn, em, img, ins, kbd, q, s, samp, small, strike, strong, sub, sup, tt, var, b, u, i, center, dl, dt, dd, ol, ul, li, fieldset, form, label, legend, table, caption, tbody, tfoot, thead, tr, th, td, article, aside, canvas, details, embed, figure, figcaption, footer, header, hgroup, menu, nav, output, ruby, section, summary, time, mark, audio, video {
		margin: 0;
		padding: 0;
		border: 0;
		font-size: 100%;
		vertical-align: baseline;
	}
	body {
		line-height: 1;
	}
	ol,
	ul {
		list-style: none;
	}
	blockquote,
	q {
		quotes: none;
	}
	blockquote:before,
	blockquote:after,
	q:before,
	q:after {
		content: '';
		content: none;
	}
	table {
		border-collapse: collapse;
		border-spacing: 0;
	}
	caption,
	th,
	td {
		font-weight: normal;
		text-align: left;
	}
	h1,
	h2,
	h3,
	h4,
	h5,
	h6 {
		clear: both;
	}
	html {
		overflow-y: auto;
		font-size: 100%;
		-webkit-text-size-adjust: 100%;
		-ms-text-size-adjust: 100%;
		margin-top: 0 !important;
	}
	a:focus {
		outline: thin dotted;
	}
	article,
	aside,
	details,
	figcaption,
	figure,
	footer,
	header,
	hgroup,
	nav,
	section {
		display: block;
	}
	audio,
	canvas,
	video {
		display: inline-block;
	}
	audio:not([controls]) {
		display: none;
	}
	del {
		color: #333;
	}
	ins {
		background: #fff9c0;
		text-decoration: none;
	}
	hr {
		background-color: #ccc;
		border: 0;
		height: 1px;
		margin: 24px;
		margin-bottom: 1.714285714rem;
	}
	sub,
	sup {
		font-size: 75%;
		line-height: 0;
		position: relative;
		vertical-align: baseline;
	}
	sup {
		top: -0.5em;
	}
	sub {
		bottom: -0.25em;
	}
	small {
		font-size: smaller;
	}
	img {
		border: 0;
		-ms-interpolation-mode: bicubic;
		max-width: 100%;
		height: auto;
	}
	h1, h2, h3, h4, h5, h6, p, ul, ol {
		line-height: 1.3;
		margin: 0 0 20px 0;
	}
	h1, h2, h3, h4, h5, h6 {
		font-family: 'Oswald', 'Arial Narrow', sans-serif;
		font-weight: normal;
		font-style: normal;
	}
	h1 {
		font-size: 2em;
	}
	h2 {
		font-size: 1.8em;
	}
	h3 {
		font-size: 1.6em;
	}
	h4 {
		font-size: 1.4em;
	}
	h5 {
		font-size: 1.2em;
	}
	h6 {
		font-size: 1em;
	}
	html, body {
		overflow-y: auto;
	}
	ul {
		list-style-type: disc;
		list-style-position: inside;
	}
	ol {
		list-style-type: decimal;
		list-style-position: inside;
	}
	</style>

	<?php echo $ipt_fsqm_settings['standalone']['head']; ?>
	<?php wp_head(); ?>
	<style type="text/css">
	body {
		background-color: #fff;
		background-image: none;
		padding: 20px;
		max-width: 1200px;
		min-width: 320px;
		margin: 0 auto;
		font-family: 'Roboto', Tahoma, Geneva, sans-serif;
		font-weight: normal;
		font-style: normal;
		font-size: 12px;
		color: #333;
	}
	body {
		background-color: <?php echo ( $bg_color == 'transparent' ? 'transparent' : '#' . $bg_color ); ?>;
	}
	</style>
	<?php echo $base_css; ?>
	<?php echo $form_css; ?>
</head>
<body <?php body_class( 'ipt_uif_common' ); ?>>
	<?php echo do_shortcode( wpautop( $ipt_fsqm_settings['standalone']['before'] ) ); ?>
	<div id="fsqm_form">
		<?php $form->show_form(); ?>
	</div>
	<?php echo do_shortcode( wpautop( $ipt_fsqm_settings['standalone']['after'] ) ); ?>
	<?php wp_footer(); ?>
	<!-- Fix for #wpadminbar -->
	<style type="text/css">
		html {
			margin-top: 0 !important;
		}
	</style>
	<script type="text/javascript">
		// A JS API which would trigger an event to top frame
		// So that parents can easily hook into
		jQuery(document).ready(function($) {
			$('html').removeClass('no-js');
			var triggerObj = <?php echo json_encode( (object) $js_api ); ?>,
			w = window;
			triggerObj.bg_color = $('body').css('background-color');
			// fire first event on document load
			if ( w.frameElement != null ) {
				try {
					if ( typeof( w.parent.jQuery ) !== "undefined" ) {
						w.parent.jQuery(w.parent.document).trigger( 'fsqm.ready', [triggerObj] );
						w.parent.jQuery(w.parent.document).trigger( 'ipt.ready', [triggerObj] );
					}
				} catch ( e ) {

				}
			}

			// fire second on window load
			$(w).on( 'load', function() {
				if ( w.frameElement != null ) {
					try {
						if ( typeof( w.parent.jQuery ) !== "undefined" ) {
							w.parent.jQuery(w.parent.document).trigger( 'fsqm.loaded', [triggerObj] );
							w.parent.jQuery(w.parent.document).trigger( 'ipt.loaded', [triggerObj] );
						}
					} catch ( e ) {

					}
				}
			} );
		});
	</script>
</body>
</html>
		<?php
		die();
	}

	/*==========================================================================
	 * Shortcodes
	 *========================================================================*/
	protected static function ipt_fsqm_shortcodes_init() {
		global $ipt_fsqm_settings;
		add_shortcode( 'ipt_fsqm_form', array( __CLASS__, 'ipt_fsqm_form_cb' ) );
		add_shortcode( 'ipt_fsqm_trackback', array( __CLASS__, 'ipt_fsqm_track_cb' ) );
		add_shortcode( 'ipt_fsqm_utrackback', array( __CLASS__, 'ipt_fsqm_utrack_cb' ) );
		add_shortcode( 'ipt_fsqm_trends', array( __CLASS__, 'ipt_fsqm_trends_cb' ) );

		if ( $ipt_fsqm_settings['backward_shortcode'] == true ) {
			add_shortcode( 'feedback', array( __CLASS__, 'ipt_fsqm_form_cb' ) );
			add_shortcode( 'feedback_track', array( __CLASS__, 'ipt_fsqm_track_cb' ) );
			add_shortcode( 'feedback_trend', array( __CLASS__, 'ipt_fsqm_trends_cb' ) );
		}
	}

	public static function ipt_fsqm_trends_cb( $args, $content = null, $context = '' ) {
		extract( shortcode_atts( array(
					'form_id' => '1',
					'mcq_ids' => 'all',
					'title' => __( 'Trends', 'ipt_fsqm' ),
					'load' => '1',
				), $args ) );
		if ( $context == 'feedback_trend' ) {
			$form_id = isset( $args['id'] ) ? (int) $args['id'] : 0;
		}
		$utils = new IPT_FSQM_Form_Elements_Utilities( $form_id, IPT_Plugin_UIF_Front::instance( 'ipt_fsqm' ) );
		$front = new IPT_FSQM_Form_Elements_Front( null, $form_id );
		$mcqs = array();
		if ( $mcq_ids == 'all' ) {
			$mcqs = array_keys( $utils->mcq );
		} else {
			$mcqs = wp_parse_id_list( $mcq_ids );
		}
		$settings = array(
			'form_id' => $form_id,
			'report' => 'survey',
			'custom_date' => false,
			'custom_date_start' => '',
			'custom_date_end' => '',
			'load' => '1',
		);
		ob_start();
		$front->container( array( array( $utils, 'report_generate_report' ), array( $settings, $mcqs, $title ) ), true );
		return ob_get_clean();
	}

	public static function ipt_fsqm_form_cb( $args, $content = null ) {
		extract( shortcode_atts( array(
					'id' => '1'
				), $args ) );
		$form = new IPT_FSQM_Form_Elements_Front( null, $id );
		ob_start();
		$form->show_form();
		return ob_get_clean();
	}

	public static function ipt_fsqm_track_cb( $args, $content = null ) {
		extract( shortcode_atts( array(
					'label' => __( 'Track Code:', 'ipt_fsqm' ),
					'submit' => __( 'Submit', 'ipt_fsqm' ),
				), $args ) );
		$id = isset( $_GET['id'] ) ? $_GET['id'] : false;
		$action = isset( $_GET['action'] ) ? $_GET['action'] : 'show';
		ob_start();
?>
<?php if ( $id == false ) : ?>
<form action="" method="get">
	<?php foreach ( $_GET as $k => $v ) : ?>
	<input type="hidden" name="<?php echo esc_attr( $k ); ?>" value="<?php echo esc_attr( $v ); ?>" />
	<?php endforeach; ?>
	<p>
		<label for="id"><?php echo $label; ?></label><br />
		<input type="text" name="id" id="id" value="" style="width: 90%;" />
	</p>
	<p>
		<input type="submit" value="<?php echo $submit; ?>" />
	</p>
</form>
<?php else : ?>
<?php if ( $action == 'edit' ) : ?>
<?php self::ipt_fsqm_form_edit( self::decrypt( $id ) ); ?>
<?php else : ?>
<?php self::ipt_fsqm_full_preview( self::decrypt( $id ) ); ?>
<?php endif; ?>
<?php endif; ?>
		<?php
		return ob_get_clean();
	}

	public static function ipt_fsqm_utrack_cb( $args, $content = null ) {
		global $wpdb, $ipt_fsqm_info, $ipt_fsqm_settings;
		$shortcode_settings = shortcode_atts( array(
			'nosubmission' => __( 'No submissions yet.', 'ipt_fsqm' ),
			'login' => __( 'You need to login in order to view your submissions.', 'ipt_fsqm' ),
			'show_register' => '1',
			'show_forgot' => '1',
			'formlabel' => __( 'Form', 'ipt_fsqm' ),
			'datelabel' => __( 'Date', 'ipt_fsqm' ),
			'showscore' => '1',
			'scorelabel' => __( 'Score', 'ipt_fsqm' ),
			'mscorelabel' => __( 'Max', 'ipt_fsqm' ),
			'pscorelabel' => __( '%-age', 'ipt_fsqm' ),
			'linklabel' => __( 'View', 'ipt_fsqm' ),
			'actionlabel' => __( 'Action', 'ipt_fsqm' ),
			'editlabel' => __( 'Edit', 'ipt_fsqm' ),
			'avatar' => '96',
			'theme' => 'default',
			'title' => __( 'FSQM Pro User Portal', 'ipt_fsqm' ),
		), $args );
		extract( $shortcode_settings );
		$content = shortcode_unautop( $content );
		$showscore = (int) $showscore;
		$show_register = (int) $show_register;
		$show_forgot = (int) $show_forgot;

		if ( $content == null ) {
			$content = __( 'Welcome %NAME%. Below is the list of all submissions you have made.', 'ipt_fsqm' );
		}
		$user = wp_get_current_user();
		$ui = IPT_Plugin_UIF_Front::instance( 'ipt_fsqm' );
		$redirect = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		$ui->enqueue( plugins_url( '/lib/', IPT_FSQM_Loader::$abs_file ), IPT_FSQM_Loader::$version );
		ob_start();
		$ui->ajax_loader( false, '', array(), true, __( 'Please wait', 'ipt_fsqm' ) );
		$ajax_loader = ob_get_clean();
		wp_enqueue_script( 'ipt-fsqm-up-datatable', plugins_url( '/lib/js/jquery.dataTables.min.js', IPT_FSQM_Loader::$abs_file ), array( 'jquery' ), IPT_FSQM_Loader::$version );
		wp_enqueue_script( 'ipt-fsqm-up-script', plugins_url( '/static/front/js/jquery.ipt-fsqm-user-portal.js', IPT_FSQM_Loader::$abs_file ), array( 'jquery' ), IPT_FSQM_Loader::$version );
		wp_localize_script( 'ipt-fsqm-up-script', 'iptFSQMUP', array(
			'location' => plugins_url( '/static/front/', IPT_FSQM_Loader::$abs_file ),
			'version' => IPT_FSQM_Loader::$version,
			'l10n' => array(
				'sEmptyTable' => __( 'No submissions yet!', 'ipt_fsqm' ),
				'sInfo' => __( 'Showing _START_ to _END_ of _TOTAL_ entries', 'ipt_fsqm' ),
				'sInfoEmpty' => __( 'Showing 0 to 0 of 0 entries', 'ipt_fsqm' ),
				'sInfoFiltered' => __( '(filtered from _MAX_ total entries)', 'ipt_fsqm' ),
				/* translators: %s will be replaced by an empty string */
				'sInfoPostFix' => sprintf( _x( '%s', 'sInfoPostFix', 'ipt_fsqm' ), '' ),
				'sInfoThousands' => __( ',', 'ipt_fsqm' ),
				'sLengthMenu' => __( 'Show _MENU_ entries', 'ipt_fsqm' ),
				'sLoadingRecords' => $ajax_loader,
				'sProcessing' => $ajax_loader,
				'sSearch' => __( 'Search:', 'ipt_fsqm' ),
				'sZeroRecords' => __( 'No matching records found', 'ipt_fsqm' ),
				'oPaginate' => array(
					'sFirst' => __( 'First', 'ipt_fsqm' ),
					'sLast' => __( 'Last', 'ipt_fsqm' ),
					'sNext' => __( 'Next', 'ipt_fsqm' ),
					'sPrevious' => __( 'Previous', 'ipt_fsqm' ),
				),
				'oAria' => array(
					'sSortAscending' => __( ': activate to sort column ascending', 'ipt_fsqm' ),
					'sSortDescending' => __( ': activate to sort column descending', 'ipt_fsqm' ),
				),
			),
			'ajax' => array(
				'null_response' => __( 'Some error occured on the server.', 'ipt_fsqm' ),
				'ajax_error' => __( 'Error occured while fetching the content.', 'ipt_fsqm' ),
				'advice' => __( 'Please refresh this page to try again.', 'ipt_fsqm' ),
			),
			'allLabel' => __( 'All', 'ipt_fsqm' ),
		) );

		do_action( 'ipt_fsqm_form_elements_up_enqueue' );

		if ( ! is_user_logged_in() || ! ($user instanceof WP_User) ) {
			$defaults = array(
				'echo' => true,
				'redirect' => $redirect,
				'form_id' => 'ipt_fsqm_up_login',
				'label_username' => __( 'Username' ),
				'label_password' => __( 'Password' ),
				'label_remember' => __( 'Remember Me' ),
				'label_log_in' => __( 'Log In' ),
				'id_username' => 'ipt_fsqm_up_user_name',
				'id_password' => 'ipt_fsqm_up_user_pwd',
				'id_remember' => 'ipt_fsqm_up_rmm',
				'id_submit' => 'wp-submit',
				'remember' => true,
				'value_username' => '',
				'value_remember' => false, // Set this to true to default the "Remember me" checkbox to checked
			);
			$args = wp_parse_args( $args, apply_filters( 'login_form_defaults', $defaults ) );
			$login_buttons = array();
			$login_buttons[] = array(
				__( 'Login', 'ipt_fsqm' ),
				'wp-submit',
				'large',
				'none',
				'normal',
				array(),
				'submit',
				array(),
				array(),
				'',
				'switch',
			);

			if ( $show_register && get_option( 'users_can_register', false ) ) {
				$login_buttons[] = array(
					__( 'Register', 'ipt_fsqm' ),
					'ipt_fsqm_up_reg',
					'large',
					'none',
					'normal',
					array(),
					'button',
					array(),
					array( 'onclick' => 'javascript:window.location.href="' . wp_registration_url() . '"' ),
					'',
					'user-2',
				);
			}

			if ( $show_forgot ) {
				$login_buttons[] = array(
					__( 'Forgot Password', 'ipt_fsqm' ),
					'ipt_fsqm_up_rpwd',
					'large',
					'none',
					'normal',
					array(),
					'button',
					array(),
					array( 'onclick' => 'javascript:window.location.href="' . wp_lostpassword_url( $redirect ) . '"' ),
					'',
					'info-2',
				);
			}

			$login_buttons = apply_filters( 'ipt_fsqm_up_filter_login_buttons', $login_buttons );
		} else {
			$total_count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(id) FROM {$ipt_fsqm_info['data_table']} WHERE user_id = %d", $user->ID ) );
			$avg_score = $wpdb->get_var( $wpdb->prepare( "SELECT AVG((score/max_score)) FROM {$ipt_fsqm_info['data_table']} WHERE user_id = %d", $user->ID ) );

			$toolbar_buttons = array();
			$toolbar_buttons[] = array(
				__( 'Logout', 'ipt_fsqm' ),
				'ipt_fsqm_up_logout',
				'',
				'none',
				'normal',
				array(),
				'button',
				array(),
				array( 'onclick' => 'javascript:window.location.href="' . wp_logout_url( $redirect ) . '"' ),
				'',
				'switch',
			);
			$toolbar_buttons = apply_filters( 'ipt_fsqm_up_filter_toolbar', $toolbar_buttons );
		}

		// We need a $form_element instance for theme management, only the base should do
		$form_element = new IPT_FSQM_Form_Elements_Base();
		$theme_element = $form_element->get_theme_by_id( $theme );
		ob_start();
		?>
<div class="ipt_fsqm_user_portal ipt_uif_front ipt_uif_common" data-ui-theme="<?php echo esc_attr( json_encode( $theme_element['include'] ) ); ?>" data-ui-theme-id="<?php echo esc_attr( $theme ); ?>" data-settings="<?php echo esc_attr( json_encode( $shortcode_settings ) ); ?>" data-nonce="<?php echo esc_attr( wp_create_nonce( 'ipt_fsqm_up_nonce_' . $user->ID ) ); ?>" data-ajaxurl="<?php echo esc_attr( admin_url( 'admin-ajax.php' ) ); ?>">
	<noscript>
		<div class="ipt_fsqm_form_message_noscript ui-widget ui-widget-content ui-corner-all">
			<div class="ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
				<h3><?php _e( 'Javascript is disabled', 'ipt_fsqm' ); ?></h3>
			</div>
			<div class="ui-widget-content ui-corner-bottom">
				<p><?php _e( 'Javascript is disabled on your browser. Please enable it in order to use this form.', 'ipt_fsqm' ); ?></p>
			</div>
		</div>
	</noscript>
	<?php $ui->ajax_loader( false, '', array(), true, __( 'Loading', 'ipt_fsqm' ), array( 'ipt_uif_init_loader' ) ); ?>
	<div style="display: none" class="ipt_uif_hidden_init ui-widget-content ui-corner-all ipt_uif_up_main_container">
		<?php if ( !is_user_logged_in() || !( $user instanceof WP_User ) ) : ?>
		<?php $ui->divider( $login, 'h5', 'left', 0xe10f ) ?>
		<form action="<?php echo esc_url( site_url( 'wp-login.php', 'login_post' ) ); ?>" name="<?php echo $args['form_id']; ?>" id="<?php echo $args['form_id']; ?>" method="post">
			<?php $login_form_top = apply_filters( 'login_form_top', '', $args ); ?>
			<?php if ( $login_form_top != '' ) : ?>
			<div class="ipt_uif_column ipt_uif_column_full">
				<div class="ipt_uif_column_inner side_margin">
					<?php echo $login_form_top; ?>
					<div class="clear"></div>
				</div>
			</div>
			<?php endif; ?>
			<div class="ipt_uif_column ipt_uif_column_half ipt_uif_column_custom">
				<div class="ipt_uif_column_inner side_margin">
					<div class="ipt_uif_question">
						<div class="ipt_uif_question_label"><label for="<?php echo $args['id_username']; ?>"><?php echo $args['label_username']; ?><span class="ipt_uif_question_required">*</span></label></div>
						<div class="ipt_uif_question_content">
							<div class="ipt_uif_icon_and_form_elem_holder">
							<input class="ipt_uif_text"
								type="text"
								placeholder="<?php echo esc_attr( $args['label_username'] ); ?>"
								name="log"
								id="<?php echo $args['id_username']; ?>"
								value="<?php echo esc_attr( $args['value_username'] ); ?>" />
							<?php $ui->print_icon_by_class( 'user-2' ); ?>
							</div>
						</div>
						<div class="clear"></div>
					</div>
					<div class="clear"></div>
				</div>
			</div>
			<div class="ipt_uif_column ipt_uif_column_half ipt_uif_column_custom">
				<div class="ipt_uif_column_inner side_margin">
					<div class="ipt_uif_question">
						<div class="ipt_uif_question_label"><label for="<?php echo $args['id_password']; ?>"><?php echo $args['label_password']; ?><span class="ipt_uif_question_required">*</span></label></div>
						<div class="ipt_uif_question_content">
							<div class="ipt_uif_icon_and_form_elem_holder">
							<input class="ipt_uif_text ipt_uif_password"
								type="password"
								placeholder="<?php echo esc_attr( $args['label_password'] ); ?>"
								name="pwd"
								id="<?php echo $args['id_password']; ?>"
								value="" />
							<?php $ui->print_icon_by_class( 'quill' ); ?>
							</div>
						</div>
						<div class="clear"></div>
					</div>
					<div class="clear"></div>
				</div>
			</div>
			<?php $login_form_middle = apply_filters( 'login_form_middle', '', $args ); ?>
			<?php if ( $login_form_middle != '' ) : ?>
			<div class="ipt_uif_column ipt_uif_column_full">
				<div class="ipt_uif_column_inner side_margin">
					<?php echo $login_form_middle; ?>
					<div class="clear"></div>
				</div>
			</div>
			<?php endif; ?>
			<?php if ( $args['remember'] ) : ?>
			<div class="ipt_uif_column ipt_uif_column_forth ipt_uif_column_custom">
				<div class="ipt_uif_column_inner side_margin">
					<div class="ipt_uif_label_column column_1">
						<input class="ipt_uif_checkbox" name="rememberme" type="checkbox" id="<?php echo esc_attr( $args['id_remember'] ); ?>" value="forever"<?php echo ( $args['value_remember'] ? ' checked="checked"' : '' ); ?> />
						<label data-labelcon="&#xe18e;" for="<?php echo esc_attr( $args['id_remember'] ); ?>"><?php echo esc_html( $args['label_remember'] ); ?></label>
					</div>
					<div class="clear"></div>
				</div>
			</div>
			<?php endif; ?>
			<div class="ipt_uif_column ipt_uif_column_three_forth ipt_uif_column_custom">
				<div class="ipt_uif_column_inner" style="margin: 0">
					<?php $ui->buttons( $login_buttons, '', 'center' ); ?>
					<div class="clear"></div>
				</div>
			</div>
			<?php $login_form_bottom = apply_filters( 'login_form_bottom', '', $args ); ?>
			<?php if ( $login_form_bottom != '' ) : ?>
			<div class="ipt_uif_column ipt_uif_column_full">
				<div class="ipt_uif_column_inner side_margin">
					<?php echo $login_form_bottom; ?>
					<div class="clear"></div>
				</div>
			</div>
			<?php endif; ?>
			<input type="hidden" name="redirect_to" value="<?php echo esc_url( $args['redirect'] ); ?>" />
			<div class="clear"></div>
		</form>

		<?php else : ?>

		<div class="ipt_fsqm_user_portal_welcome">
			<?php if ( $avatar !== '' || $avatar !== '0' || $avatar > 0 ) : ?>
			<div class="ipt_fsqm_up_profile">
				<?php echo get_avatar( $user->ID, $avatar ); ?>
			</div>
			<?php endif; ?>
			<div class="ipt_fsqm_up_welcome">
				<?php if ( $title != '' ) : ?>
				<h2><?php echo $title; ?></h2>
				<?php endif; ?>
				<div class="ipt_fsqm_up_msg">
					<?php echo do_shortcode( wpautop( str_replace( '%NAME%', '<strong>' . $user->display_name . '</strong>', $content ) ) ); ?>
				</div>
			</div>
			<div class="clear"></div>
			<div class="ipt_fsqm_up_toolbar">
				<?php $ui->buttons( $toolbar_buttons ); ?>
				<div class="ipt_fsqm_up_meta">
					<h6><?php $ui->print_icon_by_class( 'drawer2', false ); ?><?php printf( _n( '%d Submission', '%d Submissions', $total_count, 'ipt_fsqm' ), $total_count ); ?></h6>
					<?php if ( $showscore == '1' ) : ?>
					<h6><?php $ui->print_icon_by_class( 'quill', false ); ?><?php printf( __( '%2$s%% Avarage %1$s', 'ipt_fsqm' ), $scorelabel, number_format_i18n( $avg_score * 100, 2 ) ); ?></h6>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<?php $ui->progressbar( '', 0, array( 'ipt_fsqm_up_pb' ) ); ?>
		<?php $ui->ajax_loader( false, '', array(), true, __( 'Fetching Data', 'ipt_fsqm' ), array( 'ipt_fsqm_up_al' ) ); ?>
		<table class="ipt_fsqm_up_table" style="display: none">
			<thead>
				<tr>
					<th class="form_label" scope="col"><?php echo $formlabel; ?></th>
					<th class="date_label" scope="col"><?php echo $datelabel; ?></th>
					<?php if ( $showscore == '1' ) : ?>
					<th class="score_label" scope="col"><?php echo $scorelabel; ?></th>
					<th class="mscore_label" scope="col"><?php echo $mscorelabel; ?></th>
					<th class="pscore_label" scope="col"><?php echo $pscorelabel; ?></th>
					<?php endif; ?>
					<th class="action_label" scope="col"><?php echo $actionlabel; ?></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td><?php echo $nosubmission; ?></td>
					<td></td>
					<?php if ( $showscore == '1' ) : ?>
					<td></td>
					<td></td>
					<td></td>
					<?php endif; ?>
					<td></td>
				</tr>
			</tbody>
			<tfoot>
				<tr>
					<th class="form_label" scope="col"><?php echo $formlabel; ?></th>
					<th class="date_label" scope="col"><?php echo $datelabel; ?></th>
					<?php if ( $showscore == '1' ) : ?>
					<th class="score_label" scope="col"><?php echo $scorelabel; ?></th>
					<th class="mscore_label" scope="col"><?php echo $mscorelabel; ?></th>
					<th class="pscore_label" scope="col"><?php echo $pscorelabel; ?></th>
					<?php endif; ?>
					<th class="action_label" scope="col"><?php echo $actionlabel; ?></th>
				</tr>
			</tfoot>
		</table>
		<?php endif; ?>
	</div>
</div>
		<?php
		return ob_get_clean();
	}

	public static function user_portal_init() {
		// Just for the logged in users, so no need nopriv
		add_action( 'wp_ajax_ipt_fsqm_user_portal', array( __CLASS__, 'user_portal_ajax_response' ) );
	}

	public static function user_portal_ajax_response() {
		global $wpdb, $ipt_fsqm_info;
		@header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );

		// Get post variables
		$settings = isset( $_POST['settings'] ) ? (array) $_POST['settings'] : array();
		$nonce = isset( $_POST['_wpnonce'] ) ? $_POST['_wpnonce'] : '';
		$user = wp_get_current_user();
		$doing = isset( $_POST['doing'] ) ? (int) $_POST['doing'] : 0;

		// Parse the settings - Basically the shortcode atts
		$settings = wp_parse_args( $settings, array(
			'nosubmission' => __( 'No submissions yet.', 'ipt_fsqm' ),
			'login' => __( 'You need to login in order to view your submissions.', 'ipt_fsqm' ),
			'show_register' => '1',
			'show_forgot' => '1',
			'formlabel' => __( 'Form', 'ipt_fsqm' ),
			'datelabel' => __( 'Date', 'ipt_fsqm' ),
			'showscore' => '1',
			'scorelabel' => __( 'Score', 'ipt_fsqm' ),
			'mscorelabel' => __( 'Max', 'ipt_fsqm' ),
			'pscorelabel' => __( '%-age', 'ipt_fsqm' ),
			'linklabel' => __( 'View', 'ipt_fsqm' ),
			'actionlabel' => __( 'Action', 'ipt_fsqm' ),
			'editlabel' => __( 'Edit', 'ipt_fsqm' ),
			'avatar' => '96',
			'theme' => 'default',
			'title' => __( 'FSQM Pro User Portal', 'ipt_fsqm' ),
		) );

		// Check for authenticity
		if ( ! is_user_logged_in() || ! ( $user instanceof WP_User ) ) {
			echo json_encode( array(
				'success' => false,
				'error_msg' => __( 'You need to be logged in', 'ipt_fsqm' ),
			) );
			die();
		}

		if ( ! wp_verify_nonce( $nonce, 'ipt_fsqm_up_nonce_' . $user->ID ) ) {
			echo json_encode( array(
				'success' => false,
				'error_msg' => __( 'Invalid Nonce. Cheating?', 'ipt_fsqm' ),
			) );
			die();
		}

		// Prepare the return
		$return = array(
			'success' => true,
			'html' => '',
			'done' => 0,
		);

		// Prepare the UI
		$ui = IPT_Plugin_UIF_Front::instance( 'ipt_fsqm' );

		// Prepare the db variables
		$total = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(id) FROM {$ipt_fsqm_info['data_table']} WHERE user_id = %d", $user->ID ) );
		$per_page = 30;
		$start_page = $doing * $per_page;
		$data_ids = $wpdb->get_col( $wpdb->prepare( "SELECT id FROM {$ipt_fsqm_info['data_table']} WHERE user_id = %d ORDER BY date DESC LIMIT %d, %d", $user->ID, $start_page, $per_page ) );
		$data = new IPT_FSQM_Form_Elements_Data();

		// Check for empty
		if ( empty( $data_ids ) ) {
			$return['html'] .= '';
			$return['done'] = 100;
			echo json_encode( $return );
			die();
		}

		// Loop through and add the html
		foreach ( $data_ids as $id ) {
			$data->init( $id );
			$action_buttons = array();
			$action_buttons[] = array(
				$settings['linklabel'],
				'',
				'auto',
				'none',
				'normal',
				array( 'ipt_fsqm_up_tb' ),
				'anchor',
				array(),
				array(),
				$data->get_trackback_url(),
				'newspaper',
			);
			if ( $data->can_user_edit() ) {
				$action_buttons[] = array(
					$settings['editlabel'],
					'',
					'auto',
					'none',
					'normal',
					array( 'ipt_fsqm_up_edit' ),
					'anchor',
					array(),
					array(),
					$data->get_edit_url(),
					'pencil',
				);
			}
			$action_buttons = apply_filters( 'ipt_fsqm_up_filter_action_button', $action_buttons, $data );

			$return['html'] .= '<tr>';
			$return['html'] .= '<td class="form_label">' . $data->name . '</td>';
			$return['html'] .= '<td class="date_label">' . date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $data->data->date ) ) . '</td>';
			if ( $settings['showscore'] == '1' ) {
				$return['html'] .= '<td class="score_label ipt_fsqm_up_number">' . number_format_i18n( $data->data->score, 2 ) . '</td>';
				$return['html'] .= '<td class="mscore_label ipt_fsqm_up_number">' . number_format_i18n( $data->data->max_score, 2 ) . '</td>';
				$percentage = 0;
				if ( $data->data->max_score != 0 ) {
					$percentage = $data->data->score * 100 / $data->data->max_score;
				}
				$return['html'] .= '<td class="pscore_label ipt_fsqm_up_number">' . number_format_i18n( $percentage, 2 ) . '</td>';
			}
			ob_start();
			$ui->buttons( $action_buttons );
			$buttons = ob_get_clean();
			$return['html'] .= '<td class="action_label">' . $buttons . '</td>';
			$return['html'] .= '</tr>';
		}

		$done_till_now = $doing * $per_page + $per_page;
		if ( $done_till_now >= $total ) {
			$return['done'] = 100;
		} else {
			$return['done'] = (float) $done_till_now * 100 / $total;
		}

		echo json_encode( $return );
		die();
	}

	/*==========================================================================
	 * Quick Preview
	 *========================================================================*/
	public static function ipt_fsqm_quick_preview() {
		add_action( 'wp_ajax_ipt_fsqm_quick_preview', array( __CLASS__, 'ipt_fsqm_quick_preview_cb' ) );
	}

	public static function ipt_fsqm_quick_preview_cb() {
		$data_id = $_REQUEST['id'];
		$preview = new IPT_FSQM_Form_Elements_Data( $data_id );
		if ( $preview->data_id == null ) {
			echo 'Invalid ID';
			die();
		}
?>
<script type="text/javascript">
jQuery(document).ready(function($) {
	$('#ipt_fsqm_quick_preview_print').click(function(){
		$('#ipt_fsqm_quick_preview').printElement({
			leaveOpen:true,
			printMode:'popup'
		});
	});
});
</script>
<div style="text-align: center; margin: 10px">
	<a id="ipt_fsqm_quick_preview_print" class="button-primary"><?php _e( 'Print', 'ipt_fsqm' ); ?></a>
</div>
<div id="ipt_fsqm_quick_preview">
	<?php $preview->show_quick_preview(); ?>
</div>
		<?php
		die();
	}

	/*==========================================================================
	 * TrackBack and Admin Preview
	 *========================================================================*/
	public static function ipt_fsqm_full_preview_cb( $form, $score ) {
		$buttons = array();
		$buttons[] = array(
			__( 'Print', 'ipt_fsqm' ),
			'ipt_fsqm_report_print_' . $form->form_id,
			'large',
			'none',
			'normal',
			array( 'ipt_uif_printelement' ),
			'button',
			array( 'printid' => 'ipt_fsqm_score_data_' . $form->data_id ),
			array(),
			'',
			'print',
		);

		if ( $form->can_user_edit() && ! is_admin() ) {
			$buttons[] = array(
				__( 'Edit', 'ipt_fsqm' ),
				'ipt_fsqm_report_print_' . $form->form_id,
				'large',
				'none',
				'normal',
				array( 'ipt_uif_printelement' ),
				'button',
				array(),
				array( 'onclick' => 'javascript:window.location.href="' . $form->get_edit_url() . '"' ),
				'',
				'pencil',
			);
		}
		$buttons = apply_filters( 'ipt_fsqm_filter_static_report_print', $buttons, $form, $score );
?>
<div class="ipt_uif_front ipt_uif_common">
	<div class="ipt_uif_mother_wrap ui-widget-content ui-widget-default">
		<div id="ipt_fsqm_submission_data_<?php echo $form->data_id; ?>" class="ipt_uif_column ipt_uif_column_full" style="margin-bottom: 20px;">
			<div class="ipt_uif_column ipt_uif_column_full ipt_fsqm_main_heading_column ipt_fsqm_full_preview_sb">
				<div class="ipt_uif_column_inner">
					<?php $form->ui->heading( __( 'Submission Data', 'ipt_fsqm' ), 'h2', 'left', 0xe020, false, false, array( 'ipt_fsqm_main_heading' ) ); ?>
				</div>
			</div>

			<?php $form->show_form( false, false, 0, false ); ?>
		</div>

		<?php $form->ui->clear(); ?>

		<div class="ipt_uif_column ipt_uif_column_full ipt_fsqm_main_heading_column ipt_fsqm_full_preview_print" style="margin-bottom: 20px">
			<div class="ipt_uif_column_inner">
				<?php if ( $score->data->max_score != 0 ) : ?>
				<?php $form->ui->heading( __( 'Score & Summary', 'ipt_fsqm' ), 'h2', 'left', 0xe0d3, false, false, array( 'ipt_fsqm_main_heading' ) ); ?>
				<?php else : ?>
				<?php $form->ui->heading( __( 'Print & Summary', 'ipt_fsqm' ), 'h2', 'left', 0xe08a, false, false, array( 'ipt_fsqm_main_heading' ) ); ?>
				<?php endif; ?>
			</div>
		</div>

		<div id="ipt_fsqm_score_data_<?php echo $form->data_id; ?>" class="ipt_uif_column ipt_uif_column_full">
			<?php $score->show_quick_preview(); ?>
		</div>
		<?php $form->ui->buttons( $buttons, '', 'center' ); ?>

		<?php $form->ui->clear(); ?>
	</div>
	<?php $form->ui->clear(); ?>
</div>
		<?php
	}

	public static function ipt_fsqm_full_preview( $id ) {
		$form = new IPT_FSQM_Form_Elements_Front( $id );
		if ( $form->form_id == null ) {
			$param = array( __( 'The ID you have provided is either invalid or has been deleted. Please go back and try again.', 'ipt_fsqm' ), true, __( 'Invalid ID', 'ipt_fsqm' ) );
			$form->container( array( array( $form->ui, 'msg_error' ), $param ), true );
			return;
		}
		$score = new IPT_FSQM_Form_Elements_Data( $id );
		$form->settings['type_specific']['normal']['wrapper'] = false;
		$form->type = 0;
		$form->container( array( array( __CLASS__, 'ipt_fsqm_full_preview_cb' ), array( $form, $score ) ), true );
	}

	public static function ipt_fsqm_form_edit( $id ) {
		$form = new IPT_FSQM_Form_Elements_Front( $id );
		if ( $form->form_id == null ) {
			$param = array( __( 'The ID you have provided is either invalid or has been deleted. Please go back and try again.', 'ipt_fsqm' ), true, __( 'Invalid ID', 'ipt_fsqm' ) );
			$form->container( array( array( $form->ui, 'msg_error' ), $param ), true );
			return;
		}
		if ( $form->can_user_edit() == false ) {
			$param = array( __( 'Invalid request. You can not edit this submission. If you were expecting this, then please contact the administrator of this website.', 'ipt_fsqm' ), true, __( 'Error', 'ipt_fsqm' ) );
			$form->container( array( array( $form->ui, 'msg_error' ), $param ), true );
			return;
		}
		$form->show_form( true, false, null, true, true );
	}


	/*==========================================================================
	 * Save a data
	 *========================================================================*/
	public static function ipt_fsqm_save_form() {
		add_action( 'wp_ajax_ipt_fsqm_save_form', array( __CLASS__, 'ipt_fsqm_save_form_cb' ) );
		add_action( 'wp_ajax_nopriv_ipt_fsqm_save_form', array( __CLASS__, 'ipt_fsqm_save_form_cb' ) );
		add_action( 'wp_ajax_ipt_fsqm_refresh_nonce', array( __CLASS__, 'ipt_fsqm_form_refresh_nonce' ) );
		add_action( 'wp_ajax_nopriv_ipt_fsqm_refresh_nonce', array( __CLASS__, 'ipt_fsqm_form_refresh_nonce' ) );
	}

	public static function ipt_fsqm_form_refresh_nonce() {
		global $wpdb, $ipt_fsqm_info;
		$form_id = isset( $_POST['form_id'] ) ? (int) $_POST['form_id'] : null;
		$data_id = isset( $_POST['data_id'] ) ? (int) $_POST['data_id'] : null;
		$return = array(
			'success' => false,
			'save_nonce' => '',
			'edit_nonce' => '',
		);

		@header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );
		$admin_update = false;
		$user_update = false;

		// if data id present then this can be either from admin or user
		$user_edit = false;
		if ( $data_id !== null ) {
			$user_edit = isset( $_POST['user_edit'] ) && $_POST['user_edit'] == '1' ? true : false;
		}

		// if from user then check the nonce
		if ( $user_edit ) {
			$user_update = true;
		} else {
			// Maybe Admin request
			// Check for user capability
			if ( $data_id !== null && ( !is_admin() || !current_user_can( 'manage_feedback' ) ) ) {
				$return = array(
					'success' => false,
					'errors' => array(
						0 => array(
							'id' => '',
							'msgs' => array( __( 'Invalid request.', 'ipt_fsqm' ) ),
						),
					),
				);
				echo json_encode( (object) $return );
				die();
			}
			$admin_update = true;
		}

		//Check for validity of form_id
		$form_id_check = $wpdb->get_var( $wpdb->prepare( "SELECT name FROM {$ipt_fsqm_info['form_table']} WHERE id=%d", $form_id ) );
		if ( null === $form_id_check ) {
			$return = array(
				'success' => false,
				'errors' => array(
					0 => array(
						'id' => '',
						'msgs' => array( __( 'Invalid form. Cheating?', 'ipt_fsqm' ) ),
					),
				),
			);
			echo json_encode( (object) $return );
			die();
		}
		// Check for validity of data_id
		if ( $data_id !== null ) {
			$data_id_check = $wpdb->get_var( $wpdb->prepare( "SELECT form_id FROM {$ipt_fsqm_info['data_table']} WHERE id = %d", $data_id ) );
			if ( $form_id != $data_id_check ) {
				$return = array(
					'success' => false,
					'errors' => array(
						0 => array(
							'id' => '',
							'msgs' => array( __( 'Invalid data. Cheating?', 'ipt_fsqm' ) ),
						),
					),
				);
				echo json_encode( (object) $return );
				die();
			}
		}

		// All set now instantiate and save and return
		$form_data = new IPT_FSQM_Form_Elements_Data( $data_id, $form_id );

		// But again check for the user edit capability
		if ( $user_edit && $form_data->can_user_edit() !== true ) {
			$return = array(
				'success' => false,
				'errors' => array(
					0 => array(
						'id' => '',
						'msgs' => array( __( 'Invalid request.', 'ipt_fsqm' ) ),
					),
				),
			);
			echo json_encode( (object) $return );
			die();
		}

		// At this point, everything is just fine
		$return['success'] = true;
		$return['save_nonce'] = wp_create_nonce( 'ipt_fsqm_form_data_save_' . $form_id );
		if ( $data_id !== null ) {
			$return['edit_nonce'] = wp_create_nonce( 'ipt_fsqm_user_edit_' . $data_id );
		}
		echo json_encode( (object) $return );
		die();
	}
	/**
	 *
	 *
	 * @global wpdb $wpdb
	 * @global array $ipt_fsqm_info
	 */
	public static function ipt_fsqm_save_form_cb() {
		global $wpdb, $ipt_fsqm_info;
		$post_data = $_POST;
		if (  isset( $post_data['ipt_ps_send_as_str'] ) && $post_data['ipt_ps_send_as_str'] == 'true' && isset( $post_data['ipt_ps_look_into'] ) ) {
			$parse_post = array();
			parse_str( $post_data[$post_data['ipt_ps_look_into']], $parse_post );
			if ( get_magic_quotes_gpc() ) {
				$parse_post = array_map( 'stripslashes_deep', $parse_post );
			}
			$post_data = $parse_post;
		}
		$form_id = (int) $post_data['form_id'];
		$data_id = isset( $post_data['data_id'] ) ? (int) $post_data['data_id'] : null;
		@header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );
		$admin_update = false;
		$user_update = false;

		// if data id present then this can be either from admin or user
		$user_edit = false;
		if ( $data_id !== null ) {
			$user_edit = isset( $post_data['user_edit'] ) && $post_data['user_edit'] == '1' ? true : false;
		}

		// if from user then check the nonce
		if ( $user_edit ) {
			if ( ! isset( $post_data['ipt_fsqm_user_edit_nonce'] ) || ! wp_verify_nonce( $post_data['ipt_fsqm_user_edit_nonce'], 'ipt_fsqm_user_edit_' . $data_id ) ) {
				$return = array(
					'success' => false,
					'errors' => array(
						0 => array(
							'id' => '',
							'msgs' => array( __( 'Invalid nonce. Cheating?', 'ipt_fsqm' ) ),
						),
					),
				);
				echo json_encode( (object) $return );
				die();
			}
			$user_update = true;
		} else {
			// Maybe Admin request
			// Check for user capability
			if ( $data_id !== null && ( !is_admin() || !current_user_can( 'manage_feedback' ) ) ) {
				$return = array(
					'success' => false,
					'errors' => array(
						0 => array(
							'id' => '',
							'msgs' => array( __( 'Invalid request.', 'ipt_fsqm' ) ),
						),
					),
				);
				echo json_encode( (object) $return );
				die();
			}
			$admin_update = true;
		}



		//Check for nonce
		$wpnonce = $post_data['ipt_fsqm_form_data_save'];
		if ( !wp_verify_nonce( $wpnonce, 'ipt_fsqm_form_data_save_' . $form_id ) ) {
			$return = array(
				'success' => false,
				'errors' => array(
					0 => array(
						'id' => '',
						'msgs' => array( __( 'Invalid nonce. Cheating?', 'ipt_fsqm' ) ),
					),
				),
			);
			echo json_encode( (object) $return );
			die();
		}

		//Check for validity of form_id
		$form_id_check = $wpdb->get_var( $wpdb->prepare( "SELECT name FROM {$ipt_fsqm_info['form_table']} WHERE id=%d", $form_id ) );
		if ( null === $form_id_check ) {
			$return = array(
				'success' => false,
				'errors' => array(
					0 => array(
						'id' => '',
						'msgs' => array( __( 'Invalid form. Cheating?', 'ipt_fsqm' ) ),
					),
				),
			);
			echo json_encode( (object) $return );
			die();
		}
		// Check for validity of data_id
		if ( $data_id !== null ) {
			$data_id_check = $wpdb->get_var( $wpdb->prepare( "SELECT form_id FROM {$ipt_fsqm_info['data_table']} WHERE id = %d", $data_id ) );
			if ( $form_id != $data_id_check ) {
				$return = array(
					'success' => false,
					'errors' => array(
						0 => array(
							'id' => '',
							'msgs' => array( __( 'Invalid data. Cheating?', 'ipt_fsqm' ) ),
						),
					),
				);
				echo json_encode( (object) $return );
				die();
			}
		}

		// All set now instantiate and save and return
		$form_data = new IPT_FSQM_Form_Elements_Data( $data_id, $form_id );

		// But again check for the user edit capability
		if ( $user_edit && $form_data->can_user_edit() !== true ) {
			$return = array(
				'success' => false,
				'errors' => array(
					0 => array(
						'id' => '',
						'msgs' => array( __( 'Invalid request.', 'ipt_fsqm' ) ),
					),
				),
			);
			echo json_encode( (object) $return );
			die();
		}

		$return = $form_data->save_form( $admin_update, $user_update );
		echo json_encode( (object) $return );
		die();
	}

	/*==========================================================================
	 * Report Generator
	 *========================================================================*/
	public static function ipt_fsqm_report() {
		add_action( 'wp_ajax_ipt_fsqm_report', array( __CLASS__, 'ipt_fsqm_report_cb' ) );
		add_action( 'wp_ajax_nopriv_ipt_fsqm_report', array( __CLASS__, 'ipt_fsqm_report_cb' ) );
	}

	public static function ipt_fsqm_report_cb() {
		global $wpdb, $ipt_fsqm_info;
		$settings = isset( $_POST['settings'] ) ? $_POST['settings'] : array();
		$survey = isset( $_POST['survey'] ) ? $_POST['survey'] : array();
		$feedback = isset( $_POST['feedback'] ) ? $_POST['feedback'] : array();
		$doing = isset( $_POST['doing'] ) ? (int) $_POST['doing'] : 0;
		$form_id = isset( $_POST['form_id'] ) ? (int) $_POST['form_id'] : 0;
		$do_data = isset( $_POST['do_data'] ) && $_POST['do_data'] == 'true' ? true : false;

		if ( !wp_verify_nonce( $_POST['wpnonce'], 'ipt_fsqm_report_ajax_' . $form_id ) ) {
			die( __( 'Cheatin&#8217; uh?' ) );
		}
		if ( $do_data && !wp_verify_nonce( $_POST['do_data_nonce'], 'ipt_fsqm_report_ajax_do_data_' . $form_id ) ) {
			die( __( 'Cheatin&#8217; uh?' ) );
		}

		@header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );

		$return = array(
			'type' => 'success',
			'done' => '0',
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

		$where = '';
		$where_arr = array();
		if ( isset( $settings['custom_date'] ) && $settings['custom_date'] == 'true' ) {
			if ( isset( $settings['custom_date_start'] ) && $settings['custom_date_start'] != '' ) {
				$where_arr[] = $wpdb->prepare( 'date >= %s', date( 'Y-m-d H:i:s', strtotime( $settings['custom_date_start'] ) ) );
			}

			if ( isset( $settings['custom_date_end'] ) && $settings['custom_date_end'] != '' ) {
				$where_arr[] = $wpdb->prepare( 'date <= %s', date( 'Y-m-d H:i:s', strtotime( $settings['custom_date_end'] ) ) );
			}

			if ( !empty( $where_arr ) ) {
				$where .= ' AND ' . implode( ' AND ', $where_arr );
			}
		}

		$data_ids = $wpdb->get_col( $wpdb->prepare( "SELECT id FROM {$ipt_fsqm_info['data_table']} WHERE form_id = %d{$where} ORDER BY id ASC LIMIT %d,%d", $form_id, $doing * $per_page, $per_page ) );
		$total = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(id) FROM {$ipt_fsqm_info['data_table']} WHERE form_id = %d{$where}", $form_id ) );

		if ( empty( $data_ids ) ) {
			$return['done'] = 100;
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
							if ( ! isset($return['survey']["$m_key"] ) ) {
								$return['survey']["$m_key"] = array();
							}
							$return['survey']["$m_key"] = call_user_func( $definition['callback_report_calculator'], $element, $data->data->mcq[$m_key], $m_key, $do_data, $return['survey']["$m_key"]  );
						}
						break;
					case 'radio' :
					case 'checkbox' :
					case 'select' :
						if ( !isset( $return['survey']["$m_key"] ) ) {
							$return['survey']["$m_key"] = array();
							$return['survey']["$m_key"]['others_data'] = array();
						}
						if ( empty( $data->data->mcq[$m_key]['options'] ) ) {
							continue 2;
						}
						foreach ( $data->data->mcq[$m_key]['options'] as $o_key ) {
							$return['survey']["$m_key"]["$o_key"] = isset( $return['survey']["$m_key"]["$o_key"] ) ? $return['survey']["$m_key"]["$o_key"] + 1 : 1;
						}
						if ( !empty( $data->data->mcq[$m_key]['others'] ) && $do_data ) {
							$return['survey']["$m_key"]['others_data'][] = array(
								'value' => esc_textarea( $data->data->mcq[$m_key]['others'] ),
								'name' => $data->data->f_name . ' ' . $data->data->l_name,
								'email' => $data->data->email == '' ? __( 'anonymous', 'ipt_fsqm' ) : '<a href="mailto:' . $data->data->email . '">' . $data->data->email . '</a>',
								'id' => $data->data_id,
							);
						}
						break;
					case 'slider' :
						if ( !isset( $return['survey']["$m_key"] ) ) {
							$return['survey']["$m_key"] = array();
						}
						if ( !isset($data->data->mcq[$m_key]['value']) || '' == $data->data->mcq[$m_key]['value'] ) {
							continue 2;
						}
						$return['survey']["$m_key"]["{$data->data->mcq[$m_key]['value']}"] = isset( $return['survey']["$m_key"]["{$data->data->mcq[$m_key]['value']}"] ) ? $return['survey']["$m_key"]["{$data->data->mcq[$m_key]['value']}"] + 1 : 1;
						break;
					case 'range' :
						if ( !isset( $return['survey']["$m_key"] ) ) {
							$return['survey']["$m_key"] = array();
						}
						if ( empty( $data->data->mcq[$m_key]['values'] ) ) {
							continue 2;
						}
						$key = "{$data->data->mcq[$m_key]['values']['min']},{$data->data->mcq[$m_key]['values']['max']}";
						$return['survey']["$m_key"][$key] = isset( $return['survey']["$m_key"][$key] ) ? $return['survey']["$m_key"][$key] + 1 : 1;
						break;
					case 'spinners' :
					case 'grading' :
					case 'starrating' :
					case 'scalerating' :
						if ( !isset( $return['survey']["$m_key"] ) ) {
							$return['survey']["$m_key"] = array();
						}
						if ( empty( $data->data->mcq[$m_key]['options'] ) ) {
							continue 2;
						}

						foreach ( $data->mcq[$m_key]['settings']['options'] as $o_key => $o_val ) {
							if ( !isset( $return['survey']["$m_key"]["$o_key"] ) ) {
								$return['survey']["$m_key"]["$o_key"] = array();
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

							$return['survey']["$m_key"]["$o_key"][$key] = isset( $return['survey']["$m_key"]["$o_key"][$key] ) ? $return['survey']["$m_key"]["$o_key"][$key] + 1 : 1;
						}
						break;
					case 'matrix' :
						if ( !isset( $return['survey']["$m_key"] ) ) {
							$return['survey']["$m_key"] = array();
						}
						if ( empty( $data->data->mcq[$m_key]['rows'] ) ) {
							continue 2;
						}
						foreach ( $data->data->mcq[$m_key]['rows'] as $r_key => $columns ) {
							if ( !isset( $return['survey']["$m_key"]["$r_key"] ) ) {
								$return['survey']["$m_key"]["$r_key"] = array();
							}
							foreach ( $columns as $c_key ) {
								$return['survey']["$m_key"]["$r_key"]["$c_key"] = isset( $return['survey']["$m_key"]["$r_key"]["$c_key"] ) ? $return['survey']["$m_key"]["$r_key"]["$c_key"] + 1 : 1;
							}
						}
						break;
					case 'toggle' :
						if ( !isset( $return['survey']["$m_key"] ) ) {
							$return['survey']["$m_key"] = array(
								'on' => 0,
								'off' => 0,
							);
						}
						if ( $data->data->mcq[$m_key]['value'] == false ) {
							$return['survey']["$m_key"]['off']++;
						} else {
							$return['survey']["$m_key"]['on']++;
						}
						break;
					case 'sorting' :
						if ( !isset( $return['survey']["$m_key"] ) ) {
							$return['survey']["$m_key"] = array(
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
							$return['survey']["$m_key"]['preset']++;
						} else {
							$return['survey']["$m_key"]['other']++;
						}
						$return['survey']["$m_key"]['orders'][$user_order] = isset( $return['survey']["$m_key"]['orders'][$user_order] ) ? $return['survey']["$m_key"]['orders'][$user_order] + 1 : 1;
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
							if ( isset( $return['feedback']["$f_key"] ) ) {
								$return['feedback']["$f_key"] = array();
							}
							$return['feedback']["$f_key"] = call_user_func( $definition['callback_report_calculator'], $element, $data->data->freetype[$f_key], $f_key, $do_data, $return['feedback']["$f_key"] );
						}
						break;
					case 'upload' :
						// Create the array
						if ( ! isset( $return['feedback']["$f_key"] ) ) {
							$return['feedback']["$f_key"] = array();
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

						$return['feedback']["$f_key"][] = array(
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
						if ( !isset( $return['feedback']["$f_key"] ) ) {
							$return['feedback']["$f_key"] = array();
						}
						$return['feedback']["$f_key"][] = array(
							'value' => wpautop( esc_textarea( $data->data->freetype["$f_key"]['value'] ) ),
							'name'  => $data->data->f_name . ' ' . $data->data->l_name,
							'email' => $data->data->email == '' ? __( 'anonymous', 'ipt_fsqm' ) : '<a href="mailto:' . $data->data->email . '">' . $data->data->email . '</a>',
							'phone' => $data->data->phone,
							'date'  => date_i18n( get_option( 'date_format' ) . __(' \a\t ', 'ipt_fsqm') . get_option( 'time_format' ), strtotime( $data->data->date ) ),
							'id'    => $data->data_id,
						);
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

		$return['survey'] = (object) $return['survey'];
		$return['feedback'] = (object) $return['feedback'];

		echo json_encode( (object) $return );
		die();
	}

	/*==========================================================================
	 * Database abstractions
	 *========================================================================*/

	/**
	 * Get all of the forms
	 *
	 * @global wpdb $wpdb
	 * @global array $ipt_fsqm_info
	 * @return array
	 */
	public static function get_forms() {
		global $wpdb, $ipt_fsqm_info;
		return $wpdb->get_results( "SELECT * FROM {$ipt_fsqm_info['form_table']} ORDER BY id DESC" );
	}

	public static function get_form( $form_id ) {
		global $wpdb, $ipt_fsqm_info;
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$ipt_fsqm_info['form_table']} WHERE id = %d", $form_id ) );
	}

	public static function delete_submissions( $ids = array() ) {
		global $wpdb, $ipt_fsqm_info;
		if ( empty( $ids ) ) {
			return;
		}

		if ( ! is_array( $ids ) ) {
			$ids = (array) $ids;
		}

		$ids = array_map( 'intval', $ids );

		$delete_ids = implode( ',', $ids );

		do_action( 'ipt_fsqm_submissions_deleted', $ids );

		return $wpdb->query( "DELETE FROM {$ipt_fsqm_info['data_table']} WHERE id IN ({$delete_ids})" );
	}

	public static function star_submissions( $ids = array() ) {
		global $wpdb, $ipt_fsqm_info;
		if ( empty( $ids ) ) {
			return;
		}

		if ( ! is_array( $ids ) ) {
			$ids = (array) $ids;
		}

		$ids = array_map( 'intval', $ids );

		$update_ids = implode( ',', $ids );

		do_action( 'ipt_fsqm_submissions_starred', $ids );

		return $wpdb->query( "UPDATE {$ipt_fsqm_info['data_table']} SET star = 1 WHERE id IN ({$update_ids})" );
	}

	public static function unstar_submissions( $ids = array() ) {
		global $wpdb, $ipt_fsqm_info;
		if ( empty( $ids ) ) {
			return;
		}

		if ( ! is_array( $ids ) ) {
			$ids = (array) $ids;
		}

		$ids = array_map( 'intval', $ids );

		$update_ids = implode( ',', $ids );

		do_action( 'ipt_fsqm_submissions_unstarred', $ids );

		return $wpdb->query( "UPDATE {$ipt_fsqm_info['data_table']} SET star = 0 WHERE id IN ({$update_ids})" );
	}

	public static function delete_forms( $ids = array() ) {
		global $wpdb, $ipt_fsqm_info;
		if ( empty( $ids ) ) {
			return;
		}

		if ( ! is_array( $ids ) ) {
			$ids = (array) $ids;
		}

		$ids = array_map( 'intval', $ids );

		$delete_ids = implode( ',', $ids );

		$submission_ids = $wpdb->get_col( "SELECT id FROM {$ipt_fsqm_info['data_table']} WHERE form_id IN ({$delete_ids})" );

		self::delete_submissions( $submission_ids );

		do_action( 'ipt_fsqm_forms_deleted', $ids );
		return $wpdb->query( "DELETE FROM {$ipt_fsqm_info['form_table']} WHERE id IN ({$delete_ids})" );
	}

	public static function copy_form( $id ) {
		global $wpdb, $ipt_fsqm_info;
		$prev = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$ipt_fsqm_info['form_table']} WHERE id = %d", $id ) );
		if ( null == $prev ) {
			return;
		}

		$prev->name .= ' Copy';
		$wpdb->insert( $ipt_fsqm_info['form_table'], array(
			'name' => $prev->name,
			'settings' => $prev->settings,
			'layout' => $prev->layout,
			'design' => $prev->design,
			'mcq' => $prev->mcq,
			'freetype' => $prev->freetype,
			'pinfo' => $prev->pinfo,
			'type' => $prev->type,
		), '%s' );

		do_action( 'ipt_fsqm_form_copied', $id, $wpdb->insert_id );
	}

	/*==========================================================================
	 * Encrypt & Decrypt
	 *========================================================================*/
	public static function encrypt( $input_string ) {
		$key = get_option( 'ipt_fsqm_key' );
		$iv_size = mcrypt_get_iv_size( MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB );
		$iv = mcrypt_create_iv( $iv_size, MCRYPT_RAND );
		$h_key = hash( 'sha256', $key, TRUE );
		return base64_encode( mcrypt_encrypt( MCRYPT_RIJNDAEL_256, $h_key, $input_string, MCRYPT_MODE_ECB, $iv ) );
	}

	public static function decrypt( $encrypted_input_string ) {
		$key = get_option( 'ipt_fsqm_key' );
		$iv_size = mcrypt_get_iv_size( MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB );
		$iv = mcrypt_create_iv( $iv_size, MCRYPT_RAND );
		$h_key = hash( 'sha256', $key, TRUE );
		return trim( mcrypt_decrypt( MCRYPT_RIJNDAEL_256, $h_key, base64_decode( $encrypted_input_string ), MCRYPT_MODE_ECB, $iv ) );
	}

	/*==========================================================================
	 * Some other functions
	 *========================================================================*/
	public static function get_current_url() {
		global $wp;
		$current_url = home_url( add_query_arg( array(), $wp->request ) );
		return $current_url;
	}

}
