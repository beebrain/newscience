/**
 * University Website API Module
 * Handles all AJAX data fetching with jQuery
 */

const UniversityAPI = (function ($) {
    'use strict';

    // Configuration
    const config = {
        baseUrl: window.BASE_URL || '/newScience/public/',
        apiPath: 'api/',
        defaultLimit: 6
    };

    // Cache for API responses
    const cache = new Map();
    const CACHE_TTL = 5 * 60 * 1000; // 5 minutes

    /**
     * Make AJAX request to API
     */
    function request(endpoint, options = {}) {
        const url = config.baseUrl + config.apiPath + endpoint;
        const cacheKey = url + JSON.stringify(options.data || {});

        // Check cache
        if (options.cache !== false && cache.has(cacheKey)) {
            const cached = cache.get(cacheKey);
            if (Date.now() - cached.timestamp < CACHE_TTL) {
                return Promise.resolve(cached.data);
            }
            cache.delete(cacheKey);
        }

        return new Promise((resolve, reject) => {
            $.ajax({
                url: url,
                method: options.method || 'GET',
                data: options.data || {},
                dataType: 'json',
                beforeSend: function () {
                    if (options.loader) {
                        $(options.loader).addClass('loading');
                    }
                },
                success: function (response) {
                    if (options.cache !== false) {
                        cache.set(cacheKey, {
                            data: response,
                            timestamp: Date.now()
                        });
                    }
                    resolve(response);
                },
                error: function (xhr, status, error) {
                    console.error('API Error:', error);
                    reject({ status, error, xhr });
                },
                complete: function () {
                    if (options.loader) {
                        $(options.loader).removeClass('loading');
                    }
                }
            });
        });
    }

    /**
     * News API
     */
    const News = {
        /**
         * Get paginated news list
         */
        getList: function (page = 1, limit = config.defaultLimit, options = {}) {
            return request('news', {
                data: { page, limit },
                ...options
            });
        },

        /**
         * Get featured news (latest 3)
         */
        getFeatured: function (options = {}) {
            return request('news/featured', options);
        },

        /**
         * Get single news detail
         */
        getDetail: function (id, options = {}) {
            return request('news/' + id, options);
        },

        /**
         * Get news by category/tag slug (1 ข่าวมีได้หลาย tag)
         */
        getByCategory: function (categorySlug, limit = config.defaultLimit, options = {}) {
            return request('news/category/' + encodeURIComponent(categorySlug), {
                data: { limit },
                ...options
            });
        },

        /**
         * Search news
         */
        search: function (query, limit = 10, options = {}) {
            return request('news/search', {
                data: { q: query, limit },
                cache: false,
                ...options
            });
        }
    };

    /**
     * News Tags API (ชนิดข่าว)
     */
    const NewsTags = {
        list: function (options = {}) {
            return request('news-tags', options);
        }
    };

    /**
     * Stats API
     */
    const Stats = {
        get: function (options = {}) {
            return request('stats', options);
        }
    };

    /**
     * UI Components
     */
    const UI = {
        /**
         * Render news card HTML
         */
        newsCard: function (article, size = 'medium') {
            const imgUrl = (article.featured_image_thumb && article.featured_image_thumb.trim()) ? article.featured_image_thumb : article.featured_image;
            const imageHtml = imgUrl
                ? `<img src="${imgUrl}" alt="${article.title}" loading="lazy">`
                : `<div class="placeholder-image"><svg viewBox="0 0 24 24"><path d="M19 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2zM9 17l-4-4 1.41-1.41L9 14.17l7.59-7.59L18 8l-9 9z"/></svg></div>`;

            return `
                <article class="news-card news-card--${size}" data-news-id="${article.id}">
                    <div class="news-card__image">
                        ${imageHtml}
                    </div>
                    <div class="news-card__content">
                        <span class="news-card__date">${article.formatted_date}</span>
                        <h3 class="news-card__title">${article.title}</h3>
                        <p class="news-card__excerpt">${article.excerpt || ''}</p>
                        <a href="javascript:void(0)" class="news-card__link" onclick="UniversityAPI.showNewsModal(${article.id})">
                            Read More <span>&rarr;</span>
                        </a>
                    </div>
                </article>
            `;
        },

        /**
         * Render news grid
         */
        newsGrid: function (articles, container) {
            const $container = $(container);
            $container.empty();

            if (articles.length === 0) {
                $container.html('<p class="no-results">No news articles found.</p>');
                return;
            }

            articles.forEach((article, index) => {
                const size = index === 0 ? 'large' : 'medium';
                $container.append(UI.newsCard(article, size));
            });

            // Animate in
            $container.find('.news-card').each(function (i) {
                $(this).css({
                    opacity: 0,
                    transform: 'translateY(20px)'
                }).delay(i * 100).animate({
                    opacity: 1
                }, 300).css('transform', 'translateY(0)');
            });
        },

        /**
         * Render load more button
         */
        loadMoreButton: function (pagination, loadMoreFn) {
            if (!pagination.has_more) {
                return '';
            }
            return `
                <button class="btn btn-outline load-more-btn" onclick="${loadMoreFn}">
                    Load More News
                </button>
            `;
        },

        /**
         * Skeleton loader
         */
        skeleton: function (count = 3) {
            let html = '';
            for (let i = 0; i < count; i++) {
                html += `
                    <div class="news-card news-card--skeleton">
                        <div class="skeleton skeleton--image"></div>
                        <div class="news-card__content">
                            <div class="skeleton skeleton--text" style="width: 30%"></div>
                            <div class="skeleton skeleton--text" style="width: 100%"></div>
                            <div class="skeleton skeleton--text" style="width: 80%"></div>
                            <div class="skeleton skeleton--text" style="width: 60%"></div>
                        </div>
                    </div>
                `;
            }
            return html;
        }
    };

    /**
     * News Modal
     */
    function showNewsModal(id) {
        // Create modal if doesn't exist
        if ($('#newsModal').length === 0) {
            $('body').append(`
                <div class="modal" id="newsModal">
                    <div class="modal__backdrop"></div>
                    <div class="modal__container">
                        <button class="modal__close" onclick="UniversityAPI.closeNewsModal()">&times;</button>
                        <div class="modal__content" id="newsModalContent">
                            <div class="modal__loading">Loading...</div>
                        </div>
                    </div>
                </div>
            `);

            // Close on backdrop click
            $('#newsModal .modal__backdrop').on('click', closeNewsModal);

            // Close on ESC key
            $(document).on('keydown', function (e) {
                if (e.key === 'Escape') closeNewsModal();
            });
        }

        // Show modal with loading
        $('#newsModal').addClass('active');
        $('#newsModalContent').html('<div class="modal__loading"><div class="spinner"></div>Loading article...</div>');
        $('body').css('overflow', 'hidden');

        // Fetch and display news
        News.getDetail(id)
            .then(response => {
                if (response.success) {
                    const article = response.data;
                    $('#newsModalContent').html(`
                        <article class="news-detail">
                            ${article.featured_image ? `<img src="${article.featured_image}" alt="" class="news-detail__image">` : ''}
                            <div class="news-detail__meta">
                                <span class="news-detail__date">${article.formatted_date}</span>
                                ${article.author ? `<span class="news-detail__author">By ${article.author}</span>` : ''}
                                <span class="news-detail__views">${article.view_count} views</span>
                            </div>
                            <h1 class="news-detail__title">${article.title}</h1>
                            <div class="news-detail__content">${article.content}</div>
                            ${article.images && article.images.length > 0 ? `
                                <div class="news-detail__gallery">
                                    ${article.images.map(img => `
                                        <img src="${img.url}" alt="${img.caption || ''}" loading="lazy">
                                    `).join('')}
                                </div>
                            ` : ''}
                        </article>
                    `);
                }
            })
            .catch(error => {
                $('#newsModalContent').html('<p class="error">Failed to load article. Please try again.</p>');
            });
    }

    function closeNewsModal() {
        $('#newsModal').removeClass('active');
        $('body').css('overflow', '');
    }

    /**
     * Initialize news section on page
     */
    function initNewsSection(containerSelector, options = {}) {
        const $container = $(containerSelector);
        if ($container.length === 0) return;

        const $grid = $container.find('.news-grid') || $container;
        const $loadMore = $container.find('.load-more-container');

        let currentPage = 1;
        const limit = options.limit || config.defaultLimit;

        // Show skeleton
        $grid.html(UI.skeleton(limit));

        // Load news
        function loadNews(append = false) {
            News.getList(currentPage, limit)
                .then(response => {
                    if (response.success) {
                        if (append) {
                            response.data.forEach(article => {
                                $grid.append(UI.newsCard(article));
                            });
                        } else {
                            UI.newsGrid(response.data, $grid);
                        }

                        // Update load more button
                        if ($loadMore.length) {
                            if (response.pagination.has_more) {
                                $loadMore.html(`
                                    <button class="btn btn-outline" id="loadMoreNews">
                                        Load More News
                                    </button>
                                `);
                                $('#loadMoreNews').on('click', function () {
                                    currentPage++;
                                    $(this).text('Loading...').prop('disabled', true);
                                    loadNews(true);
                                });
                            } else {
                                $loadMore.html('<p class="all-loaded">All news loaded</p>');
                            }
                        }
                    }
                })
                .catch(error => {
                    $grid.html('<p class="error">Failed to load news. Please try again.</p>');
                });
        }

        loadNews();
    }

    /**
     * Initialize featured news (homepage)
     */
    function initFeaturedNews(containerSelector) {
        const $container = $(containerSelector);
        if ($container.length === 0) return;

        $container.html(UI.skeleton(3));

        News.getFeatured()
            .then(response => {
                if (response.success) {
                    UI.newsGrid(response.data, $container);
                }
            })
            .catch(error => {
                $container.html('<p class="error">Failed to load featured news.</p>');
            });
    }

    /**
     * Initialize search functionality
     */
    function initSearch(inputSelector, resultsSelector) {
        const $input = $(inputSelector);
        const $results = $(resultsSelector);

        if ($input.length === 0) return;

        let debounceTimer;

        $input.on('input', function () {
            const query = $(this).val().trim();

            clearTimeout(debounceTimer);

            if (query.length < 2) {
                $results.hide();
                return;
            }

            debounceTimer = setTimeout(() => {
                News.search(query)
                    .then(response => {
                        if (response.success && response.data.length > 0) {
                            let html = '<ul class="search-results">';
                            response.data.forEach(article => {
                                html += `
                                    <li>
                                        <a href="javascript:void(0)" onclick="UniversityAPI.showNewsModal(${article.id})">
                                            ${article.featured_image ? `<img src="${article.featured_image}" alt="">` : ''}
                                            <div>
                                                <strong>${article.title}</strong>
                                                <p>${article.excerpt}</p>
                                            </div>
                                        </a>
                                    </li>
                                `;
                            });
                            html += '</ul>';
                            $results.html(html).show();
                        } else {
                            $results.html('<p class="no-results">No results found</p>').show();
                        }
                    });
            }, 300);
        });

        // Hide on click outside
        $(document).on('click', function (e) {
            if (!$(e.target).closest(inputSelector).length) {
                $results.hide();
            }
        });
    }

    /**
     * Initialize stats counters with animation
     */
    function initStatsCounter(containerSelector) {
        const $container = $(containerSelector);
        if ($container.length === 0) return;

        Stats.get()
            .then(response => {
                if (response.success) {
                    const stats = response.data;
                    $container.find('[data-stat]').each(function () {
                        const key = $(this).data('stat');
                        if (stats[key]) {
                            animateCounter($(this), stats[key]);
                        }
                    });
                }
            });
    }

    function animateCounter($element, value) {
        const isNumber = /^[\d,]+$/.test(value.replace(/\+/g, ''));

        if (isNumber) {
            const target = parseInt(value.replace(/[,+]/g, ''));
            const suffix = value.includes('+') ? '+' : '';
            let current = 0;
            const increment = Math.ceil(target / 50);
            const duration = 1500;
            const stepTime = duration / 50;

            const timer = setInterval(() => {
                current += increment;
                if (current >= target) {
                    current = target;
                    clearInterval(timer);
                }
                $element.text(current.toLocaleString() + suffix);
            }, stepTime);
        } else {
            $element.text(value);
        }
    }

    // Public API
    return {
        News,
        NewsTags,
        Stats,
        UI,
        showNewsModal,
        closeNewsModal,
        initNewsSection,
        initFeaturedNews,
        initSearch,
        initStatsCounter,
        config
    };

})(jQuery);

// Auto-init when DOM ready
$(document).ready(function () {
    // Set base URL from meta tag or default
    const baseUrlMeta = $('meta[name="base-url"]').attr('content');
    if (baseUrlMeta) {
        UniversityAPI.config.baseUrl = baseUrlMeta;
    }

    // Initialize components if they exist
    if ($('.news-section').length) {
        UniversityAPI.initNewsSection('.news-section');
    }

    if ($('.featured-news-grid').length) {
        UniversityAPI.initFeaturedNews('.featured-news-grid');
    }

    if ($('#searchInput').length) {
        UniversityAPI.initSearch('#searchInput', '#searchResults');
    }

    if ($('.stats-section').length) {
        UniversityAPI.initStatsCounter('.stats-section');
    }
});
