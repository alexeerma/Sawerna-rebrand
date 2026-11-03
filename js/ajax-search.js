/**
 * Sawerna AJAX search dropdown.
 */
(function() {
	'use strict';

	const settings = window.sawernaAjaxSearch || {};
	const forms = document.querySelectorAll('[data-sawerna-search-form]');

	if (!forms.length || !settings.ajaxUrl || !settings.nonce) {
		return;
	}

	const minChars = Number(settings.minChars || 2);
	const debounceDelay = Number(settings.debounce || 220);
	const labels = {
		viewAll: settings.viewAllLabel || '',
		loading: settings.loadingLabel || '',
		noResults: settings.noResultsLabel || '',
		error: settings.errorLabel || ''
	};
	const cache = new Map();
	let openPanel = null;

	function createElement(tag, className, text) {
		const element = document.createElement(tag);

		if (className) {
			element.className = className;
		}

		if (typeof text === 'string') {
			element.textContent = text;
		}

		return element;
	}

	function setPanelOpen(input, panel, isOpen) {
		if (isOpen) {
			panel.removeAttribute('hidden');
			input.setAttribute('aria-expanded', 'true');
			openPanel = panel;
			return;
		}

		panel.setAttribute('hidden', '');
		input.setAttribute('aria-expanded', 'false');

		if (openPanel === panel) {
			openPanel = null;
		}
	}

	function renderState(panel, message) {
		panel.innerHTML = '';
		panel.appendChild(createElement('div', 'sawerna-ajax-search__state', message));
	}

	function buildResultItem(item, index) {
		const link = createElement('a', 'sawerna-ajax-search__item');
		link.href = item.url || '#';
		link.setAttribute('role', 'option');
		link.setAttribute('data-sawerna-search-option', String(index));
		link.setAttribute('aria-selected', 'false');

		const media = createElement('span', 'sawerna-ajax-search__media');

		if (item.image) {
			const image = document.createElement('img');
			image.src = item.image;
			image.alt = '';
			image.loading = 'lazy';
			image.decoding = 'async';
			media.appendChild(image);
		}

		const body = createElement('span', 'sawerna-ajax-search__body');
		const title = createElement('span', 'sawerna-ajax-search__title', item.title || '');
		const meta = createElement('span', 'sawerna-ajax-search__meta', item.meta || '');

		body.appendChild(title);
		body.appendChild(meta);

		if (item.price_html) {
			const price = createElement('span', 'sawerna-ajax-search__price');
			price.innerHTML = item.price_html;
			body.appendChild(price);
		}

		link.appendChild(media);
		link.appendChild(body);

		return link;
	}

	function setActiveOption(options, activeIndex) {
		options.forEach(function(option, index) {
			option.classList.toggle('is-active', index === activeIndex);
			option.setAttribute('aria-selected', index === activeIndex ? 'true' : 'false');
		});
	}

	function renderResults(input, panel, payload) {
		panel.innerHTML = '';

		const results = Array.isArray(payload.results) ? payload.results : [];

		if (!results.length) {
			renderState(panel, labels.noResults);
			setPanelOpen(input, panel, true);
			return;
		}

		const list = createElement('div', 'sawerna-ajax-search__list');
		list.setAttribute('role', 'listbox');

		results.forEach(function(item, index) {
			list.appendChild(buildResultItem(item, index));
		});

		panel.appendChild(list);

		if (payload.search_url) {
			const footer = createElement('a', 'sawerna-ajax-search__footer', labels.viewAll);
			footer.href = payload.search_url;
			panel.appendChild(footer);
		}

		setActiveOption(Array.from(panel.querySelectorAll('[data-sawerna-search-option]')), -1);
		setPanelOpen(input, panel, true);
	}

	function fetchResults(term, controller) {
		const params = new URLSearchParams({
			action: 'sawerna_ajax_search',
			nonce: settings.nonce,
			term: term
		});

		return fetch(`${settings.ajaxUrl}?${params.toString()}`, {
			method: 'GET',
			credentials: 'same-origin',
			signal: controller.signal
		})
			.then(function(response) {
				if (!response.ok) {
					throw new Error('Search request failed');
				}

				return response.json();
			})
			.then(function(response) {
				if (!response || !response.success) {
					throw new Error('Search response failed');
				}

				cache.set(term, response.data || {});
				return response.data || {};
			});
	}

	forms.forEach(function(form) {
		const input = form.querySelector('[data-sawerna-search-input]');
		const panel = form.querySelector('[data-sawerna-search-results]');

		if (!input || !panel) {
			return;
		}

		let debounceTimer = null;
		let controller = null;
		let activeIndex = -1;

		function close() {
			activeIndex = -1;
			setPanelOpen(input, panel, false);
		}

		function search() {
			const term = input.value.trim();

			window.clearTimeout(debounceTimer);
			activeIndex = -1;

			if (controller) {
				controller.abort();
				controller = null;
			}

			if (term.length < minChars) {
				close();
				panel.innerHTML = '';
				return;
			}

			debounceTimer = window.setTimeout(function() {
				if (cache.has(term)) {
					renderResults(input, panel, cache.get(term));
					return;
				}

				controller = new AbortController();
				renderState(panel, labels.loading);
				setPanelOpen(input, panel, true);

				fetchResults(term, controller)
					.then(function(payload) {
						renderResults(input, panel, payload);
					})
					.catch(function(error) {
						if (error.name === 'AbortError') {
							return;
						}

						renderState(panel, labels.error);
						setPanelOpen(input, panel, true);
					});
			}, debounceDelay);
		}

		input.addEventListener('input', search);

		input.addEventListener('focus', function() {
			if (panel.innerHTML.trim() && input.value.trim().length >= minChars) {
				setPanelOpen(input, panel, true);
			}
		});

		input.addEventListener('keydown', function(event) {
			const options = Array.from(panel.querySelectorAll('[data-sawerna-search-option]'));

			if (event.key === 'Escape') {
				close();
				return;
			}

			if (!options.length || panel.hasAttribute('hidden')) {
				return;
			}

			if (event.key === 'ArrowDown') {
				event.preventDefault();
				activeIndex = activeIndex < options.length - 1 ? activeIndex + 1 : 0;
				setActiveOption(options, activeIndex);
				options[activeIndex].scrollIntoView({ block: 'nearest' });
			}

			if (event.key === 'ArrowUp') {
				event.preventDefault();
				activeIndex = activeIndex > 0 ? activeIndex - 1 : options.length - 1;
				setActiveOption(options, activeIndex);
				options[activeIndex].scrollIntoView({ block: 'nearest' });
			}

			if (event.key === 'Enter' && activeIndex >= 0 && options[activeIndex]) {
				event.preventDefault();
				window.location.href = options[activeIndex].href;
			}
		});

		document.addEventListener('click', function(event) {
			if (!form.contains(event.target)) {
				close();
			}
		});
	});

	document.addEventListener('keydown', function(event) {
		if (event.key === 'Escape' && openPanel) {
			const form = openPanel.closest('[data-sawerna-search-form]');
			const input = form ? form.querySelector('[data-sawerna-search-input]') : null;

			if (input) {
				setPanelOpen(input, openPanel, false);
			}
		}
	});
})();
