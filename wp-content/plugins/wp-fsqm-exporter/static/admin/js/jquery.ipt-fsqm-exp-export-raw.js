/**
 * IPT FSQM Export RAW
 *
 * @package IPT FSQM Export
 * @subpackage jQuery Plugin
 * @author Swashata Ghosh <swashata@intechgrity.com>
 */

(function($) {
	var methods = {
		init: function(options) {
			var settings = $.extend(true, {
				mcq : {},
				freetype : {},
				pinfo : {},
				settings : {},
				wpnonce : '',
				ajaxurl : '',
				action : 'ipt_fsqm_exp_raw_csv',
				raw_id : null,
				raw_nonce : ''
			}, options);

			return this.each(function() {
				methods.populate_csv.apply(this, [settings]);
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
		populate_csv : function(op) {
			op.progress_bar = $(this).find('.ipt_fsqm_exp_raw_progressbar');
			op.loader = $(this).find('.ipt_fsqm_exp_raw_ajax');
			op.button = $(this).find('#ipt_fsqm_exp_raw_button_container_' + op.settings.form_id).hide();
			methods.generate_csv.apply(this, [0, op]);
		},
		generate_csv : function(doing, op) {
			$.post(op.ajaxurl, {
				action : op.action,
				mcq : op.mcq,
				freetype : op.freetype,
				pinfo : op.pinfo,
				settings : op.settings,
				wpnonce : op.wpnonce,
				raw_id : op.raw_id,
				raw_nonce : op.raw_nonce,
				doing : doing
			}, function(response) {
				if (response === null) {
					op.loader.find('.ipt_uif_ajax_loader_inner').removeClass('ipt_uif_ajax_loader_animate');
					op.loader.find('.ipt_uif_ajax_loader_text').text('Server Side Error');
					return;
				}
				if (response.type === 'success') {
					op.progress_bar.progressbar('option', 'value', methods.number_format(response.done));
					op.raw_id = response.raw_id;
					op.raw_nonce = response.raw_nonce;
					if (response.done == 100) {
						op.loader.find('.ipt_uif_ajax_loader_inner').removeClass('ipt_uif_ajax_loader_animate');
						op.loader.find('.ipt_uif_ajax_loader_text').text(op.loader.data('done'));
						op.button.slideDown('fast').find('a').attr('href', response.download_url);
						op.progress_bar.delay(1000).slideUp('fast');
					} else {
						methods.generate_csv.apply(this, [++doing, op]);
					}
				} else {
					op.loader.find('.ipt_uif_ajax_loader_inner').removeClass('ipt_uif_ajax_loader_animate');
					op.loader.find('.ipt_uif_ajax_loader_text').text('Error');
					$(this).append($(response.html));
				}
			}, 'json').fail(function(jqXHR, textStatus, errorThrown) {
				op.loader.find('.ipt_uif_ajax_loader_inner').removeClass('ipt_uif_ajax_loader_animate');
				op.loader.find('.ipt_uif_ajax_loader_text').text('AJAX ERROR: ' + textStatus + ' ' + errorThrown);
				return;
			});
		}
	};

	$.fn.iptFSQMEXPRaw = function(method) {
		if(methods[method]) {
			return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
		} else if (typeof(method) == 'object' || !method) {
			return methods.init.apply(this, arguments);
		} else {
			$.error('Method ' + method + ' does not exist on jQuery.iptFSQMEXPRaw');
			return this;
		}
	};
})(jQuery);
