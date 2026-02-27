/**
 * program-detail.js — AUN-QA Program Detail Page
 * jQuery/AJAX logic for dynamic content loading, accordion, ELO interaction, smooth scroll.
 * Depends on: jQuery (loaded from main_layout), theme.css + base.css + program-detail.css
 */

(function ($) {
    'use strict';

    var ICONS = {
        cpu: '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="4" width="16" height="16" rx="2"/><rect x="9" y="9" width="6" height="6"/><line x1="9" y1="1" x2="9" y2="4"/><line x1="15" y1="1" x2="15" y2="4"/><line x1="9" y1="20" x2="9" y2="23"/><line x1="15" y1="20" x2="15" y2="23"/><line x1="20" y1="9" x2="23" y2="9"/><line x1="20" y1="14" x2="23" y2="14"/><line x1="1" y1="9" x2="4" y2="9"/><line x1="1" y1="14" x2="4" y2="14"/></svg>',
        chart: '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>',
        search: '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>',
        code: '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>',
        users: '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>',
        rocket: '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4.5 16.5c-1.5 1.26-2 5-2 5s3.74-.5 5-2c.71-.84.7-2.13-.09-2.91a2.18 2.18 0 00-2.91-.09z"/><path d="M12 15l-3-3a22 22 0 012-3.95A12.88 12.88 0 0122 2c0 2.72-.78 7.5-6 11a22.35 22.35 0 01-4 2z"/><path d="M9 12H4s.55-3.03 2-4c1.62-1.08 5 0 5 0"/><path d="M12 15v5s3.03-.55 4-2c1.08-1.62 0-5 0-5"/></svg>',
        file: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>',
        lock: '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>',
        download: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>',
        chevron: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>',
        mortar: '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c0 2 2 4 6 4s6-2 6-4v-5"/></svg>',
        target: '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="6"/><circle cx="12" cy="12" r="2"/></svg>',
        user: '<svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>'
    };

    function getUrlParam(key) {
        var params = new URLSearchParams(window.location.search);
        return params.get(key);
    }

    function loadProgramData(idOrSlug) {
        var baseUrl = (window.BASE_URL || '').replace(/\/$/, '');
        if (!baseUrl && typeof window.location !== 'undefined') {
            var path = window.location.pathname || '';
            var idx = path.indexOf('/index.php');
            baseUrl = idx >= 0 ? path.substring(0, idx + 10) : path;
        }
        if (!baseUrl) baseUrl = '/newScience/public';

        var numericId = parseInt(idOrSlug, 10);
        if (!isNaN(numericId) && numericId > 0) {
            return $.ajax({
                url: baseUrl + '/api/program/' + numericId,
                method: 'GET',
                dataType: 'json'
            }).then(function (response) {
                if (response && response.success && response.data) {
                    return response.data;
                }
                return $.Deferred().reject({ error: 'Invalid response' }).promise();
            });
        }

        return $.Deferred().reject({ error: 'Program not found' }).promise();
    }

    // =====================================================================
    //  DOM Renderers
    // =====================================================================

    function renderHero(data) {
        $('#pd-hero-badge').text(data.level || '');
        $('#pd-hero-title').text(data.name_th || '');
        $('#pd-hero-degree').text((data.degree_th || '') + (data.degree_en ? ' / ' + data.degree_en : ''));
        $('#pd-hero-credits').text(data.credits || '-');
        $('#pd-hero-duration').text(data.duration || '-');

        if (data.hero_image) {
            $('#pd-hero-bg').css('background-image', 'url(' + data.hero_image + ')');
        }
    }

    function renderOverview(data) {
        var boxes = [];
        if (data.philosophy) {
            boxes.push({ label: 'ปรัชญา (Philosophy)', text: data.philosophy });
        }
        if (data.vision) {
            boxes.push({ label: 'วัตถุประสงค์ (Objectives)', text: data.vision });
        }
        if (data.graduate_profile) {
            boxes.push({ label: 'คุณลักษณะบัณฑิต (Graduate Profile)', text: data.graduate_profile });
        }

        if (boxes.length === 0) {
            $('#pd-overview').hide();
            return;
        }

        var $section = $('#pd-overview-section');
        $section.empty();
        $.each(boxes, function (i, box) {
            $section.append(
                '<div class="pd-overview-box">' +
                '<div class="pd-overview-box__label">' + escHtml(box.label) + '</div>' +
                '<p class="pd-overview-box__text">' + escHtml(box.text) + '</p>' +
                '</div>'
            );
        });
        if (boxes.length === 1) {
            $section.css('grid-template-columns', '1fr');
        }
        $('#pd-overview').show();
    }

    function renderELOs(data) {
        var $grid = $('#pd-elo-grid');
        $grid.empty();

        if (!data.elos || !data.elos.length) {
            $('#pd-elo').hide();
            return;
        }

        $.each(data.elos, function (i, elo) {
            var html = '<div class="pd-elo-card" data-index="' + i + '">' +
                '<div class="pd-elo-card__icon">' + ICONS.target + '</div>' +
                '<div class="pd-elo-card__category">' + escHtml(elo.category) + '</div>' +
                '<h4 class="pd-elo-card__title">' + escHtml(elo.title) + '</h4>' +
                '<p class="pd-elo-card__summary">' + escHtml(elo.summary) + '</p>' +
                '<div class="pd-elo-card__detail">' + escHtml(elo.detail) + '</div>' +
                '<span class="pd-elo-card__toggle">รายละเอียด ' + ICONS.chevron + '</span>' +
                '</div>';
            $grid.append(html);
        });
    }

    function renderCurriculum(data) {
        var $acc = $('#pd-curriculum-accordion');
        $acc.empty();

        if (!data.curriculum || !data.curriculum.length) {
            $('#pd-curriculum').hide();
            return;
        }

        $.each(data.curriculum, function (i, year) {
            var semestersHtml = '';
            $.each(year.semesters, function (j, sem) {
                var rows = '';
                $.each(sem.courses, function (k, c) {
                    rows += '<tr>' +
                        '<td>' + escHtml(c.code) + '</td>' +
                        '<td>' + escHtml(c.name) + '</td>' +
                        '<td class="credits">' + c.credits + '</td>' +
                        '</tr>';
                });
                semestersHtml += '<div class="pd-accordion__semester">' +
                    '<h5 class="pd-accordion__semester-title">' + escHtml(sem.name) + '</h5>' +
                    '<table class="pd-course-table"><thead><tr><th>รหัสวิชา</th><th>ชื่อวิชา</th><th>หน่วยกิต</th></tr></thead>' +
                    '<tbody>' + rows + '</tbody></table></div>';
            });

            var item = '<div class="pd-accordion__item' + (i === 0 ? ' open' : '') + '">' +
                '<div class="pd-accordion__header">' +
                '<div class="pd-accordion__header-left">' +
                '<span class="pd-accordion__year-badge">' + year.year + '</span>' +
                '<div><div class="pd-accordion__header-title">' + escHtml(year.title) + '</div>' +
                '<div class="pd-accordion__header-meta">รวม ' + year.total_credits + ' หน่วยกิต</div></div>' +
                '</div>' +
                '<svg class="pd-accordion__chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>' +
                '</div>' +
                '<div class="pd-accordion__panel"' + (i === 0 ? ' style="display:block"' : '') + '>' + semestersHtml + '</div>' +
                '</div>';
            $acc.append(item);
        });
    }

    function renderExtraContent(data) {
        var sections = [
            { key: 'curriculum_structure', title: 'โครงสร้างหลักสูตร' },
            { key: 'study_plan', title: 'แผนการเรียน' },
            { key: 'career_prospects', title: 'อาชีพที่สามารถประกอบได้' },
            { key: 'tuition_fees', title: 'ค่าเล่าเรียน/ค่าธรรมเนียม' },
            { key: 'admission_info', title: 'การรับสมัคร' },
            { key: 'contact_info', title: 'ข้อมูลติดต่อ' }
        ];

        var $container = $('#pd-extra-content');
        $container.empty();
        var hasContent = false;

        $.each(sections, function (i, sec) {
            var content = data[sec.key];
            if (!content || !content.trim()) return;
            hasContent = true;
            $container.append(
                '<div class="pd-content-block">' +
                '<h3 class="pd-content-block__title">' + escHtml(sec.title) + '</h3>' +
                '<div class="pd-content-block__body">' + content + '</div>' +
                '</div>'
            );
        });

        if (!hasContent) {
            $('#pd-content-sections').hide();
        }
    }

    function renderCareers(data) {
        var $grid = $('#pd-career-grid');
        if (!$grid.length) return;
        $grid.empty();

        if (!data.careers || !data.careers.length) {
            $('#pd-career').hide();
            return;
        }

        $.each(data.careers, function (i, career) {
            var icon = ICONS[career.icon] || ICONS.rocket;
            var html = '<div class="pd-career-item" style="transition-delay:' + (i * 80) + 'ms">' +
                '<div class="pd-career-item__icon">' + icon + '</div>' +
                '<div>' +
                '<div class="pd-career-item__title">' + escHtml(career.title) + '</div>' +
                '<p class="pd-career-item__desc">' + escHtml(career.desc) + '</p>' +
                '</div></div>';
            $grid.append(html);
        });
    }

    function renderStaff(data) {
        var $grid = $('#pd-staff-grid');
        $grid.empty();

        if (!data.staff || !data.staff.length) {
            $('#pd-staff').hide();
            return;
        }

        $.each(data.staff, function (i, person) {
            var imgHtml;
            if (person.image) {
                imgHtml = '<img src="' + escAttr(person.image) + '" alt="' + escAttr(person.name) + '" class="pd-staff-card__image">';
            } else {
                imgHtml = '<div class="pd-staff-card__placeholder">' + ICONS.user + '</div>';
            }

            var roleHtml = person.role ? '<span class="pd-staff-card__role">' + escHtml(person.role) + '</span>' : '';

            var html = '<div class="pd-staff-card">' +
                imgHtml +
                '<div class="pd-staff-card__name">' + escHtml(person.name) + '</div>' +
                '<div class="pd-staff-card__position">' + escHtml(person.position) + '</div>' +
                roleHtml +
                '</div>';
            $grid.append(html);
        });
    }

    function renderDocuments(data) {
        var $list = $('#pd-doc-list');
        $list.empty();

        if (!data.documents || !data.documents.length) {
            $('#pd-docs').hide();
            return;
        }

        $.each(data.documents, function (i, doc) {
            var isLocked = !doc.is_public;
            var lockClass = isLocked ? ' pd-doc-item--locked' : '';
            var actionHtml;

            if (isLocked) {
                actionHtml = '<span class="pd-doc-item__lock">' + ICONS.lock + ' ล็อค</span>';
            } else {
                actionHtml = '<a href="' + escAttr(doc.url) + '" class="btn btn-primary" style="padding:0.4rem 1rem;font-size:0.85rem;" download>' + ICONS.download + ' ดาวน์โหลด</a>';
            }

            var html = '<div class="pd-doc-item' + lockClass + '">' +
                '<div class="pd-doc-item__icon">' + ICONS.file + '</div>' +
                '<div class="pd-doc-item__info">' +
                '<div class="pd-doc-item__title">' + escHtml(doc.title) + '</div>' +
                '<div class="pd-doc-item__meta">' + escHtml(doc.type) + ' • ' + escHtml(doc.size) + '</div>' +
                '</div>' +
                '<div class="pd-doc-item__action">' + actionHtml + '</div>' +
                '</div>';
            $list.append(html);
        });
    }

    function renderNews(data) {
        var $grid = $('#pd-news-grid');

        if (data.news && data.news.length) {
            renderNewsCards($grid, data.news);
            return;
        }

        $grid.html('<p class="text-muted" style="grid-column:1/-1;text-align:center;padding:2rem;">ยังไม่มีข่าวสาร</p>');
    }

    function renderNewsCards($grid, items) {
        $grid.empty();
        var baseUrl = (window.BASE_URL || '').replace(/\/$/, '');
        $.each(items, function (i, item) {
            var imgSrc = item.image_url || item.thumbnail || '';
            var imgHtml = imgSrc
                ? '<div class="news-card__image"><img src="' + escAttr(imgSrc) + '" alt=""></div>'
                : '';
            var link = item.url || (baseUrl + '/news/' + item.id);
            var html = '<a href="' + escAttr(link) + '" class="news-card" style="text-decoration:none;color:inherit;">' +
                imgHtml +
                '<div class="news-card__content">' +
                '<span class="news-card__date">' + escHtml(item.date || item.created_at || '') + '</span>' +
                '<h4 class="news-card__title">' + escHtml(item.title_th || item.title || '') + '</h4>' +
                '<p class="news-card__excerpt">' + escHtml(item.excerpt || '') + '</p>' +
                '</div></a>';
            $grid.append(html);
        });
    }

    function renderFacilities(data) {
        var $grid = $('#pd-facilities-grid');
        var $section = $('#pd-facilities');
        if (!data.facilities || !data.facilities.length) {
            $section.hide();
            return;
        }
        $grid.empty();
        var facilityIcons = {
            lab: 'M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0112 15a9.065 9.065 0 00-6.23.693L5 14.5m14.8.8l1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0112 21c-2.773 0-5.491-.235-8.135-.687-1.718-.293-2.3-2.379-1.067-3.61L5 14.5',
            server: 'M21.75 17.25v-.228a4.5 4.5 0 00-.12-1.03l-2.268-9.64a3.375 3.375 0 00-3.285-2.602H7.923a3.375 3.375 0 00-3.285 2.602l-2.268 9.64a4.5 4.5 0 00-.12 1.03v.228m19.5 0a3 3 0 01-3 3H5.25a3 3 0 01-3-3m19.5 0a3 3 0 00-3-3H5.25a3 3 0 00-3 3m16.5 0h.008v.008h-.008v-.008zm-3 0h.008v.008h-.008v-.008z',
            coworking: 'M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3H21m-3.75 3H21'
        };
        $.each(data.facilities, function (i, f) {
            var iconPath = facilityIcons[f.facility_type] || facilityIcons.lab;
            var imgHtml = f.image
                ? '<div class="pd-facility-card__img"><img src="' + escAttr(f.image) + '" alt=""></div>'
                : '<div class="pd-facility-card__placeholder"><svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1"><path stroke-linecap="round" stroke-linejoin="round" d="' + iconPath + '"/></svg></div>';
            var html = '<div class="pd-facility-card">' +
                imgHtml +
                '<div class="pd-facility-card__body">' +
                '<h4 class="pd-facility-card__title">' + escHtml(f.title || '') + '</h4>' +
                '<p class="pd-facility-card__desc">' + escHtml((f.description || '').substring(0, 200)) + (f.description && f.description.length > 200 ? '...' : '') + '</p>' +
                '</div></div>';
            $grid.append(html);
        });
        $section.show();
    }

    function renderVideo(data) {
        if (!data.intro_video_url) return;
        var url = data.intro_video_url;
        var $wrap = $('#pd-video-wrap');

        if (url.indexOf('youtube.com') !== -1 || url.indexOf('youtu.be') !== -1) {
            var match = url.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([^&\n?#]+)/);
            if (match) {
                $wrap.html('<iframe src="https://www.youtube.com/embed/' + escAttr(match[1]) + '" class="program-video__iframe" allowfullscreen></iframe>');
                $('#pd-video').show();
            }
        } else if (url) {
            $wrap.html('<video controls class="program-video__player"><source src="' + escAttr(url) + '" type="video/mp4"></video>');
            $('#pd-video').show();
        }
    }

    // =====================================================================
    //  Interactivity
    // =====================================================================

    $(document).on('click', '.pd-accordion__header', function () {
        var $item = $(this).closest('.pd-accordion__item');
        var $panel = $item.find('.pd-accordion__panel');

        if ($item.hasClass('open')) {
            $panel.slideUp(300);
            $item.removeClass('open');
        } else {
            $('.pd-accordion__item.open').each(function () {
                $(this).find('.pd-accordion__panel').slideUp(300);
                $(this).removeClass('open');
            });
            $panel.slideDown(300);
            $item.addClass('open');
        }
    });

    $(document).on('click', '.pd-elo-card', function () {
        var $card = $(this);
        var $detail = $card.find('.pd-elo-card__detail');

        if ($card.hasClass('expanded')) {
            $detail.slideUp(250);
            $card.removeClass('expanded');
        } else {
            $detail.slideDown(250);
            $card.addClass('expanded');
        }
    });

    $(document).on('click', '.pd-sticky-nav__link', function (e) {
        e.preventDefault();
        var targetId = $(this).attr('href');
        var $target = $(targetId);
        if ($target.length) {
            var headerH = parseInt(getComputedStyle(document.documentElement).getPropertyValue('--header-height')) || 80;
            var navH = $('.pd-sticky-nav').outerHeight() || 50;
            var offset = headerH + navH + 16;

            $('html, body').animate({
                scrollTop: $target.offset().top - offset
            }, 500);
        }
    });

    function updateActiveNav() {
        var scrollPos = $(window).scrollTop();
        var headerH = parseInt(getComputedStyle(document.documentElement).getPropertyValue('--header-height')) || 80;
        var navH = $('.pd-sticky-nav').outerHeight() || 50;
        var offset = headerH + navH + 40;

        $('.pd-sticky-nav__link').each(function () {
            var $link = $(this);
            var targetId = $link.attr('href');
            var $target = $(targetId);

            if ($target.length && $target.is(':visible')) {
                var sectionTop = $target.offset().top - offset;
                var sectionBottom = sectionTop + $target.outerHeight();

                if (scrollPos >= sectionTop && scrollPos < sectionBottom) {
                    $('.pd-sticky-nav__link').removeClass('active');
                    $link.addClass('active');
                }
            }
        });
    }

    // =====================================================================
    //  Scroll Animations (IntersectionObserver)
    // =====================================================================
    function initScrollAnimations() {
        if (!('IntersectionObserver' in window)) {
            $('.pd-section').addClass('visible');
            $('.pd-career-item').addClass('visible');
            return;
        }

        var sectionObserver = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    $(entry.target).addClass('visible');
                    sectionObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1 });

        $('.pd-section:visible').each(function () {
            sectionObserver.observe(this);
        });

        setTimeout(function () {
            var careerObserver = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        $(entry.target).addClass('visible');
                        careerObserver.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.1 });

            $('.pd-career-item').each(function () {
                careerObserver.observe(this);
            });
        }, 100);
    }

    // =====================================================================
    //  Helpers
    // =====================================================================
    function escHtml(str) {
        if (!str) return '';
        return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    function escAttr(str) {
        return escHtml(str);
    }

    // =====================================================================
    //  Init
    // =====================================================================
    $(document).ready(function () {
        var param = window.PROGRAM_ID || getUrlParam('id') || getUrlParam('program');

        if (!param) {
            $('#pd-content').html('<div class="pd-loading"><p>ไม่พบพารามิเตอร์หลักสูตร กรุณาระบุ ?id=1</p></div>');
            return;
        }

        $('#pd-content').html('<div class="pd-loading"><div class="spinner"></div><p>กำลังโหลดข้อมูลหลักสูตร...</p></div>');

        loadProgramData(param)
            .then(function (data) {
                $('#pd-content').remove();
                $('#pd-real-content').show();

                renderHero(data);
                renderOverview(data);
                renderELOs(data);
                renderCurriculum(data);
                renderExtraContent(data);
                renderCareers(data);
                renderStaff(data);
                renderFacilities(data);
                renderDocuments(data);
                renderNews(data);
                renderVideo(data);

                initScrollAnimations();

                if (data.name_th) {
                    document.title = data.name_th + ' | คณะวิทยาศาสตร์และเทคโนโลยี';
                }

                if (data.theme_color) {
                    document.documentElement.style.setProperty('--program-accent', data.theme_color);
                }
            })
            .fail(function () {
                $('#pd-content').html('<div class="pd-loading"><p style="color:var(--color-error);">ไม่พบข้อมูลหลักสูตร</p>' +
                    '<a href="' + (window.BASE_URL || '/') + '/academics" class="btn btn-primary" style="margin-top:1rem;">กลับไปหน้าหลักสูตร</a></div>');
            });

        var scrollTimer;
        $(window).on('scroll', function () {
            clearTimeout(scrollTimer);
            scrollTimer = setTimeout(updateActiveNav, 50);
        });
    });

})(jQuery);
