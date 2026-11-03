(function() {
  'use strict';

  const doc = document;
  const body = doc.body;
  const drawer = doc.querySelector('[data-mini-cart="1"]');
  const config = window.sawernaMiniCart || {};
  const pendingKeys = new Set();

  if (!drawer) {
    return;
  }

  const panel = drawer.querySelector('.sawerna-mini-cart__panel');
  const purchaseRoot = doc.querySelector('.sawerna-single-product__purchase');
  let lastActiveElement = null;

  const getStatusNode = () => {
    if (!purchaseRoot) {
      return null;
    }

    let node = purchaseRoot.querySelector('.sawerna-single-product__cart-feedback');
    if (node) {
      return node;
    }

    node = doc.createElement('p');
    node.className = 'sawerna-single-product__cart-feedback';
    node.setAttribute('role', 'status');
    node.setAttribute('aria-live', 'polite');
    node.hidden = true;
    purchaseRoot.appendChild(node);
    return node;
  };

  const clearLegacyViewCartLinks = () => {
    if (!purchaseRoot) {
      return;
    }

    purchaseRoot.querySelectorAll('.added_to_cart.wc-forward').forEach((link) => {
      link.remove();
    });
  };

  const syncTriggerState = (isOpen) => {
    doc.querySelectorAll('[data-mini-cart-trigger]').forEach((trigger) => {
      trigger.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    });
  };

  const openCart = () => {
    lastActiveElement = doc.activeElement instanceof HTMLElement ? doc.activeElement : null;
    drawer.hidden = false;
    body.classList.add('sawerna-mini-cart-open');
    window.requestAnimationFrame(() => {
      drawer.classList.add('is-open');
      syncTriggerState(true);
      if (panel) {
        panel.focus();
      }
    });
  };

  const closeCart = () => {
    drawer.classList.remove('is-open');
    syncTriggerState(false);
    window.setTimeout(() => {
      if (drawer.classList.contains('is-open')) {
        return;
      }

      drawer.hidden = true;
      body.classList.remove('sawerna-mini-cart-open');

      if (lastActiveElement) {
        lastActiveElement.focus();
      }
    }, 280);
  };

  const replaceFragments = (fragments) => {
    if (!fragments) {
      return;
    }

    Object.keys(fragments).forEach((selector) => {
      const html = fragments[selector];

      if (window.jQuery) {
        window.jQuery(selector).replaceWith(html);
        return;
      }

      doc.querySelectorAll(selector).forEach((node) => {
        node.outerHTML = html;
      });
    });
  };

  const setRowBusy = (cartItemKey, isBusy) => {
    if (!cartItemKey) {
      return;
    }

    const row = Array.from(doc.querySelectorAll('[data-mini-cart-item]')).find((item) => {
      return item.getAttribute('data-mini-cart-item') === cartItemKey;
    });
    if (!row) {
      return;
    }

    row.classList.toggle('is-updating', isBusy);
    row.querySelectorAll('button').forEach((button) => {
      button.disabled = isBusy;
    });
  };

  const requestCartAction = async (action, payload, cartItemKey) => {
    if (!config.ajaxUrl || !config.nonce || !window.fetch || !action || pendingKeys.has(cartItemKey)) {
      return;
    }

    pendingKeys.add(cartItemKey);
    setRowBusy(cartItemKey, true);

    const bodyPayload = new URLSearchParams({
      action,
      nonce: config.nonce,
      ...payload,
    });

    try {
      const response = await fetch(config.ajaxUrl, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
          'X-Requested-With': 'XMLHttpRequest',
        },
        body: bodyPayload.toString(),
      });
      const data = await response.json();

      if (!data || !data.success || !data.data || !data.data.fragments) {
        throw new Error('Invalid mini-cart response');
      }

      replaceFragments(data.data.fragments);
    } catch (error) {
      window.console.error('[Sawerna mini-cart] Request failed', error);
    } finally {
      pendingKeys.delete(cartItemKey);
      setRowBusy(cartItemKey, false);
    }
  };

  doc.addEventListener('click', (event) => {
    const removeButton = event.target.closest('[data-mini-cart-remove]');
    if (removeButton) {
      event.preventDefault();
      requestCartAction(
        'sawerna_mini_cart_remove_item',
        { cart_item_key: removeButton.getAttribute('data-mini-cart-remove') || '' },
        removeButton.getAttribute('data-mini-cart-remove') || ''
      );
      return;
    }

    const quantityButton = event.target.closest('[data-mini-cart-increase], [data-mini-cart-decrease]');
    if (quantityButton) {
      event.preventDefault();

      const row = quantityButton.closest('[data-mini-cart-item]');
      const cartItemKey = row ? row.getAttribute('data-mini-cart-item') || '' : '';
      const currentQuantity = row ? parseInt(row.getAttribute('data-mini-cart-quantity') || '1', 10) || 1 : 1;
      const nextQuantity = quantityButton.hasAttribute('data-mini-cart-increase')
        ? currentQuantity + 1
        : Math.max(1, currentQuantity - 1);

      if (!cartItemKey || nextQuantity === currentQuantity) {
        return;
      }

      requestCartAction(
        'sawerna_mini_cart_update_quantity',
        {
          cart_item_key: cartItemKey,
          quantity: String(nextQuantity),
        },
        cartItemKey
      );
      return;
    }

    const trigger = event.target.closest('[data-mini-cart-trigger]');
    if (trigger) {
      event.preventDefault();
      openCart();
      return;
    }

    if (event.target.closest('[data-mini-cart-close]')) {
      event.preventDefault();
      closeCart();
    }
  });

  doc.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && drawer.classList.contains('is-open')) {
      closeCart();
    }
  });

  if (window.jQuery) {
    window.jQuery(doc.body).on('added_to_cart', function(event, fragments) {
      replaceFragments(fragments);
      openCart();
    });
  }

  doc.body.addEventListener('sawerna:added_to_cart', (event) => {
    const detail = event.detail || {};
    replaceFragments(detail.fragments);
    clearLegacyViewCartLinks();
    openCart();
  });

  doc.body.addEventListener('sawerna:single_product_added', (event) => {
    const detail = event.detail || {};
    const statusNode = getStatusNode();

    clearLegacyViewCartLinks();

    if (!statusNode) {
      return;
    }

    statusNode.textContent = detail.message || 'Toode lisati ostukorvi.';
    statusNode.classList.toggle('is-error', detail.type === 'error');
    statusNode.hidden = false;
  });

  clearLegacyViewCartLinks();
})();
