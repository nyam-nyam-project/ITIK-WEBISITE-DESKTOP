//DraftJawaban
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

// Penanda soal terjawab di daftar soal
function perbaruiNavigasi() {

    const terjawab = new Set();

    document.querySelectorAll('input[type="radio"]:checked').forEach(function (radio) {
        terjawab.add(radio.dataset.soal);
    });

    document.querySelectorAll('[data-nav]').forEach(function (link) {
        link.classList.toggle('answered', terjawab.has(link.dataset.nav));
    });

    document.getElementById('jumlahTerjawab').textContent = terjawab.size;
}

perbaruiNavigasi();

document.querySelectorAll('input[type="radio"]').forEach(function (radio) {

    radio.addEventListener('change', function () {

        perbaruiNavigasi();

        const idSoal = this.dataset.soal;
        const jawaban = this.value;

        if (!csrfToken) {
            console.error('CSRF token tidak ditemukan. Tambahkan meta csrf-token di view atau layout.');
            return;
        }

        fetch(
            document.getElementById('formKuis').dataset.jawabanUrl,
            {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    id_soal: idSoal,
                    jawaban: jawaban
                })
            }
        )
        .then(response => response.json())
        .then(data => {

            if (data.success) {
                console.log('Jawaban tersimpan:', idSoal, jawaban);
            } else {
                console.error(data.message);
            }

        })
        .catch(error => {
            console.error('Gagal menyimpan jawaban:', error);
        });

    });

});

