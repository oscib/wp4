/**
 * IPT FSQM Export Report
 *
 * @package IPT FSQM Export
 * @subpackage jQuery Plugin
 * @author Swashata Ghosh <swashata@intechgrity.com>
 */

(function($) {
	var methods = {
		init: function(options) {
			return this.each(function() {
				var op = $.extend(true, {
					settings: {},
					survey: {},
					feedback: {},
					wpnonce: '',
					ajaxurl: {},
					form_id: 0
				}, options);
				methods.populate_report.apply(this, [op]);
			});
		},
		number_format : function(num) {
			var number = parseFloat(num);
			if(isNaN(number)) {
				return num;
			} else {
				return (parseInt(num * 100, 10) / 100);
			}
		},
		populate_report : function(op) {
			op.progress_bar = $(this).find('.ipt_fsqm_exp_export_report_progressbar'),
			op.loader = $(this).find('.ipt_fsqm_exp_export_report_al'),
			op.buttons = $(this).find('.ipt_uif_button_container').hide(),
			op.exp_nonce = '',
			op.exp_id = 0;
			methods.generate_report.apply(this, [0, op]);
		},
		generate_report : function(doing, op) {
			$.post(op.ajaxurl, {
				action : 'ipt_fsqm_exp_export_report',
				settings : op.settings,
				survey : op.survey.elements,
				feedback : op.feedback.elements,
				wpnonce : op.wpnonce,
				form_id : op.form_id,
				doing : doing,
				exp_nonce : op.exp_nonce,
				exp_id : op.exp_id
			}, function(response) {
				if(response === null || response === '') {
					op.loader.find('.ipt_uif_ajax_loader_inner').removeClass('ipt_uif_ajax_loader_animate');
					op.loader.find('.ipt_uif_ajax_loader_text').text('ServerSide Error');
					return;
				}

				op.progress_bar.progressbar('option', 'value', methods.number_format(response.done));
				if(response.done == 100) {
					op.progress_bar.delay(1000).slideUp('fast');
					op.loader.find('.ipt_uif_ajax_loader_inner').removeClass('ipt_uif_ajax_loader_animate');
					op.loader.find('.ipt_uif_ajax_loader_text').text(op.loader.data('done'));
					if(typeof(response.links === 'object')) {
						for(var format in response.links) {
							op.buttons.find('a.ipt_fsqm_exp_export_button_' + format).attr('href', response.links[format]);
						}
					}
					op.buttons.slideDown('fast');
				} else {
					op.exp_nonce = response.exp_nonce;
					op.exp_id = response.exp_id;
					methods.generate_report.apply(this, [++doing, op]);
				}
			}, 'json').fail(function(jqXHR, textStatus, errorThrown) {
				op.loader.find('.ipt_uif_ajax_loader_inner').removeClass('ipt_uif_ajax_loader_animate');
				op.loader.find('.ipt_uif_ajax_loader_text').text('AJAX ERROR: ' + textStatus + ' ' + errorThrown);
				return;
			});
		}
	};

	$.fn.iptFSQMEXPExportReport = function(method) {
		if(methods[method]) {
			return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
		} else if (typeof(method) === 'object' || !method) {
			return methods.init.apply(this, arguments);
		} else {
			$.error('Method ' + method + ' does not exist on jQuery.iptFSQMEXPExportReport');
			return this;
		}
	};
})(jQuery);
