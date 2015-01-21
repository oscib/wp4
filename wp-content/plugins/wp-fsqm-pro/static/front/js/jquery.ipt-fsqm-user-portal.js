
(function($) {
	var methods = {
		init: function() {
			return this.each(function() {
				var _self = this;
				var primary_css = {
					id : 'ipt_fsqm_up_primary_css',
					src : iptFSQMUP.location + 'css/user-portal.css?version=' + iptFSQMUP.version
				};
				$(this).iptPluginUIFFront({
					callback : function() {
						methods.initDataTable.apply(_self);
					},
					additionalThemes : [primary_css]
				});
			});
		},

		//Other methods
		number_format : function(num) {
			var number = parseFloat(num);
			if(isNaN(number)) {
				return num;
			} else {
				return (parseInt(num * 100) / 100);
			}
		},
		initDataTable : function() {
			var _self = this,
			op = {
				settings : $(this).data('settings'),
				nonce : $(this).data('nonce'),
				progressbar : $(this).find('.ipt_fsqm_up_pb'),
				ajaxloader : $(this).find('.ipt_fsqm_up_al'),
				ajaxurl : $(this).data('ajaxurl')
			};

			// Clear the HTML
			$(this).find('.ipt_fsqm_up_table tbody').html('');

			// Call the fetchData recursively
			methods.fetchData.apply(_self, [0, op]);
		},

		fetchData : function(doing, op) {
			var _self = this;
			$.post(op.ajaxurl, {
				action : 'ipt_fsqm_user_portal',
				settings : op.settings,
				_wpnonce : op.nonce,
				doing : doing
			}, function(response) {
				if ( response == null ) {
					op.ajaxloader.find('.ipt_uif_ajax_loader_inner').removeClass('ipt_uif_ajax_loader_animate');
					op.ajaxloader.find('.ipt_uif_ajax_loader_text').text(iptFSQMUP.ajax.null_response + ' ' + iptFSQMUP.ajax.advice);
				}
				if ( response.success == true ) {
					op.progressbar.progressbar('option', 'value', methods.number_format(response.done));
					$(_self).find('.ipt_fsqm_up_table tbody').append(response.html);

					if ( response.done == 100 ) {
						methods.applyDataTable.apply(_self, [op]);
					} else {
						methods.fetchData.apply(_self, [++doing, op]);
					}
				} else {
					op.ajaxloader.find('.ipt_uif_ajax_loader_inner').removeClass('ipt_uif_ajax_loader_animate');
					op.ajaxloader.find('.ipt_uif_ajax_loader_text').text(response.error_msg);
				}

			}, 'json').fail(function(jqXHR, textStatus, errorThrown) {
				op.ajaxloader.find('.ipt_uif_ajax_loader_inner').removeClass('ipt_uif_ajax_loader_animate');
				op.ajaxloader.find('.ipt_uif_ajax_loader_text').text(iptFSQMUP.ajax.ajax_error + ' (' + textStatus + ' ' + errorThrown + ') ' + iptFSQMUP.ajax.advice);
			});
		},
		applyDataTable : function(op) {
			var _self = this;
			$(this).find('.ipt_fsqm_up_table').iptPluginUIFFront({
				callback : function() {
					$(_self).find('.ipt_fsqm_up_table').show().dataTable({
						"bJQueryUI": true,
						"oLanguage" : iptFSQMUP.l10n,
						"sPaginationType": "full_numbers",
						"aaSorting" : [[1, "desc"]],
						"bProcessing" : true,
						"aLengthMenu" : [[10, 30, 60, -1], [10, 30, 60, iptFSQMUP.allLabel]],
						"iDisplayLength" : 30
					}).addClass('ui-widget-content');
					$('.dataTables_filter input[type="text"]').addClass('ipt_uif_text');
					$('.dataTables_length select').addClass('ipt_uif_select');
					op.progressbar.hide();
					op.ajaxloader.hide();
				}
			});
		}
	};

	$.fn.iptFSQMUserPortal = function(method) {
		if(methods[method]) {
			return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
		} else if (typeof(method) == 'object' || !method) {
			return methods.init.apply(this, arguments);
		} else {
			$.error('Method ' + method + ' does not exist on jQuery.iptFSQMUserPortal');
			return this;
		}
	};
})(jQuery);

jQuery(document).ready(function($) {
	$('.ipt_fsqm_user_portal').iptFSQMUserPortal();
});
