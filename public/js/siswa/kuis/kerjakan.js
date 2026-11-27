//DraftJawaban
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

// Penanda soal terjawab di daftar soal
function perbaruiNavigasi() {

    const terjawab = new Set();

    document.querySelectorAll('input[type="radio"]:checked').forEach(function (radio) {
        terjawab.add(radio.dataset.soal);
    });

    document.querySelectorAll('[data-essay-answer]').forEach(function (textarea) {
        if (textarea.value.trim()) {
            terjawab.add(textarea.dataset.soal);
        }
    });

    document.querySelectorAll('[data-nav]').forEach(function (tombol) {
        const nomor = tombol.dataset.soalIndex ? (Number(tombol.dataset.soalIndex) + 1) : tombol.textContent.trim();
        const sudahDijawab = terjawab.has(tombol.dataset.nav);
        tombol.classList.toggle('answered', sudahDijawab);
        tombol.setAttribute('aria-label', 'Soal ' + nomor + (sudahDijawab ? ', sudah dijawab' : ', belum dijawab'));
    });

    const jumlahEl = document.getElementById('jumlahTerjawab');
    if (jumlahEl) {
        jumlahEl.textContent = terjawab.size;
    }
}

// Navigasi antar soal (Sebelumnya, Berikutnya, dan Daftar Nomor Soal)
const daftarSoalItem = Array.from(document.querySelectorAll('.soal-item[data-soal-index]'));
const tombolNavigasiNomor = Array.from(document.querySelectorAll('.nav-soal-btn[data-soal-index]'));
const btnSoalSebelumnya = document.getElementById('btnSoalSebelumnya');
const btnSoalBerikutnya = document.getElementById('btnSoalBerikutnya');
const indikatorSoalAktif = document.getElementById('indikatorSoalAktif');
let indeksSoalAktif = 0;

function bukaSoal(indeksBaru, pindahkanFokus) {
    if (daftarSoalItem.length === 0) {
        return;
    }

    const indeksValid = Math.max(0, Math.min(daftarSoalItem.length - 1, indeksBaru));
    indeksSoalAktif = indeksValid;

    daftarSoalItem.forEach(function (item, idx) {
        item.classList.toggle('is-cbt-hidden', idx !== indeksValid);
    });

    tombolNavigasiNomor.forEach(function (btn, idx) {
        const aktif = idx === indeksValid;
        btn.classList.toggle('active', aktif);
        if (aktif) {
            btn.setAttribute('aria-current', 'step');
        } else {
            btn.removeAttribute('aria-current');
        }
    });

    if (btnSoalSebelumnya) {
        btnSoalSebelumnya.disabled = indeksValid === 0;
    }
    if (btnSoalBerikutnya) {
        btnSoalBerikutnya.disabled = indeksValid >= daftarSoalItem.length - 1;
    }
    if (indikatorSoalAktif) {
        indikatorSoalAktif.textContent = 'Soal ' + (indeksValid + 1) + ' dari ' + daftarSoalItem.length;
    }

    if (pindahkanFokus) {
        const soalAktif = daftarSoalItem[indeksValid];
        const judulSoal = soalAktif ? soalAktif.querySelector('.soal-text') : null;
        if (judulSoal) {
            judulSoal.focus();
        }
    }
}

tombolNavigasiNomor.forEach(function (tombol) {
    tombol.addEventListener('click', function () {
        const idx = Number(this.dataset.soalIndex);
        if (!Number.isNaN(idx)) {
            bukaSoal(idx, true);
        }
    });
});

if (btnSoalSebelumnya) {
    btnSoalSebelumnya.addEventListener('click', function () {
        bukaSoal(indeksSoalAktif - 1, true);
    });
}

if (btnSoalBerikutnya) {
    btnSoalBerikutnya.addEventListener('click', function () {
        bukaSoal(indeksSoalAktif + 1, true);
    });
}

bukaSoal(0, false);

perbaruiNavigasi();

