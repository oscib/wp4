(function($) {
	var methods = {
		defaults : {
			settings : {},
			survey : {},
			feedback : {},
			wpnonce : '',
			ajaxurl : '',
			form_id : 0,
			do_data : false,
			do_data_nonce : '',
			action : 'ipt_fsqm_report'
		},
		init : function(options) {
			var op = $.extend(true, {}, methods.defaults, options);
			return $(this).each(function() {
				methods.populate_report.apply(this, [op]);
			});
		},
		populate_report : function(op) {
			op.progress_bar = $(this).find('.ipt_fsqm_report_progressbar').eq(0).progressbar();
			op.loader = $(this).find('.ipt_fsqm_report_ajax').eq(0);
			op.container = $(this);
			op.printer = $('#ipt_fsqm_report_button_container_' + op.form_id);
			op.printer.hide();
			$(this).data('ipt_fsqm_report_op', op);
			methods.generate_report.apply(this, [0, op]);
		},
		object_length : function(obj) {
			if ( typeof(obj) !== 'object' ) {
				return false;
			}
			var count = 0, i;
			for ( i in obj ) {
				if ( obj.hasOwnProperty( i ) ) {
					count++;
				}
			}
			return count;
		},
		number_format : function(num) {
			var number = parseFloat(num);
			if(isNaN(number)) {
				return num;
			} else {
				return (parseInt(num * 100, 10) / 100);
			}
		},
		appendToTableInPositionForMinMax : function(to_append, table, append_to, append_after, search_class, position, min, max, step) {
			if(!position.length) {
				if(append_after === null) {
					append_to.append(to_append);
				} else {
				   append_after.after(to_append);
				}
			   return;
			}
			var position_min = parseFloat(position[0]),
			position_max = parseFloat(position[1]);
			min = parseFloat(min);
			max = parseFloat(max);
			step = parseFloat(step);

			if(table.find('tbody tr td.' + search_class).length && !(isNaN(position_min) || isNaN(position_max) || isNaN(min) || isNaN(max) || isNaN(step))) {
				//First search for possible minimum
				for(var i = position_min, j = position_min; i >= min || j <= max; i = i - step, j = j + step) {
					var possible_chunk_after = table.find('td[data-value-min="' + i + '"].' + search_class);
					if(possible_chunk_after.length) {
						//Here we have to put after the td whose max value is just lesser than the current position_max
						//Or before the td whose max value is just greater than the current position_max
						for(var k = position_max, l = position_max; k >= min || l <= max; k = k - step, l = l + step) {
							//This is to put just after
							if(possible_chunk_after.filter('td[data-value-max="' + k + '"].' + search_class).length) {
								possible_chunk_after.filter('td[data-value-max="' + k + '"].' + search_class).parent().after(to_append);
								return;
							}

							//This is to put just before
							if(possible_chunk_after.filter('td[data-value-max="' + l + '"].' + search_class).length) {
								possible_chunk_after.filter('td[data-value-max="' + l + '"].' + search_class).parent().before(to_append);
								return;
							}
						}
					}

					var possible_chunk_before = table.find('td[data-value-min="' + j + '"].' + search_class);
					if(possible_chunk_before.length) {
						for(var k = position_max, l = position_max; k >= min || l <= max; k = k - step, l = l + step) {
							//This is to put just after
							if(possible_chunk_before.filter('td[data-value-max="' + k + '"].' + search_class).length) {
								possible_chunk_before.filter('td[data-value-max="' + k + '"].' + search_class).parent().after(to_append);
								return;
							}

							//This is to put just before
							if(possible_chunk_before.filter('td[data-value-max="' + l + '"].' + search_class).length) {
								possible_chunk_before.filter('td[data-value-max="' + l + '"].' + search_class).parent().before(to_append);
								return;
							}
						}
					}
				}
			}

			if(append_after == null) {
				append_to.append(to_append);
			} else {
				append_after.after(to_append);
			}
		},
		appendToTableInPositionForVal : function(to_append, table, append_to, append_after, search_class, position, min, max, step) {
			position = parseFloat(position);
			min = parseFloat(min);
			max = parseFloat(max);
			step = parseFloat(step);

			if(table.find('tbody tr td.' + search_class).length && !(isNaN(position) || isNaN(min) || isNaN(max) || isNaN(step))) {
				for(var i = position, j = position; i >= min || j <= max; i = i - step, j = j + step) {
					var possible_after = table.find('td[data-value="' + i + '"].' + search_class);
					if(possible_after.length) {
						possible_after.parent().after(to_append);
						return;
					}
					var possible_before = table.find('td[data-value="' + j + '"].' + search_class);
					if(possible_before.length) {
						possible_before.parent().before(to_append);
						return;
					}
				}

			}
			if(append_after == null) {
				append_to.append(to_append);
			} else {
				append_after.after(to_append);
			}
		},
		updateCount : function(to_update, val) {
			to_update.html(val);
		},
		calculateRating : function(values, data, data_table, append_to, append_after, tr_class, td_class, avg_update, settings) {
			var avg_total = 0,
			avg_count = 0,
			value, td_count_to_update, rating_img, i, a, to_append, avg_img, avg_to_check;
			for(value in values) {
				if(!isNaN(values[value])) {
					avg_total += value * values[value];
					avg_count += values[value];

					if(undefined === data[value]) {
						data[value] = values[value];
					} else {
						data[value] += values[value];
					}

					td_count_to_update = data_table.find('td[data-value="' + value + '"].' + td_class);
					if(!td_count_to_update.length) {
						rating_img = '';
						for(i = 1; i <= settings.max; i++) {
							if(i <= value) {
								rating_img += iptFSQMReport.rating_img_full;
							} else {
								rating_img += iptFSQMReport.rating_img_empty;
							}
						}
						to_append = '<tr class="' + tr_class + '"><th>' + rating_img + '</th><td class="' + td_class + '" data-value="' + value + '">' +  data[value] + '</td></tr>';
						methods.appendToTableInPositionForVal(to_append, data_table, append_to, append_after, td_class, value, settings.min, settings.max, settings.step);
					} else {
						td_count_to_update.text(data[value]);
					}
				}
			}

			if(undefined === data.average_meta) {
				data.average_meta = {
					total : avg_total,
					count : avg_count
				};
			} else {
				data.average_meta.total += avg_total;
				data.average_meta.count += avg_count;
			}
			data.average = methods.number_format(data.average_meta.total / data.average_meta.count);

			avg_img = '';
			avg_to_check = Math.floor(data.average);
			for(a = 1; a <= settings.max; a++) {
				if(avg_to_check >= a) {
					avg_img += iptFSQMReport.rating_img_full;
				} else {
					//Check for the crossing point
					if(avg_to_check + 1 === a) {
						if(a === Math.round(data.average)) {
							avg_img += iptFSQMReport.rating_img_half;
						} else {
							avg_img += iptFSQMReport.rating_img_empty;
						}
					} else {
						avg_img += iptFSQMReport.rating_img_empty;
					}
				}
			}
			methods.updateCount(avg_update.find('span.avg'), data.average);
			methods.updateCount(avg_update.find('span.avg_img'), avg_img);
			methods.updateCount(avg_update.find('span.avg_count'), data.average_meta.count);
		},
		calculateSlider : function(values, data, data_table, append_to, append_after, tr_class, td_class, avg_update, settings) {
			var avg_total = 0,
			avg_count = 0,
			value, td_count_to_update, to_append;
			if ( undefined === settings.prefix ) {
				settings.prefix = '';
			}
			if ( undefined === settings.suffix ) {
				settings.suffix = '';
			}
			for(value in values) {
				if(!isNaN(values[value])) {
					avg_total += value * values[value];
					avg_count += values[value];

					if(undefined === data[value]) {
						data[value] = values[value];
					} else {
						data[value] += values[value];
					}

					td_count_to_update = data_table.find('td[data-value="' + value + '"].' + td_class);
					if(!td_count_to_update.length) {
						to_append = '<tr class="' + tr_class + '"><th>' + settings.prefix + value + settings.suffix + '</th><td class="' + td_class + '" data-value="' + value + '">' +  data[value] + '</td></tr>';
						methods.appendToTableInPositionForVal(to_append, data_table, append_to, append_after, td_class, value, settings.min, settings.max, settings.step);
					} else {
						td_count_to_update.text(data[value]);
					}
				}
			}

			if(undefined === data.average_meta) {
				data.average_meta = {
					total : avg_total,
					count : avg_count
				};
			} else {
				data.average_meta.total += avg_total;
				data.average_meta.count += avg_count;
			}
			data.average = methods.number_format(data.average_meta.total / data.average_meta.count);
			methods.updateCount(avg_update.find('span.avg'), settings.prefix + data.average + settings.suffix);
			methods.updateCount(avg_update.find('span.avg_count'), data.average_meta.count);
		},
		calculateRange : function(values_delimited, data, data_table, append_to, append_after, tr_class, td_class, avg_update, settings) {
			var avg_total_min = 0,
			avg_total_max = 0,
			avg_count = 0,
			values, value, difference, data_key, td_count_to_update, to_append, th_label;
			if ( undefined === settings.prefix ) {
				settings.prefix = '';
			}
			if ( undefined === settings.suffix ) {
				settings.suffix = '';
			}
			for(values in values_delimited) {
				if(!isNaN(values_delimited[values])) {
					value = values.split(',');
					avg_count += values_delimited[values];
					avg_total_min += value[0] * values_delimited[values];
					avg_total_max += value[1] * values_delimited[values];
					data_key = value[0] + iptFSQMReport.range_text + value[1];
					th_label = settings.prefix + value[0] + settings.suffix + iptFSQMReport.range_text + settings.prefix + value[1] + settings.suffix;
					if(undefined === data[data_key]) {
						data[data_key] = values_delimited[values];
					} else {
						data[data_key] += values_delimited[values];
					}

					td_count_to_update = data_table.find('td[data-value-min="' + value[0] + '"][data-value-max="' + value[1] + '"].' + td_class);
					if(!td_count_to_update.length) {
						to_append = '<tr class="' + tr_class + '"><th>' + th_label + '</th><td class="'+ td_class + '" data-value-min="' + value[0] + '" data-value-max="' + value[1] + '">' +  data[data_key] + '</td></tr>';
						methods.appendToTableInPositionForMinMax(to_append, data_table, append_to, append_after, td_class, value, settings.min, settings.max, settings.step);
					} else {
						td_count_to_update.text(data[data_key]);
					}
				}
			}


			if(undefined === data.average_meta) {
				data.average_meta = {
					total_min : avg_total_min,
					total_max : avg_total_max,
					count : avg_count
				};
			} else {
				data.average_meta.total_min += avg_total_min;
				data.average_meta.total_max += avg_total_max;
				data.average_meta.count += avg_count;
			}
			data.average = settings.prefix + methods.number_format(data.average_meta.total_min / data.average_meta.count) + settings.suffix + iptFSQMReport.range_text + settings.prefix + methods.number_format(data.average_meta.total_max / data.average_meta.count) + settings.suffix;
			methods.updateCount(avg_update.find('span.avg'), data.average);
			methods.updateCount(avg_update.find('span.avg_count'), data.average_meta.count);
		},
		populateSliderChart : function(data, title, height, viz_div) {
			var slider_keys = new Array();
			for(var val in data) {
				if(!isNaN(val))
					slider_keys[slider_keys.length] = parseFloat(val);
			}

			slider_keys.sort(function(a,b) {
				return b - a;
			});

			var g_data = new Array();
			g_data[0] = new Array(iptFSQMReport.g_data.ct_label);
			g_data[1] = new Array(iptFSQMReport.g_data.vl_label);
			for(var val in slider_keys) {
				var heading = data[slider_keys[val]] + ' ' + (data[slider_keys[val]] == 0 || data[slider_keys[val]] == 1 ? iptFSQMReport.g_data.sl_head_label_s : iptFSQMReport.g_data.sl_head_label_p);
				g_data[0][g_data[0].length] = heading;
				g_data[1][g_data[1].length] = slider_keys[val];
			}
			g_data[0][g_data[0].length] = iptFSQMReport.g_data.avg;
			g_data[1][g_data[1].length] = data.average;
			//console.log(g_data);

			new google.visualization.BarChart(viz_div).draw(google.visualization.arrayToDataTable(g_data), {
				hAxis : {
					title : iptFSQMReport.g_data.sl_label
				},
				vAxis : {
					title : iptFSQMReport.g_data.ct_label
				},
				title : title,
				backgroundColor : '#f4fcfd',
				height : height,
				tooltip : {isHTML : true}
			});
		},
		populateRangeChart : function(data, title, height, viz_div, settings) {
			var range_keys = new Array();
			for(var range in data) {
				var ranges = range.split(iptFSQMReport.range_text);
				if(ranges.length != 2) {
					continue;
				}
				range_keys[range_keys.length] = ranges;
			}
			range_keys.sort(function(a, b) {
				if(a[0] > b[0]) {
					return -1;
				} else if (a[0] < b[0]) {
					return 1;
				} else {
					if(a[1] > b[1]) {
						return -1;
					} else if (a[1] < b[1]) {
						return 1;
					} else {
						return 0;
					}
				}
			});

			var g_data = new Array();

			for(var val in range_keys) {
				var key = range_keys[val].join(iptFSQMReport.range_text);
				var heading = data[key] + ' ' + (data[key] == 0 || data[key] == 1 ? iptFSQMReport.g_data.sl_head_label_s : iptFSQMReport.g_data.sl_head_label_p);
				g_data[g_data.length] = [heading, parseFloat(settings.min), parseFloat(range_keys[val][0]), parseFloat(range_keys[val][1]), parseFloat(settings.max)];
			}

			new google.visualization.CandlestickChart(viz_div).draw(google.visualization.arrayToDataTable(g_data, true), {
				hAxis : {
					title : iptFSQMReport.g_data.sg_label
				},
				vAxis : {
					title : iptFSQMReport.g_data.ct_label,
					minValue : parseFloat(settings.min),
					maxValue : parseFloat(settings.max),
					viewWindow : {
						max : parseFloat(settings.max),
						min :  parseFloat(settings.min)
					}
				},
				title : title,
				backgroundColor : '#f4fcfd',
				height : height,
				tooltip : {isHTML : true},
				legend : 'none'
			});
		},
		generate_report : function(doing, op) {
			$.post(op.ajaxurl, {
				action : op.action,
				settings : op.settings,
				survey : op.survey.elements,
				feedback : op.feedback.elements,
				wpnonce : op.wpnonce,
				form_id : op.form_id,
				do_data : op.do_data,
				do_data_nonce : op.do_data_nonce,
				doing : doing
			}, function(response) {
				if(response == null) {
					op.loader.find('.ipt_uif_ajax_loader_icon').removeClass('ipt_uif_ajax_loader_spin');
					op.loader.find('.ipt_uif_ajax_loader_text').text('ServerSide Error');
					return;
				}

				var count, other, avg, avg_total, avg_count, difference, g_data, g_key, g_tmp_arr, table_to_update, data_table, viz_table, viz_div, other_table, td_count_to_update, table_to_append, new_tr, to_append;

				//Save the survey
				for(var m_key in op.survey.elements) {
					if(response.survey[m_key] == undefined) {
						continue;
					}
					if(op.survey.data[m_key] == undefined) {
						op.survey.data[m_key] = new Object();
					}
					table_to_update = op.container.find('.ipt_fsqm_report_survey_' + m_key + ' > div > div > table.table_to_update').eq(0);
					data_table = table_to_update.find('td.data table');
					other_table = table_to_update.next('div').find('table.others');
					if(data_table.length == 0) {
						data_table = table_to_update;
					}

					//alert(op.survey.elements[m_key].type);
					switch(op.survey.elements[m_key].type) {
						default :
							if(undefined !== iptFSQMReport.callbacks[op.survey.elements[m_key].type] && typeof(window[iptFSQMReport.callbacks[op.survey.elements[m_key].type]]) == 'function') {
								window[iptFSQMReport.callbacks[op.survey.elements[m_key].type]].apply(this, [op.survey.elements[m_key], op.survey.data[m_key], response.survey[m_key], methods, m_key, table_to_update, data_table, op]);
							}
						break;
						case 'radio' :
						case 'checkbox' :
						case 'select' :
							for(var o_key in response.survey[m_key]) {
								if(o_key == 'others_data') {
									if(response.survey[m_key]['others_data'] != undefined && response.survey[m_key]['others_data'].length) {
										for(var other_data in response.survey[m_key]['others_data']) {
											other = response.survey[m_key]['others_data'][other_data];

											//Append it
											if(op.do_data) {
												table_to_append = other_table.find('tbody');
												table_to_append.find('tr.empty').remove();
												new_tr = $('<tr />');
												new_tr.append('<td>' + other.value + '</td>');
												new_tr.append('<td><a class="thickbox" href="' + op.ajaxurl + '?action=ipt_fsqm_quick_preview&id=' + other.id + '">' + other.name + '</a>');
												new_tr.append('<td>' + other.email + '</td>');
												table_to_append.append(new_tr);
											}
										}

									}
									continue;
								}
								if(op.survey.data[m_key][o_key] == undefined) {
									op.survey.data[m_key][o_key] = 0;
								}
								count = response.survey[m_key][o_key];
								if(!isNaN(count)) {
									op.survey.data[m_key][o_key] += count;
									//Update the tds
									methods.updateCount(data_table.find('td.data_op_' + o_key), op.survey.data[m_key][o_key]);
								}
							}
							break;
						case 'slider' :
							methods.calculateSlider(response.survey[m_key], op.survey.data[m_key], data_table, data_table.find('tbody'), null, 'slider_value_to_count_tr', 'slider_value_to_count', data_table.find('> tfoot > tr > th:first-child'), op.survey.elements[m_key].settings);
							break;
						case 'range' :
							methods.calculateRange(response.survey[m_key], op.survey.data[m_key], data_table, data_table.find('tbody'), null, 'range_value_to_count_tr', 'range_value_to_count', data_table.find('> tfoot > tr > th:first-child'), op.survey.elements[m_key].settings);
							break;
						case 'grading' :
							for(var o_key in response.survey[m_key]) {
								var o_head = data_table.find('tr.grading_' + o_key + '.head');
								// backward compatibility -2.4.0
								if ( typeof( op.survey.elements[m_key].settings.options[o_key] ) !== 'object' ) {
									op.survey.elements[m_key].settings.options[o_key] = {
										label: op.survey.elements[m_key].settings.options[o_key],
										prefix: '',
										suffix: ''
									};
								}
								if(!o_head.length) {
									o_head = $('<tr class="grading_' + o_key + ' head"><th class="head_th" rowspan="1">' + op.survey.elements[m_key].settings.options[o_key].label + '</th><th>' + iptFSQMReport.grading + '</th><th>' + iptFSQMReport.count + '</th></tr>');
									data_table.find('tbody').append(o_head);
									o_head.after('<tr class="grading_' + o_key + '_avg avg head"><th>' + iptFSQMReport.avg + ' <span class="avg_count">0</span> ' + iptFSQMReport.avg_count + '</th><th><span class="avg">0</span></th><th>' + (op.survey.elements[m_key].settings.range ? iptFSQMReport.avg_range : iptFSQMReport.avg_slider) + '</th></tr>');
								}
								if(undefined == op.survey.data[m_key][o_key]) {
									op.survey.data[m_key][o_key] = new Object();
								}

								op.survey.elements[m_key].settings.prefix = op.survey.elements[m_key].settings.options[o_key].prefix;
								op.survey.elements[m_key].settings.suffix = op.survey.elements[m_key].settings.options[o_key].suffix;
								var params = [response.survey[m_key][o_key], op.survey.data[m_key][o_key], data_table, data_table.find('tbody'), o_head, 'grading_' + o_key + '_value_to_count_tr', 'grading_' + o_key + '_value_to_count', data_table.find('tr.grading_' + o_key + '_avg th'), op.survey.elements[m_key].settings];

								if(op.survey.elements[m_key].settings.range) {
									methods.calculateRange.apply(this, params);
								} else {
									methods.calculateSlider.apply(this, params);
								}
								o_head.find('th.head_th').attr('rowspan', data_table.find('tr.grading_' + o_key + '_value_to_count_tr').length + 1);
							}
							break;
						case 'spinners' :
							for(var o_key in response.survey[m_key]) {
								var o_head = data_table.find('tr.spinners_' + o_key + '.head');
								if(!o_head.length) {
									o_head = $('<tr class="spinners_' + o_key + ' head"><th class="head_th" rowspan="1">' + op.survey.elements[m_key].settings.options[o_key] + '</th><th>' + iptFSQMReport.value + '</th><th>' + iptFSQMReport.count + '</th></tr>');
									data_table.find('tbody').append(o_head);
									o_head.after('<tr class="spinners_' + o_key + '_avg avg head"><th>' + iptFSQMReport.avg + ' <span class="avg_count">0</span> ' + iptFSQMReport.avg_count + '</th><th><span class="avg">0</span></th><th>' + (op.survey.elements[m_key].settings.show_range ? iptFSQMReport.avg_range : iptFSQMReport.avg_slider) + '</th></tr>');
								}
								if(undefined == op.survey.data[m_key][o_key]) {
									op.survey.data[m_key][o_key] = new Object();
								}
								var params = [response.survey[m_key][o_key], op.survey.data[m_key][o_key], data_table, data_table.find('tbody'), o_head, 'spinners_' + o_key + '_value_to_count_tr', 'spinners_' + o_key + '_value_to_count', data_table.find('tr.spinners_' + o_key + '_avg th'), op.survey.elements[m_key].settings];

								methods.calculateSlider.apply(this, params);
								o_head.find('th.head_th').attr('rowspan', data_table.find('tr.spinners_' + o_key + '_value_to_count_tr').length + 1);
							}
							break;
						case 'starrating' :
						case 'scalerating' :
							for(var o_key in response.survey[m_key]) {
								var o_head = data_table.find('tr.rating_' + o_key + '.head');
								if(!o_head.length) {
									o_head = $('<tr class="rating_' + o_key + ' head"><th class="head_th" rowspan="1">' + op.survey.elements[m_key].settings.options[o_key] + '</th><th>' + iptFSQMReport.rating + '</th><th>' + iptFSQMReport.count + '</th></tr>');
									data_table.find('tbody').append(o_head);
									o_head.after('<tr class="rating_' + o_key + '_avg avg head"><th>' + iptFSQMReport.avg + ' <span class="avg_count">0</span> ' + iptFSQMReport.avg_count + '</th><th><span class="avg_img"></span></th><th><span class="avg">0</span> ' + (op.survey.elements[m_key].settings.show_range ? iptFSQMReport.avg_range : iptFSQMReport.avg_slider) + '</th></tr>');
								}
								if(undefined == op.survey.data[m_key][o_key]) {
									op.survey.data[m_key][o_key] = new Object();
								}
								var params = [response.survey[m_key][o_key], op.survey.data[m_key][o_key], data_table, data_table.find('tbody'), o_head, 'rating_' + o_key + '_value_to_count_tr', 'rating_' + o_key + '_value_to_count', data_table.find('tr.rating_' + o_key + '_avg th'), op.survey.elements[m_key].settings];

								methods.calculateRating.apply(this, params);
								o_head.find('th.head_th').attr('rowspan', data_table.find('tr.rating_' + o_key + '_value_to_count_tr').length + 1);
							}
							break;

						case 'matrix' :
							for(var r_key in response.survey[m_key]) {
								if(op.survey.data[m_key][r_key] == undefined) {
									op.survey.data[m_key][r_key] = new Object();
								}
								for(var c_key in response.survey[m_key][r_key]) {
									if(!isNaN(response.survey[m_key][r_key][c_key])) {
										if(undefined == op.survey.data[m_key][r_key][c_key]) {
											op.survey.data[m_key][r_key][c_key] = response.survey[m_key][r_key][c_key];
										} else {
											op.survey.data[m_key][r_key][c_key] += response.survey[m_key][r_key][c_key];
										}

										methods.updateCount(data_table.find('td.row_' + r_key + '_col_' + c_key), op.survey.data[m_key][r_key][c_key]);
									}
								}
							}
							break;
						case 'toggle' :
							for(var t_key in response.survey[m_key]) {
								if(!isNaN(response.survey[m_key][t_key])) {
									if(undefined == op.survey.data[m_key][t_key]) {
										op.survey.data[m_key][t_key] = response.survey[m_key][t_key];
									} else {
										op.survey.data[m_key][t_key] += response.survey[m_key][t_key];
									}

									methods.updateCount(data_table.find('td.data_op_' + t_key), op.survey.data[m_key][t_key]);
								}
							}
							break;
						case 'sorting' :
							for(var s_key in response.survey[m_key]) {
								var td_row_span_sorting = methods.object_length(op.survey.elements[m_key].settings.options);
								if(isNaN(response.survey[m_key][s_key])) {
									//Might be other orders
									if(s_key == 'orders' && typeof(response.survey[m_key][s_key]) == 'object') {
										for(var orders in response.survey[m_key][s_key]) {
											if(orders == '') {
												continue;
											}
											if(undefined == op.survey.data[m_key]['orders'][orders]) {
												//Add it
												op.survey.data[m_key]['orders'][orders] = response.survey[m_key][s_key][orders];

												//Append it
												var order = orders.split('-');
												if(data_table.find('tbody tr').length) {
													data_table.find('tbody').append('<tr class="head"><th colspan="3"></th></tr>');
												}
												var sorting_okey_first = true;
												for(var o_key in order) {
													if ( undefined === op.survey.elements[m_key].settings.options[order[o_key]] ) {
														continue;
													}
													to_append = '';
													if(sorting_okey_first == true) {
														sorting_okey_first = false;
														to_append = '<tr><td class="icons">' + iptFSQMReport.sorting_img + '</td><th>' + op.survey.elements[m_key].settings.options[order[o_key]].label + '</th><td rowspan="' + td_row_span_sorting + '" data="' + orders + '"></td></tr>';
													} else {
														to_append = '<tr><td class="icons">' + iptFSQMReport.sorting_img + '</td><th>' + op.survey.elements[m_key].settings.options[order[o_key]].label + '</th>';
													}
													data_table.find('tbody').append(to_append);
												}

											} else {
												//Increase it
												op.survey.data[m_key]['orders'][orders] += response.survey[m_key][s_key][orders];
											}

											//Update it
											methods.updateCount(data_table.find('td[data="' + orders + '"]'), op.survey.data[m_key]['orders'][orders]);

										}
									}
								} else {
									if(undefined == op.survey.data[m_key][s_key]) {
										op.survey.data[m_key][s_key] = response.survey[m_key][s_key];
									} else {
										op.survey.data[m_key][s_key] += response.survey[m_key][s_key];
									}
								}
							}
							break;
					}
				}

				//Save the Feedback
				if(op.do_data) {
					for(var f_key in op.feedback.elements) {
						if(typeof(response.feedback[f_key]) !== 'object') {
							continue;
						}
						table_to_update = op.container.find('.ipt_fsqm_report_feedback_' + f_key + ' > div > div > table');

						table_to_append = table_to_update.find('tbody');
						table_to_append.find('tr.empty').remove();
						switch(op.feedback.elements[f_key].type) {
							default :
								if(undefined !== iptFSQMReport.callbacks[op.feedback.elements[f_key].type] && typeof(window[iptFSQMReport.callbacks[op.feedback.elements[f_key].type]]) == 'function') {
									window[iptFSQMReport.callbacks[op.feedback.elements[f_key].type]].apply(this, [op.feedback.elements[f_key], op.feedback.data[f_key], response.feedback[f_key], methods, f_key, table_to_update, op]);
								}
								break;
							case 'upload' :
								for ( var uploads_key in response.feedback[f_key] ) {
									var uploads = response.feedback[f_key][uploads_key],
									total_uploads = uploads.uploads.length,
									upload_row_span = total_uploads === 0 ? 1 : total_uploads;
									new_tr = $('<tr />');
									new_tr.append( '<th rowspan="' +  upload_row_span + '"><a class="thickbox" href="' + op.ajaxurl + '?action=ipt_fsqm_quick_preview&id=' + uploads.id + '">' + uploads.name + '</a>' );
									new_tr.append('<td rowspan="' + upload_row_span + '">' + uploads.date + '</td>');
									if ( total_uploads === 0 ) {
										new_tr.append('<td rowspan="' + upload_row_span + '">' + iptFSQMReport.noupload + '</td>');
										table_to_append.append(new_tr);
									} else {
										for ( var upload_key in uploads.uploads ) {
											var upload = uploads.uploads[upload_key],
											upload_html = '<a href="' + upload.guid + '" target="_blank" title="' + upload.filename + '">';
											if ( upload.thumb_url !== '' ) {
												upload_html += '<img src="' + upload.thumb_url + '" /><br />';
											}
											upload_html += upload.name + '</a>';
											new_tr.append('<td>' + upload_html + '</td>');
											table_to_append.append(new_tr);
											new_tr = $('<tr />');
										}
									}
								}

								break;
							case 'feedback_large' :
							case 'feedback_small' :
								for(var feedback in response.feedback[f_key]) {
									other = response.feedback[f_key][feedback];
									new_tr = $('<tr />');
									new_tr.append('<th>' + other.value + '</th>');
									new_tr.append('<td><a class="thickbox" href="' + op.ajaxurl + '?action=ipt_fsqm_quick_preview&id=' + other.id + '">' + other.name + '</a>');
									new_tr.append('<td>' + other.email + '</td>');
									new_tr.append('<td>' + other.phone + '</td>');
									new_tr.append('<td>' + other.date + '</td>');
									table_to_append.append(new_tr);
								}
								break;
						}
					}
				}

				op.progress_bar.progressbar('option', 'value', methods.number_format(response.done));
				if(response.done == 100) {
					//Show all hidden containers
					op.container.find('.ipt_fsqm_report_container').show();
					//Init the Google Charts
					var protocol = window.location.protocol;
					$.getScript(protocol + '//www.google.com/jsapi', function() {
						google.load('visualization', '1.0', {
							packages : ['corechart'],
							callback : function() {
								for(var m_key in op.survey.data) {
									table_to_update = op.container.find('.ipt_fsqm_report_survey_' + m_key + ' > div > div > table');
									viz_table = table_to_update.find('td.visualization');
									data_table = table_to_update.find('td.data');
									switch(op.survey.elements[m_key].type) {
										default :
										if(undefined !== iptFSQMReport.gcallbacks[op.survey.elements[m_key].type] && typeof(window[iptFSQMReport.gcallbacks[op.survey.elements[m_key].type]]) == 'function') {
											window[iptFSQMReport.gcallbacks[op.survey.elements[m_key].type]].apply(this, [op.survey.elements[m_key], op.survey.data[m_key], methods, m_key, table_to_update, data_table, op]);
										}
										break;
										case 'radio' :
										case 'checkbox' :
										case 'select' :
											viz_div = document.createElement('div');
											viz_table.append(viz_div);
											g_data = new Array();
											g_data[0] = [iptFSQMReport.g_data.op_label, iptFSQMReport.g_data.ct_label];
											for(var o_key in op.survey.data[m_key]) {
												if(undefined !== op.survey.data[m_key][o_key]) {
													if(o_key == 'others' && undefined !== op.survey.elements[m_key].settings.o_label) {
														g_data[g_data.length] = [op.survey.elements[m_key].settings.o_label, op.survey.data[m_key][o_key]];
													} else if( undefined !== op.survey.elements[m_key].settings.options[o_key] ) {
														g_data[g_data.length] = [op.survey.elements[m_key].settings.options[o_key].label, op.survey.data[m_key][o_key]];
													}
												}
											}
											new google.visualization.PieChart(viz_div).draw(google.visualization.arrayToDataTable(g_data), {
												title : op.survey.elements[m_key].title,
												is3D : true,
												backgroundColor : '#f4fcfd',
												height : 300,
												legend : {position : 'right'},
												tooltip : {isHTML : true}
											});
											break;
										case 'slider' :
											viz_div = document.createElement('div');
											viz_table.append(viz_div);
											methods.populateSliderChart(op.survey.data[m_key], op.survey.elements[m_key].title, 300, viz_div);
											break;
										case 'range' :
											viz_div = document.createElement('div');
											viz_table.append(viz_div);
											var slider_keys = new Array();
											methods.populateRangeChart(op.survey.data[m_key], op.survey.elements[m_key].title, 300, viz_div, op.survey.elements[m_key].settings);
											break;
										case 'grading' :
											for(var o_key in op.survey.data[m_key]) {
												if ( undefined === op.survey.elements[m_key].settings.options[o_key] ) {
													continue;
												}
												var title = typeof(op.survey.elements[m_key].settings.options[o_key]) === 'object' ? op.survey.elements[m_key].settings.options[o_key].label : op.survey.elements[m_key].settings.options[o_key];
												var search = 'grading';
												var new_viz_table = $('<td colspan=""></td>');
												new_viz_table.attr('rowspan', table_to_update.find('tbody tr.' + search + '_' + o_key + '_value_to_count_tr').length + 2);
												table_to_update.find('tbody tr.' + search + '_' + o_key + '.head').prepend(new_viz_table);
												viz_div = document.createElement('div');

												new_viz_table.append(viz_div);
												if(op.survey.elements[m_key].settings.range == true) {
													methods.populateRangeChart(op.survey.data[m_key][o_key], title, 'auto', viz_div, op.survey.elements[m_key].settings);
												} else {
													methods.populateSliderChart(op.survey.data[m_key][o_key], title, 'auto', viz_div);
												}
											}
											break;
										case 'spinners' :
										case 'starrating' :
										case 'scalerating' :
											//viz_table.next('td').attr('colspan', '2').css('width', '100%');
											for(var o_key in op.survey.data[m_key]) {
												if ( undefined === op.survey.elements[m_key].settings.options[o_key] ) {
													continue;
												}
												var title = op.survey.elements[m_key].settings.options[o_key];
												var search = op.survey.elements[m_key].type == 'spinners' ? 'spinners' : 'rating';
												var new_viz_table = $('<td colspan=""></td>');
												new_viz_table.attr('rowspan', table_to_update.find('tbody tr.' + search + '_' + o_key + '_value_to_count_tr').length + 2);
												table_to_update.find('tbody tr.' + search + '_' + o_key + '.head').prepend(new_viz_table);
												viz_div = document.createElement('div');
												new_viz_table.append(viz_div);
												methods.populateSliderChart(op.survey.data[m_key][o_key], title, 'auto', viz_div);
											}
											break;
										case 'matrix' :
											g_data = new Array();
											g_data[0] = new Array('');
											for(var i in op.survey.elements[m_key].settings.columns) {
												g_data[0][g_data[0].length] = op.survey.elements[m_key].settings.columns[i];
											}

											for(var row in op.survey.data[m_key]) {
												if ( undefined === op.survey.elements[m_key].settings.rows[row] ) {
													continue;
												}
												var tmp_arr = new Array(op.survey.elements[m_key].settings.rows[row]);
												for(var col in op.survey.data[m_key][row]) {
													if ( undefined === op.survey.elements[m_key].settings.columns[col] ) {
														continue;
													}
													tmp_arr[tmp_arr.length] = parseFloat(op.survey.data[m_key][row][col]);
												}
												g_data[g_data.length] = tmp_arr;
											}

											viz_div = document.createElement('div');
											viz_table.append(viz_div);
											new google.visualization.BarChart(viz_div).draw(google.visualization.arrayToDataTable(g_data), {
												title : op.survey.elements[m_key].title,
												height : 300,
												backgroundColor : '#f4fcfd'
											});
											break;
										case 'toggle' :
											g_data = new Array();
											g_data[0] = new Array('Label', 'Count');
											g_data[1] = new Array(op.survey.elements[m_key].settings.on, op.survey.data[m_key].on);
											g_data[2] = new Array(op.survey.elements[m_key].settings.off, op.survey.data[m_key].off);

											viz_div = document.createElement('div');
											viz_table.append(viz_div);

											new google.visualization.PieChart(viz_div).draw(google.visualization.arrayToDataTable(g_data), {
												title : op.survey.elements[m_key].title,
												is3D : true,
												backgroundColor : '#f4fcfd',
												height : 300,
												legend : {position : 'right'},
												tooltip : {isHTML : true}
											});
											break;
										case 'sorting' :
											var correct_order = [];
											for(var i in op.survey.elements[m_key].settings.options) {
												correct_order[correct_order.length] = op.survey.elements[m_key].settings.options[i].label;
											}
											g_data = new Array();
											g_data[0] = ['Label', 'Count'];
											g_data[1] = ['Bogus', 0];
											g_data[2] = [iptFSQMReport.g_data.s_presets, {
													v : (undefined !== op.survey.data[m_key].preset ? op.survey.data[m_key].preset : 0),
													f : iptFSQMReport.g_data.s_order + "\n" + correct_order.join("\n")
											}];
											g_data[3] = [iptFSQMReport.g_data.s_others, {
													v : ( undefined !== op.survey.data[m_key].other ? op.survey.data[m_key].other : 0 ),
													f : iptFSQMReport.g_data.s_order_custom
											}];

											viz_div = document.createElement('div');
											viz_table.append(viz_div);

											new google.visualization.PieChart(viz_div).draw(google.visualization.arrayToDataTable(g_data), {
												title : op.survey.elements[m_key].title,
												is3D : true,
												backgroundColor : '#f4fcfd',
												height : 300,
												legend : {position : 'right'},
												tooltip : {isHTML : true}
											});

											g_data = new Array();

											op.survey.data[m_key].orders.sort(function(a, b) {
												return b - a;
											});
											g_data[0] = ['Label', 'Count'];
											g_data[1] = ['Bogus', 0];
											for(var order in op.survey.data[m_key].orders) {
												var s_key = order.split('-');
												var title = [];
												for(var i in s_key) {
													if ( undefined === op.survey.elements[m_key].settings.options[s_key[i]] ) {
														continue;
													}
													title[title.length] = op.survey.elements[m_key].settings.options[s_key[i]].label;
												}
												g_data[g_data.length] = [orders, {
														v : op.survey.data[m_key].orders[order],
														f : iptFSQMReport.g_data.s_order + "\n" + title.join("\n")
												}];
											}

											viz_div = document.createElement('div');
											viz_table.append(viz_div);
											var g_data_table = google.visualization.arrayToDataTable(g_data);

											new google.visualization.PieChart(viz_div).draw(g_data_table, {
												title : iptFSQMReport.g_data.s_breakdown,
												is3D : true,
												backgroundColor : '#f4fcfd',
												height : 300,
												tooltip : {isHTML : true},
												legend : 'none'
											});
											break;
									}
								}
							}
						});
					});
					op.printer.show().find('button').filter('.ipt_fsqm_report_print').on('click', function() {
						op.container.printElement({
							leaveOpen:true,
							printMode:'popup',
							pageTitle : document.title,
							printBodyOptions : {
								classNameToAdd : op.container.parents('.ipt_uif_common').attr('class'),
								styleToAdd : 'padding:10px;margin:10px;background: #fff none;color:#333;font-size:12px;'
							}
						});
					});
					if(typeof($.fn.iptPluginUIFAdmin) == 'function') {
						op.container.iptPluginUIFAdmin('reinitTBAnchors');
						//tb_init('a.thickbox');
					}
					//Hide the ajax loader & Progress bar
					op.loader.hide();
					op.progress_bar.hide();
				} else {
					methods.generate_report.apply(this, [++doing, op]);
				}
			}, 'json').fail(function(jqXHR, textStatus, errorThrown) {
				op.loader.find('.ipt_uif_ajax_loader_inner').removeClass('ipt_uif_ajax_loader_animate');
				op.loader.find('.ipt_uif_ajax_loader_text').text('HTTP ERROR: ' + textStatus + ' ' + errorThrown);
				return;
			});
		}
	};

	$.fn.iptFSQMReport = function(method) {
		if(methods[method]) {
			return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
		} else if (typeof(method) == 'object' || !method) {
			return methods.init.apply(this, arguments);
		} else {
			$.error('Method ' + method + ' does not exist on jQuery.iptFSQMReport');
			return this;
		}
	};
})(jQuery);

