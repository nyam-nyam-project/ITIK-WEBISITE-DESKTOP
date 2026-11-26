/* Progres Siswa — animasi bar saat muncul */
(function () {
    'use strict';

    function animateBars() {
        document.querySelectorAll('.progres-kategori-bar').forEach(function (bar) {
            var persen = bar.getAttribute('data-persen');
            bar.style.width = persen + '%';
        });

        document.querySelectorAll('.progres-bar-inline__fill').forEach(function (bar) {
            var persen = bar.getAttribute('data-persen');
            bar.style.width = persen + '%';
        });

        document.querySelectorAll('.progres-bar-inline__kkm').forEach(function (marker) {
            var persen = marker.getAttribute('data-persen');
            marker.style.left = persen + '%';
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', animateBars);
    } else {
        animateBars();
    }
})();
