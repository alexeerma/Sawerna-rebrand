(() => {
  const footer = document.querySelector('.rm-footer');
  if (!footer) {
    return;
  }

  const items = footer.querySelectorAll('.rm-footer__accordionItem');
  if (!items.length) {
    return;
  }

  const mobileQuery = window.matchMedia('(max-width: 600px)');

  const setState = (item, isOpen) => {
    const toggle = item.querySelector('.rm-footer__accordionToggle');
    const panel = item.querySelector('.rm-footer__accordionPanel');
    if (!toggle || !panel) {
      return;
    }

    toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    panel.hidden = !isOpen;
    item.classList.toggle('is-open', isOpen);
  };

  const syncState = () => {
    if (mobileQuery.matches) {
      items.forEach((item) => {
        const defaultOpen = item.getAttribute('data-accordion-default') === 'open';
        setState(item, defaultOpen);
      });
      return;
    }

    items.forEach((item) => setState(item, true));
  };

  items.forEach((item) => {
    const toggle = item.querySelector('.rm-footer__accordionToggle');
    if (!toggle) {
      return;
    }

    toggle.addEventListener('click', () => {
      if (!mobileQuery.matches) {
        return;
      }

      const isOpen = toggle.getAttribute('aria-expanded') === 'true';
      setState(item, !isOpen);
    });
  });

  syncState();
  if (typeof mobileQuery.addEventListener === 'function') {
    mobileQuery.addEventListener('change', syncState);
  } else if (typeof mobileQuery.addListener === 'function') {
    mobileQuery.addListener(syncState);
  }
})();
