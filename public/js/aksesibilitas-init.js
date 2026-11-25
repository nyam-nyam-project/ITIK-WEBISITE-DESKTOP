(() => {
    const THEME_KEY = 'itik-theme';
    const FONT_KEY = 'itik-font-size';
    const BACAKAN_KEY = 'itik-bacakan-soal';
    const EXAM_SESSION_KEY = 'itik-sedang-ujian';

    const VALID_FONTS = ['kecil', 'normal', 'besar', 'sangat-besar'];
    const root = document.documentElement;

    let tema = null;
    let ukuranTeks = 'normal';
    let bacakanSoal = '1';

    try {
        const temaTersimpan = window.localStorage.getItem(THEME_KEY);
        if (temaTersimpan === 'dark' || temaTersimpan === 'light') {
            tema = temaTersimpan;
        }

        const fontTersimpan = window.localStorage.getItem(FONT_KEY);
        if (fontTersimpan && VALID_FONTS.includes(fontTersimpan)) {
            ukuranTeks = fontTersimpan;
        }

        const bacakanTersimpan = window.localStorage.getItem(BACAKAN_KEY);
        if (bacakanTersimpan === '0' || bacakanTersimpan === '1') {
            bacakanSoal = bacakanTersimpan;
        }
    } catch (e) {
        // Abaikan jika localStorage tidak tersedia
    }

    if (!tema) {
        const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
        tema = prefersDark ? 'dark' : 'light';
    }

    root.setAttribute('data-theme', tema);
    root.setAttribute('data-font-size', ukuranTeks);
    root.setAttribute('data-bacakan-soal', bacakanSoal);

    try {
        const path = window.location.pathname || '';
        if (/\/kuis\/[^/]+\/(kerjakan|mulai)\/?$/i.test(path)) {
            window.sessionStorage.setItem(EXAM_SESSION_KEY, '1');
        }
    } catch (e) {
        // Abaikan jika sessionStorage tidak tersedia
    }
})();
