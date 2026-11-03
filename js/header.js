/**
 * Header functionality
 * Handles mobile menu toggle and scroll detection
 */
(function() {
  'use strict';

  const header = document.querySelector('.site-header');
  if (!header) return;

  const toggleBtn = header.querySelector('.site-header__toggle');
  const closeBtn = header.querySelector('.site-header__closeBtn');
  const overlay = header.querySelector('.site-header__mobileOverlay');
  const body = document.body;

  // Scroll detection for header background
  function handleScroll() {
    if (window.scrollY > 50) {
      header.classList.add('scrolled');
    } else {
      header.classList.remove('scrolled');
    }
  }

  // Initial check
  handleScroll();
  
  // Listen for scroll events with passive listener for better performance
  window.addEventListener('scroll', handleScroll, { passive: true });

  if (!toggleBtn || !overlay) return;

  function openMenu() {
    toggleBtn.setAttribute('aria-expanded', 'true');
    overlay.removeAttribute('hidden');
    body.style.overflow = 'hidden';
  }

  function closeMenu() {
    toggleBtn.setAttribute('aria-expanded', 'false');
    overlay.setAttribute('hidden', '');
    body.style.overflow = '';
  }

  toggleBtn.addEventListener('click', function() {
    const isOpen = toggleBtn.getAttribute('aria-expanded') === 'true';
    if (isOpen) {
      closeMenu();
    } else {
      openMenu();
    }
  });

  if (closeBtn) {
    closeBtn.addEventListener('click', closeMenu);
  }

  // Close on overlay click (outside menu)
  overlay.addEventListener('click', function(e) {
    if (e.target === overlay) {
      closeMenu();
    }
  });

  // Close on escape key
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && toggleBtn.getAttribute('aria-expanded') === 'true') {
      closeMenu();
    }
  });
})();
