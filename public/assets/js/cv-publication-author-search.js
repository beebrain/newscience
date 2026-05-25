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
  var activeInput = null;
  var globalBound = false;

  function endpoints() {
    var ep = window.CV_AUTHOR_SEARCH_ENDPOINTS || window.CV_PUB_PAGE && window.CV_PUB_PAGE.endpoints || {};
    return ep;
  }

  function nameSearchUrl() {
    var ep = endpoints();
    return ep.names || ep.name || '';
  }

  function authorListSelector() {
    return '#cv-p-authors-list, #cv-m-authors-list';
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
    activeInput = null;
  }

  function syncHidden() {
    if (typeof window.syncPublicationAuthorsHidden === 'function') {
      window.syncPublicationAuthorsHidden();
    }
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
    clearDropdown();
    if (typeof window.renderPublicationAuthors === 'function' && typeof window.collectPublicationAuthors === 'function') {
      var authors = window.collectPublicationAuthors();
      if (typeof window.dedupePublicationAuthors === 'function') {
        authors = window.dedupePublicationAuthors(authors);
      }
      window.renderPublicationAuthors(authors);
      return;
    }
    syncHidden();
  }

  function showDropdown(input, results, emptyHint) {
    clearDropdown();
    activeInput = input;

    var rect = input.getBoundingClientRect();
    var dropdown = document.createElement('div');
    dropdown.className = 'cv-author-search-dropdown';
    dropdown.setAttribute('role', 'listbox');
    dropdown.style.position = 'fixed';
    dropdown.style.left = rect.left + 'px';
    dropdown.style.top = rect.bottom + 4 + 'px';
    dropdown.style.width = Math.max(rect.width, 280) + 'px';
    dropdown.style.zIndex = '10050';

    if (!results || !results.length) {
      dropdown.innerHTML = '<div class="cv-author-search-empty px-3 py-2 text-sm text-slate-500">' +
        escapeHtml(emptyHint || 'ไม่พบรายชื่อ — พิมพ์ชื่อหรืออีเมลอย่างน้อย 2 ตัวอักษร') +
        '</div>';
      document.body.appendChild(dropdown);
      activeDropdown = dropdown;
      return;
    }

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
      .then(function (res) {
        return res.json().then(function (data) {
          return { ok: res.ok, status: res.status, data: data };
        }).catch(function () {
          return { ok: false, status: res.status, data: null };
        });
      })
      .catch(function () {
        return { ok: false, status: 0, data: null };
      });
  }

  function schedule(input, delay, fn) {
    window.clearTimeout(timers.get(input));
    timers.set(input, window.setTimeout(fn, delay));
  }

  function searchName(input) {
    var nameUrl = nameSearchUrl();
    var value = input.value.trim();
    if (!nameUrl) {
      showDropdown(input, [], 'ยังไม่ได้ตั้ง URL ค้นหาบุคลากร');
      return;
    }
    if (value.length < config.minNameLength) {
      clearDropdown();
      return;
    }
    var url = nameUrl + (nameUrl.indexOf('?') >= 0 ? '&' : '?') + new URLSearchParams({ name: value, limit: '10' }).toString();
    fetchJson(url).then(function (res) {
      if (!res.ok || !res.data) {
        showDropdown(input, [], res.status === 401 ? 'กรุณาเข้าสู่ระบบใหม่' : 'ค้นหาไม่สำเร็จ (HTTP ' + res.status + ')');
        return;
      }
      var data = res.data;
      showDropdown(input, data.success ? data.results || [] : [], data.message || 'ไม่พบรายชื่อ');
    });
  }

  function resolveEmail(input) {
    var emailUrl = endpoints().email;
    var value = input.value.trim().toLowerCase();
    if (!emailUrl || value.length < config.minEmailLength) return;
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) return;
    var url = emailUrl + (emailUrl.indexOf('?') >= 0 ? '&' : '?') + new URLSearchParams({ email: value }).toString();
    fetchJson(url).then(function (res) {
      if (!res.ok || !res.data) return;
      var data = res.data;
      if (data.success && data.found && data.result) {
        applyResult(input, data.result);
      }
    });
  }

  function handleInput(event) {
    var input = event.target;
    if (!input.closest(authorListSelector())) return;
    if (input.classList.contains('cv-author-name')) {
      schedule(input, config.nameDelay, function () { searchName(input); });
    } else if (input.classList.contains('cv-author-email')) {
      schedule(input, config.emailDelay, function () { resolveEmail(input); });
    }
  }

  function handleFocusIn(event) {
    var input = event.target;
    if (!input.closest(authorListSelector())) return;
    if (input.classList.contains('cv-author-name')) {
      searchName(input);
    }
  }

  function bindGlobal() {
    if (globalBound) return;
    globalBound = true;
    document.addEventListener('input', handleInput, true);
    document.addEventListener('focusin', handleFocusIn, true);
    document.addEventListener('mousedown', function (event) {
      if (activeDropdown && !activeDropdown.contains(event.target)) {
        var inList = event.target.closest && event.target.closest(authorListSelector());
        if (!inList || !event.target.classList.contains('cv-author-name')) {
          clearDropdown();
        }
      }
    });
    window.addEventListener('scroll', function () {
      if (activeDropdown && activeInput) {
        var rect = activeInput.getBoundingClientRect();
        activeDropdown.style.left = rect.left + 'px';
        activeDropdown.style.top = rect.bottom + 4 + 'px';
      }
    }, true);
    window.addEventListener('resize', clearDropdown);
  }

  function init() {
    bindGlobal();
  }

  window.CvPublicationAuthorSearch = { init: init, refresh: init };
  init();
})();
