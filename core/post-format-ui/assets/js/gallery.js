/**
 * Define class field
 */
var SF_GalleryClass = function($container) {
	this.$container = $container;
};
(function ($) {
	"use strict";
	/**
	 * Define class field prototype
	 */
	SF_GalleryClass.prototype = {
		init: function() {
			this.select();
			this.remove();
			this.sortable();
		},
		select: function () {
			var _media = new SFMedia(),
				$addButton = this.$container.find('.sf-gallery-add');
			_media.selectGallery($addButton, {filter: 'image'}, function(attachments) {
				if (attachments.length) {
					var $this = $(_media.clickedButton);
					var $parent = $this.parent();
					var $input = $parent.find('input[type="hidden"]');
					var valInput = $input.val();
					var arrInput = valInput.split('|');
					var imgHtml = '';
					attachments.each(function(attachment) {
						attachment = attachment.toJSON();

						if (arrInput.indexOf('' + attachment.id) != -1) {
							return;
						}
						if (valInput != '') {
							valInput += '|' + attachment.id;
						}
						else {
							valInput = '' + attachment.id;
						}
						arrInput.push('' + attachment.id);

						imgHtml += '<div class="sf-image-preview" data-id="' + attachment.id + '">';
						imgHtml +='<div class="centered">';
						imgHtml += '<img src="' + attachment.sizes.thumbnail.url + '" alt=""/>';
						imgHtml += '</div>';
						imgHtml += '<span class="sf-gallery-remove dashicons dashicons dashicons-no-alt"></span>';
						imgHtml += '</div>';
					});
					$input.val(valInput);
					$this.before(imgHtml);
					$this.trigger('sf-gallery-selected');
				}
			});
		},
		remove: function() {
			this.$container.on('click', '.sf-gallery-remove', function() {
				var $this = $(this).parent();
				var $parent = $this.parent();
				var $input = $parent.find('input[type="hidden"]');
				$this.remove();
				var valInput = '';
				$('.sf-image-preview', $parent).each(function() {
					if (valInput != '') {
						valInput += '|' + $(this).data('id');
					}
					else {
						valInput = '' + $(this).data('id');
					}
				});
				$input.val(valInput);
				$parent.trigger('sf-gallery-removed');
			});
		},
		sortable: function () {
			this.$container.sortable({
				placeholder: "sf-gallery-sortable-placeholder",
				items: '.sf-image-preview',
				update: function( event, ui ) {
					var $wrapper = $(event.target);
					var valInput = '';
					$('.sf-image-preview', $wrapper).each(function() {
						if (valInput != '') {
							valInput += '|' + $(this).data('id');
						}
						else {
							valInput = '' + $(this).data('id');
						}
					});
					var $input = $wrapper.find('input[type="hidden"]');
					$input.val(valInput);
					$wrapper.trigger('sf-gallery-sortable-updated');
				}
			});
		}
	};
})(jQuery);
