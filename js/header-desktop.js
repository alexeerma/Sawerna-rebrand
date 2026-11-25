/**
 * Desktop Header Functionality
 * Handles mega menu dropdown, search overlay, and desktop interactions
 */
(function() {
  'use strict';

  const header = document.querySelector('.sawerna-header');
  if (!header) return;

  // Mega Menu Toggle
  const megaMenuToggle = header.querySelector('.sawerna-header__mega-menu-toggle');
  const megaMenu = header.querySelector('.sawerna-header__mega-menu');
  
  if (megaMenuToggle && megaMenu) {
    function toggleMegaMenu() {
      const isExpanded = megaMenuToggle.getAttribute('aria-expanded') === 'true';
      megaMenuToggle.setAttribute('aria-expanded', !isExpanded);
      
      if (!isExpanded) {
        megaMenu.removeAttribute('hidden');
        // Close menu when clicking outside
        document.addEventListener('click', handleOutsideClick);
      } else {
        megaMenu.setAttribute('hidden', '');
        document.removeEventListener('click', handleOutsideClick);
      }
    }

    function handleOutsideClick(e) {
      if (!megaMenu.contains(e.target) && !megaMenuToggle.contains(e.target)) {
        megaMenuToggle.setAttribute('aria-expanded', 'false');
        megaMenu.setAttribute('hidden', '');
        document.removeEventListener('click', handleOutsideClick);
      }
    }

    megaMenuToggle.addEventListener('click', function(e) {
      e.stopPropagation();
      toggleMegaMenu();
    });

    // Close on escape key
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape' && megaMenuToggle.getAttribute('aria-expanded') === 'true') {
        toggleMegaMenu();
      }
    });
  }

  // Search Overlay
  const searchBtn = header.querySelector('.sawerna-header__icon-btn--search');
  const searchOverlay = header.querySelector('.sawerna-header__search-overlay');
  const searchClose = header.querySelector('.sawerna-header__search-close');
  const body = document.body;

  if (searchBtn && searchOverlay) {
    function openSearch() {
      searchBtn.setAttribute('aria-expanded', 'true');
      searchOverlay.removeAttribute('hidden');
      body.style.overflow = 'hidden';
      
      // Focus on search input
      const searchInput = searchOverlay.querySelector('input[type="search"], .search-field');
      if (searchInput) {
        setTimeout(() => searchInput.focus(), 100);
      }
    }

    function closeSearch() {
      searchBtn.setAttribute('aria-expanded', 'false');
      searchOverlay.setAttribute('hidden', '');
      body.style.overflow = '';
    }

    searchBtn.addEventListener('click', function(e) {
      e.preventDefault();
      openSearch();
    });

    if (searchClose) {
      searchClose.addEventListener('click', closeSearch);
    }

    // Close on overlay click
    searchOverlay.addEventListener('click', function(e) {
      if (e.target === searchOverlay) {
        closeSearch();
      }
    });

    // Close on escape key
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape' && !searchOverlay.hasAttribute('hidden')) {
        closeSearch();
      }
    });
  }


  // Desktop-only sticky mega menu row.
  const desktopHeaderQuery = window.matchMedia('(min-width: 1025px)');
  const promoRow = header.querySelector('.sawerna-header__row-1');
  const mainRow = header.querySelector('.sawerna-header__row-2');
  const megaRow = header.querySelector('.sawerna-header__row-3');
  let stickyFrame = null;

  function getAdminBarOffset() {
    const adminBar = document.getElementById('wpadminbar');
    if (!adminBar || window.getComputedStyle(adminBar).position !== 'fixed') {
      return 0;
    }

    return adminBar.offsetHeight || 0;
  }

  function syncStickyMegaMenu() {
    stickyFrame = null;

    if (!desktopHeaderQuery.matches || !megaRow) {
      header.classList.remove('sawerna-header--scrolled', 'sawerna-header--mega-sticky');
      header.style.removeProperty('--sawerna-sticky-mega-height');
      header.style.removeProperty('--sawerna-sticky-offset');
      return;
    }

    const stickyStart = (promoRow ? promoRow.offsetHeight : 0) + (mainRow ? mainRow.offsetHeight : 0);
    const isSticky = window.scrollY >= stickyStart;

    header.classList.toggle('sawerna-header--scrolled', isSticky);
    header.classList.toggle('sawerna-header--mega-sticky', isSticky);
    header.style.setProperty('--sawerna-sticky-mega-height', `${megaRow.offsetHeight}px`);
    header.style.setProperty('--sawerna-sticky-offset', `${getAdminBarOffset()}px`);
  }

  function requestStickyMegaMenuSync() {
    if (stickyFrame !== null) {
      return;
    }

    stickyFrame = window.requestAnimationFrame(syncStickyMegaMenu);
  }

  syncStickyMegaMenu();
  window.addEventListener('scroll', requestStickyMegaMenuSync, { passive: true });
  window.addEventListener('resize', requestStickyMegaMenuSync);
  if (typeof desktopHeaderQuery.addEventListener === 'function') {
    desktopHeaderQuery.addEventListener('change', syncStickyMegaMenu);
  } else if (typeof desktopHeaderQuery.addListener === 'function') {
    desktopHeaderQuery.addListener(syncStickyMegaMenu);
  }

  // Add dropdown icon (Asset 4.svg) to menu items that have submenus
  const megaMenuItems = header.querySelectorAll('.sawerna-mega-menu > li');
  megaMenuItems.forEach(function(item) {
    const subMenu = item.querySelector('.sub-menu');
    const isStandaloneHtmlBlock = item.classList.contains('menu-item-has-html-block');
    if (subMenu || isStandaloneHtmlBlock) {
      const link = item.querySelector('a');
      if (link && !link.querySelector('.mega-menu-arrow-icon')) {
        // Create icon element using Asset 4.svg
        const icon = document.createElement('span');
        icon.className = 'mega-menu-arrow-icon';
        icon.setAttribute('aria-hidden', 'true');
        icon.innerHTML = '<svg viewBox="0 0 9.53 5.77" xmlns="http://www.w3.org/2000/svg"><polyline fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.4" points=".7 .7 4.73 5.07 8.83 .97"/></svg>';
        link.appendChild(icon);
      }
    }
  });

  // Add Asset 4.svg icon to top bar menu items with submenus
  const topMenuItems = header.querySelectorAll('.sawerna-top-menu > li');
  topMenuItems.forEach(function(item) {
    const subMenu = item.querySelector('.sub-menu');
    if (subMenu) {
      const link = item.querySelector('a');
      if (link && !link.querySelector('.top-menu-arrow-icon')) {
        const icon = document.createElement('span');
        icon.className = 'top-menu-arrow-icon';
        icon.setAttribute('aria-hidden', 'true');
        icon.innerHTML = '<svg viewBox="0 0 9.53 5.77" xmlns="http://www.w3.org/2000/svg"><polyline fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.4" points=".7 .7 4.73 5.07 8.83 .97"/></svg>';
        link.appendChild(icon);
        item.classList.add('has-top-menu-icon');
      }
    }
  });

  // Add Asset 5.svg icon to all second-level submenu items
  const secondLevelMenuItems = header.querySelectorAll('.sawerna-mega-menu .sub-menu .sub-menu a');
  secondLevelMenuItems.forEach(function(link) {
    if (!link.querySelector('.submenu-item-icon')) {
      // Create icon element using Asset 5.svg
      const icon = document.createElement('span');
      icon.className = 'submenu-item-icon';
      icon.setAttribute('aria-hidden', 'true');
      icon.innerHTML = '<svg viewBox="0 0 4.08 6.75" xmlns="http://www.w3.org/2000/svg"><defs><style>.cls-1{fill:none;stroke:#333;stroke-linecap:round;stroke-linejoin:round;stroke-width:.99px}</style></defs><g><polyline class="cls-1" points=".5 6.25 3.59 3.4 .68 .5"/></g></svg>';
      link.insertBefore(icon, link.firstChild);
    }
  });

  // Mega-menu HTML block slot is an in-flow grid item (header-desktop.css),
  // so the panel sizes itself; no JS height sync needed.

  // HTML Block Dropdown Functionality
  // Scoped to the two desktop nav menus only: the mobile overlay renders the
  // same walker markup but mobile-menu.js drives it as an accordion instead,
  // so it must not also get this hover/click flyout behavior wired up here.
  const htmlBlockTriggers = header.querySelectorAll('.sawerna-top-menu .menu-item-html-block-trigger, .sawerna-mega-menu .menu-item-html-block-trigger');
  
  htmlBlockTriggers.forEach(function(trigger) {
    const menuItem = trigger.closest('.menu-item-has-html-block');
    const dropdown = menuItem ? menuItem.querySelector('.sawerna-html-block-dropdown') : null;
    
    if (!dropdown) return;
    
    let isOpen = false;
    let closeTimeout = null;
    
    function openDropdown() {
      if (closeTimeout) {
        clearTimeout(closeTimeout);
        closeTimeout = null;
      }
      
      // Close other open dropdowns
      document.querySelectorAll('.menu-item-html-block-trigger[aria-expanded="true"]').forEach(function(otherTrigger) {
        if (otherTrigger !== trigger) {
          otherTrigger.setAttribute('aria-expanded', 'false');
        }
      });
      
      trigger.setAttribute('aria-expanded', 'true');
      isOpen = true;
      
      // Add click outside listener
      setTimeout(function() {
        document.addEventListener('click', handleOutsideClick);
      }, 10);
    }
    
    function closeDropdown() {
      trigger.setAttribute('aria-expanded', 'false');
      isOpen = false;
      document.removeEventListener('click', handleOutsideClick);
    }
    
    function delayedClose() {
      closeTimeout = setTimeout(function() {
        closeDropdown();
      }, 200);
    }
    
    function handleOutsideClick(e) {
      if (!menuItem.contains(e.target)) {
        closeDropdown();
      }
    }
    
    // Click to toggle
    trigger.addEventListener('click', function(e) {
      e.preventDefault();
      e.stopPropagation();
      
      if (isOpen) {
        closeDropdown();
      } else {
        openDropdown();
      }
    });
    
    // Hover to open (optional - keeps dropdown open while hovering)
    menuItem.addEventListener('mouseenter', function() {
      if (closeTimeout) {
        clearTimeout(closeTimeout);
        closeTimeout = null;
      }
    });
    
    menuItem.addEventListener('mouseleave', function() {
      if (isOpen) {
        delayedClose();
      }
    });
    
    // Keep dropdown open when hovering over it
    dropdown.addEventListener('mouseenter', function() {
      if (closeTimeout) {
        clearTimeout(closeTimeout);
        closeTimeout = null;
      }
    });
    
    dropdown.addEventListener('mouseleave', function() {
      if (isOpen) {
        delayedClose();
      }
    });
    
    // Close on escape key
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape' && isOpen) {
        closeDropdown();
        trigger.focus();
      }
    });
  });
})();
