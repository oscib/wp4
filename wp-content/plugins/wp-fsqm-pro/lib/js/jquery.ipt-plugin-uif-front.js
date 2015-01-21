/**
 * iPanelThemes Plugin Framework
 *
 * This is a jQuery plugin which works on the plugin framework to populate the UI
 * Front area
 *
 * Dependencies: TODO
 *
 * @author Swashata Ghosh <swashata@intechgrity.com>
 * @version 1.0.0
 */
(function($) {
	//Default Options
	var defaultOp = {
		callback : null,
		themeCheckTimeout : 30000,
		additionalThemes : [],
		waypoints : true
	};

	//Captcha check function
	window.ipt_uif_front_captcha = function(field, rules, i, options) {
		if($(field).val() != $(field).data('sum')) {
			return iptPluginUIFFront.L10n.validationEngine.requiredInFunction.alertText + $(field).data('sum');
		}
	};

	//Methods
	var methods = {
		init : function(options) {
			var op = $.extend(true, {}, defaultOp, options);
			//Append the default theme
			var links = [], link_ui;
			if($('#ipt_uif_default_theme_link').length) {
				links[0] = $('#ipt_uif_default_theme_link').get(0);
			} else {
				link_ui = $('<link id="ipt_uif_default_theme_link" rel="stylesheet" media="all" type="text/css" href="' + iptPluginUIFFront.location + 'css/ipt-plugin-uif-front.css?version=' + iptPluginUIFFront.version + '" />');
				$('body').append(link_ui);
				links[0] = link_ui.get(0);
			}
			//Check for IE8 YUCK
			if($.support.opacity === false) {
				if($('#ipt_uif_ie8_hack').length) {
					links[links.length] = $('#ipt_uif_ie8_hack').get(0);
				} else {
					link_ui = $('<link id="#ipt_uif_ie8_hack" rel="stylesheet" media="all" type="text/css" href="' + iptPluginUIFFront.location + 'css/ie8.css?version=' + iptPluginUIFFront.version + '" />');
					$('body').append(link_ui);
					links[links.length] = link_ui.get(0);
				}
			}
			if(op.additionalThemes.length) {
				for(var i = 0; i < op.additionalThemes.length; i++) {
					if(typeof(op.additionalThemes[i]) == 'object' && 'id' in op.additionalThemes[i] && 'src' in op.additionalThemes[i]) {
						if($('#' + op.additionalThemes[i].id).length) {
							links[links.length] = $('#' + op.additionalThemes[i].id).get(0);
						} else {
							link_ui = $('<link id="' + op.additionalThemes[i].id + '" rel="stylesheet" media="all" type="text/css" href="' + op.additionalThemes[i].src + '" />');
							$('body').append(link_ui);
							links[links.length] = link_ui.get(0);
						}
					}
				}
			}
			var _self = this;
			methods.loadThemes(links, function() {
				methods.afterThemeLoaded.apply(_self, [op]);
			}, op.themeCheckTimeout);
			return this;
		},
		loadThemes : function(links, callback, themeCheckTimeout) {
			if(!links.length) {
				if(typeof(callback) == 'function') {
					callback();
				}
				return;
			}
			//Crossbrowser compatibility
			var sheet, cssRules;
			if('sheet' in links[0]) {
				sheet = 'sheet'; cssRules = 'cssRules';
			} else {
				sheet = 'styleSheet'; cssRules = 'rules';
			}

			//Set the interval
			var interval_id = setInterval(function() {
				var all_done = true;
				for(var i = 0; i < links.length; i ++) {
					if(links[i][sheet] != undefined && cssRules in links[i][sheet]) {
						try {
							if(!links[i][sheet][cssRules].length) {
								all_done = false;
								break;
							}
						} catch(e) {
							all_done = false;
						}
					} else {
						all_done = false;
						break;
					}
				}
				if(all_done) {
					clearInterval(interval_id);
					clearTimeout(timeout_id);
					if(typeof(callback) == 'function') {
						callback();
					}
				}
			}, 300);

			//Set the timeout
			var timeout_id = setTimeout(function() {
				clearInterval(interval_id);
				clearTimeout(timeout_id);
				for(var i = 0; i < links.length; i++) {
					$(links[i]).remove();
				}
				if(typeof(callback) == 'function') {
					callback();
				}
			}, themeCheckTimeout);
		},
		afterThemeLoaded : function(op) {
			return this.each(function() {
				var self = $(this),
				_self = this;
				self.addClass('ipt_uif_common');
				if(self.data('ui-theme') && self.data('ui-theme-id')) {
					//Get the data
					var ui_themes = self.data('ui-theme'),
					ui_theme_id = self.data('ui-theme-id');

					//Append the class
					self.addClass('ipt-uif-custom-' + ui_theme_id);

					//Init the link elements
					var links = [];
					var themes_to_append = [];

					//Check what we need to append
					if(typeof(ui_themes) == 'object' && ui_themes.length) {
						for(var i = 0; i < ui_themes.length; i++) {
							var link_element = $(document).find('#' + ui_theme_id + '_' + i);
							if(!link_element.length) {
								themes_to_append[themes_to_append.length] = i;
							} else {
								links[links.length] = link_element.get(0);
							}
						}
					}

					//If needed then append it
					if(themes_to_append.length) {
						//Store the DOM Objs
						var ui_theme_element;
						for(var i = 0; i < themes_to_append.length; i++) {
							ui_theme_element = $('<link media="all" id="' + ui_theme_id + '_' + i + '" type="text/css" rel="stylesheet" href="' + ui_themes[themes_to_append[i]] + '" />');
							links[links.length] = ui_theme_element.get(0);
							$('body').append(ui_theme_element);
						}
					}

					methods.loadThemes(links, function() {
						methods.applyUIElements.apply(_self, [op.callback, 'ipt-uif-custom-' + ui_theme_id, op]);
					}, op.themeCheckTimeout);
				} else {
					//No theme give, fallback to the default
					methods.applyUIElements.apply(_self, [op.callback, 'ipt-uif-custom-none', op]);
				}
			});
		},

		applyUIElements : function(callback, ui_theme_id, op) {
			var self = $(this);
			//hide the init loader
			self.find('.ipt_uif_init_loader').hide();
			//Show this
			self.find('.ipt_uif_hidden_init').show();
			//Show any messages
			self.find('.ipt_uif_message').show();

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

			//Init the datepickers
			self.find('.ipt_uif_datepicker').each(function() {
				methods.applyDatePicker.apply(this, [ui_theme_id]);
			});
			self.find('.ipt_uif_datetimepicker').each(function() {
				methods.applyDateTimePicker.apply(this, [ui_theme_id]);
			});
			self.find('.ipt_uif_timepicker').each(function() {
				methods.applyTimePicker.apply(this, [ui_theme_id]);
			});

			//Init the printElements
			methods.applyPrintElement.apply(this, [ui_theme_id]);

			//Init the conditional
			self.find('.ipt_uif_conditional_input').each(function() {
				methods.applyConditionalInput.apply(this);
			});
			self.find('.ipt_uif_conditional_select').each(function() {
				methods.applyConditionalSelect.apply(this);
			});

			//Init the image slider
			self.find('.ipt_uif_image_slider_wrap').each(function() {
				methods.applyImageSlider.apply(this);
			});

			//Init the scroll to top
			methods.applyScrollToTop.apply(this, [self]);

			//Init the rating
			self.find('.ipt_uif_rating').each(function() {
				methods.applyRating.apply(this);
			});

			//Init the keyboard
			self.find('.ipt_uif_keypad').each(function() {
				methods.applyKeypad.apply(this, [ui_theme_id]);
			});

			//Init the autocomplete
			self.find('.ipt_uif_autocomplete').each(function() {
				methods.applyAutoComplete.apply(this);
			});

			//Init the buttons
			methods.applyButtons.apply(self.find('.ipt_uif_button, .ipt_uif_ul_menu > li > a'));

			//Init the validation engine
			self.find('form.ipt_uif_validate_form').each(function() {
				methods.applyValidation.apply(this);
			});

			//Init the collapsible
			self.find('.ipt_uif_collapsible').each(function() {
				methods.applyCollapsible.apply(this);
			});

			//Init the Sortable
			self.find('.ipt_uif_sorting').each(function() {
				methods.applySorting.apply(this);
			});

			//Init the fileupload
			self.find('.ipt_uif_uploader').each(function() {
				methods.applyUploader.apply(this);
			});
			self.on('dragover', '.fileinput-dragdrop', function() {
				$(this).addClass('hover');
			});
			self.on('dragleave', '.fileinput-dragdrop', function() {
				$(this).removeClass('hover');
			});

			// Set the blueimp container
			var blueimp_container = $('#blueimp-gallery');
			if ( blueimp_container.length === 0 ) {
				$('body').append('<div data-filter=":even" class="blueimp-gallery blueimp-gallery-controls" id="blueimp-gallery" style="display: none;">' +
					'<div class="slides" style="width: 21600px;"></div>' +
					'<h3 class="title">Desert (1).jpg</h3>' +
					'<a class="prev">‹</a>' +
					'<a class="next">›</a>' +
					'<a class="close">×</a>' +
					'<a class="play-pause"></a>' +
					'<ol class="indicator"></ol>' +
				'</div>');
			}

			//Init the Tabs
			self.find('.ipt_uif_tabs').each(function() {
				methods.applyTabs.apply(this, [op]);
			});
			self.on('click', '.ipt_uif_tabs_toggler', function(e) {
				e.preventDefault();
				e.stopPropagation();
				$(this).siblings('.ui-tabs-nav').toggleClass('ipt_uif_tabs_toggle_active');
			});

			// Apply the conditional
			methods.applyConditionalLogic.apply(this);

			// Apply the waypoint animation
			methods.applyWayPoints.apply(this, [op]);

			//Apply the callback
			if(typeof(callback) == 'function') {
				callback.apply(this);
			}
		},

		applyWayPoints: function(op) {
			if ( op.waypoints !== true ) {
				return;
			}
			var columns = $(this).find('.ipt_uif_conditional').filter(':visible').css({opacity: 0}).removeClass('iptAnimated iptFadeInLeft');
			setTimeout(function() {
				columns.waypoint({
					handler: function(direction) {
						var _self = $(this);
						_self.css({opacity: ''});
						if ( _self.is(':visible') ) {
							_self.addClass('iptAnimated iptFadeInLeft');
							setTimeout(function() {
								_self.removeClass('iptAnimated iptFadeInLeft');
							}, 500);
						}
					},
					triggerOnce: true,
					offset: '98%'
				});
			}, 100);
		},

		applyConditionalLogic: function() {
			var conditionals = {},
			do_conditional = true;

			try {
				conditionals = JSON.parse( $(this).find('.ipt_uif_conditional_logic').val() );
			} catch( e ) {
				do_conditional = false;
			}

			if ( ! do_conditional ) {
				return;
			}


			// Hide everything that should be hidden at first
			for ( var elm_id in conditionals.logics ) {
				var status = conditionals.logics[elm_id].status;
				if ( status === false ) {
					$('#' + elm_id).hide();
				}
			}

			$(this).on( 'change', function(e) {
				// Get the target
				var target = $(e.target),
				conditional_selector = target.closest('.ipt_uif_conditional'), // Parent conditional div
				selector_index = conditional_selector.attr( 'id' ); // The ID of the div is the selector index

				if ( selector_index && conditionals.indexes[selector_index] !== undefined ) { // Check if it has impact on certain logics
					for ( var target_index in conditionals.indexes[selector_index] ) { // Loop through all indexes
						// The conditional logic of the target element
						var logics_of_element = conditionals.logics[conditionals.indexes[selector_index][target_index]],
						target_element = $('#' + conditionals.indexes[selector_index][target_index]);

						// Validate all logics
						if ( methods.validateLogic( conditionals.base, logics_of_element.logic, logics_of_element.relation ) ) {
							// Matched so change the state
							if ( logics_of_element.change === true ) {
								methods.conditionalShowElement.apply(target_element);
								// target_element.slideDown( 'fast', methods.refreshiFrames );
							} else {
								methods.conditionalHideElement.apply(target_element);
								// target_element.slideUp( 'fast' );
							}
						} else {
							// Not matched, so revert to inital state
							if ( logics_of_element.status === true ) {
								methods.conditionalShowElement.apply(target_element);
								// target_element.slideDown( 'fast', methods.refreshiFrames );
							} else {
								methods.conditionalHideElement.apply(target_element);
								// target_element.slideUp( 'fast' );
							}
						}
					}
				}
			} );

			// Trigger the change event for all conditional events
			$(this).find( '.ipt_uif_conditional' ).trigger( 'change' );

			$(this).find( '.ipt_uif_text, .ipt_uif_textarea' ).typeWatch({
				callback: function() {
					$(this).trigger( 'change' );
				},
				wait: 750,
				highlight: false,
				captureLength: 1
			});
		},

		conditionalShowElement: function() {
			var _self = this;
			// Don't do anything if it is already visible
			if ( _self.is(':visible') ) {
				return;
			}
			_self.slideDown('fast').addClass('iptAnimated iptAppear');
			setTimeout(function() {
				_self.removeClass('iptAnimated iptAppear');
				methods.refreshiFrames.apply(_self);
			}, 500);
		},

		conditionalHideElement: function() {
			var _self = this;
			// Don't do anything if it is already hidden
			if ( ! _self.is(':visible') ) {
				return;
			}
			_self.addClass('iptAnimated iptDisappear').fadeOut('fast');
			setTimeout(function() {
				_self.removeClass('iptAnimated iptDisappear').hide();
			}, 500);
		},

		validateLogic: function( base, logics ) {
			var return_val = false;
			var relation_check = [];
			var relation_operator = [];
			var debug_info = [];
			for ( var logic_id in logics ) {
				var logic = logics[logic_id], // Store the logic
				conditional_div = $('#ipt_fsqm_form_' + base + '_' + logic.m_type + '_' + logic.key), // get the conditional div to check against
				check_type = conditional_div.prev('.ipt_fsqm_hf_type').val(), // And the type of the element
				this_validated = false,
				compare_source = null,
				do_comparison = true;
				debug_info[logic_id] = {};
				debug_info[logic_id].x = logic.m_type;
				debug_info[logic_id].k = logic.key;
				debug_info[logic_id].has = logic.check;
				debug_info[logic_id].value = logic.value;
				debug_info[logic_id].rel = logic.rel;
				debug_info[logic_id].which = logic.operator;

				switch( check_type ) {
					// Radios
					case 'radio' :
					case 'p_radio' :
						compare_source = [];
						conditional_div.find('input.ipt_uif_radio').filter(':checked').each( function() {
							compare_source[compare_source.length] = $(this).next('label').text().trim();
						} );
						break;

					// Checkboxes
					case 'checkbox' :
					case 'p_checkbox' :
						compare_source = [];
						conditional_div.find('input.ipt_uif_checkbox').filter(':checked').each( function() {
							compare_source[compare_source.length] = $(this).next('label').text().trim();
						} );
						break;

					case 'select' :
					case 'p_select' :
						compare_source = [];
						conditional_div.find('select.ipt_uif_select option').filter(':selected').each( function() {
							compare_source[compare_source.length] = $(this).text().trim();
						} );
						break;

					case 'slider' :
						compare_source = methods.intelParseFloat( conditional_div.find('input.ipt_uif_slider').val() );
						logic.value = methods.intelParseFloat( logic.value );
						break;

					case 'range' :
						compare_source = [methods.intelParseFloat( conditional_div.find('input.ipt_uif_slider.slider_range').val() ), methods.intelParseFloat( conditional_div.find('input.ipt_uif_slider.slider_range').next('input').val() )];
						logic.value = methods.intelParseFloat( logic.value );
						break;

					case 'spinners' :
						compare_source = [];
						conditional_div.find( 'input.ipt_uif_uispinner' ).each(function() {
							compare_source[compare_source.length] = methods.intelParseFloat( $(this).val() );
						});
						logic.value = methods.intelParseFloat( logic.value );
						break;

					case 'grading' :
						compare_source = [];
						conditional_div.find('input.ipt_uif_slider').each(function() {
							compare_source[compare_source.length] = methods.intelParseFloat( $(this).val() );
						});
						conditional_div.find('input.ipt_uif_slider.slider_range').each(function() {
							compare_source[compare_source.length] = methods.intelParseFloat( $(this).val() );
							compare_source[compare_source.length] = methods.intelParseFloat( $(this).next('input').val() );
						});
						logic.value = methods.intelParseFloat( logic.value );
						break;

					case 'starrating' :
					case 'scalerating' :
						compare_source = [];
						conditional_div.find('.ipt_uif_rating').each(function() {
							compare_source[compare_source.length] = methods.intelParseFloat( $(this).find('input.ipt_uif_radio:checked').val() );
						});
						logic.value = methods.intelParseFloat( logic.value );
						break;

					case 'matrix' :
						compare_source = [];

						// First get the column heads
						var m_columns = [];
						conditional_div.find('.ipt_uif_matrix thead th').each(function() {
							m_columns[m_columns.length] = $(this).text().trim();
						});
						conditional_div.find('.ipt_uif_checkbox,.ipt_uif_radio').filter(':checked').each(function() {
							var m_check_index = $(this).parent().parent().find('> *').index( $(this).parent() );
							if ( m_columns[m_check_index] !== '' || m_columns[m_check_index] !== undefined ) {
								if ( -1 === $.inArray( m_columns[m_check_index], compare_source ) ) {
									compare_source[compare_source.length] = m_columns[m_check_index];
								}
							}
						});
						break;
					case 'toggle' :
					case 's_checkbox' :
						compare_source = conditional_div.find('input[type="checkbox"]').is(':checked') ? '1' : '0';
						logic.value = methods.intelParseFloat( logic.value );
						break;

					case 'feedback_small' :
					case 'f_name' :
					case 'l_name' :
					case 'email' :
					case 'phone' :
					case 'p_name' :
					case 'p_email' :
					case 'p_phone' :
					case 'textinput' :
					case 'password' :
					case 'keypad' :
						compare_source = conditional_div.find('input[type="text"]').val();
						break;

					case 'feedback_large' :
					case 'textarea' :
						compare_source = conditional_div.find('textarea').val();
						break;

					case 'upload' :
						compare_source = conditional_div.find('.ipt_uif_uploader').data('totalUpload');
						break;

					case 'address' :
						compare_source = [];
						conditional_div.find('.ipt_uif_text').each(function() {
							compare_source[compare_source.length] = $(this).val();
						});
						break;

					case 'datetime' :
						compare_source = conditional_div.find('.ipt_uif_text').val();
						compare_result = methods.dates.compare(new Date( compare_source ), new Date( logic.value ));
						switch( logic.operator ) {
							case 'eq' :
								if ( compare_result === 0 ) {
									this_validated = true;
								}
								break;
							case 'neq' :
								if ( compare_result !== 0 ) {
									this_validated = true;
								}
								break;
							case 'gt' :
								if ( compare_result === 1 ) {
									this_validated = true;
								}
								break;
							case 'lt' :
								if ( compare_result === -1 ) {
									this_validated = true;
								}
								break;
							default :
								break;
						}
						do_comparison = false;
						break;
					default :
						this_validated = false;
						do_comparison = false;
						break;
				}

				// Now do the comparison
				if ( do_comparison ) {
					var final_compare_against = null,
					final_compare_with = ( typeof( logic.value ) == 'number' ? logic.value : logic.value.toLowerCase() ); // Lower case for comparison

					if ( logic.check === 'val' ) { // Compare value
						if ( typeof( compare_source ) === 'object' ) { // If collected values are object
							final_compare_against = []; // Init as array
							for( var i in compare_source ) {
								final_compare_against[final_compare_against.length] = ( typeof( compare_source[i] ) == 'number' ? compare_source[i] : compare_source[i].toLowerCase() ); // Store lowercased string for comparison
							}
						} else {
							final_compare_against = ( typeof( compare_source ) == 'number' ? compare_source : compare_source.toLowerCase() ); // Store lowercased value
						}

					} else { // Compare length
						final_compare_against = ( typeof( compare_source ) == 'number' ? compare_source : compare_source.length ); // Valid both for string and array type object
						final_compare_with = methods.intelParseFloat( final_compare_with );
					}

					// Now do the comparison
					var compare_against_object = typeof( final_compare_against ) === 'object' ? true : false;
					switch( logic.operator ) {
						case 'eq' :
							if ( compare_against_object ) {
								for( var i in final_compare_against ) {
									if ( final_compare_against[i] !== '' && final_compare_against[i] == final_compare_with ) {
										this_validated = true;
										break;
									} else if ( final_compare_against[i] === '' && final_compare_with === '' ) {
										this_validated = true;
										break;
									}
								}
							} else {
								if ( final_compare_against !== '' && final_compare_against == final_compare_with ) {
									this_validated = true;
								} else if ( final_compare_against === '' && final_compare_with === '' ) {
									this_validated = true;
								}
							}
							break;
						case 'neq' :
							if ( compare_against_object ) {
								for( var i in final_compare_against ) {
									if ( final_compare_against[i] !== '' && final_compare_against[i] != final_compare_with ) {
										this_validated = true;
										break;
									}
								}
							} else {
								if ( final_compare_against !== '' && final_compare_against != final_compare_with ) {
									this_validated = true;
								}
							}
							break;
						case 'gt' :
							if ( compare_against_object ) {
								for( var i in final_compare_against ) {
									if ( final_compare_against[i] > final_compare_with ) {
										this_validated = true;
										break;
									}
								}
							} else {
								if ( final_compare_against > final_compare_with ) {
									this_validated = true;
								}
							}
							break;
						case 'lt' :
							if ( compare_against_object ) {
								for( var i in final_compare_against ) {
									if ( final_compare_against[i] < final_compare_with ) {
										this_validated = true;
										break;
									}
								}
							} else {
								if ( final_compare_against < final_compare_with ) {
									this_validated = true;
								}
							}
							break;
						case 'ct' :
							if ( compare_against_object ) {
								for( var i in final_compare_against ) {
									if ( final_compare_against[i] !== '' && final_compare_against[i].indexOf( final_compare_with ) !== -1 ) {
										this_validated = true;
										break;
									}
								}
							} else {
								if ( final_compare_against !== '' && final_compare_against.indexOf( final_compare_with ) !== -1 ) {
									this_validated = true;
								}
							}
							break;
						case 'dct' :
							if ( compare_against_object ) {
								for( var i in final_compare_against ) {
									if ( final_compare_against[i] !== '' && final_compare_against[i].indexOf( final_compare_with ) === -1 ) {
										this_validated = true;
										break;
									}
								}
							} else {
								if ( final_compare_against !== '' && final_compare_against.indexOf( final_compare_with ) === -1 ) {
									this_validated = true;
								}
							}
							break;
						case 'sw' :
							var regEx = new RegExp( '^' + final_compare_with, 'm' );
							if ( compare_against_object ) {
								for( var i in final_compare_against ) {
									if ( regEx.test( final_compare_against[i] ) ) {
										this_validated = true;
										break;
									}
								}
							} else {
								if ( regEx.test( final_compare_against ) ) {
									this_validated = true;
								}
							}
							break;

						case 'ew' :
							var regEx = new RegExp( final_compare_with + '$', 'm' );
							if ( compare_against_object ) {
								for( var i in final_compare_against ) {
									if ( regEx.test( final_compare_against[i] ) ) {
										this_validated = true;
										break;
									}
								}
							} else {
								if ( regEx.test( final_compare_against ) ) {
									this_validated = true;
								}
							}
							break;

						default :
							break;
					}
				}

				// Store for further checking
				relation_check[logic_id] = this_validated;
				relation_operator[logic_id] = logic.rel;
			}

			// Now check individual if necessary
			var relation_check_against = null,
			relation_check_operator = null,
			relation_check_array = [],
			relation_check_array_key = 0;

			for ( var logic_key in relation_check ) {
				if ( null === relation_check_against ) {
					relation_check_against = relation_check[logic_key];
				} else {
					switch ( relation_check_operator ) {
						case 'and' :
							relation_check_against = relation_check_against && relation_check[logic_key];
							break;
						case 'or' :
							relation_check_array_key++;
							relation_check_against = relation_check[logic_key];
							break;
						default :
							break;
					}
				}

				relation_check_operator = relation_operator[logic_key];
				relation_check_array[relation_check_array_key] = relation_check_against;
			}

			return_val = null;
			for ( var i in relation_check_array ) {
				if ( return_val === null ) {
					return_val = relation_check_array[i];
				} else {
					return_val = return_val || relation_check_array[i];
				}
			}
			return return_val;
		},

		intelParseFloat: function( num, default_val ) {
			if ( default_val === undefined ) {
				default_val = 0;
			}
			var parsedNum = parseFloat( num );
			if ( isNaN( parsedNum ) ) {
				parsedNum = default_val;
			}
			return parsedNum;
		},

		dates: {
			convert:function(d) {
				// Converts the date in d to a date-object. The input can be:
				//   a date object: returned without modification
				//  an array      : Interpreted as [year,month,day]. NOTE: month is 0-11.
				//   a number     : Interpreted as number of milliseconds
				//                  since 1 Jan 1970 (a timestamp)
				//   a string     : Any format supported by the javascript engine, like
				//                  "YYYY/MM/DD", "MM/DD/YYYY", "Jan 31 2009" etc.
				//  an object     : Interpreted as an object with year, month and date
				//                  attributes.  **NOTE** month is 0-11.
				return (
					d.constructor === Date ? d :
					d.constructor === Array ? new Date(d[0],d[1],d[2]) :
					d.constructor === Number ? new Date(d) :
					d.constructor === String ? new Date(d) :
					typeof d === "object" ? new Date(d.year,d.month,d.date) :
					NaN
				);
			},
			compare:function(a,b) {
				// Compare two dates (could be of any type supported by the convert
				// function above) and returns:
				//  -1 : if a < b
				//   0 : if a = b
				//   1 : if a > b
				// NaN : if a or b is an illegal date
				// NOTE: The code inside isFinite does an assignment (=).
				return (
					isFinite(a=this.convert(a).valueOf()) &&
					isFinite(b=this.convert(b).valueOf()) ?
					(a>b)-(a<b) :
					NaN
				);
			},
			inRange:function(d,start,end) {
				// Checks if date in d is between dates in start and end.
				// Returns a boolean or NaN:
				//    true  : if d is between start and end (inclusive)
				//    false : if d is before start or after end
				//    NaN   : if one or more of the dates is illegal.
				// NOTE: The code inside isFinite does an assignment (=).
			   return (
					isFinite(d=this.convert(d).valueOf()) &&
					isFinite(start=this.convert(start).valueOf()) &&
					isFinite(end=this.convert(end).valueOf()) ?
					start <= d && d <= end :
					NaN
				);
			}
		},

		applyUploader: function() {
			var widget = $(this), // jQuery object of the widget
			settings = widget.data('settings'), // JSON settings
			configuration = widget.data('configuration'), // JSON configuration
			formData = widget.data('formdata'), // JSON formData
			uploadHandle = widget.find('.ipt_uif_uploader_handle'), // Input type file which is listened for change events
			dropZone = widget.find('.fileinput-dragdrop'), // jQuery object of the dropzone, can be empty in which case it will be disabeld
			acceptFileTypes = new RegExp( "(\.|\/)(" + settings.accept_file_types.split(',').join('|') + ")$", 'i' );

			widget.fileupload({
				url : iptPluginUIFFront.ajaxurl + configuration.upload_url,
				dropZone : dropZone,
				fileInput : uploadHandle,
				formData : formData,
				acceptFileTypes : acceptFileTypes,
				maxFileSize : parseInt(settings.max_file_size, 10),
				minFileSize : parseInt(settings.min_file_size, 10),
				maxNumberOfFiles : parseInt(settings.max_number_of_files, 10),
				uploadTemplateId : configuration.id + '_tmpl_upload',
				downloadTemplateId : configuration.id + '_tmpl_download',
				previewMaxHeight : 100,
				previewMaxWidth : 150,
				autoUpload: settings.auto_upload === true ? true : false
			});

			// Set the active upload data
			widget.data( 'activeUpload', 0 );
			widget.data ( 'totalUpload', 0 );

			// Listen to process event and manipulate the activeUpload data accordingly
			widget.on( 'fileuploadsend', function( e, data ) {
				var activeUpload = widget.data( 'activeUpload' );
				activeUpload++;
				widget.data( 'activeUpload', activeUpload );
			} );
			widget.on( 'fileuploadalways', function( e, data ) {
				var activeUpload = widget.data( 'activeUpload' );
				activeUpload--;
				widget.data( 'activeUpload', activeUpload );
				widget.trigger('change');
			} );
			widget.on( 'fileuploaddone', function( e, data ) {
				var totalUpload = widget.data( 'totalUpload' );
				if ( data._response.result.files[0].error === undefined ) {
					totalUpload++;
				}
				widget.data( 'totalUpload', totalUpload );
			} );
			widget.on( 'fileuploaddestroyed', function( e, data ) {
				var totalUpload = widget.data( 'totalUpload' );
				if ( data.url !== '' ) {
					totalUpload--;
				}
				widget.data( 'totalUpload', totalUpload );
				widget.trigger('change');
			} );

			// Now fetch files if necessary
			if ( configuration.do_download === true ) {
				widget.addClass( 'fileupload-processing' );
				$.ajax({
					url : iptPluginUIFFront.ajaxurl + configuration.download_url,
					data : formData,
					context : widget.get(0)
				}).always(function() {
					$(this).removeClass('fileupload-processing');
				}).done(function( result ) {
					// Update the totalUpload count
					if ( result.files.length !== undefined ) {
						$(this).data( 'totalUpload', result.files.length );
					}
					$(this).fileupload('option', 'done').call( this, $.Event('done'), {result: result} );
				});
			}
		},

		applyValidation : function() {
			$(this).validationEngine({
				promptPosition : 'topLeft'
			});
		},

		applyKeypad : function(ui_theme_id) {
			var settings = $(this).data('settings');
			$(this).keyboard({
				layout : settings.layout,
				usePreview : false,
				autoAccept : true,
				appendLocally : false,
				beforeClose : function() {
					$('body').removeClass('ipt_uif_common ' + ui_theme_id);
				}
			}).on('focus', function() {
				$('body').addClass('ipt_uif_common ' + ui_theme_id);
			});
		},

		applyButtons : function() {
			this.button();
		},

		applyAutoComplete : function() {
			$(this).autocomplete({
				source: $(this).data('autocomplete'),
				appendTo : $(this).parents('.ipt_uif_front')
			});
		},

		applySorting : function() {
			$(this).sortable({
				handle : '.ipt_uif_sorting_handle',
				items : '> .ipt_uif_sortme',
				helper : 'clone',
				appendTo : $(this).parents('.ipt_uif_front'),
				containment : 'parent',
				placeholder : 'ipt_uif_sortme_placeholder',
				forcePlaceholderSize : true
			});
		},

		applyRating : function() {
			$(this).find('label').hover(function() {
				$(this).siblings('input').removeClass('active');
				$(this).prevAll('input').addClass('hover');
			}, function() {
				$(this).prevAll('input').removeClass('hover');
				$(this).siblings('input:checked').addClass('active').prevAll('input').addClass('active');
			});

			$(this).find('input').on('change', function() {
				if($(this).is(':checked')) {
					$(this).nextAll('input').removeClass('active');
					$(this).addClass('active');
					$(this).prevAll('input').addClass('active');
				}
			});

			$(this).find('input:checked').addClass('active').prevAll('input').addClass('active');
		},

		applyScrollToTop : function(container) {
			$(this).on('click', 'a.ipt_uif_scroll_to_top', function(e) {
				e.preventDefault();
				var scrollTo = container.offset().top - 10;
				var htmlTop = parseFloat($('html').css('margin-top'));
				if(isNaN(htmlTop)) {
					htmlTop = 0;
				}
				htmlTop += parseFloat($('html').css('padding-top'));
				if(!isNaN(htmlTop) || htmlTop != 0) {
					scrollTo -= htmlTop;
				}
				$('html, body').animate({scrollTop : scrollTo}, 'fast');
			})
		},

		applyImageSlider : function() {
			var self = $(this),
			settings = self.data('settings'),
			controller = $('<a class=""></a>'),
			slider = self.find('.ipt_uif_image_slider'),
			controller_on_play = settings.on_play,
			controller_on_pause = settings.on_pause;

			//Init the slider
			slider.nivoSlider({
				effect : settings.animation,
				animSpeed : settings.transition * 1000,
				pauseTime : settings.duration * 1000,
				pauseOnHover : false,
				manualAdvance : !settings.autoslide,
				controlNav : true,
				prevText : '',
				nextText : ''
			});

			slider.find('a.nivo-prevNav').after(controller);

			//Init the controller event
			controller.on('click', function(e) {
				e.preventDefault();
				var nivoSlider = slider.data('nivoslider');
				if($(this).hasClass('ipt_uif_image_slider_sliding')) {
					nivoSlider.stop();
					$(this).removeClass('ipt_uif_image_slider_sliding');
					$(this).removeClass(controller_on_play);
					$(this).addClass(controller_on_pause);
				} else {
					nivoSlider.start();
					$(this).addClass('ipt_uif_image_slider_sliding');
					$(this).removeClass(controller_on_pause);
					$(this).addClass(controller_on_play);
				}
			});

			//Initial state of the controller
			if(settings.autoslide == true) {
				controller.addClass('ipt_uif_image_slider_sliding');
				controller.removeClass(controller_on_pause);
				controller.addClass(controller_on_play);
			} else {
				controller.removeClass('ipt_uif_image_slider_sliding');
				controller.removeClass(controller_on_play);
				controller.addClass(controller_on_pause);
			}
		},

		applyCheckboxToggler : function() {
			var selector = $($(this).data('selector'));
			var self = $(this);
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

		applyPrintElement : function(ui_theme_id) {
			$(this).on('click', '.ipt_uif_printelement', function(e) {
				e.preventDefault();
				$('#' + $(this).data('printid')).printElement({
					leaveOpen:true,
					printMode:'popup',
					printBodyOptions : {
						classNameToAdd : 'ipt_uif_common ' + ui_theme_id,
						styleToAdd : 'padding:10px;margin:10px;background: #fff none;color:#333;font-size:12px;'
					},
					pageTitle : document.title
				});
			});
		},

		applyDatePicker : function(ui_theme_id) {
			$(this).datepicker({
				dateFormat : $(this).data('dateformat'),
				duration : 0,
				beforeShow : function(input, ins) {
					$('body').addClass('ipt_uif_common ' + ui_theme_id);
					//return ins.settings;
				},
				onClose : function() {
					$('body').removeClass('ipt_uif_common ' + ui_theme_id);
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

		applyDateTimePicker : function(ui_theme_id) {
			$(this).datetimepicker({
				dateFormat : $(this).data('dateformat'),
				duration : 0,
				timeFormat : $(this).data('timeformat'),
				beforeShow : function() {
					$('body').addClass('ipt_uif_common ' + ui_theme_id);
				},
				onClose : function() {
					$('body').removeClass('ipt_uif_common ' + ui_theme_id);
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

		applyTimePicker : function(ui_theme_id) {
			$(this).timepicker({
				timeFormat : $(this).data('timeformat'),
				duration : 0,
				beforeShow : function() {
					$('body').addClass('ipt_uif_common ' + ui_theme_id);
				},
				onClose : function() {
					$('body').removeClass('ipt_uif_common ' + ui_theme_id);
				},
				showButtonPanel: true,
				closeText: iptPluginUIFDTPL10n.closeText,
				currentText: iptPluginUIFDTPL10n.tcurrentText,
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

		applyCollapsible : function() {
			var state = false;
			var self = $(this);
			var collapse_box = $(this).find('> .ipt_uif_container_inner');
			if($(this).data('opened') == true) {
				state = true;
			}
			var controller = $(this).find('> .ipt_uif_container_head h3 a');

			//Attach the event
			controller.on('click', function() {
				self.toggleClass('ipt_uif_collapsible_open');
				collapse_box.slideToggle('fast', methods.refreshiFrames);
			});

			//Check the initial state
			if(state) {
				collapse_box.show();
				self.addClass('ipt_uif_collapsible_open');
			} else {
				collapse_box.hide();
				self.removeClass('ipt_uif_collapsible_open');
			}
		},

		applyConditionalInput : function() {
			//Get all the inputs
			var inputs = $(this).find('input');
			//Store all the IDs
			var ids = new Array();

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
						$('#' + show_ids[id]).fadeIn('normal');
					}
				});
				//Hide rest
				for(var id in ids) {
					if(shown[ids[id]] != true) {
						$('#' + ids[id]).stop(true, true).hide();
					}
				}
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
					$('#' + activated_ids[id]).fadeIn('fast');
				}
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
			this.spinner();
			$(this).on('mousewheel', function(e) {
				$(this).trigger('change');
			});
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
			var count_div = first_input.siblings('div.ipt_uif_slider_count');

			//Now append the div
			var slider_div = $('<div />');
			slider_div.addClass(slider_range ? 'ipt_uif_slider_range' : 'ipt_uif_slider_single');

			var slider_div_duplicate;
			if(slider_range) {
				slider_div_duplicate = second_input.next('div.ipt_uif_slider_range');
			} else {
				slider_div_duplicate = first_input.next('div.ipt_uif_slider_range');
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
					first_input.val(ui.value).trigger('change');
					if(count_div.length) {
						count_div.find('span').text(ui.value);
					}
				};
				slider_settings.value = value;
			} else {
				//alert('atta boy');
				slider_settings.slide = function(event, ui) {
					first_input.val(ui.values[0]).trigger('change');
					second_input.val(ui.values[1]).trigger('change');
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

		applyTabs : function(op) {
			//Default tab functionality
			var tab_ops = {
				collapsible : $(this).data('collapsible') ? true : false,
				show : 200,
				create: function(event, ui) {
					if ( op.waypoints === true ) {
						ui.panel.data('iptWaypoints', true);
					}
				},
				beforeActivate: function(event, ui) {
					if ( ! ui.newPanel.data('iptWaypoints') && op.waypoints === true ) {
						ui.newPanel.find('.ipt_uif_conditional').css({opacity: 0}).removeClass('iptAnimated iptFadeInLeft')
					}
				},
				activate: function(event, ui) {
					methods.refreshiFrames.apply(ui.newPanel);
					// Don't refresh if either this tab has been shown or it is the first tab
					if ( ! ui.newPanel.data('iptWaypoints') && op.waypoints === true ) {
						var columns = ui.newPanel.find('.ipt_uif_conditional');
						columns.waypoint({
							handler: function(direction) {
								var _self = $(this).css({opacity: ''}).addClass('iptAnimated iptFadeInLeft');
								setTimeout(function() {
									_self.removeClass('iptAnimated iptFadeInLeft');
								}, 500);
							},
							triggerOnce: true,
							offset: '98%'
						});
						ui.newPanel.data('iptWaypoints', true);
					}
				}
			};
			$(this).tabs(tab_ops);

			//Fix for vertical tabs
			if($(this).hasClass('vertical')) {
				$(this).addClass('ui-tabs-vertical ui-helper-clearfix');
				$(this).find('> ul > li').removeClass( "ui-corner-top" ).addClass( "ui-corner-left" );
			}
		},

		quote : function(str) {
			return str.replace(/([.?*+^$[\]\\(){}|-])/g, "\\$1");
		},

		testImage : function(filename) {
			return (/\.(gif|jpg|jpeg|tiff|png)$/i).test(filename);
		},

		refreshiFrames: function() {
			var _self = $(this);
			_self.find('iframe').each(function() {
				$(this).attr('src', $(this).attr('src'));
			});
		}
	};

	$.fn.iptPluginUIFFront = function(method) {
		if(methods[method]) {
			return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
		} else if (typeof(method) == 'object' || !method) {
			return methods.init.apply(this, arguments);
		} else {
			$.error('Method ' + method + ' does not exist on jQuery.iptPluginUIFFront');
			return this;
		}
	};
})(jQuery);
