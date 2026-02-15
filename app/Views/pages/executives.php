<?= $this->extend($layout) ?>

<?= $this->section('content') ?>
<?php
$siteName = $site_info['site_name_th'] ?? $settings['site_name_th'] ?? 'คณะวิทยาศาสตร์และเทคโนโลยี';
$apiExecutivesUrl = base_url('api/executives');
?>
<div class="exec-page" id="executives-page" data-api-url="<?= esc($apiExecutivesUrl) ?>">
  <header class="exec-header">
    <h1 class="exec-header__title">โครงสร้างองค์กร</h1>
    <p class="exec-header__subtitle"><?= esc($siteName) ?></p>
  </header>

  <div id="executives-error" class="executives-error" style="display:none;">
    <p>ไม่สามารถโหลดข้อมูลได้ กรุณารีเฟรชหน้าหรือลองใหม่ในภายหลัง</p>
    <button type="button" class="executives-retry-btn" id="executives-retry-btn">ลองอีกครั้ง</button>
  </div>

  <div class="exec-main-grid" id="executives-main">
    <div class="exec-dean-area">
      <div id="executives-tier1" class="executives-section">
        <span class="executives-loading">กำลังโหลด...</span>
      </div>
    </div>
    <div class="exec-vice-area">
      <div class="exec-row" id="executives-tier2" class="executives-section">
        <span class="executives-loading">กำลังโหลด...</span>
      </div>
    </div>
    <div class="exec-asst-area">
      <div class="exec-row" id="executives-tier3" class="executives-section">
        <span class="executives-loading">กำลังโหลด...</span>
      </div>
    </div>
  </div>

  <section class="tier-section page-about-team" id="executives-head-office-wrap" style="display:none;">
    <h2 class="tier-title">หัวหน้าสำนักงาน</h2>
    <div class="team-grid" id="executives-head-office">
      <span class="executives-loading">กำลังโหลด...</span>
    </div>
  </section>

  <section class="tier-section page-about-team" id="executives-head-research-wrap" style="display:none;">
    <h2 class="tier-title">หัวหน้าหน่วยจัดการงานวิจัย</h2>
    <div class="team-grid" id="executives-head-research">
      <span class="executives-loading">กำลังโหลด...</span>
    </div>
  </section>

  <section class="tier-section page-about-team" id="executives-program-chairs-wrap" style="display:none;">
    <h2 class="tier-title">ประธานหลักสูตร</h2>
    <div class="team-grid" id="executives-program-chairs">
      <span class="executives-loading">กำลังโหลด...</span>
    </div>
  </section>

  <div id="executives-empty" class="executives-empty" style="display:none;text-align:center;padding:4rem;color:#94a3b8;">
    ยังไม่มีข้อมูลบุคลากร
  </div>
</div>

