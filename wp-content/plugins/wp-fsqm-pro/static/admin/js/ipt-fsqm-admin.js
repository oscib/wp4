/**
 * iPanelThemes Feedback Survey & Quiz Manager Pro specific admin js
 */
jQuery(document).ready(function($) {
	var save_button = $('#ipt_fsqm_save'),
	preview_button = $('#ipt_fsqm_preview'),
	ajax_loader = $('#ipt_fsqm_ajax_loader'),
	ajax_label_save = ajax_loader.data('save'),
	ajax_label_preview = ajax_loader.data('preview'),
	ajax_label_success = ajax_loader.data('success'),
	ajax_loader_text = ajax_loader.find('.ipt_uif_ajax_loader_text'),
	ajax_loader_animator = ajax_loader.find('.ipt_uif_ajax_loader_inner'),
	autosave_ajax_loader = $('#ipt_fsqm_auto_save');
	doing_autosave = false,
	doing_save = false,
	changes = false,
	intervalID = null;

	var setAutoSave = function() {
		intervalID = setInterval(function() {
			// Stop if still doing autosave
			if (doing_autosave || doing_save || !changes) {
				return;
			}
			doing_autosave = true;
			save_button.attr('disabled', true);
			preview_button.attr('disabled', true);
			autosave_ajax_loader.show();
			save(function() {
				doing_autosave = false;
				save_button.attr('disabled', false);
				preview_button.attr('disabled', false);
				autosave_ajax_loader.fadeOut('fast');
			});
		}, 20000);
	};

	var clearAutoSave = function() {
		doing_autosave = false;
		clearInterval(intervalID);
	};

	var setConfirmExit = function() {
		$(window).on('beforeunload', function() {
			if(changes) {
				return 'You have unsaved changes. Leaving this page will discard the changes';
			} else {
				return;
			}
		});
	};

	var setChangeEvents = function() {
		//Click events
		$(document).on('click', '#ipt_fsqm_settings_save, #ipt_fsqm_add_layout, .ipt_uif_builder_layout_settings, .ipt_uif_builder_layout_del', function() {
			changes = true;
		});
		//Drag events
		$(document).on('dragstop', '.ipt_uif_droppable_element, .ipt_uif_builder_layout_tabs', function() {
			changes = true;
		});
		//Sort events, PS: Do not do sortupdate, although it is a good idea
		//but it may fail when we are programatically moving an element to another list
		//especially in the case of moving elements through tabs.
		$(document).on('sortstop', '.ipt_uif_builder_drop_here, .ipt_uif_builder_layout_tab', function() {
			changes = true;
		});
		//Change events
		$(document).on('change', 'input, select, textarea', function() {
			changes = true;
		});
		//Range and Slider Events
		$(document).on('slidestop', function() {
			changes = true;
		});
	};


	var save = function(callback) {
		if(doing_save) {
			return;
		}
		doing_save = true;
		if(!doing_autosave) {
			ajax_loader_text.html(ajax_label_save);
			ajax_loader_animator.addClass('ipt_uif_ajax_loader_animate');
			ajax_loader.show();
		}

		var data_str = $('.ipt_uif > form').eq(0).serialize();
		var return_id = 0;
		var data = {
			action: $('.ipt_uif > form').eq(0).find('[name="action"]').val(),
			ipt_ps_post: data_str,
			ipt_ps_send_as_str: true,
			ipt_ps_look_into: 'ipt_ps_post'
		};

		$.post(ajaxurl, data, function(data) {
			ajax_loader_animator.removeClass('ipt_uif_ajax_loader_animate');
			if(data == 'cheating') {
				ajax_loader_text.html('Cheating');
			} else {
				var form_id = $('#form_id');
				if(form_id.length) {
					form_id.val(data);
				} else {
					$('<input type="hidden" name="form_id" id="form_id" value="' + data + '" />').prependTo($('#ipt_fsqm_form'))
				}
				return_id = data;
				ajax_loader_text.html(ajax_label_success);
			}

			changes = false;
		}).fail(function() {
			ajax_loader_animator.removeClass('ipt_uif_ajax_loader_animate');
			ajax_loader_text.html('HTTP Error');
		}).always(function() {
			ajax_loader.delay(1000).fadeOut('fast', function() {
				if(typeof(callback) == 'function') {
					callback(return_id);
				}
				doing_save = false;
			});
		});
	};

	var keyPressListeners = function() {
		$(document).on('keypress', function(e) {
			if (e.ctrlKey) {
				if (e.charCode == 115) {
					e.preventDefault();
					save_button.trigger('click');
				}
				if (e.charCode == 113) {
					e.preventDefault();
					preview_button.trigger('click');
				}
			}
		});
	};


	if(save_button.length) {
		save_button.attr('disabled', false);
		//Set the change event
		setChangeEvents();
		//Set the autosave
		setAutoSave();
		//Set the before exit
		setConfirmExit();
		//Set the on click event
		save_button.on('click', function(e) {
			e.preventDefault();
			save();
		});
		//Set the ctrl+s, ctrl+q
		keyPressListeners();
	}

	if(preview_button.length) {
		preview_button.attr('disabled', false);
		preview_button.on('click', function(e) {
			e.preventDefault();
			save(function(return_id) {
				var width = $(window).width(), H = $(window).height(), W = ( 1280 < width ) ? 1280 : width, adminbar_height = 0;
				if ( $('body.admin-bar').length )
					adminbar_height = 28;

				var title = 'WP Feedback, Survey & Quiz Manager Pro - Preview',
				url = ajaxurl + '?action=ipt_fsqm_preview_form&form_id=' + return_id,
				dialog = $('<div><iframe src="' + url + '" style="width: 100%; height: 100%; border: 0 none;"></iframe>');
				dialog.dialog({
					autoOpen : true,
					modal : true,
					width : ( W - 80 ),
					height : ( H - 85 - adminbar_height ),
					title : title,
					closeOnEscape: true,
					create : function(event, ui) {
						$('body').addClass('ipt_uif_common ipt_uif');
					},
					close : function(event, ui) {
						$('body').removeClass('ipt_uif_common ipt_uif');
					}
				});
			});
		});
	}
});
