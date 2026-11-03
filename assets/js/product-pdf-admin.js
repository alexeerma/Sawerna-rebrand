/**
 * Media picker for the Sawerna product/variation PDF fields.
 */
(function ($) {
	'use strict';

	function openPicker($field) {
		var frame = wp.media({
			title: (window.sawernaPdfAdmin && sawernaPdfAdmin.uploadTitle) || 'Select PDF',
			library: { type: 'application/pdf' },
			multiple: false,
			button: { text: (window.sawernaPdfAdmin && sawernaPdfAdmin.uploadButton) || 'Use this PDF' },
		});

		frame.on('select', function () {
			var attachment = frame.state().get('selection').first().toJSON();
			$field.find('.sawerna-pdf-field__id').val(attachment.id);
			$field.find('.sawerna-pdf-field__filename').text(attachment.filename || attachment.title || '');
			$field.find('.sawerna-pdf-field__remove').show();
		});

		frame.open();
	}

	$(document).on('click', '[data-sawerna-pdf-field] .sawerna-pdf-field__select', function (e) {
		e.preventDefault();
		openPicker($(this).closest('[data-sawerna-pdf-field]'));
	});

	$(document).on('click', '[data-sawerna-pdf-field] .sawerna-pdf-field__remove', function (e) {
		e.preventDefault();
		var $field = $(this).closest('[data-sawerna-pdf-field]');
		$field.find('.sawerna-pdf-field__id').val('');
		$field.find('.sawerna-pdf-field__filename').text('');
		$(this).hide();
	});
})(jQuery);
