/**
 * News detail gallery lightbox – คลิกรูปเพื่อดูขนาดใหญ่ และเลื่อนดูรูปอื่นได้
 * ใช้ในหน้ารายละเอียดข่าว (news_detail.php)
 */
(function() {
    'use strict';
    var gallery = document.getElementById('newsGallery');
    if (!gallery) return;
    var thumbs = gallery.querySelectorAll('.news-gallery-thumb');
    var lightbox = document.getElementById('newsLightbox');
    var lightboxImg = document.getElementById('newsLightboxImg');
    var lightboxCaption = document.getElementById('newsLightboxCaption');
    var lightboxCounter = document.getElementById('newsLightboxCounter');
    var closeBtn = lightbox && lightbox.querySelector('.news-lightbox__close');
    var prevBtn = lightbox && lightbox.querySelector('.news-lightbox__prev');
    var nextBtn = lightbox && lightbox.querySelector('.news-lightbox__next');
    var currentIndex = 0;

    function showLightbox(index) {
        if (!lightbox || !thumbs[index]) return;
        currentIndex = index;
        var btn = thumbs[index];
        lightboxImg.src = btn.dataset.src || '';
        lightboxImg.alt = btn.dataset.alt || '';
        lightboxCaption.textContent = btn.dataset.alt || '';
        lightboxCounter.textContent = (index + 1) + ' / ' + thumbs.length;
        lightbox.hidden = false;
        document.body.style.overflow = 'hidden';
        if (closeBtn) closeBtn.focus();
    }

    function hideLightbox() {
        if (!lightbox) return;
        lightbox.hidden = true;
        document.body.style.overflow = '';
    }

    function goPrev() {
        currentIndex = currentIndex <= 0 ? thumbs.length - 1 : currentIndex - 1;
        showLightbox(currentIndex);
    }

    function goNext() {
        currentIndex = currentIndex >= thumbs.length - 1 ? 0 : currentIndex + 1;
        showLightbox(currentIndex);
    }

    thumbs.forEach(function(btn, i) {
        btn.addEventListener('click', function() { showLightbox(i); });
    });
    if (closeBtn) closeBtn.addEventListener('click', hideLightbox);
    if (prevBtn) prevBtn.addEventListener('click', goPrev);
    if (nextBtn) nextBtn.addEventListener('click', goNext);

    if (lightbox) {
        lightbox.addEventListener('click', function(e) {
            if (e.target === lightbox) hideLightbox();
        });
        lightbox.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') hideLightbox();
            if (e.key === 'ArrowLeft') goPrev();
            if (e.key === 'ArrowRight') goNext();
        });
    }
})();
