(function () {
	document.addEventListener('DOMContentLoaded', function () {
		var list = document.getElementById('sawerna-custom-tabs-list');
		var addButton = document.getElementById('sawerna-custom-tabs-add');

		if (!list || !addButton) {
			return;
		}

		var MAX_TABS = 3;
		var strings = window.sawernaCustomTabsAdmin || {};
		var titleLabel = strings.titleLabel || 'Tab title';
		var removeLabel = strings.removeLabel || 'Remove tab';

		function getEditorSettings() {
			var defaults = (window.wp && wp.editor && typeof wp.editor.getDefaultSettings === 'function')
				? wp.editor.getDefaultSettings()
				: {};

			return {
				tinymce: defaults.tinymce || true,
				quicktags: defaults.quicktags || true,
				mediaButtons: true,
			};
		}

		function refreshAddButtonState() {
			var count = list.querySelectorAll('.sawerna-custom-tab-row').length;
			addButton.disabled = count >= MAX_TABS;
		}

		function removeRow(row) {
			var textarea = row.querySelector('textarea');

			if (textarea && window.wp && wp.editor && typeof wp.editor.remove === 'function') {
				wp.editor.remove(textarea.id);
			}

			row.remove();
			refreshAddButtonState();
		}

		list.addEventListener('click', function (event) {
			var removeBtn = event.target.closest('.sawerna-custom-tab-row__remove');

			if (!removeBtn) {
				return;
			}

			event.preventDefault();

			var row = removeBtn.closest('.sawerna-custom-tab-row');

			if (row) {
				removeRow(row);
			}
		});

		addButton.addEventListener('click', function (event) {
			event.preventDefault();

			var count = list.querySelectorAll('.sawerna-custom-tab-row').length;

			if (count >= MAX_TABS) {
				return;
			}

			var key = 'new' + Date.now();
			var titleId = 'sawerna_custom_tab_title_' + key;
			var contentId = 'sawerna_custom_tab_content_' + key;

			var row = document.createElement('div');
			row.className = 'sawerna-custom-tab-row';
			row.setAttribute('data-tab-key', key);
			row.innerHTML =
				'<p class="sawerna-custom-tab-row__title-field">' +
					'<label for="' + titleId + '">' + titleLabel + '</label>' +
					'<input type="text" class="widefat" id="' + titleId + '" name="sawerna_custom_tabs[' + key + '][title]" value="">' +
				'</p>' +
				'<textarea id="' + contentId + '" name="sawerna_custom_tabs[' + key + '][content]"></textarea>' +
				'<p><button type="button" class="button-link-delete sawerna-custom-tab-row__remove">' + removeLabel + '</button></p>';

			list.appendChild(row);

			if (window.wp && wp.editor && typeof wp.editor.initialize === 'function') {
				wp.editor.initialize(contentId, getEditorSettings());
			}

			refreshAddButtonState();
		});
	});
})();
