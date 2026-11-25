(() => {
    const THEME_KEY = 'itik-theme';
    const FONT_KEY = 'itik-font-size';
    const BACAKAN_KEY = 'itik-bacakan-soal';
    const EXAM_SESSION_KEY = 'itik-sedang-ujian';
    const VALID_FONTS = ['kecil', 'normal', 'besar', 'sangat-besar'];

    const root = document.documentElement;
    const widget = document.getElementById('a11y-widget');
    const toggleBtn = document.getElementById('a11y-toggle');
    const panel = document.getElementById('a11y-panel');
    const fontButtons = document.querySelectorAll('[data-a11y-font]');
    const themeButtons = document.querySelectorAll('[data-a11y-theme]');
    const bacakanToggle = document.getElementById('a11y-bacakan-toggle');
    const resetBtn = document.getElementById('a11y-reset');

    function isOpenedFromExamSession() {
        try {
            const path = window.location.pathname || '';
            const referrer = document.referrer || '';
            const inExamFlag = window.sessionStorage.getItem(EXAM_SESSION_KEY) === '1';
            const fromKerjakanReferrer = /\/kuis\/[^/]+\/(kerjakan|mulai)/i.test(referrer);
            const fromKerjakanPath = /\/kuis\/[^/]+\/(kerjakan|mulai)/i.test(path);

            return fromKerjakanPath || (inExamFlag && fromKerjakanReferrer);
        } catch (e) {
            return false;
        }
    }

    if (widget && isOpenedFromExamSession()) {
        widget.remove();
    }

    function readStorage(key, fallback) {
        try {
            const val = window.localStorage.getItem(key);
            return val !== null ? val : fallback;
        } catch (e) {
            return fallback;
        }
    }

    function writeStorage(key, value) {
        try {
            window.localStorage.setItem(key, value);
        } catch (e) {
            // Abaikan jika localStorage diblokir
        }
    }

    function syncTopbarThemeButton(theme) {
        const topbarToggle = document.getElementById('theme-toggle');
        if (!topbarToggle) {
            return;
        }
        const isDark = theme === 'dark';
        topbarToggle.setAttribute('aria-pressed', isDark ? 'true' : 'false');
        topbarToggle.setAttribute('aria-label', isDark ? 'Aktifkan mode terang' : 'Aktifkan mode gelap');
        const labelEl = topbarToggle.querySelector('.theme-toggle-label');
        if (labelEl) {
            labelEl.textContent = isDark ? 'Mode Terang' : 'Mode Gelap';
        }
    }

    function applyTheme(theme, persist = true) {
        const normalized = theme === 'dark' ? 'dark' : 'light';
        root.setAttribute('data-theme', normalized);
        if (persist) {
            writeStorage(THEME_KEY, normalized);
        }
        themeButtons.forEach((btn) => {
            const active = btn.dataset.a11yTheme === normalized;
            btn.setAttribute('aria-pressed', active ? 'true' : 'false');
        });
        syncTopbarThemeButton(normalized);
    }

    function applyFontSize(size, persist = true) {
        const normalized = VALID_FONTS.includes(size) ? size : 'normal';
        root.setAttribute('data-font-size', normalized);
        if (persist) {
            writeStorage(FONT_KEY, normalized);
        }
        fontButtons.forEach((btn) => {
            const active = btn.dataset.a11yFont === normalized;
            btn.setAttribute('aria-pressed', active ? 'true' : 'false');
        });
    }

    function syncBacakanSoalButtons(enabled) {
        const aktif = enabled !== '0';
        document.querySelectorAll('.btn-bacakan-soal').forEach((btn) => {
            btn.disabled = !aktif;
            btn.setAttribute('aria-disabled', aktif ? 'false' : 'true');
            const label = btn.querySelector('.btn-bacakan-soal-label');
            if (label && !btn.classList.contains('is-speaking')) {
                label.textContent = aktif ? 'Bacakan soal' : 'Bacakan soal (nonaktif)';
            }
        });
    }

    function applyBacakanSoal(value, persist = true) {
        const normalized = value === '0' ? '0' : '1';
        root.setAttribute('data-bacakan-soal', normalized);
        if (persist) {
            writeStorage(BACAKAN_KEY, normalized);
        }
        if (bacakanToggle) {
            const isOn = normalized === '1';
            bacakanToggle.setAttribute('aria-pressed', isOn ? 'true' : 'false');
            bacakanToggle.textContent = isOn ? 'Aktif' : 'Nonaktif';
        }
        syncBacakanSoalButtons(normalized);
    }

    function setPanelOpen(open) {
        if (!toggleBtn || !panel) {
            return;
        }
        panel.hidden = !open;
        toggleBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
    }

    // Sinkronkan UI dengan nilai awal dari localStorage / <head>
    const currentTheme = root.getAttribute('data-theme') || readStorage(THEME_KEY, 'light');
    const currentFont = root.getAttribute('data-font-size') || readStorage(FONT_KEY, 'normal');
    const currentBacakan = root.getAttribute('data-bacakan-soal') || readStorage(BACAKAN_KEY, '1');

    applyTheme(currentTheme, false);
    applyFontSize(currentFont, false);
    applyBacakanSoal(currentBacakan, false);

    if (toggleBtn && panel) {
        toggleBtn.addEventListener('click', () => {
            setPanelOpen(panel.hidden);
        });

        document.addEventListener('click', (event) => {
            if (panel.hidden || !widget) {
                return;
            }
            if (!widget.contains(event.target)) {
                setPanelOpen(false);
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && !panel.hidden) {
                setPanelOpen(false);
                toggleBtn.focus();
            }
        });
    }

    fontButtons.forEach((btn) => {
        btn.addEventListener('click', () => {
            applyFontSize(btn.dataset.a11yFont, true);
        });
    });

    themeButtons.forEach((btn) => {
        btn.addEventListener('click', () => {
            applyTheme(btn.dataset.a11yTheme, true);
        });
    });

    if (bacakanToggle) {
        bacakanToggle.addEventListener('click', () => {
            const nextVal = root.getAttribute('data-bacakan-soal') === '1' ? '0' : '1';
            applyBacakanSoal(nextVal, true);
        });
    }

    if (resetBtn) {
        resetBtn.addEventListener('click', () => {
            applyFontSize('normal', true);
            applyBacakanSoal('1', true);
            const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
            applyTheme(prefersDark ? 'dark' : 'light', true);
        });
    }

    // Sinkronisasi jika tombol tema di header topbar diklik
    const topbarThemeBtn = document.getElementById('theme-toggle');
    if (topbarThemeBtn) {
        topbarThemeBtn.addEventListener('click', () => {
            window.setTimeout(() => {
                const updated = root.getAttribute('data-theme') || 'light';
                themeButtons.forEach((btn) => {
                    btn.setAttribute('aria-pressed', btn.dataset.a11yTheme === updated ? 'true' : 'false');
                });
            }, 0);
        });
    }

    // Fitur Bacakan Soal di halaman kerjakan kuis (Web Speech API)
    const speechButtons = document.querySelectorAll('.btn-bacakan-soal');
    let activeSpeechBtn = null;
    let activeUtterance = null;
    let cachedVoices = [];

    function refreshVoices() {
        if (!('speechSynthesis' in window)) {
            return [];
        }
        cachedVoices = window.speechSynthesis.getVoices() || [];
        return cachedVoices;
    }

    if ('speechSynthesis' in window) {
        refreshVoices();
        if (typeof window.speechSynthesis.addEventListener === 'function') {
            window.speechSynthesis.addEventListener('voiceschanged', refreshVoices);
        }
    }

    function resetSpeechState() {
        if (!activeSpeechBtn) {
            return;
        }
        activeSpeechBtn.classList.remove('is-speaking');
        activeSpeechBtn.setAttribute('aria-pressed', 'false');
        const label = activeSpeechBtn.querySelector('.btn-bacakan-soal-label');
        if (label) {
            const aktif = root.getAttribute('data-bacakan-soal') !== '0';
            label.textContent = aktif ? 'Bacakan soal' : 'Bacakan soal (nonaktif)';
        }
        activeSpeechBtn = null;
        activeUtterance = null;
    }

    function pickBestVoice() {
        const voices = cachedVoices.length ? cachedVoices : refreshVoices();
        if (!voices.length) {
            return null;
        }

        const isId = (v) => v.lang && (v.lang.toLowerCase() === 'id-id' || v.lang.toLowerCase().startsWith('id'));
        return (
            voices.find((v) => isId(v) && v.localService) ||
            voices.find((v) => isId(v)) ||
            voices.find((v) => v.default && v.localService) ||
            voices.find((v) => v.localService) ||
            voices.find((v) => v.default) ||
            voices[0]
        );
    }

    function buildQuestionSpeechText(soalId) {
        const container = document.getElementById('soal-' + soalId);
        if (!container) {
            return '';
        }

        const parts = [];
        const nomor = container.querySelector('.soal-no')?.textContent?.trim();
        const pertanyaan = container.querySelector('.soal-text')?.textContent?.trim();

        if (nomor && pertanyaan) {
            parts.push(`Soal nomor ${nomor}. ${pertanyaan}`);
        } else if (pertanyaan) {
            parts.push(pertanyaan);
        }

        container.querySelectorAll('.opsi').forEach((opsiEl) => {
            const teksOpsi = opsiEl.textContent?.replace(/\s+/g, ' ').trim();
            if (teksOpsi) {
                parts.push(teksOpsi);
            }
        });

        return parts.join('. ');
    }

    function startSpeaking(btn, teks) {
        const utterance = new SpeechSynthesisUtterance(teks);
        const voice = pickBestVoice();
        if (voice) {
            utterance.voice = voice;
            utterance.lang = voice.lang || 'id-ID';
        } else {
            utterance.lang = 'id-ID';
        }
        utterance.rate = 1;
        utterance.volume = 1;

        activeSpeechBtn = btn;
        activeUtterance = utterance;
        btn.classList.add('is-speaking');
        btn.setAttribute('aria-pressed', 'true');
        const label = btn.querySelector('.btn-bacakan-soal-label');
        if (label) {
            label.textContent = 'Hentikan suara';
        }

        utterance.onend = resetSpeechState;
        utterance.onerror = (event) => {
            const errType = event?.error || '';
            if (errType !== 'canceled' && errType !== 'interrupted') {
                console.warn('Gagal membacakan soal:', errType);
            }
            resetSpeechState();
        };

        if (window.speechSynthesis.paused) {
            window.speechSynthesis.resume();
        }
        window.speechSynthesis.speak(utterance);
    }

    speechButtons.forEach((btn) => {
        btn.addEventListener('click', () => {
            if (root.getAttribute('data-bacakan-soal') === '0') {
                return;
            }

            if (!('speechSynthesis' in window) || typeof SpeechSynthesisUtterance === 'undefined') {
                const label = btn.querySelector('.btn-bacakan-soal-label');
                if (label) {
                    label.textContent = 'Suara tidak didukung browser';
                }
                return;
            }

            const wasSpeakingSame = activeSpeechBtn === btn && (window.speechSynthesis.speaking || window.speechSynthesis.pending);
            const needsCancel = window.speechSynthesis.speaking || window.speechSynthesis.pending;

            if (needsCancel) {
                window.speechSynthesis.cancel();
            }
            resetSpeechState();

            if (wasSpeakingSame) {
                return;
            }

            const teks = buildQuestionSpeechText(btn.dataset.soalId);
            if (!teks) {
                return;
            }

            // Chrome/Chromium butuh jeda singkat setelah cancel() agar speak() baru tidak ikut terbatalkan
            if (needsCancel) {
                window.setTimeout(() => startSpeaking(btn, teks), 80);
            } else {
                startSpeaking(btn, teks);
            }
        });
    });

    window.addEventListener('beforeunload', () => {
        if ('speechSynthesis' in window && window.speechSynthesis.speaking) {
            window.speechSynthesis.cancel();
        }
    });
})();
