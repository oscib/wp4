(function() {
	tinymce.create('tinymce.plugins.iptFSQM', {
		init : function(ed, url) {
			var image_url = url + '/../images/fsqm-editor-button.png',
			retina = window.devicePixelRatio > 1;
			if(retina) {
				image_url = url + '/../images/fsqm-editor-button@2x.png';
			}
			ed.addButton('ipt_fsqm_shortcode_insert', {
				title : 'Insert Shortcodes for WP Feedback, Survey & Quiz Manager - Pro',
				cmd : 'ipt_fsqm_shortcode_insert',
				image : image_url
			});

			ed.addCommand('ipt_fsqm_shortcode_insert', function() {
				tb_show('Insert WP Feedback, Survey & Quiz Manager - Pro Shortcodes', ajaxurl + '?action=ipt_fsqm_shortcode_insert');
			});

			jQuery(document).on('ipt_fsqm_shortcode_insert', function(e, cb) {
				if(typeof(cb) == 'function') {
					cb.call(this, ed);
				}
				tb_remove();
				//jQuery(document).off('ipt_fsqm_shortcode_insert');
			});

			jQuery(document).on('click', '#ipt_fsqm_shortcode_wizard_back', function(e) {
				e.preventDefault();
				var href = jQuery(this).attr('href');
				var title = jQuery(this).attr('title');
				//tb_remove();
				tb_show(title, href);
				return false;
			});
		},
		// Meta info method
		getInfo : function() {
			return {
				longname : 'WP Feedback, Survey & Quiz Manager - Pro',
				author : 'iPanelThemes',
				authorurl : 'http://ipanelthemes.com/',
				infourl : 'http://ipanelthemes.com/fsqm/',
				version : "2.1.8"
			};
		}

	});
	tinymce.PluginManager.add('iptFSQM', tinymce.plugins.iptFSQM);
})();