//Timer
    const batasWaktu = new Date(
        document.getElementById('timer').dataset.batasWaktu
    ).getTime();

    const timerElement = document.getElementById('timer');
    let sudahSubmit = false;

    function updateTimer() {

        const sekarang = new Date().getTime();

        const sisa = batasWaktu - sekarang;

        if (sisa <= 0) {
            timerElement.innerHTML = "00:00";
            timerElement.classList.add('warning');

            // Submit otomatis (hanya sekali)
            if (!sudahSubmit) {
                sudahSubmit = true;
                document.getElementById('form-kuis').submit();
            }

            return;
        }

        const totalDetik = Math.floor(sisa / 1000);

        const menit = Math.floor(totalDetik / 60);
        const detik = totalDetik % 60;

        timerElement.innerHTML =
            String(menit).padStart(2, '0') +
            ':' +
            String(detik).padStart(2, '0');

        // Peringatan saat sisa waktu kurang dari 1 menit
        timerElement.classList.toggle('warning', totalDetik < 60);
    }

    updateTimer();

    setInterval(updateTimer, 1000);

    /* ========================================================
       PENGUNCI NAVIGASI & KEAMANAN MODA UJIAN (CBT LOCK)
       ======================================================== */
    let formSubmitted = false;
    let ujianDimulai = false;
    let waktuPelanggaranTerakhir = 0;
    const JEDA_DEBOUNCE_MS = 1500;

    // Gerbang Layar Penuh (Wajib sebelum soal ditampilkan)
    const btnMulaiLayarPenuh = document.getElementById('btnMulaiLayarPenuh');
    const modalFullscreen = document.getElementById('fullscreenModal');
    const cbtLayout = document.getElementById('cbtLayout');
    const overlayPelanggaran = document.getElementById('pelanggaranOverlay');
    const btnKembaliLayarPenuh = document.getElementById('btnKembaliLayarPenuh');

    async function mintaLayarPenuh() {
        try {
            if (!document.fullscreenElement) {
                if (document.documentElement.requestFullscreen) {
                    await document.documentElement.requestFullscreen();
                } else if (document.documentElement.webkitRequestFullscreen) {
                    await document.documentElement.webkitRequestFullscreen();
                }
            }
            if (navigator.keyboard && typeof navigator.keyboard.lock === 'function') {
                try {
                    await navigator.keyboard.lock(['Escape']);
                } catch (_) {
                    // Abaikan jika tidak diizinkan atau tidak didukung
                }
            }
        } catch (err) {
            console.warn('Layar penuh tidak dapat diaktifkan secara otomatis:', err);
        }
    }

    if (btnMulaiLayarPenuh) {
        btnMulaiLayarPenuh.addEventListener('click', async function () {
            await mintaLayarPenuh();
            ujianDimulai = true;
            if (modalFullscreen) {
                modalFullscreen.classList.add('is-cbt-hidden');
            }
            if (cbtLayout) {
                cbtLayout.classList.remove('is-cbt-hidden');
            }
        });
    }

    if (btnKembaliLayarPenuh) {
        btnKembaliLayarPenuh.addEventListener('click', async function () {
            await mintaLayarPenuh();
            if (overlayPelanggaran) {
                overlayPelanggaran.classList.add('is-cbt-hidden');
            }
        });
    }

    // Catat pelanggaran ke server
    function laporkanPelanggaran() {
        if (!ujianDimulai || formSubmitted || sudahSubmit) {
            return;
        }

        const sekarang = Date.now();
        if (sekarang - waktuPelanggaranTerakhir < JEDA_DEBOUNCE_MS) {
            return;
        }
        waktuPelanggaranTerakhir = sekarang;

        const formKuisEl = document.getElementById('formKuis');
        const endpoint = formKuisEl?.dataset.pelanggaranUrl;
        const hasilUrl = formKuisEl?.dataset.hasilUrl;

        if (!endpoint || !csrfToken) {
            return;
        }

        fetch(endpoint, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({})
        })
        .then(response => response.json())
        .then(data => {
            if (data.autoSubmitted) {
                sudahSubmit = true;
                formSubmitted = true;
                if (hasilUrl) {
                    window.location.href = hasilUrl;
                } else {
                    window.location.reload();
                }
                return;
            }

            const pesanEl = document.getElementById('pelanggaranPesan');
            if (pesanEl) {
                pesanEl.textContent = '⚠️ PERINGATAN (' + data.jumlah + '/' + data.batas + '): Anda terdeteksi keluar dari layar penuh atau berpindah jendela. Segera kembali ke mode layar penuh.';
            }
            if (overlayPelanggaran) {
                overlayPelanggaran.classList.remove('is-cbt-hidden');
            }
        })
        .catch(err => {
            console.error('Gagal mencatat pelanggaran:', err);
        });
    }

    // 1. Tangani submit resmi kuis
    const formKuisSubmit = document.getElementById('form-kuis');
    if (formKuisSubmit) {
        formKuisSubmit.addEventListener('submit', function (e) {
            if (!confirm('Yakin ingin mengumpulkan kuis? Jawaban tidak dapat diubah lagi.')) {
                e.preventDefault();
                return;
            }
            formSubmitted = true;
        });
    }

    // 2. Cegah reload / tutup tab / navigasi keluar
    window.addEventListener('beforeunload', function (e) {
        if (!formSubmitted && !sudahSubmit) {
            e.preventDefault();
            e.returnValue = 'Ujian sedang berlangsung! Jawaban Anda belum dikumpulkan. Jangan tinggalkan halaman ini.';
            return e.returnValue;
        }
    });

    // 3. Kunci tombol Back / Forward browser
    history.pushState(null, document.title, location.href);
    window.addEventListener('popstate', function () {
        history.pushState(null, document.title, location.href);
        alert('⚠️ PERINGATAN: Navigasi browser dinonaktifkan selama ujian berlangsung. Harap selesaikan kuis dan klik tombol "Kumpulkan kuis".');
    });

    // 4. Blokir tombol refresh keyboard (F5, Ctrl+R, Alt+Left) dan tombol sistem (Alt, Windows/Meta)
    window.addEventListener('keydown', function (e) {
        // Tombol Windows (Meta/OS) dan kombinasi Alt+Tab ditangani langsung oleh sistem operasi (OS)
        // dan hanya bisa dicegah sebagian oleh browser web.
        if (e.key === 'Alt' || e.key === 'Meta' || e.key === 'OS') {
            e.preventDefault();
            alert('⚠️ Tombol sistem (Alt / Windows / Meta) dinonaktifkan selama ujian.');
            return false;
        }

        if (
            e.key === 'F5' ||
            (e.ctrlKey && (e.key === 'r' || e.key === 'R')) ||
            (e.altKey && e.key === 'ArrowLeft')
        ) {
            e.preventDefault();
            alert('⚠️ Tombol refresh dan navigasi kembali dinonaktifkan selama ujian.');
            return false;
        }
    });

    // 5. Catat pelanggaran dari tiga kejadian: fullscreenchange, visibilitychange, dan window.blur
    document.addEventListener('fullscreenchange', function () {
        if (ujianDimulai && !document.fullscreenElement) {
            laporkanPelanggaran();
        } else if (document.fullscreenElement && overlayPelanggaran && !formSubmitted && !sudahSubmit) {
            overlayPelanggaran.classList.add('is-cbt-hidden');
        }
    });

    document.addEventListener('visibilitychange', function () {
        if (ujianDimulai && document.hidden) {
            laporkanPelanggaran();
        }
    });

    window.addEventListener('blur', function () {
        if (ujianDimulai && !formSubmitted && !sudahSubmit) {
            laporkanPelanggaran();
        }
    });