document.querySelectorAll('[data-essay-answer]').forEach(function (textarea) {
    textarea.addEventListener('input', perbaruiNavigasi);
});

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
                formSubmitted = true;
                try {
                    window.sessionStorage.removeItem('itik-sedang-ujian');
                } catch (err) {
                    // Abaikan jika sessionStorage tidak tersedia
                }
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
            try {
                window.sessionStorage.removeItem('itik-sedang-ujian');
            } catch (err) {
                // Abaikan jika sessionStorage tidak tersedia
            }
                if (hasilUrl) {
                    window.location.href = hasilUrl;
                } else {
                    window.location.reload();
                }
                return;
            }

            const pesanEl = document.getElementById('pelanggaranPesan');
            if (pesanEl) {
                if (data.jumlah === 1) {
                    pesanEl.textContent = 'Peringatan 1 dari 2: jangan keluar dari halaman ujian.';
                } else if (data.jumlah === 2) {
                    pesanEl.textContent = 'Peringatan 2 dari 2: pelanggaran berikutnya akan langsung mengumpulkan kuis.';
                } else {
                    pesanEl.textContent = 'Peringatan ' + data.jumlah + ' dari ' + (data.batas - 1) + ': jangan keluar dari halaman ujian.';
                }
            }
            if (overlayPelanggaran) {
                overlayPelanggaran.classList.remove('is-cbt-hidden');
            }
        })
        .catch(err => {
            console.error('Gagal mencatat pelanggaran:', err);
        });
    }

    // 1. Tangani submit resmi kuis dengan overlay konfirmasi (tanpa native confirm)
    const formKuisSubmit = document.getElementById('form-kuis');
    const overlayKonfirmasi = document.getElementById('konfirmasiSubmitOverlay');
    const konfirmasiTerjawab = document.getElementById('konfirmasiTerjawab');
    const btnBatalKumpul = document.getElementById('btnBatalKumpul');
    const btnYaKumpul = document.getElementById('btnYaKumpul');
    let konfirmasiSubmitDiterima = false;

    if (formKuisSubmit) {
        formKuisSubmit.addEventListener('submit', function (e) {
            if (!konfirmasiSubmitDiterima) {
                e.preventDefault();
                if (konfirmasiTerjawab) {
                    const jumlahEl = document.getElementById('jumlahTerjawab');
                    konfirmasiTerjawab.textContent = jumlahEl ? jumlahEl.textContent : '0';
                }
                if (overlayKonfirmasi) {
                    overlayKonfirmasi.classList.remove('is-cbt-hidden');
                }
                if (btnBatalKumpul) {
                    btnBatalKumpul.focus();
                }
                return;
            }

            formSubmitted = true;
            try {
                window.sessionStorage.removeItem('itik-sedang-ujian');
            } catch (err) {
                // Abaikan jika sessionStorage tidak tersedia
            }
        });
    }

    if (btnBatalKumpul && overlayKonfirmasi) {
        btnBatalKumpul.addEventListener('click', function () {
            overlayKonfirmasi.classList.add('is-cbt-hidden');
        });
    }

    if (btnYaKumpul && formKuisSubmit) {
        btnYaKumpul.addEventListener('click', function () {
            konfirmasiSubmitDiterima = true;
            formSubmitted = true;
            sudahSubmit = true;
            if (overlayKonfirmasi) {
                overlayKonfirmasi.classList.add('is-cbt-hidden');
            }
            try {
                window.sessionStorage.removeItem('itik-sedang-ujian');
            } catch (err) {
                // Abaikan jika sessionStorage tidak tersedia
            }
            formKuisSubmit.submit();
        });
    }

    // 2. Pesan status di halaman (tanpa popup alert/confirm/prompt browser)
    const cbtStatusBanner = document.getElementById('cbtStatusBanner');
    let timerPesanStatus = null;

    function tampilkanPesanCbt(pesan) {
        if (!cbtStatusBanner) {
            return;
        }
        cbtStatusBanner.textContent = pesan;
        cbtStatusBanner.classList.remove('is-cbt-hidden');
        if (timerPesanStatus) {
            clearTimeout(timerPesanStatus);
        }
        timerPesanStatus = setTimeout(function () {
            cbtStatusBanner.classList.add('is-cbt-hidden');
        }, 5000);
    }

    // 3. Cegah reload / tutup tab / navigasi keluar
    window.addEventListener('beforeunload', function (e) {
        if (!formSubmitted && !sudahSubmit) {
            e.preventDefault();
            e.returnValue = 'Ujian sedang berlangsung! Jawaban Anda belum dikumpulkan. Jangan tinggalkan halaman ini.';
            return e.returnValue;
        }
    });

    // 4. Kunci tombol Back / Forward browser (tanpa alert popup)
    history.pushState(null, document.title, location.href);
    window.addEventListener('popstate', function () {
        history.pushState(null, document.title, location.href);
        tampilkanPesanCbt('Navigasi browser dinonaktifkan selama ujian berlangsung. Gunakan tombol Sebelumnya, Berikutnya, atau daftar nomor soal.');
    });

    // 5. Blokir tombol refresh keyboard (F5, Ctrl+R, Alt+Left) dan tombol sistem (Alt, Windows/Meta)
    window.addEventListener('keydown', function (e) {
        if (e.key === 'Alt' || e.key === 'Meta' || e.key === 'OS') {
            e.preventDefault();
            tampilkanPesanCbt('Tombol sistem dinonaktifkan selama ujian. Harap tetap berada di halaman kuis.');
            laporkanPelanggaran();
            return false;
        }

        if (
            e.key === 'F5' ||
            (e.ctrlKey && (e.key === 'r' || e.key === 'R')) ||
            (e.altKey && e.key === 'ArrowLeft')
        ) {
            e.preventDefault();
            tampilkanPesanCbt('Tombol refresh dan navigasi kembali dinonaktifkan selama ujian.');
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
