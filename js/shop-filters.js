(function () {
  'use strict';

  var config = window.sawernaShopFilters || null;
  if (!config) {
    return;
  }

  var app = document.querySelector('[data-shop-root="1"]');
  if (!app) {
    return;
  }
  app.classList.add('has-shop-filters-js');

  var state = normalizeState(config.state || {});
  var draftState = normalizeState(config.state || {});
  var context = config.context || {};
  var request = null;
  var mobileMedia = window.matchMedia('(max-width: 1024px)');

  function normalizeState(next) {
    var normalized = {
      per_page: toInt(next.per_page, 24),
      columns: toInt(next.columns, 3),
      orderby: String(next.orderby || 'menu_order'),
      page: Math.max(1, toInt(next.page, 1)),
      min_price: Number(next.min_price || 0),
      max_price: Number(next.max_price || 0),
      interior_images: isTruthy(next.interior_images),
      terms: {}
    };

    if (next.terms && typeof next.terms === 'object') {
      Object.keys(next.terms).forEach(function (key) {
        if (!Array.isArray(next.terms[key])) {
          return;
        }
        normalized.terms[key] = next.terms[key]
          .map(function (item) {
            return String(item || '').trim();
          })
          .filter(Boolean);
      });
    }

    return normalized;
  }

  function toInt(value, fallback) {
    var parsed = parseInt(value, 10);
    return Number.isFinite(parsed) ? parsed : fallback;
  }

  function isTruthy(value) {
    if (typeof value === 'boolean') {
      return value;
    }

    var normalized = String(value || '').toLowerCase();
    return normalized === '1' || normalized === 'true' || normalized === 'yes' || normalized === 'on';
  }

  function cloneState(source) {
    return normalizeState(JSON.parse(JSON.stringify(source || {})));
  }

  function setBusy(isBusy) {
    app.classList.toggle('is-loading', isBusy);
  }

  function setInteriorToggleState(isEnabled) {
    var toggles = document.querySelectorAll('[data-shop-interior-toggle="1"]');
    toggles.forEach(function (toggle) {
      toggle.checked = !!isEnabled;
    });
  }

  function isMobileFiltersViewport() {
    return mobileMedia.matches;
  }

  function setMobileFilterButtonState(isOpen) {
    var button = app.querySelector('[data-shop-mobile-open]');
    if (!button) {
      return;
    }
    button.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
  }

  function openMobileFilters() {
    if (!isMobileFiltersViewport()) {
      return;
    }

    draftState = cloneState(state);
    syncMobileOverlayControlsFromState(state);
    app.classList.add('is-mobile-filters-open');
    document.body.classList.add('sawerna-mobile-filters-open');
    setMobileFilterButtonState(true);
  }

  function closeMobileFilters() {
    app.classList.remove('is-mobile-filters-open');
    document.body.classList.remove('sawerna-mobile-filters-open');
    setMobileFilterButtonState(false);
  }

  function buildPayload(sourceState) {
    return {
      action: 'sawerna_shop_filter_products',
      nonce: config.nonce,
      state: sourceState,
      context: {
        context_taxonomy: context.taxonomy || '',
        context_term_id: Number(context.term_id || 0)
      }
    };
  }

  function requestUpdate(pushHistory) {
    if (request) {
      request.abort();
    }

    setBusy(true);
    request = new AbortController();

    var body = new URLSearchParams();
    var payload = buildPayload(state);

    Object.keys(payload).forEach(function (key) {
      if (key === 'state' || key === 'context') {
        body.append(key, JSON.stringify(payload[key]));
      } else {
        body.append(key, payload[key]);
      }
    });

    fetch(config.ajaxUrl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
      },
      body: body.toString(),
      credentials: 'same-origin',
      signal: request.signal
    })
      .then(function (response) {
        return response.json();
      })
      .then(function (response) {
        if (!response || !response.success || !response.data) {
          return;
        }

        swapHtml('[data-shop-toolbar="1"]', response.data.toolbar);
        swapHtml('[data-shop-active="1"]', response.data.active);
        swapHtml('[data-shop-sidebar="1"]', response.data.sidebar);
        swapHtml('[data-shop-products="1"]', response.data.products);
        setInteriorToggleState(state.interior_images);
        paintPriceTrack();
        paintRangeTracks();
        setMobileFilterButtonState(app.classList.contains('is-mobile-filters-open'));

        if (pushHistory && response.data.url) {
          window.history.pushState({ state: state }, '', response.data.url);
        }
      })
      .catch(function (error) {
        if (error && error.name === 'AbortError') {
          return;
        }
      })
      .finally(function () {
        setBusy(false);
      });
  }

  function swapHtml(selector, html) {
    var target = app.querySelector(selector);
    if (!target || typeof html !== 'string') {
      return;
    }
    target.innerHTML = html;
  }

  function getCheckedValues(key) {
    var inputs = app.querySelectorAll('input[type="checkbox"][data-filter-key="' + key + '"]');
    var values = [];
    inputs.forEach(function (input) {
      if (input.checked) {
        values.push(input.value);
      }
    });
    return values;
  }

  function isMobileOverlayInteraction(target) {
    return isMobileFiltersViewport() && app.classList.contains('is-mobile-filters-open') && !!target.closest('[data-filter-sidebar="1"]');
  }

  function getWorkingState(target) {
    return isMobileOverlayInteraction(target) ? draftState : state;
  }

  function syncDraftPriceInputs() {
    var minRange = app.querySelector('[data-price-control="min"]');
    var maxRange = app.querySelector('[data-price-control="max"]');
    var minInput = app.querySelector('[data-price-input="min"]');
    var maxInput = app.querySelector('[data-price-input="max"]');

    if (!minRange || !maxRange || !minInput || !maxInput) {
      return;
    }

    draftState.min_price = Number(minInput.value || 0);
    draftState.max_price = Number(maxInput.value || 0);

    if (draftState.max_price <= 0 || draftState.max_price < draftState.min_price) {
      draftState.min_price = 0;
      draftState.max_price = 0;
    }
  }

  function syncDraftRangeState(container) {
    if (!container) {
      return;
    }

    var key = container.getAttribute('data-range-filter');
    if (!key) {
      return;
    }

    var minInput = container.querySelector('[data-range-input="min"]');
    var maxInput = container.querySelector('[data-range-input="max"]');
    var minRange = container.querySelector('[data-range-control="min"]');
    var maxRange = container.querySelector('[data-range-control="max"]');
    if (!minInput || !maxInput || !minRange || !maxRange) {
      return;
    }

    var min = Number(minInput.value);
    var max = Number(maxInput.value);
    if (!Number.isFinite(min) || !Number.isFinite(max)) {
      return;
    }

    var minBound = Number(minRange.min || 0);
    var maxBound = Number(maxRange.max || 0);
    var terms = readRangeTerms(container);
    var selected = terms.filter(function (term) {
      return term.value >= min && term.value <= max;
    }).map(function (term) {
      return term.slug;
    });

    var fullRange = Number.isFinite(minBound) && Number.isFinite(maxBound) && min <= minBound && max >= maxBound;
    if (fullRange) {
      selected = [];
    }

    if (selected.length) {
      draftState.terms[key] = Array.from(new Set(selected));
    } else {
      delete draftState.terms[key];
    }
  }

  function resetMobileOverlayControls() {
    var checkboxInputs = app.querySelectorAll('input[type="checkbox"][data-filter-key]');
    checkboxInputs.forEach(function (input) {
      input.checked = false;
    });

    var mobileSortInputs = app.querySelectorAll('input[type="radio"][data-mobile-sort]');
    mobileSortInputs.forEach(function (input) {
      input.checked = input.value === 'popularity';
    });

    setInteriorToggleState(false);

    var minInput = app.querySelector('[data-price-input="min"]');
    var maxInput = app.querySelector('[data-price-input="max"]');
    var minRange = app.querySelector('[data-price-control="min"]');
    var maxRange = app.querySelector('[data-price-control="max"]');
    if (minInput && maxInput && minRange && maxRange) {
      minInput.value = minRange.min;
      maxInput.value = maxRange.max;
      minRange.value = minRange.min;
      maxRange.value = maxRange.max;
      paintPriceTrack();
    }

    var rangeContainers = app.querySelectorAll('[data-range-filter]');
    rangeContainers.forEach(function (container) {
      var localMinInput = container.querySelector('[data-range-input="min"]');
      var localMaxInput = container.querySelector('[data-range-input="max"]');
      var localMinRange = container.querySelector('[data-range-control="min"]');
      var localMaxRange = container.querySelector('[data-range-control="max"]');

      if (!localMinInput || !localMaxInput || !localMinRange || !localMaxRange) {
        return;
      }

      localMinInput.value = localMinRange.min;
      localMaxInput.value = localMaxRange.max;
      localMinRange.value = localMinRange.min;
      localMaxRange.value = localMaxRange.max;
      paintRangeTrack(container);
    });
  }

  function syncMobileOverlayControlsFromState(sourceState) {
    var nextState = cloneState(sourceState);

    var checkboxInputs = app.querySelectorAll('input[type="checkbox"][data-filter-key]');
    checkboxInputs.forEach(function (input) {
      var key = input.getAttribute('data-filter-key');
      var selectedValues = key && nextState.terms[key] ? nextState.terms[key] : [];
      input.checked = selectedValues.indexOf(input.value) !== -1;
    });

    var mobileSortInputs = app.querySelectorAll('input[type="radio"][data-mobile-sort]');
    var activeOrder = nextState.orderby === 'menu_order' ? 'popularity' : nextState.orderby;
    mobileSortInputs.forEach(function (input) {
      input.checked = input.value === activeOrder;
    });

    setInteriorToggleState(nextState.interior_images);

    var minInput = app.querySelector('[data-price-input="min"]');
    var maxInput = app.querySelector('[data-price-input="max"]');
    var minRange = app.querySelector('[data-price-control="min"]');
    var maxRange = app.querySelector('[data-price-control="max"]');
    if (minInput && maxInput && minRange && maxRange) {
      var nextMinPrice = nextState.max_price > 0 ? nextState.min_price : Number(minRange.min || 0);
      var nextMaxPrice = nextState.max_price > 0 ? nextState.max_price : Number(maxRange.max || 0);

      minInput.value = String(nextMinPrice);
      maxInput.value = String(nextMaxPrice);
      minRange.value = String(nextMinPrice);
      maxRange.value = String(nextMaxPrice);
      paintPriceTrack();
    }

    var rangeContainers = app.querySelectorAll('[data-range-filter]');
    rangeContainers.forEach(function (container) {
      var key = container.getAttribute('data-range-filter');
      if (!key) {
        return;
      }

      var localMinInput = container.querySelector('[data-range-input="min"]');
      var localMaxInput = container.querySelector('[data-range-input="max"]');
      var localMinRange = container.querySelector('[data-range-control="min"]');
      var localMaxRange = container.querySelector('[data-range-control="max"]');
      if (!localMinInput || !localMaxInput || !localMinRange || !localMaxRange) {
        return;
      }

      var selected = nextState.terms[key] || [];
      var terms = readRangeTerms(container);
      var values = terms.filter(function (term) {
        return selected.indexOf(term.slug) !== -1;
      }).map(function (term) {
        return term.value;
      });

      var nextMin = values.length ? Math.min.apply(Math, values) : Number(localMinRange.min || 0);
      var nextMax = values.length ? Math.max.apply(Math, values) : Number(localMaxRange.max || 0);

      localMinInput.value = String(nextMin);
      localMaxInput.value = String(nextMax);
      localMinRange.value = String(nextMin);
      localMaxRange.value = String(nextMax);
      paintRangeTrack(container);
    });
  }

  function resetMobileFilterSection(clearKey) {
    if (clearKey === 'price') {
      var minInput = app.querySelector('[data-price-input="min"]');
      var maxInput = app.querySelector('[data-price-input="max"]');
      var minRange = app.querySelector('[data-price-control="min"]');
      var maxRange = app.querySelector('[data-price-control="max"]');

      if (minInput && maxInput && minRange && maxRange) {
        minInput.value = minRange.min;
        maxInput.value = maxRange.max;
        minRange.value = minRange.min;
        maxRange.value = maxRange.max;
        paintPriceTrack();
      }
      return;
    }

    var checkboxInputs = app.querySelectorAll('input[type="checkbox"][data-filter-key="' + clearKey + '"]');
    if (checkboxInputs.length) {
      checkboxInputs.forEach(function (input) {
        input.checked = false;
      });
      return;
    }

    var rangeContainer = app.querySelector('[data-range-filter="' + clearKey + '"]');
    if (rangeContainer) {
      var minField = rangeContainer.querySelector('[data-range-input="min"]');
      var maxField = rangeContainer.querySelector('[data-range-input="max"]');
      var minSlider = rangeContainer.querySelector('[data-range-control="min"]');
      var maxSlider = rangeContainer.querySelector('[data-range-control="max"]');

      if (minField && maxField && minSlider && maxSlider) {
        minField.value = minSlider.min;
        maxField.value = maxSlider.max;
        minSlider.value = minSlider.min;
        maxSlider.value = maxSlider.max;
        paintRangeTrack(rangeContainer);
      }
    }
  }

  function bindEvents() {
    app.addEventListener('change', function (event) {
      var target = event.target;
      if (!(target instanceof HTMLElement)) {
        return;
      }

      if (target.matches('input[type="checkbox"][data-filter-key]')) {
        var key = target.getAttribute('data-filter-key');
        if (!key) {
          return;
        }
        var workingState = getWorkingState(target);
        workingState.terms[key] = getCheckedValues(key);
        if (!workingState.terms[key].length) {
          delete workingState.terms[key];
        }
        workingState.page = 1;
        if (workingState === state) {
          requestUpdate(true);
        }
      }

      if (target.matches('[data-shop-control="per_page"]')) {
        state.per_page = toInt(target.value, 24);
        state.page = 1;
        requestUpdate(true);
      }

      if (target.matches('[data-shop-control="orderby"]')) {
        state.orderby = target.value || 'menu_order';
        state.page = 1;
        requestUpdate(true);
      }

      if (target.matches('[data-mobile-sort="1"]')) {
        draftState.orderby = target.value || 'popularity';
        draftState.page = 1;
      }

      if (target.matches('[data-shop-interior-toggle="1"]')) {
        var nextInteriorState = !!target.checked;
        setInteriorToggleState(nextInteriorState);

        if (isMobileOverlayInteraction(target)) {
          draftState.interior_images = nextInteriorState;
          draftState.page = 1;
        } else {
          state.interior_images = nextInteriorState;
          state.page = 1;
          requestUpdate(true);
        }
      }

      if (target.matches('[data-price-input="min"], [data-price-input="max"]')) {
        syncPriceInputs();
        if (isMobileOverlayInteraction(target)) {
          syncDraftPriceInputs();
        } else {
          debouncePriceApply();
        }
      }

      if (target.matches('[data-price-control="min"], [data-price-control="max"]')) {
        syncPriceRanges();
        if (isMobileOverlayInteraction(target)) {
          syncDraftPriceInputs();
        } else {
          debouncePriceApply();
        }
      }

      if (target.matches('[data-range-input="min"], [data-range-input="max"]')) {
        var rangeContainer = target.closest('[data-range-filter]');
        if (rangeContainer) {
          syncRangeInputs(rangeContainer);
          if (isMobileOverlayInteraction(target)) {
            syncDraftRangeState(rangeContainer);
          } else {
            debounceRangeApply(rangeContainer);
          }
        }
      }

      if (target.matches('[data-range-control="min"], [data-range-control="max"]')) {
        var rangeControlContainer = target.closest('[data-range-filter]');
        if (rangeControlContainer) {
          syncRangeRanges(rangeControlContainer);
          if (isMobileOverlayInteraction(target)) {
            syncDraftRangeState(rangeControlContainer);
          } else {
            debounceRangeApply(rangeControlContainer);
          }
        }
      }
    });

    app.addEventListener('click', function (event) {
      var target = event.target;
      if (!(target instanceof HTMLElement)) {
        return;
      }

      var pagination = target.closest('.sawerna-shop-pagination a[data-page]');
      if (pagination) {
        event.preventDefault();
        state.page = toInt(pagination.getAttribute('data-page'), 1);
        requestUpdate(true);
        return;
      }

      var mobileOpen = target.closest('[data-shop-mobile-open]');
      if (mobileOpen) {
        openMobileFilters();
        return;
      }

      var mobileClose = target.closest('[data-shop-mobile-close]');
      if (mobileClose) {
        closeMobileFilters();
        return;
      }

      var mobileApply = target.closest('[data-mobile-apply]');
      if (mobileApply) {
        state = cloneState(draftState);
        state.page = 1;
        requestUpdate(true);
        closeMobileFilters();
        return;
      }

      var mobileClearAll = target.closest('[data-mobile-clear-all]');
      if (mobileClearAll) {
        draftState = cloneState(state);
        draftState.terms = {};
        draftState.min_price = 0;
        draftState.max_price = 0;
        draftState.orderby = 'popularity';
        draftState.interior_images = false;
        draftState.page = 1;
        state = cloneState(draftState);
        resetMobileOverlayControls();
        requestUpdate(true);
        return;
      }

      var remove = target.closest('[data-remove-filter]');
      if (remove) {
        var removeKey = remove.getAttribute('data-remove-filter');
        var removeValue = remove.getAttribute('data-remove-value');

        if (removeKey === 'price') {
          state.min_price = 0;
          state.max_price = 0;
        } else if (removeKey === 'interior_images') {
          state.interior_images = false;
          setInteriorToggleState(false);
        } else if (removeKey) {
          var list = state.terms[removeKey] || [];
          state.terms[removeKey] = list.filter(function (item) {
            return item !== removeValue;
          });
          if (!state.terms[removeKey].length) {
            delete state.terms[removeKey];
          }
        }

        state.page = 1;
        requestUpdate(true);
        return;
      }

      var clearFilter = target.closest('[data-clear-filter]');
      if (clearFilter) {
        var clearKey = clearFilter.getAttribute('data-clear-filter');
        var currentState = getWorkingState(target);
        if (clearKey === 'price') {
          currentState.min_price = 0;
          currentState.max_price = 0;
        } else if (clearKey === 'interior_images') {
          currentState.interior_images = false;
          setInteriorToggleState(false);
        } else if (clearKey) {
          delete currentState.terms[clearKey];
        }
        currentState.page = 1;
        if (currentState === state) {
          requestUpdate(true);
        } else if (clearKey) {
          resetMobileFilterSection(clearKey);
        }
        return;
      }

      var clearAll = target.closest('[data-clear-all]');
      if (clearAll) {
        var activeState = getWorkingState(target);
        activeState.terms = {};
        activeState.min_price = 0;
        activeState.max_price = 0;
        activeState.interior_images = false;
        activeState.page = 1;
        setInteriorToggleState(false);
        if (activeState === state) {
          requestUpdate(true);
        }
        return;
      }

      var groupToggle = target.closest('[data-group-toggle]');
      if (groupToggle) {
        var groupName = groupToggle.getAttribute('data-group-toggle');
        if (!groupName) {
          return;
        }
        var group = app.querySelector('[data-filter-group="' + groupName + '"]');
        if (group) {
          group.classList.toggle('is-open');
        }
      }
    });

    window.addEventListener('popstate', function () {
      var params = new URLSearchParams(window.location.search);
      state = readStateFromParams(params);
      requestUpdate(false);
    });

    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape') {
        closeMobileFilters();
      }
    });

    if (typeof mobileMedia.addEventListener === 'function') {
      mobileMedia.addEventListener('change', function () {
        if (!isMobileFiltersViewport()) {
          closeMobileFilters();
        }
        updateCategoryNavState();
      });
    } else if (typeof mobileMedia.addListener === 'function') {
      mobileMedia.addListener(function () {
        if (!isMobileFiltersViewport()) {
          closeMobileFilters();
        }
        updateCategoryNavState();
      });
    }

    var categoryTrack = document.querySelector('[data-shop-categories-track="1"]');
    if (categoryTrack) {
      categoryTrack.addEventListener('scroll', updateCategoryNavState, { passive: true });
    }
  }

  function getCategoryTrack() {
    return document.querySelector('[data-shop-categories-track="1"]');
  }

  function updateCategoryNavState() {
    var track = getCategoryTrack();
    var prev = document.querySelector('[data-shop-categories-nav="prev"]');
    var next = document.querySelector('[data-shop-categories-nav="next"]');

    if (!track || !prev || !next) {
      return;
    }

    if (!isMobileFiltersViewport()) {
      prev.disabled = false;
      next.disabled = false;
      return;
    }

    var maxScroll = Math.max(0, track.scrollWidth - track.clientWidth);
    prev.disabled = track.scrollLeft <= 4;
    next.disabled = track.scrollLeft >= maxScroll - 4;
  }

  function bindCategoryNav() {
    document.addEventListener('click', function (event) {
      var target = event.target;
      if (!(target instanceof HTMLElement)) {
        return;
      }

      var navButton = target.closest('[data-shop-categories-nav]');
      if (!navButton) {
        return;
      }

      var track = getCategoryTrack();
      if (!track) {
        return;
      }

      var direction = navButton.getAttribute('data-shop-categories-nav');
      var amount = Math.max(220, Math.round(track.clientWidth * 0.8));
      var left = direction === 'prev' ? -amount : amount;

      track.scrollBy({
        left: left,
        behavior: 'smooth'
      });
    });

    updateCategoryNavState();
  }

  function readStateFromParams(params) {
    var next = {
      per_page: toInt(params.get('per_page'), 24),
      columns: toInt(params.get('columns'), 3),
      orderby: params.get('orderby') || 'menu_order',
      page: Math.max(1, toInt(params.get('paged'), 1)),
      min_price: Number(params.get('min_price') || 0),
      max_price: Number(params.get('max_price') || 0),
      interior_images: isTruthy(params.get('interior_images')),
      terms: {}
    };

    Array.from(params.keys()).forEach(function (key) {
      if (key.indexOf('f_') !== 0) {
        return;
      }
      var filterKey = key.replace('f_', '');
      var values = String(params.get(key) || '').split(',').map(function (item) {
        return item.trim();
      }).filter(Boolean);
      if (values.length) {
        next.terms[filterKey] = values;
      }
    });

    return next;
  }

  var priceDebounce = null;
  function debouncePriceApply() {
    if (priceDebounce) {
      window.clearTimeout(priceDebounce);
    }
    priceDebounce = window.setTimeout(function () {
      var minInput = app.querySelector('[data-price-input="min"]');
      var maxInput = app.querySelector('[data-price-input="max"]');
      if (!minInput || !maxInput) {
        return;
      }

      state.min_price = Number(minInput.value || 0);
      state.max_price = Number(maxInput.value || 0);
      if (state.max_price <= 0 || state.max_price < state.min_price) {
        state.min_price = 0;
        state.max_price = 0;
      }
      state.page = 1;
      requestUpdate(true);
    }, 250);
  }

  function syncPriceRanges() {
    var minRange = app.querySelector('[data-price-control="min"]');
    var maxRange = app.querySelector('[data-price-control="max"]');
    var minInput = app.querySelector('[data-price-input="min"]');
    var maxInput = app.querySelector('[data-price-input="max"]');

    if (!minRange || !maxRange || !minInput || !maxInput) {
      return;
    }

    var min = Number(minRange.value);
    var max = Number(maxRange.value);

    if (min > max) {
      if (document.activeElement === minRange) {
        max = min;
        maxRange.value = String(max);
      } else {
        min = max;
        minRange.value = String(min);
      }
    }

    minInput.value = String(min);
    maxInput.value = String(max);
    paintPriceTrack();
  }

  function syncPriceInputs() {
    var minRange = app.querySelector('[data-price-control="min"]');
    var maxRange = app.querySelector('[data-price-control="max"]');
    var minInput = app.querySelector('[data-price-input="min"]');
    var maxInput = app.querySelector('[data-price-input="max"]');

    if (!minRange || !maxRange || !minInput || !maxInput) {
      return;
    }

    var min = Number(minInput.value || minRange.min);
    var max = Number(maxInput.value || maxRange.max);

    min = Math.max(Number(minRange.min), Math.min(min, Number(minRange.max)));
    max = Math.max(Number(maxRange.min), Math.min(max, Number(maxRange.max)));

    if (min > max) {
      max = min;
      maxInput.value = String(max);
    }

    minRange.value = String(min);
    maxRange.value = String(max);
    minInput.value = String(min);
    maxInput.value = String(max);
    paintPriceTrack();
  }

  function paintPriceTrack() {
    var container = app.querySelector('[data-price-filter=\"1\"]');
    var minRange = app.querySelector('[data-price-control=\"min\"]');
    var maxRange = app.querySelector('[data-price-control=\"max\"]');
    if (!container || !minRange || !maxRange) {
      return;
    }

    var min = Number(minRange.value || 0);
    var max = Number(maxRange.value || 0);
    var start = Number(minRange.min || 0);
    var end = Number(maxRange.max || 0);
    var span = Math.max(1, end - start);

    var minPercent = ((min - start) / span) * 100;
    var maxPercent = ((max - start) / span) * 100;

    container.style.setProperty('--min-percent', String(Math.max(0, Math.min(100, minPercent))) + '%');
    container.style.setProperty('--max-percent', String(Math.max(0, Math.min(100, maxPercent))) + '%');
  }

  var rangeDebounce = {};
  function debounceRangeApply(container) {
    var key = container ? container.getAttribute('data-range-filter') : '';
    if (!key) {
      return;
    }

    if (rangeDebounce[key]) {
      window.clearTimeout(rangeDebounce[key]);
    }

    rangeDebounce[key] = window.setTimeout(function () {
      applyRangeFilter(container);
    }, 250);
  }

  function applyRangeFilter(container) {
    if (!container) {
      return;
    }

    var key = container.getAttribute('data-range-filter');
    if (!key) {
      return;
    }

    var minInput = container.querySelector('[data-range-input="min"]');
    var maxInput = container.querySelector('[data-range-input="max"]');
    var minRange = container.querySelector('[data-range-control="min"]');
    var maxRange = container.querySelector('[data-range-control="max"]');
    if (!minInput || !maxInput || !minRange || !maxRange) {
      return;
    }

    var min = Number(minInput.value);
    var max = Number(maxInput.value);
    if (!Number.isFinite(min) || !Number.isFinite(max)) {
      return;
    }

    var minBound = Number(minRange.min || 0);
    var maxBound = Number(maxRange.max || 0);
    var terms = readRangeTerms(container);
    var selected = terms.filter(function (term) {
      return term.value >= min && term.value <= max;
    }).map(function (term) {
      return term.slug;
    });

    var fullRange = Number.isFinite(minBound) && Number.isFinite(maxBound) && min <= minBound && max >= maxBound;
    if (fullRange) {
      selected = [];
    }

    if (selected.length) {
      state.terms[key] = Array.from(new Set(selected));
    } else {
      delete state.terms[key];
    }

    state.page = 1;
    requestUpdate(true);
  }

  function readRangeTerms(container) {
    if (!container) {
      return [];
    }

    var raw = container.getAttribute('data-range-terms');
    if (!raw) {
      return [];
    }

    try {
      var parsed = JSON.parse(raw);
      if (!Array.isArray(parsed)) {
        return [];
      }

      return parsed.map(function (entry) {
        return {
          slug: String(entry && entry.slug ? entry.slug : ''),
          value: Number(entry && entry.value !== undefined ? entry.value : NaN)
        };
      }).filter(function (entry) {
        return entry.slug && Number.isFinite(entry.value);
      });
    } catch (error) {
      return [];
    }
  }

  function syncRangeRanges(container) {
    var minRange = container.querySelector('[data-range-control="min"]');
    var maxRange = container.querySelector('[data-range-control="max"]');
    var minInput = container.querySelector('[data-range-input="min"]');
    var maxInput = container.querySelector('[data-range-input="max"]');

    if (!minRange || !maxRange || !minInput || !maxInput) {
      return;
    }

    var min = Number(minRange.value);
    var max = Number(maxRange.value);

    if (min > max) {
      if (document.activeElement === minRange) {
        max = min;
        maxRange.value = String(max);
      } else {
        min = max;
        minRange.value = String(min);
      }
    }

    minInput.value = String(min);
    maxInput.value = String(max);
    paintRangeTrack(container);
  }

  function syncRangeInputs(container) {
    var minRange = container.querySelector('[data-range-control="min"]');
    var maxRange = container.querySelector('[data-range-control="max"]');
    var minInput = container.querySelector('[data-range-input="min"]');
    var maxInput = container.querySelector('[data-range-input="max"]');

    if (!minRange || !maxRange || !minInput || !maxInput) {
      return;
    }

    var min = Number(minInput.value || minRange.min);
    var max = Number(maxInput.value || maxRange.max);

    min = Math.max(Number(minRange.min), Math.min(min, Number(minRange.max)));
    max = Math.max(Number(maxRange.min), Math.min(max, Number(maxRange.max)));

    if (min > max) {
      max = min;
      maxInput.value = String(max);
    }

    minRange.value = String(min);
    maxRange.value = String(max);
    minInput.value = String(min);
    maxInput.value = String(max);
    paintRangeTrack(container);
  }

  function paintRangeTrack(container) {
    if (!container) {
      return;
    }

    var minRange = container.querySelector('[data-range-control="min"]');
    var maxRange = container.querySelector('[data-range-control="max"]');
    if (!minRange || !maxRange) {
      return;
    }

    var min = Number(minRange.value || 0);
    var max = Number(maxRange.value || 0);
    var start = Number(minRange.min || 0);
    var end = Number(maxRange.max || 0);
    var span = Math.max(1, end - start);

    var minPercent = ((min - start) / span) * 100;
    var maxPercent = ((max - start) / span) * 100;

    container.style.setProperty('--min-percent', String(Math.max(0, Math.min(100, minPercent))) + '%');
    container.style.setProperty('--max-percent', String(Math.max(0, Math.min(100, maxPercent))) + '%');
  }

  function paintRangeTracks() {
    var containers = app.querySelectorAll('[data-range-filter]');
    containers.forEach(function (container) {
      paintRangeTrack(container);
    });
  }

  paintPriceTrack();
  paintRangeTracks();
  setInteriorToggleState(state.interior_images);
  bindEvents();
  bindCategoryNav();
})();
