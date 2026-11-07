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

    // 4. Blokir tombol refresh keyboard (F5, Ctrl+R, Alt+Left)
    window.addEventListener('keydown', function (e) {
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

    // 5. Deteksi siswa berpindah window / tab browser
    let switchTabCount = 0;
    document.addEventListener('visibilitychange', function () {
        if (document.hidden) {
            switchTabCount++;
        } else {
            if (switchTabCount > 0 && !formSubmitted && !sudahSubmit) {
                alert('⚠️ PERINGATAN: Anda terdeteksi meninggalkan halaman ujian (' + switchTabCount + ' kali). Harap tetap berada di halaman ujian hingga selesai dikumpulkan!');
            }
        }
    });
