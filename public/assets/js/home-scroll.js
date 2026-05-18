/**
 * Home page: reveal .animate-on-scroll elements (pairs with home.css).
 */
(function () {
  'use strict';

  var observer = null;

  function reveal(el) {
    if (!el || el.classList.contains('visible')) {
      return;
    }
    el.classList.add('visible');
    if (observer) {
      observer.unobserve(el);
    }
  }

  function ensureObserver() {
    if (observer) {
      return observer;
    }
    if (!('IntersectionObserver' in window)) {
      return null;
    }
    observer = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          reveal(entry.target);
        }
      });
    }, { threshold: 0.08, rootMargin: '0px 0px -5% 0px' });
    return observer;
  }

  function collectElements(root) {
    if (root && root.nodeType === 1) {
      var list = [];
      if (root.classList.contains('animate-on-scroll') && !root.classList.contains('visible')) {
        list.push(root);
      }
      return list.concat(Array.from(root.querySelectorAll('.animate-on-scroll:not(.visible)')));
    }
    return Array.from(document.querySelectorAll('.animate-on-scroll:not(.visible)'));
  }

  function initAnimations(root) {
    var els = collectElements(root);
    if (!els.length) {
      return;
    }
    var obs = ensureObserver();
    if (!obs) {
      els.forEach(reveal);
      return;
    }
    els.forEach(function (el) {
      obs.observe(el);
    });
  }

  window.initAnimations = initAnimations;

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function () {
      initAnimations();
    });
  } else {
    initAnimations();
  }
})();
