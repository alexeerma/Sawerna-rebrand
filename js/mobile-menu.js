/**
 * Mobile Menu Functionality
 * Handles mobile overlay, accordion menus, and icon injection
 */
(function() {
  'use strict';

  const header = document.querySelector('.sawerna-header');
  if (!header) return;

  const body = document.body;

  // Mobile Menu Toggle
  const mobileToggle = header.querySelector('.sawerna-header__mobile-toggle');
  const mobileOverlay = header.querySelector('.sawerna-header__mobile-overlay');
  const mobileClose = header.querySelector('.sawerna-header__mobile-close');

  if (mobileToggle && mobileOverlay) {
    function openMobileMenu() {
      mobileToggle.setAttribute('aria-expanded', 'true');
      mobileOverlay.removeAttribute('hidden');
      body.style.overflow = 'hidden';
    }

    function closeMobileMenu() {
      mobileToggle.setAttribute('aria-expanded', 'false');
      mobileOverlay.setAttribute('hidden', '');
      body.style.overflow = '';
    }

    function toggleMobileMenu() {
      const isOpen = mobileToggle.getAttribute('aria-expanded') === 'true';
      if (isOpen) {
        closeMobileMenu();
      } else {
        openMobileMenu();
      }
    }

    mobileToggle.addEventListener('click', toggleMobileMenu);

    if (mobileClose) {
      mobileClose.addEventListener('click', closeMobileMenu);
    }

    mobileOverlay.addEventListener('click', function(e) {
      if (e.target === mobileOverlay) {
        closeMobileMenu();
      }
    });

    // Close on escape key
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape' && mobileToggle.getAttribute('aria-expanded') === 'true') {
        closeMobileMenu();
      }
    });

    // Close overlay when navigating to a leaf link
    const overlayLinks = mobileOverlay.querySelectorAll('.sawerna-mobile-menu--main a, .sawerna-mobile-topbar a, .sawerna-header__mobile-user-link');
    overlayLinks.forEach(function(link) {
      const menuItem = link.closest('li');
      if (!menuItem || !menuItem.classList.contains('menu-item-has-children')) {
        link.addEventListener('click', closeMobileMenu);
      }
    });
  }

  // Mobile menu accordion behavior
  const mobileMenus = header.querySelectorAll('.sawerna-mobile-menu--main');
  let submenuIndex = 0;
  const parentIconClosed = '<svg viewBox="0 0 6.75 4.08" xmlns="http://www.w3.org/2000/svg"><defs><style>.cls-1{fill:none;stroke:currentColor;stroke-linecap:round;stroke-linejoin:round;stroke-width:.99px}</style></defs><g><polyline class="cls-1" points=".5 .5 3.35 3.59 6.26 .68"/></g></svg>';
  const parentIconOpen = '<svg viewBox="0 0 6.75 4.08" xmlns="http://www.w3.org/2000/svg"><defs><style>.cls-1{fill:none;stroke:currentColor;stroke-linecap:round;stroke-linejoin:round;stroke-width:.99px}</style></defs><g><polyline class="cls-1" points="6.26 3.59 3.4 .5 .5 3.4"/></g></svg>';
  const submenuIconSvg = '<svg viewBox="0 0 13.74 11.06" xmlns="http://www.w3.org/2000/svg"><defs><style>.cls-1{fill:none;stroke:currentColor;stroke-linecap:round;stroke-linejoin:round;stroke-width:1.56px}</style></defs><g><line class="cls-1" x1=".78" y1="5.53" x2="12.76" y2="5.53"/><polyline class="cls-1" points="8.21 10.28 12.96 5.53 8.21 .78"/></g></svg>';
  const subItemBulletSvg = '<svg viewBox="0 0 4.08 6.75" xmlns="http://www.w3.org/2000/svg"><defs><style>.cls-1{fill:none;stroke:currentColor;stroke-linecap:round;stroke-linejoin:round;stroke-width:.99px}</style></defs><g><polyline class="cls-1" points=".5 6.25 3.59 3.4 .68 .5"/></g></svg>';
  const closeIconSvg = '<svg viewBox="0 0 17.12 17.13" xmlns="http://www.w3.org/2000/svg"><defs><style>.cls-1{fill:none;stroke:currentColor;stroke-linecap:round;stroke-linejoin:round;stroke-width:2.13px}</style></defs><g><line class="cls-1" x1="1.06" y1="1.06" x2="16.06" y2="16.06"/><line class="cls-1" x1="1.06" y1="16.06" x2="16.06" y2="1.06"/></g></svg>';

  function toggleSubmenu(item, toggle, subMenu) {
    const isOpen = item.classList.contains('is-open');
    if (isOpen) {
      item.classList.remove('is-open');
      toggle.setAttribute('aria-expanded', 'false');
      subMenu.setAttribute('aria-hidden', 'true');
      const icon = toggle.querySelector('.sawerna-mobile-menu__parent-icon');
      if (icon) {
        icon.innerHTML = parentIconClosed;
      }
    } else {
      item.classList.add('is-open');
      toggle.setAttribute('aria-expanded', 'true');
      subMenu.setAttribute('aria-hidden', 'false');
      const icon = toggle.querySelector('.sawerna-mobile-menu__parent-icon');
      if (icon) {
        icon.innerHTML = parentIconOpen;
      }
    }
  }

  mobileMenus.forEach(function(menu) {
    const itemsWithChildren = menu.querySelectorAll(':scope > li.menu-item-has-children');
    const itemsWithoutChildren = menu.querySelectorAll(':scope > li:not(.menu-item-has-children) > a');

    itemsWithChildren.forEach(function(item) {
      const link = item.querySelector(':scope > a');
      // HTML-block-only items (no real WP submenu) still get an accordion
      // panel, using the same dropdown markup the desktop menus render.
      const subMenu = item.querySelector(':scope > .sub-menu') || item.querySelector(':scope > .sawerna-html-block-dropdown');
      if (!link || !subMenu) {
        return;
      }

      let parentIcon = link.querySelector('.sawerna-mobile-menu__parent-icon');
      if (!parentIcon) {
        parentIcon = document.createElement('span');
        parentIcon.className = 'sawerna-mobile-menu__parent-icon';
        parentIcon.setAttribute('aria-hidden', 'true');
        parentIcon.innerHTML = parentIconClosed;
      }
      link.appendChild(parentIcon);

      submenuIndex += 1;
      if (!subMenu.id) {
        subMenu.id = 'sawerna-mobile-submenu-' + submenuIndex;
      }

      subMenu.setAttribute('aria-hidden', 'true');
      link.classList.add('sawerna-mobile-menu__link');
      link.setAttribute('aria-expanded', 'false');
      link.setAttribute('aria-controls', subMenu.id);

      const existingClose = subMenu.querySelector('.sawerna-mobile-submenu-close');
      if (existingClose) {
        existingClose.remove();
      }

      const closeButton = document.createElement('button');
      closeButton.type = 'button';
      closeButton.className = 'sawerna-mobile-submenu-close';
      closeButton.setAttribute('aria-label', 'Close submenu');
      closeButton.innerHTML = closeIconSvg;
      subMenu.insertBefore(closeButton, subMenu.firstChild);

      closeButton.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        toggleSubmenu(item, link, subMenu);
      });

      link.addEventListener('click', function(e) {
        e.preventDefault();
        toggleSubmenu(item, link, subMenu);
      });
    });

    itemsWithoutChildren.forEach(function(link) {
      if (link.querySelector('.sawerna-mobile-menu__leaf-icon')) {
        return;
      }
      const icon = document.createElement('span');
      icon.className = 'sawerna-mobile-menu__leaf-icon';
      icon.setAttribute('aria-hidden', 'true');
      icon.innerHTML = subItemBulletSvg;
      link.appendChild(icon);
    });

    // Add Asset 9.svg icon after level-1 submenu titles
    const levelOneLinks = menu.querySelectorAll(':scope > li > .sub-menu > li > a');
    levelOneLinks.forEach(function(link) {
      if (link.querySelector('.sawerna-mobile-menu__submenu-icon')) {
        return;
      }
      const icon = document.createElement('span');
      icon.className = 'sawerna-mobile-menu__submenu-icon';
      icon.setAttribute('aria-hidden', 'true');
      icon.innerHTML = submenuIconSvg;
      link.appendChild(icon);
    });

    // Add Asset 5.svg icon to level-2 submenu items
    const levelTwoLinks = menu.querySelectorAll(':scope > li > .sub-menu > li > .sub-menu > li > a');
    levelTwoLinks.forEach(function(link) {
      if (link.querySelector('.sawerna-mobile-menu__item-icon')) {
        return;
      }
      const icon = document.createElement('span');
      icon.className = 'sawerna-mobile-menu__item-icon';
      icon.setAttribute('aria-hidden', 'true');
      icon.innerHTML = subItemBulletSvg;
      link.insertBefore(icon, link.firstChild);
    });
  });

  // Add Asset 8.svg icon to top bar menu items in mobile overlay
  const mobileTopbar = header.querySelector('.sawerna-mobile-topbar');
  if (mobileTopbar) {
    mobileTopbar.querySelectorAll('a').forEach(function(link) {
      if (link.querySelector('.sawerna-mobile-topbar__icon')) {
        return;
      }
      const icon = document.createElement('span');
      icon.className = 'sawerna-mobile-topbar__icon';
      icon.setAttribute('aria-hidden', 'true');
      icon.innerHTML = '<svg viewBox="0 0 14.09 11.28" xmlns="http://www.w3.org/2000/svg"><defs><style>.cls-1{fill:none;stroke:currentColor;stroke-linecap:round;stroke-linejoin:round;stroke-width:1.28px}</style></defs><g><line class="cls-1" x1=".64" y1="5.64" x2="13.25" y2="5.64"/><polyline class="cls-1" points="8.46 10.64 13.46 5.64 8.46 .64"/></g></svg>';
      link.insertBefore(icon, link.firstChild);
    });
  }
})();
