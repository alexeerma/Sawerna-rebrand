/**
 * Wishlist functionality
 * Handles add/remove and UI updates via AJAX.
 */
(function() {
  'use strict';

  if (!window.sawernaWishlist) {
    return;
  }

  const data = window.sawernaWishlist;
  const buttonSelector = '.sawerna-wishlist-button';

  const getText = function(key) {
    if (!data.texts || !data.texts[key]) {
      return '';
    }
    return data.texts[key];
  };

  const setButtonState = function(button, inWishlist) {
    const label = inWishlist ? getText('remove') : getText('add');
    button.setAttribute('data-in-wishlist', inWishlist ? '1' : '0');
    button.setAttribute('aria-pressed', inWishlist ? 'true' : 'false');
    if (label) {
      button.setAttribute('aria-label', label);
      button.setAttribute('title', label);
    }
    button.classList.toggle('is-active', inWishlist);
    const textEl = button.querySelector('.sawerna-wishlist-button__text');
    if (textEl) {
      textEl.textContent = label;
    }
  };

  const setButtonLoading = function(button, isLoading, inWishlist) {
    if (isLoading) {
      button.classList.add('is-loading');
      button.disabled = true;
      const textEl = button.querySelector('.sawerna-wishlist-button__text');
      if (textEl) {
        textEl.textContent = inWishlist ? getText('removing') : getText('adding');
      }
      return;
    }
    button.classList.remove('is-loading');
    button.disabled = false;
  };

  const updateButtonsForProduct = function(productId, inWishlist) {
    document.querySelectorAll(buttonSelector + '[data-product-id="' + productId + '"]').forEach(function(button) {
      setButtonState(button, inWishlist);
    });
  };

  const updateHeaderCount = function(count) {
    document.querySelectorAll('.sawerna-header__wishlist-count').forEach(function(el) {
      el.textContent = count > 0 ? String(count) : '';
    });
  };

  const updateWishlistCount = function(count) {
    document.querySelectorAll('.sawerna-wishlist__result').forEach(function(el) {
      el.textContent = String(count) + ' toodet';
    });
  };

  const updateWishlistPage = function(button, inWishlist) {
    const wishlist = document.querySelector('.sawerna-wishlist');
    if (!wishlist) {
      return;
    }
    if (inWishlist) {
      return;
    }
    const item = button.closest('.sawerna-product-card');
    if (item) {
      item.remove();
    }
    const grid = wishlist.querySelector('.sawerna-product-card-grid');
    const remainingItems = grid ? grid.querySelectorAll('.sawerna-product-card').length : 0;
    if (!remainingItems) {
      if (grid) {
        grid.remove();
      }
      const emptyMessage = document.createElement('p');
      emptyMessage.className = 'sawerna-wishlist-empty';
      emptyMessage.textContent = getText('empty') || 'Your wishlist is empty.';
      wishlist.appendChild(emptyMessage);
    }
  };

  const initFromItems = function() {
    const items = Array.isArray(data.items) ? data.items : [];
    items.forEach(function(id) {
      updateButtonsForProduct(id, true);
    });
    updateHeaderCount(items.length);
  };

  const sendToggleRequest = function(productId) {
    const formData = new FormData();
    formData.append('action', 'sawerna_wishlist_toggle');
    formData.append('product_id', String(productId));
    formData.append('nonce', data.nonce);

    return fetch(data.ajaxUrl, {
      method: 'POST',
      credentials: 'same-origin',
      body: formData,
    }).then(function(response) {
      return response.json();
    });
  };

  document.addEventListener('click', function(event) {
    const button = event.target.closest(buttonSelector);
    if (!button) {
      return;
    }
    event.preventDefault();

    const productId = parseInt(button.getAttribute('data-product-id'), 10);
    if (!productId || Number.isNaN(productId)) {
      return;
    }

    const currentlyInWishlist = button.getAttribute('data-in-wishlist') === '1';
    if (button.classList.contains('is-loading')) {
      return;
    }

    setButtonLoading(button, true, currentlyInWishlist);

    sendToggleRequest(productId)
      .then(function(payload) {
        if (!payload || !payload.success) {
          throw new Error(payload && payload.data && payload.data.message ? payload.data.message : 'Wishlist error');
        }
        const inWishlist = !!payload.data.in_wishlist;
        updateButtonsForProduct(productId, inWishlist);
        if (Array.isArray(payload.data.items)) {
          data.items = payload.data.items;
        }
        if (typeof payload.data.count === 'number') {
          updateHeaderCount(payload.data.count);
          updateWishlistCount(payload.data.count);
        }
        updateWishlistPage(button, inWishlist);
        if (!inWishlist && document.body.classList.contains('page-template-wishlist-template')) {
          window.location.reload();
        }
      })
      .catch(function() {
        if (getText('error')) {
          window.alert(getText('error'));
        }
        setButtonState(button, currentlyInWishlist);
      })
      .finally(function() {
        setButtonLoading(button, false, currentlyInWishlist);
      });
  });

  initFromItems();
})();
