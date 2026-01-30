$(document).ready(function () {
    // SPA Logic: Intercept all internal links
    $(document).on('click', 'a', function (e) {
        let href = $(this).attr('href');
        let target = $(this).attr('target');

        // Ignore if external link, anchor link, javascript:, or empty
        if (!href || href === '#' || href.startsWith('javascript:') || target === '_blank') {
            return;
        }

        // Check if link is internal (same domain)
        if (href.startsWith(window.BASE_URL) || href.startsWith('/')) {
            e.preventDefault();
            loadPage(href);
        }
    });

    // Handle Back/Forward buttons
    window.onpopstate = function (event) {
        if (event.state && event.state.path) {
            loadPage(event.state.path, false);
        } else {
            // Reload if no state (e.g. initial page)
            window.location.reload();
        }
    };

    function loadPage(url, pushState = true) {
        // Show loading state (optional: add visible loader)
        $('#main-content').css('opacity', '0.5');

        $.ajax({
            url: url,
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function (response) {
                // Update content
                $('#main-content').html(response).css('opacity', '1');

                // Update URL history
                if (pushState) {
                    window.history.pushState({ path: url }, '', url);
                }

                // Update Active Menu
                updateActiveMenu(url);

                // Scroll to top
                window.scrollTo(0, 0);

                // Re-initialize any plugins/scripts if needed (e.g. charts, sliders)
                // if (typeof initPlugins === 'function') initPlugins();
            },
            error: function (xhr) {
                console.error("Error loading page:", xhr);
                // Fallback to normal navigation if Ajax fails
                window.location.href = url;
            }
        });
    }

    function updateActiveMenu(url) {
        // Remove active class from all links
        $('.nav__link, .mobile-nav__link').removeClass('active');

        // Add active class to links matching current URL
        // Normalize URL for comparison (remove trailing slash)
        let currentUrl = url.replace(/\/$/, "");

        $('.nav__link, .mobile-nav__link').each(function () {
            let linkUrl = $(this).attr('href').replace(/\/$/, "");
            if (linkUrl === currentUrl) {
                $(this).addClass('active');
            }
        });
    }
});
