(function () {
  'use strict';

  var config = {
    minNameLength: 2,
    minEmailLength: 3,
    nameDelay: 300,
    emailDelay: 500,
  };
  var timers = new WeakMap();
  var activeDropdown = null;

  function endpoints() {
    return window.CV_AUTHOR_SEARCH_ENDPOINTS || {};
  }

  function escapeHtml(value) {
    return String(value || '').replace(/[&<>"']/g, function (ch) {
      return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' })[ch];
    });
  }

  function rowFor(input) {
    return input.closest('[data-author-index]');
  }

  function clearDropdown() {
    if (activeDropdown && activeDropdown.parentNode) {
      activeDropdown.parentNode.removeChild(activeDropdown);
    }
    activeDropdown = null;
  }

  function syncHidden() {
    if (typeof window.syncPublicationAuthorsHidden === 'function') {
      window.syncPublicationAuthorsHidden();
    }
    var hidden = document.getElementById('cv-m-authors-json');
    var list = document.getElementById('cv-m-authors-list');
    if (!hidden || !list) return;
    var out = [];
    list.querySelectorAll('[data-author-index]').forEach(function (row, i) {
      var name = (row.querySelector('.cv-author-name') || {}).value || '';
      var email = (row.querySelector('.cv-author-email') || {}).value || '';
      name = name.trim();
      email = email.trim().toLowerCase();
      if (!name && !email) return;
      out.push({
        name: name,
        email: email,
        affiliation: ((row.querySelector('.cv-author-aff') || {}).value || '').trim(),
        corresponding: (row.querySelector('.cv-author-corr') || {}).checked ? 1 : 0,
        order: i + 1,
      });
    });
    hidden.value = JSON.stringify(out);
  }

  function applyResult(input, item) {
    var row = rowFor(input);
    if (!row || !item) return;
    var nameInput = row.querySelector('.cv-author-name');
    var emailInput = row.querySelector('.cv-author-email');
    var affInput = row.querySelector('.cv-author-aff');
    if (nameInput && item.name) nameInput.value = item.name;
    if (emailInput && item.email) emailInput.value = item.email;
    if (affInput && item.affiliation) affInput.value = item.affiliation;
    row.dataset.personnelId = item.personnel_id || '';
    syncHidden();
    clearDropdown();
  }

  function showDropdown(input, results) {
    clearDropdown();
    if (!results || !results.length) return;

    var rect = input.getBoundingClientRect();
    var dropdown = document.createElement('div');
    dropdown.className = 'cv-author-search-dropdown';
    dropdown.style.position = 'fixed';
    dropdown.style.left = rect.left + 'px';
    dropdown.style.top = rect.bottom + 4 + 'px';
    dropdown.style.width = Math.max(rect.width, 260) + 'px';
    dropdown.style.zIndex = '10050';
    dropdown.innerHTML = results.map(function (item, idx) {
      return '<button type="button" class="cv-author-search-item" data-index="' + idx + '">' +
        '<span class="cv-author-search-name">' + escapeHtml(item.name || item.email) + '</span>' +
        '<span class="cv-author-search-email">' + escapeHtml(item.email || '') + '</span>' +
        '</button>';
    }).join('');
    dropdown.addEventListener('mousedown', function (event) {
      event.preventDefault();
      var btn = event.target.closest('.cv-author-search-item');
      if (!btn) return;
      applyResult(input, results[parseInt(btn.dataset.index || '0', 10)]);
    });
    document.body.appendChild(dropdown);
    activeDropdown = dropdown;
  }

  function fetchJson(url) {
    return fetch(url, { headers: { Accept: 'application/json' }, credentials: 'same-origin' })
      .then(function (res) { return res.ok ? res.json() : null; })
      .catch(function () { return null; });
  }

  function schedule(input, delay, fn) {
    window.clearTimeout(timers.get(input));
    timers.set(input, window.setTimeout(fn, delay));
  }

  function searchName(input) {
    var nameUrl = endpoints().name;
    var value = input.value.trim();
    if (!nameUrl || value.length < config.minNameLength) {
      clearDropdown();
      return;
    }
    var url = nameUrl + '?' + new URLSearchParams({ name: value, limit: '10' }).toString();
    fetchJson(url).then(function (data) {
      showDropdown(input, data && data.success ? data.results || [] : []);
    });
  }

  function resolveEmail(input) {
    var emailUrl = endpoints().email;
    var value = input.value.trim();
    if (!emailUrl || value.length < config.minEmailLength) return;
    var url = emailUrl + '?' + new URLSearchParams({ email: value }).toString();
    fetchJson(url).then(function (data) {
      if (data && data.success && data.found && data.result) {
        applyResult(input, data.result);
      }
    });
  }

  function handleInput(event) {
    var input = event.target;
    if (input.classList.contains('cv-author-name')) {
      schedule(input, config.nameDelay, function () { searchName(input); });
    } else if (input.classList.contains('cv-author-email')) {
      schedule(input, config.emailDelay, function () { resolveEmail(input); });
    }
  }

  function init() {
    var list = document.getElementById('cv-m-authors-list');
    if (!list || list.dataset.authorSearchBound === '1') return;
    list.dataset.authorSearchBound = '1';
    list.addEventListener('input', handleInput);
    list.addEventListener('focusin', function (event) {
      if (event.target.classList && event.target.classList.contains('cv-author-name')) {
        searchName(event.target);
      }
    });
    document.addEventListener('mousedown', function (event) {
      if (activeDropdown && !activeDropdown.contains(event.target)) {
        clearDropdown();
      }
    });
    window.addEventListener('scroll', clearDropdown, true);
  }

  window.CvPublicationAuthorSearch = { init: init, refresh: init };
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
