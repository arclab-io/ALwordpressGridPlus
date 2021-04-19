var GF_PFUI = GF_PFUI || {};
(function ($) {
	"use strict";
	GF_PFUI = {
		init : function() {
			GF_PFUI.gallery();
			GF_PFUI.switchTab();
			setTimeout(function () {
				$('.editor-post-format select').trigger('change');
				$('[name="post_format"]:checked').trigger('change')
			},1000);

			$(document).on('change','.editor-post-format select',function (event) {
				GF_PFUI.switch_post_format_content($(this).val());
			});

			$('[name="post_format"]').on('change',function(){
				GF_PFUI.switch_post_format_content($(this).val());
			});


		},
		switch_post_format_content : function($post_format) {
			$('.tab-pane','.gf-post-formats-ui-tabs').removeClass('active');
			$('#tab-post-format-' + $post_format).addClass('active');
		},
		gallery: function() {
			$('.sf-field-gallery-inner','.gf-post-formats-ui-tabs').each(function () {
				var field = new SF_GalleryClass($(this));
				field.init();
			});
		},
		switchTab : function() {
			$('.gf-post-formats-ui-tabs .tab-nav a').on('click',function(event){
				event.preventDefault();
				if ($(this).hasClass('active')) return;
				var tabId = $(this).attr('href').replace('#',''),
					$wrap = $(this).closest('.gf-post-formats-ui-tabs'),
					format = $(this).data('format');

				$('.tab-nav a',$wrap).removeClass('active');
				$('.tab-pane',$wrap).removeClass('active');

				$(this).addClass('active');
				$('[id="tab-'+ tabId +'"]',$wrap).addClass('active');

				if ($('#' + tabId).length) {
					$('#' + tabId).trigger('click');
				} else {
					$('[name="gsf_post_format"]').val(format);
				}
			});

			$('[name="post_format"]').on('change',function(){
				var format = $(this).val();
				$('a[href="#post-format-'+ format +'"]').trigger('click');

			});

			/**
			 * Hide format box
			 */
			var $formatToggle = $('[name="formatdiv-hide"]');
			if ($formatToggle.attr('checked') == 'checked') {
				$formatToggle.click();
			}
		}
	};
	$(document).ready(GF_PFUI.init);
})(jQuery);