<script>
  (function() {
    var PLACEHOLDER_SVG = '<svg width="48" height="48" viewBox="0 0 24 24" fill="var(--color-gray-500)"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>';
    var DELAY_MS = 120;

    function esc(s) {
      if (s == null) return '';
      s = String(s);
      return s
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
    }

    function positionLabel(p, defaultLabel) {
      var pos = (p.position || '').trim();
      var det = (p.position_detail || '').trim();
      return (pos || defaultLabel) + (det ? ' ' + det : '');
    }

    function teamCardHtml(fullName, posLabel, imageUrl) {
      var nameEsc = esc(fullName);
      var roleEsc = esc(posLabel);
      var imgHtml;
      if (imageUrl) {
        imgHtml = '<img src="' + esc(imageUrl) + '" alt="' + nameEsc + '" class="team-card__image" onerror="this.style.display=\'none\';var n=this.nextElementSibling;if(n)n.style.display=\'flex\';">' +
          '<div class="team-card__placeholder" style="display:none;width:100%;height:100%;align-items:center;justify-content:center;background:var(--color-gray-200);">' + PLACEHOLDER_SVG + '</div>';
      } else {
        imgHtml = '<div class="team-card__placeholder" style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;background:var(--color-gray-200);">' + PLACEHOLDER_SVG + '</div>';
      }
      return '<div class="team-card animate-on-scroll">' +
        '<div class="team-card__image-wrap">' + imgHtml +
        '<div class="team-card__label">' +
        '<span class="team-card__label-name">' + nameEsc + '</span>' +
        '<span class="team-card__label-role">' + roleEsc + '</span>' +
        '</div></div></div>';
    }

    function programChairCardHtml(programDisplay, fullName, imageUrl) {
      var programEsc = esc(programDisplay);
      var nameEsc = esc(fullName);
      var roleEsc = esc('ประธานหลักสูตร · ' + fullName);
      var imgHtml;
      if (imageUrl) {
        imgHtml = '<img src="' + esc(imageUrl) + '" alt="' + programEsc + '" class="team-card__image" onerror="this.style.display=\'none\';var n=this.nextElementSibling;if(n)n.style.display=\'flex\';">' +
          '<div class="team-card__placeholder" style="display:none;width:100%;height:100%;align-items:center;justify-content:center;background:var(--color-gray-200);">' + PLACEHOLDER_SVG + '</div>';
      } else {
        imgHtml = '<div class="team-card__placeholder" style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;background:var(--color-gray-200);">' + PLACEHOLDER_SVG + '</div>';
      }
      return '<div class="team-card animate-on-scroll">' +
        '<div class="team-card__image-wrap">' + imgHtml +
        '<div class="team-card__label">' +
        '<span class="team-card__label-name">' + programEsc + '</span>' +
        '<span class="team-card__label-role">' + roleEsc + '</span>' +
        '</div></div></div>';
    }

    function renderTier1(container, list) {
      if (!list || list.length === 0) {
        container.innerHTML = '';
        return;
      }
      var p = list[0];
      var name = (p.name || '').trim();
      var pos = positionLabel(p, 'คณบดี');
      var img = p.image || '';
      container.innerHTML = teamCardHtml(name, pos, img);
    }

    function renderTier2(container, list) {
      if (!list || list.length === 0) {
        container.innerHTML = '';
        return;
      }
      var html = '';
      for (var i = 0; i < list.length; i++) {
        var p = list[i];
        var name = (p.name || '').trim();
        var pos = positionLabel(p, 'รองคณบดี');
        var img = p.image || '';
        html += teamCardHtml(name, pos, img);
      }
      container.innerHTML = html;
    }

    function renderTier3(container, list) {
      if (!list || list.length === 0) {
        container.innerHTML = '';
        return;
      }
      var html = '';
      for (var i = 0; i < list.length; i++) {
        var p = list[i];
        var name = (p.name || '').trim();
        var pos = positionLabel(p, 'ผู้ช่วยคณบดี');
        var img = p.image || '';
        html += teamCardHtml(name, pos, img);
      }
      container.innerHTML = html;
    }

    function renderTier4(container, list) {
      if (!list || list.length === 0) {
        container.innerHTML = '';
        return;
      }
      var html = '';
      for (var i = 0; i < list.length; i++) {
        var p = list[i];
        var name = (p.name || '').trim();
        var pos = positionLabel(p, '');
        var img = p.image || '';
        html += teamCardHtml(name, pos, img);
      }
      container.innerHTML = html;
    }

    function renderProgramChairs(container, list) {
      if (!list || list.length === 0) {
        container.innerHTML = '';
        return;
      }
      var html = '';
      for (var i = 0; i < list.length; i++) {
        var item = list[i];
        var programName = (item.program_name || '').trim();
        var p = item.person || {};
        var fullName = (p.name || '').trim();
        var img = p.image || '';
        var programDisplay = programName && programName.indexOf('หลักสูตร') === -1 ? 'หลักสูตร ' + programName : (programName || 'หลักสูตร');
        html += programChairCardHtml(programDisplay, fullName, img);
      }
      container.innerHTML = html;
    }

    function showError(show) {
      var el = document.getElementById('executives-error');
      if (el) el.style.display = show ? 'block' : 'none';
    }

    function showEmpty(show) {
      var el = document.getElementById('executives-empty');
      if (el) el.style.display = show ? 'block' : 'none';
    }

    function loadExecutives() {
      var page = document.getElementById('executives-page');
      var apiUrl = (page && page.getAttribute('data-api-url')) || '';
      if (!apiUrl) return;

      showError(false);
      showEmpty(false);

      var tier1El = document.getElementById('executives-tier1');
      var tier2El = document.getElementById('executives-tier2');
      var tier3El = document.getElementById('executives-tier3');
      var headOfficeEl = document.getElementById('executives-head-office');
      var headOfficeWrap = document.getElementById('executives-head-office-wrap');
      var headResearchEl = document.getElementById('executives-head-research');
      var headResearchWrap = document.getElementById('executives-head-research-wrap');
      var chairsEl = document.getElementById('executives-program-chairs');
      var chairsWrap = document.getElementById('executives-program-chairs-wrap');

      function setLoading(el, loading) {
        if (!el) return;
        if (loading) el.innerHTML = '<span class="executives-loading">กำลังโหลด...</span>';
      }

      setLoading(tier1El, true);
      setLoading(tier2El, true);
      setLoading(tier3El, true);
      setLoading(headOfficeEl, true);
      setLoading(headResearchEl, true);
      setLoading(chairsEl, true);

      fetch(apiUrl, {
          method: 'GET',
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
          }
        })
        .then(function(res) {
          return res.ok ? res.json() : Promise.reject(new Error('Network error'));
        })
        .then(function(json) {
          if (!json.success || !json.data) {
            showError(true);
            return;
          }
          var d = json.data;
          var t1 = d.tier1 || [];
          var t2 = d.tier2 || [];
          var t3 = d.tier3 || [];
          var headOffice = Array.isArray(d.headOffice) ? d.headOffice : [];
          var headResearch = Array.isArray(d.headResearch) ? d.headResearch : [];
          var chairs = d.programChairs || [];
          var hasAny = t1.length > 0 || t2.length > 0 || t3.length > 0 || headOffice.length > 0 || headResearch.length > 0 || chairs.length > 0;

          if (!hasAny) {
            if (tier1El) tier1El.innerHTML = '';
            if (tier2El) tier2El.innerHTML = '';
            if (tier3El) tier3El.innerHTML = '';
            if (headOfficeEl) headOfficeEl.innerHTML = '';
            if (headResearchEl) headResearchEl.innerHTML = '';
            if (chairsEl) chairsEl.innerHTML = '';
            showEmpty(true);
            return;
          }

          function step1() {
            renderTier1(tier1El, t1);
            setTimeout(step2, DELAY_MS);
          }

          function step2() {
            renderTier2(tier2El, t2);
            setTimeout(step3, DELAY_MS);
          }

          function step3() {
            renderTier3(tier3El, t3);
            setTimeout(step4, DELAY_MS);
          }

          function step4() {
            renderTier4(headOfficeEl, headOffice);
            if (headOfficeWrap) headOfficeWrap.style.display = headOffice.length ? '' : 'none';
            setTimeout(step5, DELAY_MS);
          }

          function step5() {
            renderTier4(headResearchEl, headResearch);
            if (headResearchWrap) headResearchWrap.style.display = headResearch.length ? '' : 'none';
            setTimeout(step6, DELAY_MS);
          }

          function step6() {
            renderProgramChairs(chairsEl, chairs);
            if (chairsWrap) chairsWrap.style.display = chairs.length ? '' : 'none';
          }
          step1();
        })
        .catch(function() {
          showError(true);
          if (tier1El) tier1El.innerHTML = '';
          if (tier2El) tier2El.innerHTML = '';
          if (tier3El) tier3El.innerHTML = '';
          if (headOfficeEl) headOfficeEl.innerHTML = '';
          if (headResearchEl) headResearchEl.innerHTML = '';
          if (chairsEl) chairsEl.innerHTML = '';
        });
    }

    var retryBtn = document.getElementById('executives-retry-btn');
    if (retryBtn) retryBtn.addEventListener('click', loadExecutives);

    loadExecutives();
  })();
</script>
<?= $this->endSection() ?>