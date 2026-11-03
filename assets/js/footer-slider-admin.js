/**
 * Add/remove rows on the Footer Slider admin page.
 */
(function () {
	'use strict';

	document.addEventListener('DOMContentLoaded', function () {
		var rowsWrap = document.querySelector('[data-sawerna-footer-slider-rows]');
		var addButton = document.querySelector('[data-sawerna-footer-slider-add]');
		var template = document.getElementById('sawerna-footer-slider-row-template');

		if (!rowsWrap || !addButton || !template) {
			return;
		}

		var nextIndex = rowsWrap.querySelectorAll('[data-sawerna-footer-slider-row]').length;

		addButton.addEventListener('click', function () {
			var html = template.innerHTML.replace(/__INDEX__/g, String(nextIndex));
			nextIndex += 1;

			var wrapper = document.createElement('div');
			wrapper.innerHTML = html.trim();

			if (wrapper.firstElementChild) {
				rowsWrap.appendChild(wrapper.firstElementChild);
			}
		});

		rowsWrap.addEventListener('click', function (event) {
			var removeButton = event.target.closest('[data-sawerna-footer-slider-remove]');
			if (!removeButton) {
				return;
			}

			var row = removeButton.closest('[data-sawerna-footer-slider-row]');
			if (row) {
				row.remove();
			}
		});
	});
})();
