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

  const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
  const DURATION = 300;
  const EASING = 'cubic-bezier(0.4, 0, 0.2, 1)';

  // Smooth height + fade for the mobile accordion (Web Animations API; instant fallback).
  const animatePanel = (panel, isOpen) => {
    if (panel._rmAnim) {
      panel._rmAnim.cancel();
      panel._rmAnim = null;
    }

    if (reducedMotion.matches || typeof panel.animate !== 'function') {
      panel.hidden = !isOpen;
      return;
    }

    panel.hidden = false;
    const cs = window.getComputedStyle(panel);
    const open = {
      height: `${panel.scrollHeight}px`,
      paddingTop: cs.paddingTop,
      paddingBottom: cs.paddingBottom,
      opacity: 1,
    };
    const closed = { height: '0px', paddingTop: '0px', paddingBottom: '0px', opacity: 0 };

    panel.style.overflow = 'hidden';
    const anim = panel.animate(isOpen ? [closed, open] : [open, closed], {
      duration: DURATION,
      easing: EASING,
    });
    panel._rmAnim = anim;
    anim.onfinish = () => {
      panel._rmAnim = null;
      panel.style.overflow = '';
      panel.hidden = !isOpen;
    };
  };

  const setState = (item, isOpen, animate = false) => {
    const toggle = item.querySelector('.rm-footer__accordionToggle');
    const panel = item.querySelector('.rm-footer__accordionPanel');
    if (!toggle || !panel) {
      return;
    }

    toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    item.classList.toggle('is-open', isOpen);
    if (animate) {
      animatePanel(panel, isOpen);
    } else {
      if (panel._rmAnim) {
        panel._rmAnim.cancel();
        panel._rmAnim = null;
        panel.style.overflow = '';
      }
      panel.hidden = !isOpen;
    }
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
      setState(item, !isOpen, true);
    });
  });

  syncState();
  if (typeof mobileQuery.addEventListener === 'function') {
    mobileQuery.addEventListener('change', syncState);
  } else if (typeof mobileQuery.addListener === 'function') {
    mobileQuery.addListener(syncState);
  }
})();
