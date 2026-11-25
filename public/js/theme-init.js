(() => {
    const STORAGE_KEY = 'itik-theme';
    let tema = null;

    try {
        const tersimpan = window.localStorage.getItem(STORAGE_KEY);
        if (tersimpan === 'dark' || tersimpan === 'light') {
            tema = tersimpan;
        }
    } catch (e) {
        // Abaikan jika localStorage tidak tersedia
    }

    if (!tema) {
        const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
        tema = prefersDark ? 'dark' : 'light';
    }

    document.documentElement.setAttribute('data-theme', tema);
})();
