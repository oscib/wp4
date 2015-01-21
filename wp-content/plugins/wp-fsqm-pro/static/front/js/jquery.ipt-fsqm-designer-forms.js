/**
 * Designer Forms
 * Special JS
 * @author Swashata Ghosh
 * @since v2.4.0
 */
(function($) {
	var Plugin = function( elm ) {
		// Init the variables
		this.elm = $(elm);
		this.progressBar = this.elm.find('.ipt_fsqm_main_pb'),
		this.mainTabs = this.elm.find('.ipt_fsqm_main_tab');
		this.tabIndices = this.mainTabs.find('> ul > li');
		this.steps = this.tabIndices.length;
		this.newPB = $('<div/>', {
			'class' : 	'ipt_fsqm_designer_pb',
			'html'  : 	'<div class="ipt_fsqm_designer_pb_inner">' +
							'<div class="ipt_fsqm_designer_pb_outerline"></div>' +
							'<div class="ipt_fsqm_designer_pb_innerline" style="width: 0px"></div>' +
						'</div>'
		});
		this.totalWidth = 0;
		this.circleWidth = 70;
		this.circleOffset = 50;
		this.containerPadding = 10;
		this.tabSettings = this.mainTabs.data('settings');

		// Don't do anything if no progressbar
		if ( ! this.tabSettings || this.tabSettings['show-progress-bar'] != true ) {
			return;
		}

		// Initialize the plugin
		this.init();
	};
	Plugin.prototype = {
		/**
		 * Init
		 */
		init: function() {
			var i, left, innerPB = '';
			// Creathe new progress bar
			for ( i = 0; i < this.steps; i++ ) {
				left = i * ( this.circleWidth + this.circleOffset ) + this.containerPadding;
				innerPB +=	'<div class="ipt_fsqm_designer_pb_circleouter pb_circleouter_' + i + '" style="left: ' + left + 'px;"></div>' +
							'<div class="ipt_fsqm_designer_pb_circleinner" style="left: ' + (left + 10) + 'px;">' +
								'<div class="ipt_fsqm_designer_pb_circletext" title="' + ( i + 1 ) + '">' + ( i + 1 ) + '</div>' +
								'<div class="ipt_fsqm_designer_pb_circlecheck">' +
									'<i class="ipt-icomoon-checkmark ipticm"></i>' +
								'</div>' +
							'</div>';
			}

			// Calculate the totalWidth and set it
			this.totalWidth = ( i ) * ( this.circleWidth + this.circleOffset ) - this.circleOffset + this.containerPadding * 2; // Total width - last left offset  + padding left + padding right
			this.newPB.find('.ipt_fsqm_designer_pb_inner').append( innerPB ).css({
				width: this.totalWidth + 'px'
			});

			// Append the new progress bar and hide the old one
			this.progressBar.before( this.newPB ).css({
				visibility: 'hidden',
				marginTop: '-4em',
				zIndex: -1,
				position: 'relative'
			});

			// Attach the event on tab change
			this.mainTabs.on('tabsactivate', $.proxy(function(event, ui) {
				this.updateProgressBar( ui.newTab );
			}, this));

			// Attach the event on window resize
			$(window).on('resize', $.proxy(function() {
				this.updateProgressBar();
			}, this));

			// Fire it for the first time - on tab load
			this.mainTabs.on('tabscreate', $.proxy(function(event, ui) {
				this.updateProgressBar( ui.tab );
			}, this));
		},

		/**
		 * Update progressbar
		 */
		updateProgressBar: function( newTab ) {
			var activeTabIndex = 0;
			// If newTab is not set, then find it
			if ( newTab === undefined ) {
				newTab = this.tabIndices.filter('[aria-selected="true"]');
			}

			if ( newTab && newTab.length ) {
				// Get the index
				activeTabIndex = this.tabIndices.index( newTab );
			}

			// Get the new width for the innerline
			var activatedWidth = activeTabIndex * ( this.circleWidth + this.circleOffset ) - this.circleOffset / 2 - this.containerPadding;
			if ( activatedWidth < 0 ) {
				activatedWidth = 0;
			}

			// Set the width of the innerline
			this.newPB.find('.ipt_fsqm_designer_pb_innerline').width( activatedWidth );

			// Loop through and set done for previous tabs
			for ( var i = 0; i < this.steps; i++ ) {
				if ( i < activeTabIndex ) {
					this.newPB.find('.pb_circleouter_' + i).addClass('pb_done');
				} else {
					this.newPB.find('.pb_circleouter_' + i).removeClass('pb_done');
				}
			}

			// Now position left or right accordingly
			var availableWidth = this.newPB.width();
			if ( this.totalWidth > availableWidth ) {
				// Calculate the offset
				// The active one should be on left with an offset of circleOffset / 2
				// Put simply, it is activatedWidth - circleWidth - circleOffset
				var newLeftOffset = activatedWidth - this.circleWidth - this.circleOffset;
				if ( newLeftOffset < 0 ) {
					newLeftOffset = 0;
				}
				this.newPB.find('.ipt_fsqm_designer_pb_inner').stop(true).animate({
					left: -1 * ( newLeftOffset )
				}, 500);
			} else {
				this.newPB.find('.ipt_fsqm_designer_pb_inner').stop(true).animate({
					left: 0
				}, 500);
			}
		}
	};
	var methods = {
		init: function(options) {
			return this.each(function() {
				var themeID = $(this).data('ui-theme-id'),
				type = $(this).data('ui-type');
				// Match class
				if ( themeID.search(/designer\-/g) === -1 ) {
					return;
				}
				$(this).addClass('ipt-fsqm-designer-themes');
				// Match type
				if ( type !== 2 ) {
					return;
				}

				// Init the plugin and store the data
				$(this).data( 'ipt_fsqm_designer_form', new Plugin(this) );
			});
		}
	};

	$.fn.iptFSQMDesignerForm = function(method) {
		if(methods[method]) {
			return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
		} else if (typeof(method) == 'object' || !method) {
			return methods.init.apply(this, arguments);
		} else {
			$.error('Method ' + method + ' does not exist on jQuery.iptFSQMDesignerForm');
			return this;
		}
	};
})(jQuery);
jQuery(document).ready(function($) {
	$('.ipt_fsqm_form').iptFSQMDesignerForm();
});
