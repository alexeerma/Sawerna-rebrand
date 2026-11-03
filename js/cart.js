(function () {
  const cartForm = document.querySelector('.woocommerce-cart-form');

  if (cartForm) {
    const updateButton = cartForm.querySelector('button[name="update_cart"]');
    let updateTimer = 0;

    const submitCartUpdate = () => {
      if (!updateButton) {
        return;
      }

      updateButton.disabled = false;

      if (typeof cartForm.requestSubmit === 'function') {
        cartForm.requestSubmit(updateButton);
        return;
      }

      updateButton.click();
    };

    const scheduleCartUpdate = () => {
      window.clearTimeout(updateTimer);
      updateTimer = window.setTimeout(submitCartUpdate, 250);
    };

    cartForm.querySelectorAll('[data-cart-quantity="1"]').forEach((quantity) => {
      const input = quantity.querySelector('input.qty');
      const minus = quantity.querySelector('[data-cart-quantity-decrease="1"]');
      const plus = quantity.querySelector('[data-cart-quantity-increase="1"]');

      if (!input || !minus || !plus) {
        return;
      }

      minus.addEventListener('click', () => {
        const step = parseFloat(input.step || '1') || 1;
        const min = parseFloat(input.min || '0') || 0;
        const current = parseFloat(input.value || String(min)) || min;
        input.value = String(Math.max(min, current - step));
        input.dispatchEvent(new Event('change', { bubbles: true }));
      });

      plus.addEventListener('click', () => {
        const step = parseFloat(input.step || '1') || 1;
        const min = parseFloat(input.min || '0') || 0;
        const max = parseFloat(input.max || '0') || 0;
        const current = parseFloat(input.value || String(min)) || min;
        const next = current + step;
        input.value = String(max > 0 ? Math.min(max, next) : next);
        input.dispatchEvent(new Event('change', { bubbles: true }));
      });

      input.addEventListener('change', scheduleCartUpdate);
    });
  }

  document.querySelectorAll('[data-cart-recommendations="1"]').forEach((section) => {
    const track = section.querySelector('.sawerna-cart__recommendations-track');
    const prev = section.querySelector('[data-cart-related-nav="prev"]');
    const next = section.querySelector('[data-cart-related-nav="next"]');
    const mobileQuery = window.matchMedia('(max-width: 767px)');

    if (!track || !prev || !next) {
      return;
    }

    const getStep = () => {
      const card = track.querySelector('.sawerna-cart__recommendations-item');
      if (!card) {
        return 0;
      }

      const styles = window.getComputedStyle(track);
      const gap = parseFloat(styles.columnGap || styles.gap || '0') || 0;
      return card.getBoundingClientRect().width + gap;
    };

    const updateButtons = () => {
      const maxScroll = track.scrollWidth - track.clientWidth;
      prev.disabled = track.scrollLeft <= 4;
      next.disabled = track.scrollLeft >= maxScroll - 4;
    };

    prev.addEventListener('click', () => {
      const multiplier = mobileQuery.matches ? 1 : 4;
      track.scrollBy({ left: -(getStep() * multiplier), behavior: 'smooth' });
    });

    next.addEventListener('click', () => {
      const multiplier = mobileQuery.matches ? 1 : 4;
      track.scrollBy({ left: getStep() * multiplier, behavior: 'smooth' });
    });

    track.addEventListener('scroll', updateButtons, { passive: true });
    window.addEventListener('resize', updateButtons);

    if (typeof mobileQuery.addEventListener === 'function') {
      mobileQuery.addEventListener('change', updateButtons);
    } else if (typeof mobileQuery.addListener === 'function') {
      mobileQuery.addListener(updateButtons);
    }

    updateButtons();
  });
})();
