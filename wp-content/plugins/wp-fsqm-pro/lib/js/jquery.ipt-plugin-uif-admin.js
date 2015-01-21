/**
 * iPanelThemes Plugin Framework
 *
 * This is a jQuery plugin which works on the plugin framework to populate the UI
 * Admin area
 *
 * Dependencies: TODO
 *
 * @author Swashata Ghosh <swashata@intechgrity.com>
 * @version 1.0.3
 */
(function($) {
	//Default Options
	var defaultOp = {

	};

	var wp_media_reference = {
		input : null,
		preview : null,
		download : null,
		self : null
	};

	var ipt_uif_wp_media_frame;

	$(window).on('resize', function() {
		methods.reinitTBAnchors();
	});

	//Methods
	var methods = {
		init : function(options) {
			var op = $.extend(true, {}, defaultOp, options); //No use right now
			var _parent = this;

			return this.each(function() {
				var self = $(this);

				//Init the help + delegate
				self.on('click', '.ipt_uif_msg', function(e) {
					methods.applyHelp.apply(this, [e]);
				});

				//Init the checkbox toggler
				self.find('.ipt_uif_checkbox_toggler').each(function() {
					methods.applyCheckboxToggler.apply(this);
				});

				//Init the spinner
				methods.applySpinner.apply(self.find('.ipt_uif_uispinner'));

				//Init the Slider
				self.find('.ipt_uif_slider').each(function() {
					methods.applySlider.apply(this);
				});

				//Init the Progressbar
				self.find('.ipt_uif_progress_bar').each(function() {
					methods.applyProgressBar.apply(this);
				});

				//Init the Icon Selector + delegated
				methods.applyIconSelector.apply(this);

				//Init the datepickers
				methods.applyDatePicker.apply(self.find('.ipt_uif_datepicker input.ipt_uif_text'));
				methods.applyDateTimePicker.apply(self.find('.ipt_uif_datetimepicker input.ipt_uif_text'));
				methods.applyDateTimeNowButton.apply(this);

				//Init the printElements + delegate
				methods.applyPrintElement.apply(this);

				//Init the font selector
				self.find('.ipt_uif_font_selector').each(function() {
					methods.applyFontSelector.apply(this);
				});

				//Init the theme selector
				self.find('.ipt_uif_theme_selector').each(function() {
					methods.applyThemeSelector.apply(this);
				});

				//Uploader
				self.find('.ipt_uif_upload').each(function() {
					methods.applyUploader.apply(this);
				});

				//Init the IRIR ColorPicker + delegate
				methods.applyIRIS.apply(this);

				//Init the conditional
				self.find('.ipt_uif_conditional_input').each(function() {
					methods.applyConditionalInput.apply(this);
				});
				self.find('.ipt_uif_conditional_select').each(function() {
					methods.applyConditionalSelect.apply(this);
				});

				//Init the collapsible
				self.find('.ipt_uif_collapsible').each(function() {
					methods.applyCollapsible.apply(this);
				});

				//Init the deleter + delegate
				self.on('click', '.wp-list-table a.delete', function(e) {
					methods.applyDeleteConfirm.apply(this, [e]);
				});

				//Init the Scroll
				self.find('.ipt_uif_scroll').each(function() {
					//methods.applyScrollBar.apply(this);
				});

				//Init the SDA
				self.find('.ipt_uif_sda').each(function() {
					methods.applySDA.apply(this);
				});

				//Init the Tabs
				self.find('.ipt_uif_tabs').each(function() {
					methods.applyTabs.apply(this, op);
				});

				//Init the Builder
				self.find('.ipt_uif_builder').each(function() {
					methods.applyBuilder.apply(this);
				});

			});
		},

		applyDeleteConfirm : function(e) {
			var self = $(this);
			e.preventDefault();
			$('<div>' + iptPluginUIFAdmin.L10n.delete_msg + '</div>').dialog({
				autoOpen : true,
				modal : true,
				minWidth : 400,
				closeOnEscape : true,
				title : iptPluginUIFAdmin.L10n.delete_title,
				buttons : {
					'Confirm' : function() {
						window.location.href = self.attr('href');
						$(this).dialog('close');
					},
					'Cancel' : function() {
						$(this).dialog('close');
					}
				},
				//appendTo : '.ipt_uif_common',
				create : function(event, ui) {
					$('body').addClass('ipt_uif_common');
				},
				close : function(event, ui) {
					$('body').removeClass('ipt_uif_common');
				}
			});
		},

		applyCheckboxToggler : function() {
			var selector = $($(this).data('selector')),
			self = $(this);
			self.on('change', function() {
				if(self.is(':checked')) {
					selector.prop('checked', true);
				} else {
					selector.prop('checked', false);
				}
			});

			selector.on('change', function() {
				self.prop('checked', false);
			});

			if(self.is(':checked')) {
				selector.prop('checked', true);
			}
		},

		applyFontSelector : function() {
			var select = $(this).find('select').eq(0);
			var preview = $(this).find('.ipt_uif_collapsible').eq(0);

			//Bind the change
			select.on('change keyup', function() {
				var selected = $(this).find('option:selected');
				var font_suffix = selected.data('fontinclude');
				var font_key = selected.val();
				var font_family = selected.text();

				//Attach the link
				if(!$('#ipt_uif_webfont_' + font_key).length) {
					$('<link rel="stylesheet" type="text/css" href="http://fonts.googleapis.com/css?family=' + font_suffix + '" id="ipt_uif_webfont_' + font_key + '" />').appendTo('head');
				}

				//Change the font family
				preview.css({fontFamily : font_family});
			});

			//Create the initial
			var selected = $(this).find('option:selected');
			var font_suffix = selected.data('fontinclude');
			var font_key = selected.val();
			var font_family = selected.text();

			//Attach the link
			if(!$('#ipt_uif_webfont_' + font_key).length) {
				$('<link rel="stylesheet" type="text/css" href="http://fonts.googleapis.com/css?family=' + font_suffix + '" id="ipt_uif_webfont_' + font_key + '" />').appendTo('head');
			}

			//Change the font family
			preview.css({fontFamily : font_family});
		},

		applyThemeSelector : function() {
			var select = $(this),
			preview = select.next('.ipt_uif_theme_preview'),
			updateTheme = function() {
				var selected = select.find('option:selected'),
				colors = selected.data('colors'),
				newHTML = '', i;
				preview.html('');
				for (i = 0; i < colors.length; i++) {
					newHTML += '<div style="background-color: #' + colors[i] + ';"></div>';
				}
				preview.html(newHTML);
			};

			select.on('change keyup', function() {
				updateTheme();
			});
			updateTheme();
		},

		applyPrintElement : function() {
			$(this).on('click', '.ipt_uif_printelement', function() {
				$('#' + $(this).data('printid')).printElement({
					leaveOpen:true,
					printMode:'popup',
					pageTitle : document.title
				});
			});
		},

		applyDatePicker : function() {
			if ( ! this.length ) {
				return;
			}

			this.datepicker({
				dateFormat : 'yy-mm-dd',
				beforeShow : function() {
					$('body').addClass('ipt_uif_common');
				},
				onClose : function() {
					$('body').removeClass('ipt_uif_common');
				},
				showButtonPanel: true,
				closeText: iptPluginUIFDTPL10n.closeText,
				currentText: iptPluginUIFDTPL10n.currentText,
				monthNames: iptPluginUIFDTPL10n.monthNames,
				monthNamesShort: iptPluginUIFDTPL10n.monthNamesShort,
				dayNames: iptPluginUIFDTPL10n.dayNames,
				dayNamesShort: iptPluginUIFDTPL10n.dayNamesShort,
				dayNamesMin: iptPluginUIFDTPL10n.dayNamesMin,
				firstDay: iptPluginUIFDTPL10n.firstDay,
				isRTL: iptPluginUIFDTPL10n.isRTL,
				timezoneText : iptPluginUIFDTPL10n.timezoneText
			});
		},

		applyDateTimePicker : function() {
			if ( ! this.length ) {
				return;
			}

			this.datetimepicker({
				dateFormat : 'yy-mm-dd',
				timeFormat : 'hh:mm:ss',
				beforeShow : function() {
					$('body').addClass('ipt_uif_common');
				},
				onClose : function() {
					$('body').removeClass('ipt_uif_common');
				},
				showButtonPanel: true,
				closeText: iptPluginUIFDTPL10n.closeText,
				currentText: iptPluginUIFDTPL10n.tcurrentText,
				monthNames: iptPluginUIFDTPL10n.monthNames,
				monthNamesShort: iptPluginUIFDTPL10n.monthNamesShort,
				dayNames: iptPluginUIFDTPL10n.dayNames,
				dayNamesShort: iptPluginUIFDTPL10n.dayNamesShort,
				dayNamesMin: iptPluginUIFDTPL10n.dayNamesMin,
				firstDay: iptPluginUIFDTPL10n.firstDay,
				isRTL: iptPluginUIFDTPL10n.isRTL,
				amNames : iptPluginUIFDTPL10n.amNames,
				pmNames : iptPluginUIFDTPL10n.pmNames,
				timeSuffix : iptPluginUIFDTPL10n.timeSuffix,
				timeOnlyTitle : iptPluginUIFDTPL10n.timeOnlyTitle,
				timeText : iptPluginUIFDTPL10n.timeText,
				hourText : iptPluginUIFDTPL10n.hourText,
				minuteText : iptPluginUIFDTPL10n.minuteText,
				secondText : iptPluginUIFDTPL10n.secondText,
				millisecText : iptPluginUIFDTPL10n.millisecText,
				microsecText : iptPluginUIFDTPL10n.microsecText,
				timezoneText : iptPluginUIFDTPL10n.timezoneText
			});
		},

		applyDateTimeNowButton : function() {
			$(this).on('click', '.ipt_uif_datepicker_now', function() {
				$(this).nextAll('.ipt_uif_text').val('NOW');
			});
		},

		applyCollapsible : function() {
			var state = false;
			var self = this;
			var collapse_box = $(this).find('> .ipt_uif_collapsed');
			if($(this).data('opened') == true) {
				state = true;
			}
			var controller = $(this).find('> .ipt_uif_box.cyan h3 a');

			//Attach the event
			controller.on('click', function() {
				$(this).toggleClass('ipt_uif_collapsible_open');
				collapse_box.slideToggle('normal', function() {
					methods.reinitParentScroll.apply(self);
				});
			});

			//Check the initial state
			if(state) {
				collapse_box.show();
				controller.addClass('ipt_uif_collapsible_open');
			} else {
				collapse_box.hide();
				controller.removeClass('ipt_uif_collapsible_open');
			}
		},

		applyUploader : function() {
			var input = $(this).find('input').eq(0);
			var preview = $(this).find('div.ipt_uif_upload_preview').eq(0);
			var button = $(this).find('button.ipt_uif_upload_button').eq(0);
			var cancel = $(this).find('button.ipt_uif_upload_cancel').eq(0);
			var download = $(this).find('a').eq(0);
			var filename;

			if(button.length && input.length) {
				//Initialize
				filename = input.val();
				preview.hide();
				download.hide();
				button.removeClass('ipt_uif_upload_float');
				if(methods.testImage(filename)) {
					preview.css({backgroundImage : 'url("' + filename + '")'}).show();
				} else if(filename == '') {
					cancel.hide();
				} else {
					download.attr('href', filename).show();
					button.addClass('ipt_uif_upload_float');
				}

				//Bind to previewer
				preview.on('click', function() {
					tb_show('', input.val() + '?TB_iframe=true');
				});

				// Bind to cancel button
				cancel.on('click', function(e) {
					e.preventDefault();
					// Remove the input value
					input.val('');
					preview.hide();
					download.hide();
					cancel.hide();
				});

				//Bind to upload button
				button.on('click', function(e) {
					e.preventDefault();

					//Set the reference variables
					wp_media_reference.input = input;
					wp_media_reference.preview = preview;
					wp_media_reference.download = download;
					wp_media_reference.self = this;
					wp_media_reference.cancel = cancel;

					//If wp_media already exists
					if(ipt_uif_wp_media_frame) {
						ipt_uif_wp_media_frame.open();
						return;
					}

					//Create the media frame
					ipt_uif_wp_media_frame = wp.media.frames.ipt_uif_wp_media_frame = wp.media({
						title : $(input).data('title'),
						button : {
							text : $(input).data('select')
						},
						multiple : false
					});

					//Bind the select event
					ipt_uif_wp_media_frame.on('select', function() {
						var attachment = ipt_uif_wp_media_frame.state().get('selection').first().toJSON();
						wp_media_reference.preview.hide();
						wp_media_reference.download.hide();
						$(wp_media_reference.self).removeClass('ipt_uif_upload_float');

						if(methods.testImage(attachment.url)) {
							wp_media_reference.preview.css({backgroundImage : 'url("' + attachment.url + '")'}).show();
						} else if(attachment.url == '') {

						} else {
							wp_media_reference.download.attr('href', attachment.url).show();
							$(wp_media_reference.self).addClass('ipt_uif_upload_float');
						}

						//Change the input value
						wp_media_reference.input.val(attachment.url);

						//Check to see if title is associated
						var associated_title_elem = wp_media_reference.input.data('settitle');
						if ( associated_title_elem != undefined && $( '#' + associated_title_elem ).length ) {
							$('#' + associated_title_elem).val(attachment.title);
						}

						// Show the cancel button
						wp_media_reference.cancel.show();

						//Reinit parent scroll
						methods.reinitParentScroll.apply(wp_media_reference.self);
					});

					//open it
					ipt_uif_wp_media_frame.open();
				});
			}
		},

		applyBuilder : function() {
			var self = this;
			//Store the keys
			var keys = JSON.parse($(self).find('input.ipt_uif_builder_keys').val());
			keys = $.extend(true, {}, JSON.parse($(self).find('input.ipt_uif_builder_default_keys').val()), keys);
			$(self).data('ipt_uif_builder_keys', keys);
			var replace = JSON.parse($(self).find('input.ipt_uif_builder_replace_string').val());
			$(self).data('ipt_uif_builder_replace', replace);

			//Init the variables
			var tabs = $(this).find('.ipt_uif_builder_layout'),
			adds = $(this).find('.ipt_uif_builder_add_layout');
			var tab = undefined, add = undefined;

			//Apply the layout tabs
			if(tabs.length) {
				tab = tabs[0];
				methods.builderTabs.apply(tab, [self]);
			}

			//Init the Add New Layout button
			if(adds.length) {
				add = adds[0];
				methods.builderAddTab.apply(add, [tab, self]);
			}

			//Init the new elements button
			$(this).find('.ipt_uif_droppable').each(function() {
				methods.builderDraggables.apply(this, [tab, self]);
			});

			//Init the toolbar
			$(this).find('.ipt_uif_builder_layout_settings_toolbar').each(function() {
				methods.builderToolbar.apply(this, [tab, self, add]);
			});

			//Init the settings
			var settings_box = $(this).find('.ipt_uif_builder_settings_box').eq(0);
			$(this).data('ipt_uif_builder_settings', settings_box);
			settings_box.data('ipt_uif_builder_settings_origin', undefined);

			//Init the settings save
			var settings_save = settings_box.next().find('button');
			methods.builderSettingsSaveInit.apply(settings_save, [settings_box, self]);

			//Delegate all settings and expandables
			methods.builderElementSettingsEvent.apply(this);

			//Hide the wp_editor
			$(this).find('.ipt_uif_builder_wp_editor').css({position : 'absolute', 'left' : -9999})
			// Init the settings save
			.find('button.ipt_uif_button').on('click', function() {
				settings_save.trigger('click');
			});


			//Init the del dragger
			$(this).find('.ipt_uif_builder_deleter').each(function() {
				methods.builderDeleter.apply(this, [settings_box, self]);
			});

			// Init the copier
			$(this).on('click', '.ipt_uif_builder_copy_handle', function(e) {
				methods.builderDuplicate.apply(this, [self, settings_box]);
				// No need to stop propagation because none of the child element can have a builder within!!
			});
		},

		applyHelp : function(e) {
			e.preventDefault();
			var trigger = $(this).find('.ipt_uif_msg_icon'),
			title = trigger.attr('title'),
			temp, dialog_content;

			if(undefined === title || '' === title) {
				if(undefined !== (temp = trigger.parent().parent().siblings('th').find('label').html())) {
					title = temp;
				} else {
					title = iptPluginUIFAdmin.L10n.help;
				}
			}

			dialog_content = $('<div><div style="padding: 10px;">'  + trigger.next('.ipt_uif_msg_body').html() + '</div></div>');
			var buttons = {};
			buttons[iptPluginUIFAdmin.L10n.got_it] = function() {
				$(this).dialog("close");
			};
			dialog_content.dialog({
				autoOpen: true,
				buttons: buttons,
				modal: true,
				minWidth: 600,
				closeOnEscape: true,
				title: title,
				//appendTo : '.ipt_uif_common',
				create : function(event, ui) {
					$('body').addClass('ipt_uif_common');
				},
				close : function(event, ui) {
					$('body').removeClass('ipt_uif_common');
				}
			});
		},

		applySDA : function() {
			//get the submit button
			var $submit_button = $(this).find('> .ipt_uif_sda_foot button.ipt_uif_sda_button');
			var self = $(this);

			//get some variables
			var vars = {
				sort : self.data('draggable') == 1 ? true : false,
				add : self.data('addable') == 1 ? true : false,
				del : self.data('addable') == 1 ? true : false,
				count : ($submit_button.length && $submit_button.data('count') ? $submit_button.data('count') : 0),
				key : ($submit_button.length && $submit_button.data('key') ? $submit_button.data('key') : '__KEY__'),
				confirmDel : ($submit_button.length && $submit_button.data('confirm') ? $submit_button.data('confirm') : 'Are you sure you want to delete? This can not be undone.'),
				confirmTitle : ($submit_button.length && $submit_button.data('confirmtitle') ? $submit_button.data('confirmtitle') : 'Confirmation of Deletion')
			};
			//alert(typeof($submit_button.data('count')));

			//store this
			$(this).data('iptSortableData', vars);

			//make them sortable
			if(vars.sort)
				methods.SDAsort.apply(this);

			//make them deletable
			if(vars.del) {
				methods.SDAattachDel.apply(this, [vars]);
			}

			var $this = this;
			//attach to add new
			if(vars.add) {
				$submit_button.click(function(e) {
					e.preventDefault();
					methods.SDAadd.apply($this, [$submit_button]);
				});
			}
		},

		applyConditionalInput : function() {
			//Get all the inputs
			var inputs = $(this).find('input');
			//Store all the IDs
			var ids = new Array();

			var _self = this;

			//Loop through
			inputs.each(function() {
				var input_ids = $(this).data('condid');

				if(typeof(input_ids) == 'string') {
					input_ids = input_ids.split(',');
				} else {
					input_ids = [];
				}
				//Concat
				ids.push.apply(ids, input_ids);

				//Save it
				$(this).data('ipt_uif_conditional_inputs', input_ids);
			});

			//Show checked function
			var show_checked = function() {
				var shown = new Array();
				//Show only the checked
				inputs.filter(':checked').each(function() {
					var show_ids = $(this).data('ipt_uif_conditional_inputs');
					for(var id in show_ids) {
						shown[show_ids[id]] = true;
						$('#' + show_ids[id]).show();
					}
				});
				//Hide rest
				for(var id in ids) {
					if(shown[ids[id]] != true) {
						$('#' + ids[id]).stop(true, true).hide();
					}
				}
				methods.reinitParentScroll.apply(_self);
			};

			//Bind the change
			inputs.on('change', function() {
				show_checked();
			});
			//Init it
			show_checked();
		},

		applyConditionalSelect : function() {
			var select = $(this).find('select').eq(0);
			var _self = this;

			var ids = new Array();
			select.find('option').each(function() {
				var input_ids = $(this).data('condid');

				if(typeof(input_ids) == 'string') {
					input_ids = input_ids.split(',');
				} else {
					input_ids = [];
				}

				ids.push.apply(ids, input_ids);
			});

			var show_checked = function() {
				//Hide all
				for(var id in ids) {
					$('#' + ids[id]).hide();
				}

				//Show the current
				var activated_ids = select.find('option:selected').data('condid');

				if(typeof(activated_ids) == 'string') {
					activated_ids = activated_ids.split(',');
				} else {
					activated_ids = [];
				}

				for(var id in activated_ids) {
					$('#' + activated_ids[id]).show();
				}
				methods.reinitParentScroll.apply(_self);
			};

			//Attach listener
			select.on('change keyup', function() {
				show_checked();
			});

			show_checked();
		},

		applyProgressBar : function() {
			//First get the start value
			var start_value = $(this).data('start') ? $(this).data('start') : 0;
			var progress_self = $(this);

			//Add the value to the inner div
			var value_div = progress_self.find('.ipt_uif_progress_value').addClass('code');
			value_div.html(start_value + '%');

			//Init the progressbar
			var progressbar = progress_self.progressbar({
				value : start_value,
				change : function(event, ui) {
					value_div.html($(this).progressbar('option', 'value') + '%');
				}
			});

			if(progress_self.next('.ipt_uif_button_container').find('.ipt_uif_button.progress_random_fun').length) {
				progress_self.next('.ipt_uif_button_container').find('.ipt_uif_button.progress_random_fun').on('click', function() {
					//this.preventDefault();
					var new_value = parseInt(Math.random()*100);
					progressbar.progressbar('option', 'value', new_value);
					return false;
				});
			}
		},

		applySpinner : function() {
			if ( ! this.length ) {
				return;
			}
			this.spinner({mouseWheel: false});
			this.off('mousewheel');
			/*$(this).on('mousewheel', function(e) {
				e.preventDefault();
				var scroll = $(this).parents('.ipt_uif_scroll').eq(0);
				//console.log(scroll);
				scroll.mCustomScrollbar('disable');
			});
			$(this).on('mouseout', function() {
				var scroll = $(this).parents('.ipt_uif_scroll').eq(0);
				//console.log(scroll);
				scroll.mCustomScrollbar('update');
			});*/
		},

		applySlider : function() {
			//First get the settings
			var step = (($(this).data('step'))? parseFloat($(this).data('step')) : 1);
			if(isNaN(step))
				step = 1;

			var min = parseFloat($(this).data('min'));
			if(isNaN(min))
				min = 1;

			var max = parseFloat($(this).data('max'));
			if(isNaN(max))
				max = 9999;

			var value = parseFloat($(this).val());
			if(isNaN(value))
				value = min;

			var slider_range = $(this).hasClass('slider_range') ? true : false;
			//alert(slider_range);

			var slider_settings = {
				min: min,
				max: max,
				step: step,
				range: false
			};

			var second_value;

			//Store the reference
			var first_input = $(this);

			//Get the second input if necessary
			var second_input = null;
			if(slider_range) {
				second_input = $(this).next('input');
				second_value = parseFloat(second_input.val());
				if(isNaN(second_value)) {
					second_value = min;
				}
			}

			//Prepare the show count
			var count_div = first_input.prev('div.ipt_uif_slider_count');

			//Now append the div
			var slider_div = $('<div />');
			slider_div.addClass(slider_range ? 'ipt_uif_slider_range' : 'ipt_uif_slider_single');

			var slider_div_duplicate;
			if(slider_range) {
				slider_div_duplicate = second_input.next('div');
			} else {
				slider_div_duplicate = first_input.next('div');
			}
			if(slider_div_duplicate.length) {
				slider_div_duplicate.remove();
			}

			if(slider_range) {
				second_input.after(slider_div);
			} else {
				$(this).after(slider_div);
			}


			//Prepare the slide function
			if(!slider_range) {
				slider_settings.slide = function(event, ui) {
					first_input.val(ui.value);
					if(count_div.length) {
						count_div.find('span').text(ui.value);
					}
				};
				slider_settings.value = value;
			} else {
				//alert('atta boy');
				slider_settings.slide = function(event, ui) {
					first_input.val(ui.values[0]);
					second_input.val(ui.values[1]);
					if(count_div.length) {
						count_div.find('span.ipt_uif_slider_count_min').text(ui.values[0]);
						count_div.find('span.ipt_uif_slider_count_max').text(ui.values[1]);
					}
				};
				slider_settings.values = [value, second_value];
				slider_settings.range = true;
			}

			//Make the input(s) readonly
			$(this).attr('readonly', true);
			if(slider_range) {
				second_input.attr('readonly', true);
			}

			//Init the counter
			if(count_div.length) {
				if(slider_range) {
					count_div.find('span.ipt_uif_slider_count_min').text(value);
					count_div.find('span.ipt_uif_slider_count_max').text(second_value);
				} else {
					count_div.find('span').text(value);
				}
			}

			//Init the slider
			var slider = slider_div.slider(slider_settings);

			//Bind the change function
			if(slider_range) {
				first_input.change(function() {
					slider.slider({
						values : [parseFloat(first_input.val()), parseFloat(second_input.val())]
					});
				});
				$(second_input).change(function() {
					slider.slider({
						values : [parseFloat(first_input.val()), parseFloat(second_input.val())]
					});
				});
			} else {
				first_input.change(function() {
					slider.slider({
						value : parseFloat(first_input.val())
					});
				});
			}
		},

		applyIconSelector : function() {
			var source = {"Web Applications":[58892,61442,61451,61454,61456,61465,61474,61486,61498,61579,61584,61587,61591,61598,61612,61642,61643,61672,61677,61678,61709,61710,61714,61729,61730,61734,61763,61831,57440,57442,57467,57486,57487,57488,57489,57497,57512,57513,57543,57544,57549,57367,57368,57557,57558,57559,57560,57561,57562,57563,57564,57567,57571,57572,57424,57678,57388,57389,57735,57739,57740,57741,57746,57749,57750,57765],"Business Icons":[61447,61468,61485,61557,61570,61574,61632,61669,61670,57427,57428,57443,57444,57445,57461,57464,57465,57360,57498,57499,57500,57501,57361,57502,57503,57504,57505,57555,57720,57731,57733],"Medical Icons":[61681,61688,61689,61690,61693,61843,57527,61680,61687],"eCommerce Icons":[61483,61484,61562,61597,57452,57453,57457,57354,57459,57460,57724],"Currency Icons":[61654,61779,61780,61781,61782,61783,61784,61785,61786,61845,57458],"Form Control Icons":[61452,61453,61460,61473,61510,61527,61528,61532,61533,61636,61637,61639,61674,61713,61726,61770,57441,57352,57353,57446,57447,57448,57359,57625,57626,57649,57650,57651,57723,57732,57744,57745],"User Action & Text Editor":[61449,61450,61470,61489,61490,61491,61492,61493,61494,61495,61496,61497,61499,61500,61506,61507,61508,61541,61582,61633,61638,61644,61645,61646,61659,61660,61661,61662,61666,61728,61733,61735,61739,61740,61757,61772,61789,61790,61792,61793,61794,61795,57490,57491,57492,57493,57494,57495,57506,57514,57515,57363,57516,57517,57565,57371,57372,57681,57682,57683,57684,57685,57686,57687,57688,57689,57377,57423,57690,57691,57373,57734,57747,57748,57751,57752,57753,57369,57754,57755,57756,57757,57758,57759,57760,57761],"Icon Spinners":[61712,57507,57362,57508,57509,57510,57511,57730],"Charts and Codes":[61481,61482,61568,61641,61742,57454,57455,57529,57530,57365,57737],"Attentive Icons":[61525,61526,61529,61530,61531,61534,61543,61544,61545,61546,61553,61614,61652,61653,61694,61736,61737,61738,61760,61766,61767,61846,57569,57614,57615,57616,57617,57618,57619,57620,57622,57623,57370,57624],"Multimedia Icons":[61441,61448,61469,61478,61479,61480,61501,61502,61515,61516,61518,61520,61521,61540,61764,61802,57346,57348,57496,57364,57573,57574,57575,57627,57628,57629,57631,57632,57633,57634,57636,57637,57638,57639,57640,57642,57643,57644,57645,57646,57647,57648,57652,57416,57417,57719,57729],"Location and Contact":[61443,61461,61476,61505,61589,61592,61664,61707,61725,61732,61745,57344,57425,57426,57462,57463,57466,57468,57469,57471,57472,57484,57550,57566,57375,57376,57726,57762,57764],"Date and Time":[61463,61555,61747,57473,57474,57475,57476,57477,57479,57480,57481],"Device Icons":[61457,61458,61477,61487,61488,61571,61600,61704,61705,61706,61723,61724,61744,57347,57349,57482,57483,57355,57356,57553,57418,57725,57727],"Tools Icons":[58923,61459,61475,61504,61558,61572,61573,61596,61601,61613,61616,61648,61668,61717,61741,61758,61771,61774,57345,57429,57430,57431,57433,57421,57439,57470,57518,57519,57520,57521,57522,57523,57420,57524,57525,57526,57366,57538,57539,57542,57554,57679,57680,57415,57736,57738],"Social Networking":[61509,61569,61580,61586,61593,61594,61595,61650,61651,61665,61715,61773,61798,61799,61804,61805,61806,61811,61812,61828,61844,57449,57374,57379,57380,57381,57382,57383,57384,57385,57386,57387,57390,57391,57392,57422,57692,57394,57693,57696,57697,57698,57699,57700,57396,57701,57702,57703,57704,57399,57705,57706,57402,57708,57403,57404,57405,57406,57407,57712,57763,57766,57767,57768,57769,57770,57771,57773,57774,57775,57780,57782,57783,57784,57785,57786],"Brands Icons":[61676,61750,61755,61756,61800,61801,61803,61808,61809,61810,61817,61818,61819,61820,61821,61822,61824,61825,61833,61834,61835,61836,61837,57393,57395,57694,57695,57397,57398,57707,57400,57401,57408,57709,57409,57710,57410,57711,57413,57718,57772,57776,57777,57778,57779,57781,57789,57790,57791,57792,57793,57794],"Files & Documents":[61462,61563,61564,61603,61686,61716,61787,61788,57350,57351,57450,57451,57357,57358,57485,57713,57714,57715,57411,57412,57716,57717,57721,57722,57728,57787,57788],"Food and Beverage":[61440,61684,61685,61692,57533,57534,57535],"Travel and Living":[61464,61547,61554,61585,61617,61649,61691,61765,57456,57531,57532,57537,57545,57546,57547,57548],"Weather & Nature Icons":[58880,58881,58882,58883,58884,58885,58886,58887,58888,58889,58890,58891,58893,58894,58895,58896,58897,58898,58899,58900,58901,58902,58903,58904,58905,58906,58907,58908,58909,58910,58911,58912,58913,58914,58915,58916,58917,58918,58919,58920,58921,58922,58925,58926,61548,61549,61634,61748,61829,61830,57536,57540,57552,57556],"Like & Dislike Icons":[61444,61445,61446,61550,61552,61561,61575,61576,61577,61578,61581,61731,61796,61797,57568,57570,57576,57577,57578,57579,57580,57581,57582,57583],"Emoticons":[61720,61721,61722,57584,57585,57586,57587,57588,57589,57590,57591,57592,57593,57594,57595,57596,57597,57598,57599,57600,57601,57602,57603,57604,57605,57606,57607,57608,57609],"Directional Icons":[61466,61467,61511,61512,61513,61514,61522,61523,61524,61536,61537,61538,61539,61559,61560,61565,61566,61604,61605,61606,61607,61608,61609,61610,61611,61618,61655,61656,61657,61658,61696,61697,61698,61699,61700,61701,61702,61703,61751,61752,61753,61754,61768,61769,61776,61777,61778,61813,61814,61815,61816,61838,61840,61841,57610,57611,57612,57613,57630,57635,57641,57653,57654,57655,57656,57657,57658,57659,57660,57661,57662,57663,57664,57665,57666,57667,57668,57669,57670,57671,57672,57673,57674,57675,57676,57677],"Other Icons":[58924,61517,61542,61556,61588,61590,61602,61635,61640,61667,61671,61673,61675,61682,61683,61708,61746,61749,61761,61762,61826,61827,61832,61842,57432,57434,57435,57436,57437,57438,57478,57528,57541,57551,57419,57621,57378,57414,57742,57743]},
			searchSource = {"Web Applications":["Lines","Search","Th list","Search plus","Search minus","Download","List alt","Bookmark","List","Sign out","Sign in","Upload","Bookmark o","Rss","Globe","List ul","List ol","Sitemap","Cloud download","Cloud upload","Quote left","Quote right","Mail reply","Code","Reply all","Code fork","Rss square","Archive","Connection","Feed","Pushpin","Box add","Box remove","Download 2","Upload 2","Reply","Binoculars","Search 2","Remove","Remove 2","Accessibility","List 2","Menu","Cloud download 2","Cloud upload 2","Download 3","Upload 3","Download 4","Upload 4","Globe 2","Earth","Attachment","Bookmark 2","Bookmarks","Checkbox unchecked","Checkbox partial","Feed 2","Feed 3","Settings","List 3","Numbered list","Menu 2","Checkbox checked","Code 2","Embed","Feed 4"],"Business Icons":["User","Inbox","Book","Comment","Facebook square","Comments","Group","Comment o","Comments o","Office","Newspaper","Book 2","Books","Library","Support","Address book","Notebook","Bubbles","Bubbles 2","Bubble","Bubbles 3","Bubbles 4","Users","User 2","Users 2","User 3","User 4","Signup","Profile","Bubble 2","User 5"],"Medical Icons":["Stethoscope","Hospital o","Ambulance","Medkit","H square","Wheelchair","Aid","User md","Building o"],"eCommerce Icons":["Tag","Tags","Shopping cart","Credit card","Tag 2","Tags 2","Cart","Cart 2","Credit","Calculate","Cart 3"],"Currency Icons":["Money","Euro","Gbp","Dollar","Rupee","Cny","Ruble","Won","Bitcoin","Turkish lira","Coin"],"Form Control Icons":["Check","Times","Trash o","Refresh","Check square o","Times circle","Check circle","Times circle o","Check circle o","Cut","Copy","Save","Paste","Circle","Flag checkered","Check square","Podcast","Copy 2","Copy 3","Paste 2","Paste 3","Paste 4","Storage","Enter","Exit","Loop","Loop 2","Loop 3","Copy 4","Disk","Radio checked","Radio unchecked"],"User Action & Text Editor":["Th large","Th","Rotate right","Font","Bold","Italic","Text height","Text width","Align left","Align center","Align right","Align justify","Dedent","Indent","Adjust","Tint","Edit","Expand","External link","Chain","Paperclip","Strikethrough","Underline","Table","Columns","Unsorted","Sort down","Sort up","Rotate left","Terminal","Crop","Unlink","Superscript","Subscript","Anchor","External link square","Sort alpha asc","Sort alpha desc","Sort amount asc","Sort amount desc","Sort numeric asc","Sort numeric desc","Undo","Redo","Flip","Flip 2","Undo 2","Redo 2","Quotes left","Zoomin","Zoomout","Contract","Expand 2","Contract 2","Link","Crop 2","Scissors","Font 2","Text height 2","Text width 2","Bold 2","Underline 2","Italic 2","Strikethrough 2","Omega","Sigma","Table 2","Pilcrow","Lefttoright","Righttoleft","Console","Expand 3","Table 3","Insert template","Newtab","Indent decrease","Indent increase","Spell check","Paragraph justify","Paragraph right","Paragraph center","Paragraph left","Paragraph justify 2","Paragraph right 2","Paragraph center 2","Paragraph left 2"],"Icon Spinners":["Spinner","Busy","Spinner 2","Spinner 3","Spinner 4","Spinner 5","Spinner 6","Spinner 7"],"Charts and Codes":["Qrcode","Barcode","Bar chart o","Bars","Puzzle piece","Barcode 2","Qrcode 2","Pie","Stats","Bars 2","Bars 3"],"Attentive Icons":["Plus circle","Minus circle","Question circle","Info circle","Crosshairs","Ban","Plus","Minus","Asterisk","Exclamation circle","Warning","Tasks","Google plus square","Google plus","Plus square","Question","Info","Exclamation","Bullseye","Minus square","Minus square o","Plus square o","Eye blocked","Warning 2","Notification","Question 2","Info 2","Info 3","Blocked","Cancel circle","Spam","Close","Minus 2","Plus 2"],"Multimedia Icons":["Music","Film","Play circle o","Volume off","Volume down","Volume up","Video camera","Picture o","Play","Pause","Forward","Fast forward","Step forward","Mail forward","Play circle","Youtube play","Image","Play 2","Forward 2","Equalizer","Brightness medium","Brightness contrast","Contrast","Play 3","Pause 2","Stop 2","Forward 3","Play 4","Pause 3","Stop 3","Forward 4","First","Last","Previous","Next","Volume high","Volume medium","Volume low","Volume mute","Volume mute 2","Volume increase","Volume decrease","Shuffle","Image 2","Images","Film 2","Music 2"],"Location and Contact":["Envelope o","Home","Flag","Map marker","Phone","Phone square","Envelope","Mobile phone","Flag o","Location arrow","Microphone slash","Home 2","Home 3","Home 4","Phone 2","Phone hang up","Envelop","Location","Location 2","Map","Map 2","Mobile","Target","Flag 2","Mail","Mail 2","Mobile 2","Mail 3","Mail 4"],"Date and Time":["Clock o","Calendar","Calendar o","History","Clock","Clock 2","Alarm","Alarm 2","Stopwatch","Calendar 2","Calendar 3"],"Device Icons":["Power off","Signal","Headphones","Print","Camera","Camera retro","Hdd o","Desktop","Laptop","Tablet","Gamepad","Keyboard o","Microphone","Headphones 2","Camera 2","Print 2","Keyboard","Laptop 2","Tv","Switch","Camera 3","Screen","Tablet 2"],"Tools Icons":["Compass","Gear","Lock","Pencil","Magnet","Key","Gears","Unlock","Bullhorn","Wrench","Filter","Magic","Dashboard","Folder open o","Eraser","Unlock alt","Pencil square","Compass 2","Pencil 2","Quill","Pen","Blog","Paint format","Dice","Bullhorn 2","Compass 3","Key 2","Key 3","Lock 2","Lock 3","Unlocked","Wrench 2","Cogs","Cog","Hammer","Wand","Meter 2","Dashboard 2","Hammer 2","Magnet 2","Powercord","Filter 2","Filter 3","Pencil 3","Cog 2","Meter"],"Social Networking":["Share square o","Twitter square","Linkedin square","Github square","Twitter","Facebook","Github","Pinterest","Pinterest square","Linkedin","Github alt","Share square","Youtube square","Youtube","Stack overflow","Instagram","Flickr","Tumblr","Tumblr square","Gittip","Vimeo square","Stack","Share","Googleplus","Googleplus 2","Googleplus 3","Google drive","Facebook 2","Facebook 3","Instagram 2","Twitter 2","Twitter 3","Youtube 2","Vimeo","Vimeo 2","Flickr 2","Flickr 3","Flickr 4","Picassa","Forrst","Forrst 2","Deviantart","Deviantart 2","Steam","Github 2","Github 3","Github 4","Github 5","Blogger","Tumblr 2","Tumblr 3","Yahoo","Soundcloud","Soundcloud 2","Reddit","Lastfm","Stumbleupon","Stackoverflow","Pinterest 2","Yelp","Twitter 4","Youtube 3","Vimeo 22","Flickr 5","Facebook 4","Googleplus 4","Picassa 2","Github 6","Steam 2","Blogger 2","Linkedin 2","Flattr","Pinterest 3","Stumbleupon 2","Delicious","Lastfm 2"],"Brands Icons":["Exchange","Maxcdn","Html 5","Css 3","Xing","Xing square","Dropbox","Adn","Bitbucket","Bitbucket square","Apple","Windows","Android","Linux","Dribbble","Skype","Foursquare","Trello","Vk","Weibo","Renren","Pagelines","Stack exchange","Lanyrd","Dribbble 2","Dribbble 3","Dribbble 4","Wordpress","Joomla","Tux","Finder","Windows 2","Xing 2","Xing 3","Foursquare 2","Foursquare 3","Paypal","Paypal 2","Html 52","Css 32","Wordpress 2","Apple 2","Android 2","Windows 8","Skype 2","Paypal 3","Html 53","Chrome","Firefox","IE","Safari","Opera"],"Files & Documents":["File o","Folder","Folder open","Certificate","File text o","Folder o","File","File text","File 2","File 3","Folder 2","Folder open 2","Drawer","Drawer 2","Drawer 3","Libreoffice","File pdf","File openoffice","File zip","File powerpoint","File xml","File css","File 4","File 5","Cabinet","File word","File excel"],"Food and Beverage":["Glass","Coffee","Cutlery","Beer","Glass 2","Mug","Food"],"Travel and Living":["Road","Gift","Plane","Trophy","Briefcase","Truck","Fighter jet","Ticket","Ticket 2","Gift 2","Trophy 2","Rocket 2","Briefcase 2","Airplane","Truck 2","Road 2"],"Weather & Nature Icons":["Sunrise","Sun","Moon","Sun 2","Windy","Wind","Snowflake","Cloudy","Cloud","Weather","Weather 2","Weather 3","Cloud 2","Lightning","Lightning 2","Rainy","Rainy 2","Windy 2","Windy 3","Snowy","Snowy 2","Snowy 3","Weather 4","Cloudy 2","Cloud 3","Lightning 3","Sun 3","Moon 2","Cloudy 3","Cloud 4","Cloud 5","Lightning 4","Rainy 3","Rainy 4","Windy 4","Windy 5","Snowy 4","Snowy 5","Weather 5","Cloudy 4","Lightning 5","Thermometer","Celsius","Fahrenheit","Leaf","Fire","Cloud 6","Fire extinguisher","Sun o","Moon o","Leaf 2","Fire 2","Lightning 6","Cloud 7"],"Like & Dislike Icons":["Heart","Star","Star o","Eye","Eye slash","Retweet","Thumbs o up","Thumbs o down","Star half","Heart o","Thumb tack","Star half empty","Thumbs up","Thumbs down","Eye 2","Eye 3","Star 2","Star 3","Star 4","Heart 2","Heart 3","Heart broken","Thumbs up 2","Thumbs up 3"],"Emoticons":["Smile o","Frown o","Meh o","Happy","Happy 2","Smiley","Smiley 2","Tongue","Tongue 2","Sad","Sad 2","Wink","Wink 2","Grin","Grin 2","Cool","Cool 2","Angry","Angry 2","Evil","Evil 2","Shocked","Shocked 2","Confused","Confused 2","Neutral","Neutral 2","Wondering","Wondering 2"],"Directional Icons":["Arrow circle o down","Arrow circle o up","Arrows","Step backward","Fast backward","Backward","Eject","Chevron left","Chevron right","Arrow left","Arrow right","Arrow up","Arrow down","Chevron up","Chevron down","Arrows v","Arrows h","Hand o right","Hand o left","Hand o up","Hand o down","Arrow circle left","Arrow circle right","Arrow circle up","Arrow circle down","Arrows alt","Caret down","Caret up","Caret left","Caret right","Angle double left","Angle double right","Angle double up","Angle double down","Angle left","Angle right","Angle up","Angle down","Chevron circle left","Chevron circle right","Chevron circle up","Chevron circle down","Level up","Level down","Toggle down","Toggle up","Toggle right","Long arrow down","Long arrow up","Long arrow left","Long arrow right","Arrow circle o right","Arrow circle o left","Toggle left","Point up","Point right","Point down","Point left","Backward 2","Backward 3","Eject 2","Arrow up left","Arrow up 2","Arrow up right","Arrow right 2","Arrow down right","Arrow down 2","Arrow down left","Arrow left 2","Arrow up left 2","Arrow up 3","Arrow up right 2","Arrow right 3","Arrow down right 2","Arrow down 3","Arrow down left 2","Arrow left 3","Arrow up left 3","Arrow up 4","Arrow up right 3","Arrow right 4","Arrow down right 3","Arrow down 4","Arrow down left 3","Arrow left 4","Tab"],"Other Icons":["None","Stop","Compress","Random","Lemon o","Square o","Bell","Flask","Square","Legal","Flash","Umbrella","Lightbulb o","Suitcase","Bell o","Circle o","Shield","Rocket","Ellipsis h","Ellipsis v","Female","Male","Bug","Dot circle o","Droplet","Pacman","Spades","Clubs","Diamonds","Pawn","Bell 2","Bug 2","Lab","Shield 2","Tree","Checkmark circle","Google","IcoMoon","Checkmark","Checkmark 2"]},
			sourceClass = {"Web Applications":["ipt-icomoon-lines","ipt-icomoon-search","ipt-icomoon-th-list","ipt-icomoon-search-plus","ipt-icomoon-search-minus","ipt-icomoon-download","ipt-icomoon-list-alt","ipt-icomoon-bookmark","ipt-icomoon-list","ipt-icomoon-sign-out","ipt-icomoon-sign-in","ipt-icomoon-upload","ipt-icomoon-bookmark-o","ipt-icomoon-rss","ipt-icomoon-globe","ipt-icomoon-list-ul","ipt-icomoon-list-ol","ipt-icomoon-sitemap","ipt-icomoon-cloud-download","ipt-icomoon-cloud-upload","ipt-icomoon-quote-left","ipt-icomoon-quote-right","ipt-icomoon-mail-reply","ipt-icomoon-code","ipt-icomoon-reply-all","ipt-icomoon-code-fork","ipt-icomoon-rss-square","ipt-icomoon-archive","ipt-icomoon-connection","ipt-icomoon-feed","ipt-icomoon-pushpin","ipt-icomoon-box-add","ipt-icomoon-box-remove","ipt-icomoon-download2","ipt-icomoon-upload2","ipt-icomoon-reply","ipt-icomoon-binoculars","ipt-icomoon-search2","ipt-icomoon-remove","ipt-icomoon-remove2","ipt-icomoon-accessibility","ipt-icomoon-list2","ipt-icomoon-menu","ipt-icomoon-cloud-download2","ipt-icomoon-cloud-upload2","ipt-icomoon-download3","ipt-icomoon-upload3","ipt-icomoon-download4","ipt-icomoon-upload4","ipt-icomoon-globe2","ipt-icomoon-earth","ipt-icomoon-attachment","ipt-icomoon-bookmark2","ipt-icomoon-bookmarks","ipt-icomoon-checkbox-unchecked","ipt-icomoon-checkbox-partial","ipt-icomoon-feed2","ipt-icomoon-feed3","ipt-icomoon-settings","ipt-icomoon-list3","ipt-icomoon-numbered-list","ipt-icomoon-menu2","ipt-icomoon-checkbox-checked","ipt-icomoon-code2","ipt-icomoon-embed","ipt-icomoon-feed4"],"Business Icons":["ipt-icomoon-user","ipt-icomoon-inbox","ipt-icomoon-book","ipt-icomoon-comment","ipt-icomoon-facebook-square","ipt-icomoon-comments","ipt-icomoon-group","ipt-icomoon-comment-o","ipt-icomoon-comments-o","ipt-icomoon-office","ipt-icomoon-newspaper","ipt-icomoon-book2","ipt-icomoon-books","ipt-icomoon-library","ipt-icomoon-support","ipt-icomoon-address-book","ipt-icomoon-notebook","ipt-icomoon-bubbles","ipt-icomoon-bubbles2","ipt-icomoon-bubble","ipt-icomoon-bubbles3","ipt-icomoon-bubbles4","ipt-icomoon-users","ipt-icomoon-user2","ipt-icomoon-users2","ipt-icomoon-user3","ipt-icomoon-user4","ipt-icomoon-signup","ipt-icomoon-profile","ipt-icomoon-bubble2","ipt-icomoon-user5"],"Medical Icons":["ipt-icomoon-stethoscope","ipt-icomoon-hospital-o","ipt-icomoon-ambulance","ipt-icomoon-medkit","ipt-icomoon-h-square","ipt-icomoon-wheelchair","ipt-icomoon-aid","ipt-icomoon-user-md","ipt-icomoon-building-o"],"eCommerce Icons":["ipt-icomoon-tag","ipt-icomoon-tags","ipt-icomoon-shopping-cart","ipt-icomoon-credit-card","ipt-icomoon-tag2","ipt-icomoon-tags2","ipt-icomoon-cart","ipt-icomoon-cart2","ipt-icomoon-credit","ipt-icomoon-calculate","ipt-icomoon-cart3"],"Currency Icons":["ipt-icomoon-money","ipt-icomoon-euro","ipt-icomoon-gbp","ipt-icomoon-dollar","ipt-icomoon-rupee","ipt-icomoon-cny","ipt-icomoon-ruble","ipt-icomoon-won","ipt-icomoon-bitcoin","ipt-icomoon-turkish-lira","ipt-icomoon-coin"],"Form Control Icons":["ipt-icomoon-check","ipt-icomoon-times","ipt-icomoon-trash-o","ipt-icomoon-refresh","ipt-icomoon-check-square-o","ipt-icomoon-times-circle","ipt-icomoon-check-circle","ipt-icomoon-times-circle-o","ipt-icomoon-check-circle-o","ipt-icomoon-cut","ipt-icomoon-copy","ipt-icomoon-save","ipt-icomoon-paste","ipt-icomoon-circle","ipt-icomoon-flag-checkered","ipt-icomoon-check-square","ipt-icomoon-podcast","ipt-icomoon-copy2","ipt-icomoon-copy3","ipt-icomoon-paste2","ipt-icomoon-paste3","ipt-icomoon-paste4","ipt-icomoon-storage","ipt-icomoon-enter","ipt-icomoon-exit","ipt-icomoon-loop","ipt-icomoon-loop2","ipt-icomoon-loop3","ipt-icomoon-copy4","ipt-icomoon-disk","ipt-icomoon-radio-checked","ipt-icomoon-radio-unchecked"],"User Action & Text Editor":["ipt-icomoon-th-large","ipt-icomoon-th","ipt-icomoon-rotate-right","ipt-icomoon-font","ipt-icomoon-bold","ipt-icomoon-italic","ipt-icomoon-text-height","ipt-icomoon-text-width","ipt-icomoon-align-left","ipt-icomoon-align-center","ipt-icomoon-align-right","ipt-icomoon-align-justify","ipt-icomoon-dedent","ipt-icomoon-indent","ipt-icomoon-adjust","ipt-icomoon-tint","ipt-icomoon-edit","ipt-icomoon-expand","ipt-icomoon-external-link","ipt-icomoon-chain","ipt-icomoon-paperclip","ipt-icomoon-strikethrough","ipt-icomoon-underline","ipt-icomoon-table","ipt-icomoon-columns","ipt-icomoon-unsorted","ipt-icomoon-sort-down","ipt-icomoon-sort-up","ipt-icomoon-rotate-left","ipt-icomoon-terminal","ipt-icomoon-crop","ipt-icomoon-unlink","ipt-icomoon-superscript","ipt-icomoon-subscript","ipt-icomoon-anchor","ipt-icomoon-external-link-square","ipt-icomoon-sort-alpha-asc","ipt-icomoon-sort-alpha-desc","ipt-icomoon-sort-amount-asc","ipt-icomoon-sort-amount-desc","ipt-icomoon-sort-numeric-asc","ipt-icomoon-sort-numeric-desc","ipt-icomoon-undo","ipt-icomoon-redo","ipt-icomoon-flip","ipt-icomoon-flip2","ipt-icomoon-undo2","ipt-icomoon-redo2","ipt-icomoon-quotes-left","ipt-icomoon-zoomin","ipt-icomoon-zoomout","ipt-icomoon-contract","ipt-icomoon-expand2","ipt-icomoon-contract2","ipt-icomoon-link","ipt-icomoon-crop2","ipt-icomoon-scissors","ipt-icomoon-font2","ipt-icomoon-text-height2","ipt-icomoon-text-width2","ipt-icomoon-bold2","ipt-icomoon-underline2","ipt-icomoon-italic2","ipt-icomoon-strikethrough2","ipt-icomoon-omega","ipt-icomoon-sigma","ipt-icomoon-table2","ipt-icomoon-pilcrow","ipt-icomoon-lefttoright","ipt-icomoon-righttoleft","ipt-icomoon-console","ipt-icomoon-expand3","ipt-icomoon-table3","ipt-icomoon-insert-template","ipt-icomoon-newtab","ipt-icomoon-indent-decrease","ipt-icomoon-indent-increase","ipt-icomoon-spell-check","ipt-icomoon-paragraph-justify","ipt-icomoon-paragraph-right","ipt-icomoon-paragraph-center","ipt-icomoon-paragraph-left","ipt-icomoon-paragraph-justify2","ipt-icomoon-paragraph-right2","ipt-icomoon-paragraph-center2","ipt-icomoon-paragraph-left2"],"Icon Spinners":["ipt-icomoon-spinner","ipt-icomoon-busy","ipt-icomoon-spinner2","ipt-icomoon-spinner3","ipt-icomoon-spinner4","ipt-icomoon-spinner5","ipt-icomoon-spinner6","ipt-icomoon-spinner7"],"Charts and Codes":["ipt-icomoon-qrcode","ipt-icomoon-barcode","ipt-icomoon-bar-chart-o","ipt-icomoon-bars","ipt-icomoon-puzzle-piece","ipt-icomoon-barcode2","ipt-icomoon-qrcode2","ipt-icomoon-pie","ipt-icomoon-stats","ipt-icomoon-bars2","ipt-icomoon-bars3"],"Attentive Icons":["ipt-icomoon-plus-circle","ipt-icomoon-minus-circle","ipt-icomoon-question-circle","ipt-icomoon-info-circle","ipt-icomoon-crosshairs","ipt-icomoon-ban","ipt-icomoon-plus","ipt-icomoon-minus","ipt-icomoon-asterisk","ipt-icomoon-exclamation-circle","ipt-icomoon-warning","ipt-icomoon-tasks","ipt-icomoon-google-plus-square","ipt-icomoon-google-plus","ipt-icomoon-plus-square","ipt-icomoon-question","ipt-icomoon-info","ipt-icomoon-exclamation","ipt-icomoon-bullseye","ipt-icomoon-minus-square","ipt-icomoon-minus-square-o","ipt-icomoon-plus-square-o","ipt-icomoon-eye-blocked","ipt-icomoon-warning2","ipt-icomoon-notification","ipt-icomoon-question2","ipt-icomoon-info2","ipt-icomoon-info3","ipt-icomoon-blocked","ipt-icomoon-cancel-circle","ipt-icomoon-spam","ipt-icomoon-close","ipt-icomoon-minus2","ipt-icomoon-plus2"],"Multimedia Icons":["ipt-icomoon-music","ipt-icomoon-film","ipt-icomoon-play-circle-o","ipt-icomoon-volume-off","ipt-icomoon-volume-down","ipt-icomoon-volume-up","ipt-icomoon-video-camera","ipt-icomoon-picture-o","ipt-icomoon-play","ipt-icomoon-pause","ipt-icomoon-forward","ipt-icomoon-fast-forward","ipt-icomoon-step-forward","ipt-icomoon-mail-forward","ipt-icomoon-play-circle","ipt-icomoon-youtube-play","ipt-icomoon-image","ipt-icomoon-play2","ipt-icomoon-forward2","ipt-icomoon-equalizer","ipt-icomoon-brightness-medium","ipt-icomoon-brightness-contrast","ipt-icomoon-contrast","ipt-icomoon-play3","ipt-icomoon-pause2","ipt-icomoon-stop2","ipt-icomoon-forward3","ipt-icomoon-play4","ipt-icomoon-pause3","ipt-icomoon-stop3","ipt-icomoon-forward4","ipt-icomoon-first","ipt-icomoon-last","ipt-icomoon-previous","ipt-icomoon-next","ipt-icomoon-volume-high","ipt-icomoon-volume-medium","ipt-icomoon-volume-low","ipt-icomoon-volume-mute","ipt-icomoon-volume-mute2","ipt-icomoon-volume-increase","ipt-icomoon-volume-decrease","ipt-icomoon-shuffle","ipt-icomoon-image2","ipt-icomoon-images","ipt-icomoon-film2","ipt-icomoon-music2"],"Location and Contact":["ipt-icomoon-envelope-o","ipt-icomoon-home","ipt-icomoon-flag","ipt-icomoon-map-marker","ipt-icomoon-phone","ipt-icomoon-phone-square","ipt-icomoon-envelope","ipt-icomoon-mobile-phone","ipt-icomoon-flag-o","ipt-icomoon-location-arrow","ipt-icomoon-microphone-slash","ipt-icomoon-home2","ipt-icomoon-home3","ipt-icomoon-home4","ipt-icomoon-phone2","ipt-icomoon-phone-hang-up","ipt-icomoon-envelop","ipt-icomoon-location","ipt-icomoon-location2","ipt-icomoon-map","ipt-icomoon-map2","ipt-icomoon-mobile","ipt-icomoon-target","ipt-icomoon-flag2","ipt-icomoon-mail","ipt-icomoon-mail2","ipt-icomoon-mobile2","ipt-icomoon-mail3","ipt-icomoon-mail4"],"Date and Time":["ipt-icomoon-clock-o","ipt-icomoon-calendar","ipt-icomoon-calendar-o","ipt-icomoon-history","ipt-icomoon-clock","ipt-icomoon-clock2","ipt-icomoon-alarm","ipt-icomoon-alarm2","ipt-icomoon-stopwatch","ipt-icomoon-calendar2","ipt-icomoon-calendar3"],"Device Icons":["ipt-icomoon-power-off","ipt-icomoon-signal","ipt-icomoon-headphones","ipt-icomoon-print","ipt-icomoon-camera","ipt-icomoon-camera-retro","ipt-icomoon-hdd-o","ipt-icomoon-desktop","ipt-icomoon-laptop","ipt-icomoon-tablet","ipt-icomoon-gamepad","ipt-icomoon-keyboard-o","ipt-icomoon-microphone","ipt-icomoon-headphones2","ipt-icomoon-camera2","ipt-icomoon-print2","ipt-icomoon-keyboard","ipt-icomoon-laptop2","ipt-icomoon-tv","ipt-icomoon-switch","ipt-icomoon-camera3","ipt-icomoon-screen","ipt-icomoon-tablet2"],"Tools Icons":["ipt-icomoon-compass","ipt-icomoon-gear","ipt-icomoon-lock","ipt-icomoon-pencil","ipt-icomoon-magnet","ipt-icomoon-key","ipt-icomoon-gears","ipt-icomoon-unlock","ipt-icomoon-bullhorn","ipt-icomoon-wrench","ipt-icomoon-filter","ipt-icomoon-magic","ipt-icomoon-dashboard","ipt-icomoon-folder-open-o","ipt-icomoon-eraser","ipt-icomoon-unlock-alt","ipt-icomoon-pencil-square","ipt-icomoon-compass2","ipt-icomoon-pencil2","ipt-icomoon-quill","ipt-icomoon-pen","ipt-icomoon-blog","ipt-icomoon-paint-format","ipt-icomoon-dice","ipt-icomoon-bullhorn2","ipt-icomoon-compass3","ipt-icomoon-key2","ipt-icomoon-key3","ipt-icomoon-lock2","ipt-icomoon-lock3","ipt-icomoon-unlocked","ipt-icomoon-wrench2","ipt-icomoon-cogs","ipt-icomoon-cog","ipt-icomoon-hammer","ipt-icomoon-wand","ipt-icomoon-meter2","ipt-icomoon-dashboard2","ipt-icomoon-hammer2","ipt-icomoon-magnet2","ipt-icomoon-powercord","ipt-icomoon-filter2","ipt-icomoon-filter3","ipt-icomoon-pencil3","ipt-icomoon-cog2","ipt-icomoon-meter"],"Social Networking":["ipt-icomoon-share-square-o","ipt-icomoon-twitter-square","ipt-icomoon-linkedin-square","ipt-icomoon-github-square","ipt-icomoon-twitter","ipt-icomoon-facebook","ipt-icomoon-github","ipt-icomoon-pinterest","ipt-icomoon-pinterest-square","ipt-icomoon-linkedin","ipt-icomoon-github-alt","ipt-icomoon-share-square","ipt-icomoon-youtube-square","ipt-icomoon-youtube","ipt-icomoon-stack-overflow","ipt-icomoon-instagram","ipt-icomoon-flickr","ipt-icomoon-tumblr","ipt-icomoon-tumblr-square","ipt-icomoon-gittip","ipt-icomoon-vimeo-square","ipt-icomoon-stack","ipt-icomoon-share","ipt-icomoon-googleplus","ipt-icomoon-googleplus2","ipt-icomoon-googleplus3","ipt-icomoon-google-drive","ipt-icomoon-facebook2","ipt-icomoon-facebook3","ipt-icomoon-instagram2","ipt-icomoon-twitter2","ipt-icomoon-twitter3","ipt-icomoon-youtube2","ipt-icomoon-vimeo","ipt-icomoon-vimeo2","ipt-icomoon-flickr2","ipt-icomoon-flickr3","ipt-icomoon-flickr4","ipt-icomoon-picassa","ipt-icomoon-forrst","ipt-icomoon-forrst2","ipt-icomoon-deviantart","ipt-icomoon-deviantart2","ipt-icomoon-steam","ipt-icomoon-github2","ipt-icomoon-github3","ipt-icomoon-github4","ipt-icomoon-github5","ipt-icomoon-blogger","ipt-icomoon-tumblr2","ipt-icomoon-tumblr3","ipt-icomoon-yahoo","ipt-icomoon-soundcloud","ipt-icomoon-soundcloud2","ipt-icomoon-reddit","ipt-icomoon-lastfm","ipt-icomoon-stumbleupon","ipt-icomoon-stackoverflow","ipt-icomoon-pinterest2","ipt-icomoon-yelp","ipt-icomoon-twitter4","ipt-icomoon-youtube3","ipt-icomoon-vimeo22","ipt-icomoon-flickr5","ipt-icomoon-facebook4","ipt-icomoon-googleplus4","ipt-icomoon-picassa2","ipt-icomoon-github6","ipt-icomoon-steam2","ipt-icomoon-blogger2","ipt-icomoon-linkedin2","ipt-icomoon-flattr","ipt-icomoon-pinterest3","ipt-icomoon-stumbleupon2","ipt-icomoon-delicious","ipt-icomoon-lastfm2"],"Brands Icons":["ipt-icomoon-exchange","ipt-icomoon-maxcdn","ipt-icomoon-html5","ipt-icomoon-css3","ipt-icomoon-xing","ipt-icomoon-xing-square","ipt-icomoon-dropbox","ipt-icomoon-adn","ipt-icomoon-bitbucket","ipt-icomoon-bitbucket-square","ipt-icomoon-apple","ipt-icomoon-windows","ipt-icomoon-android","ipt-icomoon-linux","ipt-icomoon-dribbble","ipt-icomoon-skype","ipt-icomoon-foursquare","ipt-icomoon-trello","ipt-icomoon-vk","ipt-icomoon-weibo","ipt-icomoon-renren","ipt-icomoon-pagelines","ipt-icomoon-stack-exchange","ipt-icomoon-lanyrd","ipt-icomoon-dribbble2","ipt-icomoon-dribbble3","ipt-icomoon-dribbble4","ipt-icomoon-wordpress","ipt-icomoon-joomla","ipt-icomoon-tux","ipt-icomoon-finder","ipt-icomoon-windows2","ipt-icomoon-xing2","ipt-icomoon-xing3","ipt-icomoon-foursquare2","ipt-icomoon-foursquare3","ipt-icomoon-paypal","ipt-icomoon-paypal2","ipt-icomoon-html52","ipt-icomoon-css32","ipt-icomoon-wordpress2","ipt-icomoon-apple2","ipt-icomoon-android2","ipt-icomoon-windows8","ipt-icomoon-skype2","ipt-icomoon-paypal3","ipt-icomoon-html53","ipt-icomoon-chrome","ipt-icomoon-firefox","ipt-icomoon-IE","ipt-icomoon-safari","ipt-icomoon-opera"],"Files & Documents":["ipt-icomoon-file-o","ipt-icomoon-folder","ipt-icomoon-folder-open","ipt-icomoon-certificate","ipt-icomoon-file-text-o","ipt-icomoon-folder-o","ipt-icomoon-file","ipt-icomoon-file-text","ipt-icomoon-file2","ipt-icomoon-file3","ipt-icomoon-folder2","ipt-icomoon-folder-open2","ipt-icomoon-drawer","ipt-icomoon-drawer2","ipt-icomoon-drawer3","ipt-icomoon-libreoffice","ipt-icomoon-file-pdf","ipt-icomoon-file-openoffice","ipt-icomoon-file-zip","ipt-icomoon-file-powerpoint","ipt-icomoon-file-xml","ipt-icomoon-file-css","ipt-icomoon-file4","ipt-icomoon-file5","ipt-icomoon-cabinet","ipt-icomoon-file-word","ipt-icomoon-file-excel"],"Food and Beverage":["ipt-icomoon-glass","ipt-icomoon-coffee","ipt-icomoon-cutlery","ipt-icomoon-beer","ipt-icomoon-glass2","ipt-icomoon-mug","ipt-icomoon-food"],"Travel and Living":["ipt-icomoon-road","ipt-icomoon-gift","ipt-icomoon-plane","ipt-icomoon-trophy","ipt-icomoon-briefcase","ipt-icomoon-truck","ipt-icomoon-fighter-jet","ipt-icomoon-ticket","ipt-icomoon-ticket2","ipt-icomoon-gift2","ipt-icomoon-trophy2","ipt-icomoon-rocket2","ipt-icomoon-briefcase2","ipt-icomoon-airplane","ipt-icomoon-truck2","ipt-icomoon-road2"],"Weather & Nature Icons":["ipt-icomoon-sunrise","ipt-icomoon-sun","ipt-icomoon-moon","ipt-icomoon-sun2","ipt-icomoon-windy","ipt-icomoon-wind","ipt-icomoon-snowflake","ipt-icomoon-cloudy","ipt-icomoon-cloud","ipt-icomoon-weather","ipt-icomoon-weather2","ipt-icomoon-weather3","ipt-icomoon-cloud2","ipt-icomoon-lightning","ipt-icomoon-lightning2","ipt-icomoon-rainy","ipt-icomoon-rainy2","ipt-icomoon-windy2","ipt-icomoon-windy3","ipt-icomoon-snowy","ipt-icomoon-snowy2","ipt-icomoon-snowy3","ipt-icomoon-weather4","ipt-icomoon-cloudy2","ipt-icomoon-cloud3","ipt-icomoon-lightning3","ipt-icomoon-sun3","ipt-icomoon-moon2","ipt-icomoon-cloudy3","ipt-icomoon-cloud4","ipt-icomoon-cloud5","ipt-icomoon-lightning4","ipt-icomoon-rainy3","ipt-icomoon-rainy4","ipt-icomoon-windy4","ipt-icomoon-windy5","ipt-icomoon-snowy4","ipt-icomoon-snowy5","ipt-icomoon-weather5","ipt-icomoon-cloudy4","ipt-icomoon-lightning5","ipt-icomoon-thermometer","ipt-icomoon-Celsius","ipt-icomoon-Fahrenheit","ipt-icomoon-leaf","ipt-icomoon-fire","ipt-icomoon-cloud6","ipt-icomoon-fire-extinguisher","ipt-icomoon-sun-o","ipt-icomoon-moon-o","ipt-icomoon-leaf2","ipt-icomoon-fire2","ipt-icomoon-lightning6","ipt-icomoon-cloud7"],"Like & Dislike Icons":["ipt-icomoon-heart","ipt-icomoon-star","ipt-icomoon-star-o","ipt-icomoon-eye","ipt-icomoon-eye-slash","ipt-icomoon-retweet","ipt-icomoon-thumbs-o-up","ipt-icomoon-thumbs-o-down","ipt-icomoon-star-half","ipt-icomoon-heart-o","ipt-icomoon-thumb-tack","ipt-icomoon-star-half-empty","ipt-icomoon-thumbs-up","ipt-icomoon-thumbs-down","ipt-icomoon-eye2","ipt-icomoon-eye3","ipt-icomoon-star2","ipt-icomoon-star3","ipt-icomoon-star4","ipt-icomoon-heart2","ipt-icomoon-heart3","ipt-icomoon-heart-broken","ipt-icomoon-thumbs-up2","ipt-icomoon-thumbs-up3"],"Emoticons":["ipt-icomoon-smile-o","ipt-icomoon-frown-o","ipt-icomoon-meh-o","ipt-icomoon-happy","ipt-icomoon-happy2","ipt-icomoon-smiley","ipt-icomoon-smiley2","ipt-icomoon-tongue","ipt-icomoon-tongue2","ipt-icomoon-sad","ipt-icomoon-sad2","ipt-icomoon-wink","ipt-icomoon-wink2","ipt-icomoon-grin","ipt-icomoon-grin2","ipt-icomoon-cool","ipt-icomoon-cool2","ipt-icomoon-angry","ipt-icomoon-angry2","ipt-icomoon-evil","ipt-icomoon-evil2","ipt-icomoon-shocked","ipt-icomoon-shocked2","ipt-icomoon-confused","ipt-icomoon-confused2","ipt-icomoon-neutral","ipt-icomoon-neutral2","ipt-icomoon-wondering","ipt-icomoon-wondering2"],"Directional Icons":["ipt-icomoon-arrow-circle-o-down","ipt-icomoon-arrow-circle-o-up","ipt-icomoon-arrows","ipt-icomoon-step-backward","ipt-icomoon-fast-backward","ipt-icomoon-backward","ipt-icomoon-eject","ipt-icomoon-chevron-left","ipt-icomoon-chevron-right","ipt-icomoon-arrow-left","ipt-icomoon-arrow-right","ipt-icomoon-arrow-up","ipt-icomoon-arrow-down","ipt-icomoon-chevron-up","ipt-icomoon-chevron-down","ipt-icomoon-arrows-v","ipt-icomoon-arrows-h","ipt-icomoon-hand-o-right","ipt-icomoon-hand-o-left","ipt-icomoon-hand-o-up","ipt-icomoon-hand-o-down","ipt-icomoon-arrow-circle-left","ipt-icomoon-arrow-circle-right","ipt-icomoon-arrow-circle-up","ipt-icomoon-arrow-circle-down","ipt-icomoon-arrows-alt","ipt-icomoon-caret-down","ipt-icomoon-caret-up","ipt-icomoon-caret-left","ipt-icomoon-caret-right","ipt-icomoon-angle-double-left","ipt-icomoon-angle-double-right","ipt-icomoon-angle-double-up","ipt-icomoon-angle-double-down","ipt-icomoon-angle-left","ipt-icomoon-angle-right","ipt-icomoon-angle-up","ipt-icomoon-angle-down","ipt-icomoon-chevron-circle-left","ipt-icomoon-chevron-circle-right","ipt-icomoon-chevron-circle-up","ipt-icomoon-chevron-circle-down","ipt-icomoon-level-up","ipt-icomoon-level-down","ipt-icomoon-toggle-down","ipt-icomoon-toggle-up","ipt-icomoon-toggle-right","ipt-icomoon-long-arrow-down","ipt-icomoon-long-arrow-up","ipt-icomoon-long-arrow-left","ipt-icomoon-long-arrow-right","ipt-icomoon-arrow-circle-o-right","ipt-icomoon-arrow-circle-o-left","ipt-icomoon-toggle-left","ipt-icomoon-point-up","ipt-icomoon-point-right","ipt-icomoon-point-down","ipt-icomoon-point-left","ipt-icomoon-backward2","ipt-icomoon-backward3","ipt-icomoon-eject2","ipt-icomoon-arrow-up-left","ipt-icomoon-arrow-up2","ipt-icomoon-arrow-up-right","ipt-icomoon-arrow-right2","ipt-icomoon-arrow-down-right","ipt-icomoon-arrow-down2","ipt-icomoon-arrow-down-left","ipt-icomoon-arrow-left2","ipt-icomoon-arrow-up-left2","ipt-icomoon-arrow-up3","ipt-icomoon-arrow-up-right2","ipt-icomoon-arrow-right3","ipt-icomoon-arrow-down-right2","ipt-icomoon-arrow-down3","ipt-icomoon-arrow-down-left2","ipt-icomoon-arrow-left3","ipt-icomoon-arrow-up-left3","ipt-icomoon-arrow-up4","ipt-icomoon-arrow-up-right3","ipt-icomoon-arrow-right4","ipt-icomoon-arrow-down-right3","ipt-icomoon-arrow-down4","ipt-icomoon-arrow-down-left3","ipt-icomoon-arrow-left4","ipt-icomoon-tab"],"Other Icons":["ipt-icomoon-none","ipt-icomoon-stop","ipt-icomoon-compress","ipt-icomoon-random","ipt-icomoon-lemon-o","ipt-icomoon-square-o","ipt-icomoon-bell","ipt-icomoon-flask","ipt-icomoon-square","ipt-icomoon-legal","ipt-icomoon-flash","ipt-icomoon-umbrella","ipt-icomoon-lightbulb-o","ipt-icomoon-suitcase","ipt-icomoon-bell-o","ipt-icomoon-circle-o","ipt-icomoon-shield","ipt-icomoon-rocket","ipt-icomoon-ellipsis-h","ipt-icomoon-ellipsis-v","ipt-icomoon-female","ipt-icomoon-male","ipt-icomoon-bug","ipt-icomoon-dot-circle-o","ipt-icomoon-droplet","ipt-icomoon-pacman","ipt-icomoon-spades","ipt-icomoon-clubs","ipt-icomoon-diamonds","ipt-icomoon-pawn","ipt-icomoon-bell2","ipt-icomoon-bug2","ipt-icomoon-lab","ipt-icomoon-shield2","ipt-icomoon-tree","ipt-icomoon-checkmark-circle","ipt-icomoon-google","ipt-icomoon-IcoMoon","ipt-icomoon-checkmark","ipt-icomoon-checkmark2"]},
			flattenedSource = [], flattenedSourceClass = [];
			for ( var key in source ) {
				for ( var i in source[key] ) {
					flattenedSource.push( source[key][i] );
				}
			}
			for ( var key in sourceClass ) {
				for ( var i in sourceClass[key] ) {
					flattenedSourceClass.push( sourceClass[key][i] );
				}
			}
			$(this).find( '.ipt_uif_icon_selector' ).each(function() {
				// Set the variables
				var elm = $(this),
				elmValue = elm.val(),
				iconPicker = null,
				iconToggler = elm.next('.ipt_uif_fip_button').find('button'),
				iconPickerDestroyed = true,
				iconPickerArgs = {
					searchSource: searchSource,
					useAttribute: true,
					attributeName: 'data-ipt-icomoon',
					theme: 'fip-ipt',
					appendTo: 'body',
					emptyIconValue: 'none'
				},
				initFIP = false;

				// Check what to print by
				if ( elm.data( 'iconBy' ) == 'hex' ) {
					iconPickerArgs.source = source;
					if ( $.inArray( parseInt(elmValue, 10), flattenedSource ) !== -1 || ( elmValue === '' || elmValue === 'none' ) ) {
						initFIP = true;
					}
				} else {
					iconPickerArgs.source = sourceClass;
					iconPickerArgs.useAttribute = false;
					if ( $.inArray( elmValue, flattenedSourceClass ) !== -1 || ( elmValue === '' || elmValue === 'none' ) ) {
						initFIP = true;
					}
				}

				// Check if no empty value
				if ( elm.data('noEmpty') ) {
					iconPickerArgs.emptyIconValue = '';
					iconPickerArgs.emptyIcon = false;
				}

				// Init if necessary
				if ( initFIP ) {
					iconPicker = elm.fontIconPicker( iconPickerArgs );
					iconPickerDestroyed = false;
					if ( iconToggler.length ) {
						iconToggler.attr( 'title', iptThemeUIFAdmin.L10n.fip.cancel ).html( '<span class="button-icon ipt-icomoon-close"></span>' );
					}
				} else {
					iconPickerDestroyed = true;
					if ( iconToggler.length ) {
						iconToggler.attr( 'title', iptThemeUIFAdmin.L10n.fip.picker ).html( '<span class="button-icon ipt-icomoon-IcoMoon"></span>' );
					}
				}

				// Attach the toggle
				if ( iconToggler.length ) {
					iconToggler.on( 'click', function(e) {
						e.preventDefault();

						// Restore
						if ( iconPickerDestroyed ) {
							if ( iconPicker == null ) {
								iconPicker = elm.fontIconPicker( iconPickerArgs );
							}
							iconPicker.refreshPicker();
							iconPickerDestroyed = false;
							iconToggler.attr( 'title', iptThemeUIFAdmin.L10n.fip.cancel ).html( '<span class="button-icon ipt-icomoon-close"></span>' );
						// Delete
						} else {
							if ( iconPicker != null ) {
								iconPicker.destroyPicker();
								iconPickerDestroyed = true;
								iconToggler.attr( 'title', iptThemeUIFAdmin.L10n.fip.picker ).html( '<span class="button-icon ipt-icomoon-IcoMoon"></span>' );
							}
						}
					} );
				}
			});
		},

		applyIRIS : function() {
			$(this).find('.ipt_uif_colorpicker').wpColorPicker();
			$(this).on('click', '.wp-picker-container a', function() {
				methods.reinitParentScroll.apply(this);
			});
		},

		applyTabs : function(op) {
			//Default tab functionality
			var tab_ops = {
				collapsible : $(this).data('collapsible') ? true : false,
				//Add Scrollbar to the activate tab when created
				create : function(event, ui) {
					ui.panel.find('> div > .ipt_uif_tabs_scroll').each(function() {
						methods.applyScrollBar.apply(this);
					});
				}
			};
			$(this).tabs(tab_ops);

			//Fix for vertical tabs
			if($(this).hasClass('vertical')) {
				$(this).addClass('ui-tabs-vertical ui-helper-clearfix');
				$(this).find('> ul > li').removeClass( "ui-corner-top" ).addClass( "ui-corner-left" );
			}

			//Add Scrollbar on change of tabs
			$(this).on("tabsactivate", function(event, ui) {
				//Init the new scrollbar
				ui.newPanel.find('> div > .ipt_uif_tabs_scroll').each(function() {
					methods.applyScrollBar.apply(this);
				});

				//Init scrollbar to the inner tab (if any)
				var inner_tabs = ui.newPanel.find('.ipt_uif_tabs');
				if(inner_tabs.length) {
					inner_tabs.each(function() {
						var active_inner_tab = $(this).find('> .ui-tabs-panel').eq($(this).tabs('option', 'active')).find('.ipt_uif_tabs_scroll');
						active_inner_tab.each(function() {
							methods.applyScrollBar.apply(this);
						});
					});
				}
			});
		},

		applyScrollBar : function() {
			$(this).mCustomScrollbar('destroy');
			$(this).mCustomScrollbar({
				scrollInertia : 300,
				autoDraggerLength : true,
				scrollButtons : {
					enable : true
				},
				advanced : {
					autoScrollOnFocus: false
				},
				theme : 'ipt-uif'
			});
		},

		SDAattachDel : function(vars) {
			$(this).on('click', '.ipt_uif_sda_del', function() {
				var $this = this;
				var dialog = $('<p>' + vars.confirmDel + '</p>');
				dialog.dialog({
					autoOpen : true,
					modal : true,
					minWidth : 400,
					closeOnEscape : true,
					title : vars.confirmTitle,
					buttons : {
						'Confirm' : function() {
							methods.SDAdel.apply($this);
							$(this).dialog('close');
						},
						'Cancel' : function() {
							$(this).dialog('close');
						}
					},
					//appendTo : '.ipt_uif_common',
					create : function(event, ui) {
						$('body').addClass('ipt_uif_common');
					},
					close : function(event, ui) {
						$('body').removeClass('ipt_uif_common');
					}
				});
			});
		},

		SDAsort : function() {
			$(this).find('> .ipt_uif_sda_body').sortable({
				items : 'div.ipt_uif_sda_elem',
				placeholder : 'ipt_uif_sda_highlight',
				handle : 'div.ipt_uif_sda_drag',
				distance : 5,
				axis : 'y',
				helper : 'original'
			});
		},

		SDAdel : function() {
			var target = $(this).parent();
			var parent = $(this).parent().parent();
			target.slideUp('normal');
			target.css('background-color', '#ffaaaa').animate({'background-color' : '#ffffff'}, 'normal', function() {
				target.stop().remove();
				methods.reinitParentScroll.apply(parent);
			});
		},

		SDAadd : function($submit) {
			var vars = $(this).data('iptSortableData');

			var $add_string = $(this).find('> .ipt_uif_sda_data').text();
			$add_string = $('<div></div>').html($add_string).text();

			//alert($add_string);
			var count = vars.count++;
			var re = new RegExp(methods.quote(vars.key), 'g');

			$add_string = $add_string.replace(re, count);
			//alert($add_string);

			var new_div = $('<div class="ipt_uif_sda_elem" />').append($($add_string));

			$(this).find('> .ipt_uif_sda_body').append(new_div);

			//Apply the framework again
			$(new_div).iptPluginUIFAdmin();

			var old_color = new_div.css('background-color');

			new_div.hide().slideDown('fast').css('background-color', '#aaffaa').animate({'background-color' : old_color}, 'normal', function() {
				//Reinit the scrollbar
				methods.reinitParentScroll.apply(this);
			});
			$submit.data('count', vars.count);
			$submit.attr('data-count', vars.count);
		},

		builderTabs : function(container) {
			var self = this;
			var tab = $(this).tabs();
			tab.find('.ui-tabs-nav').sortable({
				placeholder : 'ipt_uif_builder_tabs_sortable_highlight',
				stop : function() {
					methods.builderTabRefresh.apply(self);
				},
				handle : '.ipt_uif_builder_tab_sort',
				tolerance : 'pointer',
				containment : 'parent',
				distance : 5
			});

			//Make existing drop_here 's droppable
			tab.find('.ui-tabs-panel').each(function() {
				methods.builderDroppables.apply(this, [container]);
			});

			//Make the tab li 's droppable
			tab.find('.ui-tabs-nav li').each(function() {
				methods.builderTabDroppable.apply(this, [container]);
			})

			//Store the tab counter
			var tab_counter = $(this).find('.ui-tabs-nav > li').length;
			$(this).data('ipt_uif_builder_tab_counter', tab_counter);

			//Add empty class if necessary
			if(tab_counter == 0) {
				$(this).addClass('ipt_uif_builder_empty');
			}
		},

		builderAddTab : function(tab, container) {
			$(this).on('click', function(e) {
				e.preventDefault();
				//alert(container);
				var key = $(container).data('ipt_uif_builder_replace')['l_key'];
				var tab_content = $(container).find('.ipt_uif_builder_tab_content').text();

				tab_content = $('<div></div>').html(tab_content).text();

				var tab_counter = $(tab).data('ipt_uif_builder_tab_counter');
				var re = new RegExp(methods.quote(key), 'g');
				tab_content = tab_content.replace(re, tab_counter);

				var id = $(tab).attr('id') + '_' + tab_counter,
				li = $(container).find('.ipt_uif_builder_tab_li').text();
				li = $('<div></div>').html(li).text();
				li = $(li);

				li.find('.tab_position').val(tab_counter);
				li.find('a').attr('href', '#' + id);
				$(tab).find('.ui-tabs-nav').append(li);

				var new_tab = $('<div id="' + id + '">' + tab_content + '</div>');
				$(tab).append(new_tab);
				tab_counter++;

				new_tab.iptPluginUIFAdmin();
				methods.builderTabDroppable.apply(li, [container]);

				$(tab).data('ipt_uif_builder_tab_counter', tab_counter);

				$(tab).removeClass('ipt_uif_builder_empty');

				methods.builderTabRefresh.apply(tab);
				methods.builderDroppables.apply(new_tab, [container]);

				//Open the last tab
				$(tab).tabs('option', 'active', $(tab).find('> .ui-tabs-nav > li').length - 1);

			});
		},

		builderTabDroppable : function(container) {
			$(this).find('.ipt_uif_builder_tab_droppable').droppable({
				greedy : true,
				accept : '.ipt_uif_droppable_element',
				tolerance : 'pointer',
				activate : function(event, ui) {
					$(this).addClass('ipt_uif_builder_tab_droppable_highlight');
				},
				deactivate : function(event, ui) {
					$(this).removeClass('ipt_uif_builder_tab_droppable_highlight');
				},
				over : function(event, ui) {
					$(this).addClass('ipt_uif_builder_tab_droppable_over');
				},
				out : function(event, ui) {
					$(this).removeClass('ipt_uif_builder_tab_droppable_over');
				},
				drop : function(event, ui) {
					var new_droppable = $('#' + $(this).parent().parent().attr('aria-controls')).find('> .ipt_uif_builder_drop_here').get(0);
					var self = this;
					var tab = $(self).parent().parent().parent().parent();
					var move_to = $(self).parent().parent().parent().find('> li').index($(self).parent().parent());
					tab.tabs('option', 'active', move_to);
					var callback = function() {
						$(self).removeClass('ipt_uif_builder_tab_droppable_highlight');
						$(self).removeClass('ipt_uif_builder_tab_droppable_over');
					};
					methods.builderHandleDrop.apply(new_droppable, [event, ui, container, callback]);
				}
			});
		},

		builderTabRefresh : function() {
			$(this).tabs('refresh');

			$(this).find('.ui-tabs-nav').sortable('refresh');
			$(this).find('.ui-tabs-nav').sortable('refreshPositions');

			var tab_counter = $(this).find('.ui-tabs-nav > li').length;
			//Add empty class if necessary
			if(tab_counter == 0) {
				$(this).addClass('ipt_uif_builder_empty');
			}
		},

		builderToolbar : function(tab, container, add) {
			$(this).find('.ipt_uif_builder_layout_settings').on('click', function() {
				var active_tab = $(tab).tabs('option', 'active');
				var panelID = $(tab).find('.ui-tabs-nav li').eq(active_tab).attr('aria-controls');
				var settings_box = $(container).data('ipt_uif_builder_settings').get(0);
				var origin = $('#' + panelID).find('.ipt_uif_builder_tab_settings').get(0);
				//console.log(origin);
				methods.builderSettingsOpen.apply(this, [settings_box, container, origin]);
			});

			$(this).find('.ipt_uif_builder_layout_copy').on('click', function() {
				// Get the original tab in question (this to copy)
				var original_active_tab = $(tab).tabs('option', 'active'),
				originalPanelID = $(tab).find('.ui-tabs-nav li').eq(original_active_tab).attr('aria-controls');

				// First close the settings box
				var settings_box = $(container).data('ipt_uif_builder_settings').get(0);
				methods.builderSettingsClose.apply(this, [settings_box, container]);

				// Add a new tab
				$(add).trigger('click');

				// Get the last added tab
				var active_tab = $(tab).tabs('option', 'active'),
				panelID = $(tab).find('.ui-tabs-nav li').eq(active_tab).attr('aria-controls');

				var originalPanel = $('#' + originalPanelID),
				newPanel = $('#' + panelID),
				cloneDroppable = originalPanel.find('>.ipt_uif_builder_drop_here').clone();

				// Remove the existing droppable area but store the key first
				var existingLayout = newPanel.find('>.ipt_uif_builder_drop_here'),
				existingKey = existingLayout.data('containerKey');
				existingLayout.remove();

				// Add the new one
				cloneDroppable.appendTo( newPanel );

				// Now modify existing elements
				methods.builderDuplicateReplaceInnerKeys.apply( cloneDroppable, [container, existingKey] );

				// Finally refresh it
				methods.builderTabRefresh.apply(tab);
				methods.builderDroppables.apply(newPanel, [container]);
				newPanel.iptPluginUIFAdmin();
			});

			$(this).find('.ipt_uif_builder_layout_del').on('click', function() {
				var title = $(this).data('title');
				var dialog_content = $('<div><div style="padding: 10px;"><p>'  + $(this).data('msg') + '</p></div></div>');
				dialog_content.dialog({
					autoOpen: true,
					buttons: {
						"Confirm": function() {
							var active_tab = $(tab).tabs('option', 'active');
							//Remove the li
							var panelID = $(tab).find('.ui-tabs-nav li').eq(active_tab).remove().attr('aria-controls');
							//Remove the Panel
							$(tab).find('#' + panelID).remove();

							methods.builderTabRefresh.apply(tab);
							$(this).dialog("close");
						},
						'Cancel' : function() {
							$(this).dialog("close");
						}
					},
					modal: true,
					minWidth: 600,
					closeOnEscape: true,
					title: title,
					//appendTo : '.ipt_uif_common',
					create : function(event, ui) {
						$('body').addClass('ipt_uif_common');
					},
					close : function(event, ui) {
						$('body').removeClass('ipt_uif_common');
					}
				});
			});
		},

		builderSettingsOpen : function(settings_box, container, origin) {
			methods.builderSettingsClose.apply(this, [settings_box, container]);
			container = $(container);
			origin = $(origin);

			//Double Click? Then Toggle
			if(!origin.length) {
				return;
			}

			//Store the parent
			var parent = origin.parent();
			$(settings_box).data('ipt_uif_builder_settings_parent', parent);

			//Append the origin settings
			$(settings_box).find('.ipt_uif_builder_settings_box_container').prepend(origin);



			//Check wp_editor
			var wp_editor_textarea = $(settings_box).find('textarea.wp_editor').eq(0);
			if(wp_editor_textarea.length) {
				var wp_editor_container = container.find('.ipt_uif_builder_wp_editor');
				var tmce_textarea = wp_editor_container.find('textarea').eq(0);

				// Init the tinyMCE API
				var editor = tinyMCE.get(tmce_textarea.attr('id'));

				// Get the original content
				var content = wp_editor_textarea.val();

				// Show it
				wp_editor_container.css({position : 'static', 'left' : 'auto'});
				console.log(tmce_textarea);

				// Set the content
				// Check to see which one is active right now
				// Visual or Text
				if ( editor && editor instanceof tinymce.Editor &&  ! tmce_textarea.is(':visible') ) {
					editor.setContent(switchEditors.wpautop(content));
					editor.save({ no_events: true });
				} else {
					tmce_textarea.val(switchEditors.pre_wpautop(content));
				}
			}

			// See if origin parent is a droppable
			if(parent.hasClass('ipt_uif_droppable_element')) {
				parent.find('> .ipt_uif_droppable_element_wrap').addClass('white');
			}

			// Store the origin
			$(settings_box).data('ipt_uif_builder_settings_origin', origin);

			// Show it
			$(settings_box).parent().stop(true, true).css({height : 'auto'}).hide().slideDown('fast', function() {
				//Apply the scrollbar
				methods.applyScrollBar.apply(settings_box);

				//Init the scroll position
				var scroll_position = $(settings_box).parent().offset().top;

				// Scroll the body
				if($('#wpadminbar').length) {
					scroll_position -= ($('#wpadminbar').outerHeight() + 10);
				}
				$('html, body').animate({scrollTop : scroll_position});
			});

			//$(settings_box).next().show();
		},

		builderSettingsClose : function(settings_box, container) {
			//Destroy the scroll bar
			methods.destroyScrollBar.apply(settings_box);

			//Get origin and parent
			var origin = $(settings_box).data('ipt_uif_builder_settings_origin');
			var parent = $(settings_box).data('ipt_uif_builder_settings_parent');

			//Check for double click on a single button
			if(origin === undefined || parent === undefined) {
				return;
			}

			//Init the container
			container = $(container);

			//Check wp_editor
			var wp_editor_textarea = $(settings_box).find('textarea.wp_editor').eq(0);
			if(wp_editor_textarea.length) {
				//Get the tmce textarea
				var tmce_textareaID = container.find('.ipt_uif_builder_wp_editor textarea').eq(0).attr('id');
				var wp_editor_container = container.find('.ipt_uif_builder_wp_editor');
				var tmce_textarea = wp_editor_container.find('textarea').eq(0);
				var content;
				var editor = tinyMCE.get(tmce_textareaID);

				//Get the content
				if( editor && editor instanceof tinymce.Editor && ! tmce_textarea.is(':visible') ) {
					content = switchEditors.pre_wpautop(editor.getContent());
				} else {
					content = switchEditors.pre_wpautop( $('#' + tmce_textareaID).val() );
				}

				//Update it
				wp_editor_textarea.val(content);

				//Hide the wp_editor
				container.find('.ipt_uif_builder_wp_editor').css({position : 'absolute', 'left' : -9999});
			}

			//See if origin parent is a droppable
			if(parent.hasClass('ipt_uif_droppable_element')) {
				parent.find('> .ipt_uif_droppable_element_wrap').removeClass('white');
			}

			// Check the grayed out class based on conditional logic
			var element_m_type = parent.find('input.ipt_uif_builder_helper.element_m_type').val(),
			element_key = parent.find('input.ipt_uif_builder_helper.element_key').val();
			if ( $('#' + element_m_type + '_' + element_key + '_conditional_active').is(':checked') && !$('#' + element_m_type + '_' + element_key + '_conditional_status').is(':checked') ) {
				parent.find('> .ipt_uif_droppable_element_wrap').addClass('grayed');
			} else {
				parent.find('> .ipt_uif_droppable_element_wrap').removeClass('grayed');
			}

			//Restore
			parent.append(origin);

			// Change the subtitle of the parent
			if ( parent.hasClass('ipt_uif_droppable_element_added') ) {
				var possible_title_id = parent.find('.element_m_type').val() + '_' + parent.find('.element_key').val() + '_title',
				possible_title = $('#' + possible_title_id).val();
				possible_title = methods.stripTags( possible_title );

				if ( possible_title && typeof( possible_title ) == 'string' ) {
					parent.find('span.element_title').text( ' : ' + possible_title.trim() );
					parent.find('h3.element_title_h3').attr( 'title', possible_title.trim() );
				}
			}


			//Hide it
			$(settings_box).data('ipt_uif_builder_settings_origin', undefined);
			$(settings_box).data('ipt_uif_builder_settings_parent', undefined);
			$(settings_box).parent().stop(true).slideUp('fast');
			//$(settings_box).next().hide();
		},

		builderSettingsSaveInit : function(settings_box, container) {
			$(this).on('click', function() {
				methods.builderSettingsClose.apply(this, [settings_box, container]);
			});
		},

		builderElementSettingsEvent : function() {
			var container = this;
			//Delegate the settings
			$(container).on('click', '.ipt_uif_builder_settings_handle', function(e) {
				e.preventDefault();
				var origin = $(this).parent().parent().find('> .ipt_uif_builder_settings').get(0),
				settings_box = $(container).data('ipt_uif_builder_settings').get(0);
				methods.builderSettingsOpen.apply(this, [settings_box, container, origin]);
			});

			$(container).on('click', '.ipt_uif_builder_droppable_handle', function(e) {
				e.preventDefault();
				if($(this).hasClass('ipt_uif_builder_droppable_handle_open')) {
					$(this).removeClass('ipt_uif_builder_droppable_handle_open');
					$(this).siblings('.ipt_uif_builder_drop_here').slideUp('normal');
				} else {
					$(this).addClass('ipt_uif_builder_droppable_handle_open');
					$(this).siblings('.ipt_uif_builder_drop_here').slideDown('normal');
				}
			});
		},

		builderDeleter : function(settings_box, container) {
			var self = $(this).css('visibility', 'hidden');
			$(this).droppable({
				greedy : true,
				tolerance : 'pointer',
				accept : '.ipt_uif_builder_drop_here .ipt_uif_droppable_element',
				activate : function(event, ui) {
					self.stop(true, true).css('visibility', 'visible');
					self.find('.ipt_uif_builder_deleter_wrap').stop(true, true).css({height : 0, opacity : 0}).animate({height : 45, opacity : 1}, 'fast');
				},
				deactivate : function(event, ui) {
					self.find('.ipt_uif_builder_deleter_wrap').stop(true, false).animate({height : 0, opacity : 0}, 'fast', function() {
						self.stop(true, true).css('visibility', 'hidden');
					});
				},
				over : function(event, ui) {
					ui.helper.find('.ipt_uif_droppable_element_wrap').addClass('red');
				},
				out : function(event, ui) {
					ui.helper.find('.ipt_uif_droppable_element_wrap').removeClass('red');
				},
				drop : function(event, ui) {
					var drop_here = ui.draggable.parent();

					//First check for dbmap
					var item = ui.draggable;
					if(item.data('dbmap') == true) {
						//Restore
						var original = item.data('ipt_uif_builder_dbmap_original');
						original.removeClass('ipt_uif_droppable_element_disabled');
					}
					ui.draggable.remove();

					methods.builderSettingsClose.apply(this, [settings_box, container]);

					if(drop_here.find('.ipt_uif_droppable_element:not(.ui-sortable-placeholder):not(.ui-sortable-helper)').length < 1) {
						drop_here.addClass('ipt_uif_builder_drop_here_empty');
					}
					self.find('.ipt_uif_builder_deleter_wrap').stop(true, false).animate({height : 0, opacity : 0}, 'fast', function() {
						self.stop(true, true).css('visibility', 'hidden');
					});
				}
			});
		},

		builderDuplicate: function(container, settings_box) {
			// Close the settings box
			methods.builderSettingsClose.apply(this, [settings_box, container]);

			// Get the mother element
			var elementToCopy = $(this).closest('.ipt_uif_droppable_element'),
			// Clone it
			duplicateDOM = elementToCopy.clone(),
			// Init the new key
			key = 0,
			// Init the inner droppable element
			innerDroppableDOM = null;

			// Do not do anything if it is a dbmap
			if ( elementToCopy.data('dbmap') ) {
				return;
			}

			// Patch the textarea
			var originalTextAreas = elementToCopy.find('textarea'),
			duplicateTextAreas = duplicateDOM.find('textarea');
			if ( originalTextAreas.length ) {
				for ( var t = 0; t < originalTextAreas.length; t++ ) {
					$(duplicateTextAreas[t]).val( $(originalTextAreas[t]).val() );
				}
			}

			// Update the DOM id, name and for attributes
			key = methods.builderDuplicateModifyElements( elementToCopy, duplicateDOM, container );

			// Hide it
			duplicateDOM.hide();

			// Append it
			elementToCopy.after( duplicateDOM );

			//Check for other droppables
			innerDroppableDOM = duplicateDOM.find('> .ipt_uif_droppable_element_wrap > .ipt_uif_builder_drop_here');
			if(innerDroppableDOM.length) {
				innerDroppableDOM.each(function() {
					methods.builderDuplicateReplaceInnerKeys.apply( this, [container, key] );
				});
			}

			//Add any new Framework item
			duplicateDOM.iptPluginUIFAdmin();

			//Show it
			duplicateDOM.slideDown('fast');
		},

		builderDuplicateModifyElements: function( originalDOM, duplicateDOM, container ) {
			var element_m_type = originalDOM.find('> input.ipt_uif_builder_helper.element_m_type').val(),
			// Get type
			element_type = originalDOM.find('> input.ipt_uif_builder_helper.element_type').val(),
			// Get key
			element_key = parseInt( originalDOM.find('> input.ipt_uif_builder_helper.element_key').val(), 10 ),
			// Prepare the name to replace
			name_replace = element_m_type + '\\[' + element_key + '\\]',
			// Prepare the id to replace
			id_replace = element_m_type + '_' + element_key + '_',
			//Get the data variables
			keys = $(container).data('ipt_uif_builder_keys'),
			// Init the new key
			key = 0;

			// Set the new key
			if(undefined !== keys[element_m_type]) {
				key = keys[element_m_type];
				keys[element_m_type]++;
			} else {
				keys[element_m_type] = key;
			}

			//Update the keys
			$(container).data('ipt_uif_builder_keys', keys);

			// Update the DOM id, name and for attributes
			duplicateDOM.find('>.ipt_uif_builder_settings').find('input, textarea, select, button, datalist, keygen, output, label').each(function() {
				var form_elem = $(this),
				name = form_elem.attr('name'),
				id = form_elem.attr('id'),
				label_for = form_elem.attr('for');
				if ( name ) {
					form_elem.attr('name', name.replace(new RegExp(name_replace, 'g'), element_m_type + '[' + key + ']'));
				}
				if ( id ) {
					form_elem.attr('id', id.replace(new RegExp(id_replace, 'g'), element_m_type + '_' + key + '_'));
				}
				if ( label_for ) {
					form_elem.attr('for', label_for.replace(new RegExp(id_replace, 'g'), element_m_type + '_' + key + '_'));
				}
			});

			// Update SDA data, if any
			duplicateDOM.find('script.ipt_uif_sda_data').each(function() {
				var originalSDAData = $(this).html(),
				modifiedSDAData = originalSDAData.replace( new RegExp(name_replace, 'g'), element_m_type + '[' + key + ']' ).replace( new RegExp(id_replace, 'g'), element_m_type + '_' + key + '_' );
				$(this).html(modifiedSDAData);
			});

			// Reset fontIconPicker (if any)
			duplicateDOM.find('.icons-selector').remove();

			// Set the new Key
			duplicateDOM.find('>input.ipt_uif_builder_helper.element_key').val(key);

			// Set the element info (M){K}
			var duplicateElementInfo = duplicateDOM.find('> .ipt_uif_droppable_element_wrap > h3 > .element_info');
			duplicateElementInfo.text( duplicateElementInfo.text().replace(element_key, key) );

			return key;
		},

		builderDuplicateReplaceInnerKeys: function(container, new_key) {
			// Update the key first
			$(this).data('containerKey', new_key);
			// First get the keys of this droppable container and stuff
			var droppable_key = new_key,
			droppable_m_type = $(this).data('replaceby'),
			new_helper_name = droppable_m_type + '[' + droppable_key + '][elements]';

			// Recursively check all ipt_uif_droppable_element
			$(this).find('>.ipt_uif_droppable_element').each( function() {
				var self = $(this),
				key = 0;

				// Update the DOM id, name and for attributes
				key = methods.builderDuplicateModifyElements( self, self, container );

				// Update new layout
				self.find('> input.ipt_uif_builder_helper.element_m_type').attr('name', new_helper_name + '[m_type][]' );
				self.find('> input.ipt_uif_builder_helper.element_type').attr('name', new_helper_name + '[type][]' );
				self.find('> input.ipt_uif_builder_helper.element_key').attr('name', new_helper_name + '[key][]' );

				// Now check if it again contains any inner droppable element
				var innerDroppableDOM = self.find('> .ipt_uif_droppable_element_wrap > .ipt_uif_builder_drop_here');
				if(innerDroppableDOM.length) {
					innerDroppableDOM.each(function() {
						methods.builderDuplicateReplaceInnerKeys.apply( this, [container, key] );
					});
				}
			} );
		},

		builderDraggables : function(tab, container) {
			//Make 'em droppable (err, sorry draggable to the droppables)
			var droppables = $(this).find('.ipt_uif_droppable_element');
			droppables.draggable({
				revert : 'invalid',
				revertDuration : 200,
				helper : 'clone',
				zIndex : 9999,
				appendTo : $(this),
				cancel : '.ipt_uif_droppable_element_disabled',
				handle : '.ipt_uif_builder_sort_handle',
				cursorAt : {left : 19, top : 17},
				delay : 100
			});

			// Emulate the same event when something is clicked
			$(this).on( 'click', '.ipt_uif_droppable_element', function(event) {
				if ( $(this).hasClass('ipt_uif_droppable_element_disabled') ) {
					return;
				}
				var helper = $(this).clone(),
				ui = $(this),
				// Get the active tab droppable
				activeTab = $(tab).tabs( 'option', 'active' ),
				activeTabAria = $(tab).find('>ul>li.ipt_uif_builder_layout_tabs').eq(activeTab).attr('aria-controls'),
				activeTabAriaDOM = $('#' + activeTabAria).find('> .ipt_uif_builder_drop_here');

				methods.builderHandleDrop.apply(activeTabAriaDOM.get(0), [null, {
					draggable: ui,
					helper: helper
				}, container]);
			} );

			//Bind the parent click function -> On click show elements under that category
			$(this).find('.ipt_uif_droppable_elements_parent').each(function() {
				$(this).on('click', function() {
					$(this).parent().find('.ipt_uif_droppable_elements_parent').hide();
					$(this).next('.ipt_uif_droppable_elements_wrap').fadeIn('fast');
				});
			});

			//Bind the child go back button function
			$(this).find('.ipt_uif_droppable_back').each(function() {
				$(this).on('click', function(e) {
					e.preventDefault();
					var self = $(this);
					self.parent().fadeOut('fast', function() {
						self.parent().parent().find('.ipt_uif_droppable_elements_parent').show();
					});
				});
			});
		},

		builderDroppables : function(container) {
			$(this).find('.ipt_uif_builder_drop_here').droppable({
				greedy : true,
				accept : '.ipt_uif_droppable_element',
				tolerance : 'pointer',
				activate : function(event, ui) {
					$(this).addClass('ipt_uif_highlight');
				},
				deactivate : function(event, ui) {
					$(this).removeClass('ipt_uif_highlight');
				},
				over : function(event, ui) {
					$(this).addClass('ipt_uif_droppable_hover');
					ui.helper.find('.ipt_uif_droppable_element_wrap').addClass('white');
				},
				out : function(event, ui) {
					$(this).removeClass('ipt_uif_droppable_hover');
					ui.helper.find('.ipt_uif_droppable_element_wrap').removeClass('white');
				},
				drop : function(event, ui) {
					methods.builderHandleDrop.apply(this, [event, ui, container]);
					return;
				}
			}).sortable({
				//accept : '.ipt_uif_droppable .ipt_uif_droppable_elements_wrap .ipt_uif_droppable_element',
				items : '> .ipt_uif_droppable_element',
				handle : '> div > a.ipt_uif_builder_sort_handle',
				helper : function(event, item) {
					var c = item.attr('class');
					var insider = item.find('> .ipt_uif_droppable_element_wrap');
					var helper = $('<div class="' + c + '"><div class="' + insider.attr('class') + '"></div></div>');
					helper.addClass('ui-sortable-helper');
					insider.find('> a.ipt_uif_builder_action_handle').each(function() {
						helper.find('> .ipt_uif_droppable_element_wrap').append($(this).clone());
					});
					helper.find('> .ipt_uif_droppable_element_wrap').append(insider.find('> h3').clone()).append('<div class="clear"></div>');
					return helper.appendTo($(this));
				},
				cancel : '.ipt_uif_droppable_element_cancel_sort',
				cursorAt : {left : 19, top : 17},
				stop : function(event, ui) {
					if(ui.item.hasClass('ipt_uif_droppable_element_move')) {
						var self = $(this);
						var append_to = ui.item.data('ipt_uif_droppable_move');
						ui.item.removeClass('ipt_uif_droppable_element_move');
						var parent = ui.item.parent();
						ui.item.slideUp('fast', function() {
							ui.item.appendTo(append_to).slideDown('fast', function() {
								if(parent.find('.ipt_uif_droppable_element:not(.ui-sortable-placeholder):not(.ui-sortable-helper)').length < 1) {
									parent.addClass('ipt_uif_builder_drop_here_empty');
								}
								append_to.sortable('refresh');
								self.sortable('refresh');
							});
						});
					}
				}
			});

			$(this).find('.ipt_uif_droppable_element').each(function() {
				//change the state of dbmap
				if($(this).data('dbmap') == true) {
					//get the original container from draggable
					var identify_class = $(this).attr('class');
					var original = $(container).find('.ipt_uif_droppable .ipt_uif_droppable_element').filter('[class="' + identify_class + '"]').addClass('ipt_uif_droppable_element_disabled');
					$(this).data('ipt_uif_builder_dbmap_original', original);
				}

				//Add the added class
				$(this).addClass('ipt_uif_droppable_element_added');
			});
		},

		builderHandleDrop : function(event, ui, container, callback) {
			ui.helper.find('.ipt_uif_droppable_element_wrap').removeClass('white');
			$(this).removeClass('ipt_uif_highlight');
			$(this).removeClass('ipt_uif_droppable_hover');
			//Two conditions
			//First the item is being dragged from .ipt_uif_droppable_elements_wrap
			//The item is being dragged within
			var item;
			var layout_key = $(this).data('containerKey');

			if(ui.draggable.hasClass('ipt_uif_droppable_element_added')) {
				item = ui.draggable;
				//Reset the names
				var new_name = $(this).data('replaceby') + '[' + $(this).data('containerKey') + '][elements]';
				item.find('> input.element_m_type').attr('name', new_name + '[m_type][]');
				item.find('> input.element_type').attr('name', new_name + '[type][]');
				item.find('> input.element_key').attr('name', new_name + '[key][]');

				//Append it
				if($(this).is(item.parent())) {
					//Do nothing
				} else {
					//Tell the bloody sortable to append it when it is done
					var append_to = $(this);
					item.data('ipt_uif_droppable_move', append_to);
					item.addClass('ipt_uif_droppable_element_move');
				}

				//That's it I guess
			} else {
				item = ui.draggable.clone();

				//Remove the template script
				var template_script = item.find('> .ipt_uif_builder_settings');
				var new_settings = $('<div class="ipt_uif_builder_settings"></div>');
				var decoded = new_settings.html(template_script.text()).text();
				new_settings.html(decoded);
				template_script.remove();
				item.find('.ipt_uif_droppable_element_wrap').before(new_settings);

				//Get the data variables
				var keys = $(container).data('ipt_uif_builder_keys');
				var replaces = $(container).data('ipt_uif_builder_replace');

				var prefix_to_replace = ui.draggable.data('replacethis');
				var prefix_replace_by = $(this).data('replaceby');

				var key = 0;
				var type = item.find('.element_m_type').val();
				if(undefined !== keys[type]) {
					key = keys[type];
					keys[type]++;
				} else {
					keys[type] = key;
				}
				var rk = new RegExp(methods.quote(replaces.key), 'g');
				var rl = new RegExp(methods.quote(replaces.l_key), 'g');
				var rprefix = new RegExp(methods.quote(prefix_to_replace), 'g');

				//Set the proper HTML name of the hidden element
				item.html(function(i, oldHTML) {
					var newHTML = oldHTML.replace(rk, key);
					newHTML = newHTML.replace(rprefix, prefix_replace_by);
					return newHTML.replace(rl, layout_key);
				});

				//Make the disabled="disabled" disappear
				item.find('> input.element_m_type').attr('disabled', false);
				item.find('> input.element_type').attr('disabled', false);
				item.find('> input.element_key').attr('disabled', false);

				//Now check for dbmap
				if(item.data('dbmap') == true) {
					ui.draggable.addClass('ipt_uif_droppable_element_disabled');
					item.data('ipt_uif_builder_dbmap_original', ui.draggable);
				}

				//Apply the added class
				item.addClass('ipt_uif_droppable_element_added');
				item.hide();

				//Append
				$(this).append(item);

				//Add any new Framework item
				item.iptPluginUIFAdmin();

				//Check for droppables
				if(item.find('.ipt_uif_builder_drop_here').length) {
					methods.builderDroppables.apply(item.get(0), [container]);
				}

				//Apply the Settings Event - not necessary since delegated
				//methods.builderElementSettingsEvent.apply(item.get(0), [container]);

				//Show it
				item.slideDown('fast');

				//Update the keys
				$(container).data('ipt_uif_builder_keys', keys);
			}

			$(this).removeClass('ipt_uif_builder_drop_here_empty');

			if(typeof(callback) == 'function') {
				callback();
			}
		},

		quote : function(str) {
			return str.replace(/([.?*+^$[\]\\(){}|-])/g, "\\$1");
		},

		stripTags: function( string ) {
			var tempDOM = $('<div />'),
			stripped = '';
			tempDOM.html(string);
			stripped = tempDOM.text();
			tempDOM.remove();
			return stripped;
		},

		testImage : function(filename) {
			return (/\.(gif|jpg|jpeg|tiff|png)$/i).test(filename);
		},

		destroyScrollBar : function() {
			$(this).mCustomScrollbar('destroy');
		},

		updateScrollBar : function() {
			$(this).mCustomScrollbar('update');
		},
		reinitParentScroll : function() {
			//Reinit the scrollbar
			var parent_scrolls = $(this).parents('.ipt_uif_tabs_scroll, .ipt_uif_scroll'); //

			parent_scrolls.each(function() {
				methods.updateScrollBar.apply(this);
			});
		},
		reinitTBAnchors : function() {
			var tbWindow = $('#TB_window'), width = $(window).width(), H = $(window).height(), W = ( 1024 < width ) ? 1024 : width, adminbar_height = 0;

			if ( $('body.admin-bar').length )
					adminbar_height = 28;

			if ( tbWindow.size() ) {
					tbWindow.width( W - 50 ).height( H - 45 - adminbar_height );
					$('#TB_iframeContent').width( W - 50 ).height( H - 75 - adminbar_height );
					$('#TB_ajaxContent').width( W - 80 ).height( H - 95 - adminbar_height );
					tbWindow.css({'margin-left': '-' + parseInt((( W - 50 ) / 2),10) + 'px'});
					if ( typeof document.body.style.maxWidth != 'undefined' )
							tbWindow.css({'top': 20 + adminbar_height + 'px','margin-top':'0'});
			};

			return $('a.thickbox').each( function() {
					var href = $(this).attr('href');
					if ( ! href ) return;
					href = href.replace(/&width=[0-9]+/g, '');
					href = href.replace(/&height=[0-9]+/g, '');
					$(this).attr( 'href', href + '&width=' + ( W - 80 ) + '&height=' + ( H - 85 - adminbar_height ) );
			});
		}
	};

	$.fn.iptPluginUIFAdmin = function(method) {
		if(methods[method]) {
			return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
		} else if (typeof(method) == 'object' || !method) {
			return methods.init.apply(this, arguments);
		} else {
			$.error('Method ' + method + ' does not exist on jQuery.iptPluginUIFAdmin');
			return this;
		}
	};
})(jQuery);

jQuery(document).ready(function($) {
	$('.ipt_uif').iptPluginUIFAdmin();
	$(document).iptPluginUIFAdmin('reinitTBAnchors');
});
