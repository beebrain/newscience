/**
 * Public site: mobile drawer + search modal (main_layout)
 */
(function () {
  'use strict';

  var menuBtn = document.querySelector('.menu-toggle');
  var mobileNav = document.querySelector('.mobile-nav');
  var closeBtn = document.querySelector('.mobile-nav__close');
  var searchBtn = document.querySelector('.nav__search');
  var searchModal = document.querySelector('.search-modal');
  var searchInput = searchModal ? searchModal.querySelector('.search-modal__input') : null;

  if (!menuBtn || !mobileNav) {
    return;
  }

  var bodyOverflowBeforeMenu = '';

  function isMenuOpen() {
    return mobileNav.classList.contains('active');
  }

  function closeSearch() {
    if (!searchModal || !searchModal.classList.contains('active')) {
      return;
    }
    searchModal.classList.remove('active');
    if (searchInput) {
      searchInput.blur();
    }
  }

  function openSearch() {
    if (!searchModal) {
      return;
    }
    if (isMenuOpen()) {
      closeMenu();
    }
    searchModal.classList.add('active');
    if (searchInput) {
      window.setTimeout(function () {
        searchInput.focus();
      }, 50);
    }
  }

  function openMenu() {
    closeSearch();
    mobileNav.classList.add('active');
    menuBtn.setAttribute('aria-expanded', 'true');
    bodyOverflowBeforeMenu = document.body.style.overflow;
    document.body.style.overflow = 'hidden';
    var list = mobileNav.querySelector('.mobile-nav__list');
    if (list) {
      list.scrollTop = 0;
    }
  }

  function closeMenu() {
    mobileNav.classList.remove('active');
    menuBtn.setAttribute('aria-expanded', 'false');
    document.body.style.overflow = bodyOverflowBeforeMenu;
  }

  function toggleMenu() {
    if (isMenuOpen()) {
      closeMenu();
    } else {
      openMenu();
    }
  }

  menuBtn.setAttribute('aria-expanded', 'false');
  if (mobileNav.id) {
    menuBtn.setAttribute('aria-controls', mobileNav.id);
  }

  menuBtn.addEventListener('click', function (e) {
    e.preventDefault();
    toggleMenu();
  });

  if (closeBtn) {
    closeBtn.addEventListener('click', function (e) {
      e.preventDefault();
      closeMenu();
    });
  }

  if (searchBtn && searchModal) {
    searchBtn.addEventListener('click', function (e) {
      e.preventDefault();
      if (searchModal.classList.contains('active')) {
        closeSearch();
      } else {
        openSearch();
      }
    });

    searchModal.addEventListener('click', function (e) {
      if (e.target === searchModal) {
        closeSearch();
      }
    });
  }

  document.addEventListener('keydown', function (e) {
    if (e.key !== 'Escape') {
      return;
    }
    if (isMenuOpen()) {
      e.preventDefault();
      closeMenu();
      return;
    }
    if (searchModal && searchModal.classList.contains('active')) {
      e.preventDefault();
      closeSearch();
    }
  });
})();
