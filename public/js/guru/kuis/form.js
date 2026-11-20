const kuisForm = document.querySelector('[data-kuis-form]');

if (kuisForm && kuisForm.dataset.kuisForm === 'create') {
    const kategori = document.getElementById('kategori');
    const materiContainer = document.getElementById('materi-container');
    const waktuContainer = document.getElementById('waktu-container');
    const remedialContainer = document.getElementById('field-remedial');

    function updateForm() {
        const value = kategori ? kategori.value : '';
        const isLocked = materiContainer && materiContainer.dataset.locked === 'true';
        materiContainer.classList.remove('u-display-block');
        materiContainer.classList.add('u-display-none');
        remedialContainer.classList.remove('u-display-block');
        remedialContainer.classList.add('u-display-none');

        if (value === 'post-test' || value === 'pre-test' || value === 'kuis harian' || value === 'ulangan' || (!value && isLocked)) {
            materiContainer.classList.remove('u-display-none');
            materiContainer.classList.add('u-display-block');
        } else if (value === 'remedial') {
            remedialContainer.classList.remove('u-display-none');
            remedialContainer.classList.add('u-display-block');
        }

        const ket = document.getElementById('waktu-keterangan');
        if (ket) {
            ket.textContent = value === 'ulangan'
                ? 'Jadwal wajib diisi untuk kategori Ulangan.'
                : 'Jadwal opsional: Tentukan tanggal dan jam untuk menerbitkan kuis secara otomatis.';
        }
    }

    if (kategori) {
        kategori.addEventListener('change', updateForm);
        updateForm();
    }

    const selectMapel = document.getElementById('id_mapel');
    const selectMateri = document.getElementById('materi-select');
    const hintMateriKosong = document.getElementById('materi-empty-hint');

    function filterMateriByMapel() {
        if (!selectMapel || !selectMateri) return;
        const mapelId = selectMapel.value;
        let visibleCount = 0;

        Array.from(selectMateri.options).forEach(opt => {
            const optMapel = opt.dataset.mapel;
            if (!mapelId || optMapel === mapelId) {
                opt.classList.remove('u-display-none');
                opt.disabled = false;
                visibleCount++;
            } else {
                opt.classList.add('u-display-none');
                opt.disabled = true;
                opt.selected = false;
            }
        });

        if (hintMateriKosong) {
            hintMateriKosong.classList.toggle('u-display-block', !!(mapelId && visibleCount === 0));
            hintMateriKosong.classList.toggle('u-display-none', !(mapelId && visibleCount === 0));
        }
    }

    if (selectMapel) {
        selectMapel.addEventListener('change', filterMateriByMapel);
        filterMateriByMapel();
    }
}

if (kuisForm && kuisForm.dataset.kuisForm === 'edit') {
    const kategori = document.getElementById('kategori');
    const materiContainer = document.getElementById('materi-container');
    const waktuContainer = document.getElementById('waktu-container');

    function updateForm() {
        if (!kategori) return;
        if (
            kategori.value === 'ulangan' ||
            kategori.value === 'post-test' ||
            kategori.value === 'pre-test' ||
            kategori.value === 'kuis harian'
        ) {
            if (materiContainer) {
                materiContainer.classList.remove('u-display-none');
                materiContainer.classList.add('u-display-block');
            }
        } else {
            if (materiContainer) {
                materiContainer.classList.remove('u-display-block');
                materiContainer.classList.add('u-display-none');
            }
        }

        const ket = document.getElementById('waktu-keterangan');
        if (ket) {
            ket.textContent = kategori.value === 'ulangan'
                ? 'Jadwal wajib diisi untuk kategori Ulangan.'
                : 'Jadwal opsional: Tentukan tanggal dan jam untuk menerbitkan kuis secara otomatis.';
        }
    }

    if (kategori) {
        kategori.addEventListener('change', updateForm);
        updateForm();
    }

    // Filter Materi sesuai Mapel Kuis
    const selectMapel = document.getElementById('id_mapel');
    const selectMateri = document.getElementById('materi-select');
    const hintMateriKosong = document.getElementById('materi-empty-hint');

    function filterMateriByMapel() {
        if (!selectMapel || !selectMateri) return;
        const mapelId = selectMapel.value;
        let visibleCount = 0;

        Array.from(selectMateri.options).forEach(opt => {
            const optMapel = opt.dataset.mapel;
            if (!mapelId || optMapel === mapelId) {
                opt.classList.remove('u-display-none');
                opt.disabled = false;
                visibleCount++;
            } else {
                opt.classList.add('u-display-none');
                opt.disabled = true;
                opt.selected = false;
            }
        });

        if (hintMateriKosong) {
            hintMateriKosong.classList.toggle('u-display-block', !!(mapelId && visibleCount === 0));
            hintMateriKosong.classList.toggle('u-display-none', !(mapelId && visibleCount === 0));
        }
    }

    if (selectMapel) {
        selectMapel.addEventListener('change', filterMateriByMapel);
        filterMateriByMapel();
    }
}

// Inisialisasi Flatpickr untuk Jadwal Kuis (waktu_mulai & waktu_selesai)
(function initFlatpickrJadwal() {
    const elMulai = document.getElementById('waktu_mulai');
    const elSelesai = document.getElementById('waktu_selesai');

    if (!elMulai || !elSelesai || typeof flatpickr === 'undefined') {
        return;
    }

    function isSameDate(d1, d2) {
        if (!d1 || !d2) return false;
        return (
            d1.getFullYear() === d2.getFullYear() &&
            d1.getMonth() === d2.getMonth() &&
            d1.getDate() === d2.getDate()
        );
    }

    function formatTime(date) {
        const hh = String(date.getHours()).padStart(2, '0');
        const mm = String(date.getMinutes()).padStart(2, '0');
        return `${hh}:${mm}`;
    }

    let fpMulai = null;
    let fpSelesai = null;

    function updateMulaiMinTime(fp) {
        const now = new Date();
        const sel = fp.selectedDates[0];
        if (!sel || isSameDate(sel, now)) {
            fp.set('minTime', formatTime(now));
        } else {
            fp.set('minTime', '00:00');
        }
    }

    function updateSelesaiMinTime(fp, mulaiDate) {
        const now = new Date();
        const selSelesai = fp.selectedDates[0];

        if (mulaiDate) {
            const targetDate = selSelesai || mulaiDate;
            if (isSameDate(targetDate, mulaiDate)) {
                const nextMin = new Date(mulaiDate.getTime() + 60 * 1000);
                fp.set('minTime', formatTime(nextMin));
            } else {
                fp.set('minTime', '00:00');
            }
        } else {
            const targetDate = selSelesai || now;
            if (isSameDate(targetDate, now)) {
                const nextMin = new Date(now.getTime() + 60 * 1000);
                fp.set('minTime', formatTime(nextMin));
            } else {
                fp.set('minTime', '00:00');
            }
        }
    }

    function handleMulaiChange(mulaiDate) {
        if (!fpSelesai) return;

        if (mulaiDate) {
            fpSelesai.set('minDate', mulaiDate);

            const selesaiDate = fpSelesai.selectedDates[0];
            if (selesaiDate && selesaiDate <= mulaiDate) {
                fpSelesai.clear();
            }

            updateSelesaiMinTime(fpSelesai, mulaiDate);
        } else {
            const now = new Date();
            fpSelesai.set('minDate', now);
            updateSelesaiMinTime(fpSelesai, null);
        }
    }

    fpMulai = flatpickr(elMulai, {
        enableTime: true,
        time_24hr: true,
        locale: 'id',
        dateFormat: 'Y-m-d H:i',
        altInput: true,
        altFormat: 'd M Y, H:i',
        minDate: 'today',
        onOpen: function (selectedDates, dateStr, instance) {
            updateMulaiMinTime(instance);
        },
        onChange: function (selectedDates, dateStr, instance) {
            updateMulaiMinTime(instance);
            handleMulaiChange(selectedDates[0] || null);
        },
    });

    fpSelesai = flatpickr(elSelesai, {
        enableTime: true,
        time_24hr: true,
        locale: 'id',
        dateFormat: 'Y-m-d H:i',
        altInput: true,
        altFormat: 'd M Y, H:i',
        minDate: 'today',
        onOpen: function (selectedDates, dateStr, instance) {
            const mulaiDate = fpMulai ? fpMulai.selectedDates[0] : null;
            updateSelesaiMinTime(instance, mulaiDate);
        },
        onChange: function (selectedDates, dateStr, instance) {
            const mulaiDate = fpMulai ? fpMulai.selectedDates[0] : null;
            updateSelesaiMinTime(instance, mulaiDate);
        },
    });

    if (fpMulai.selectedDates[0]) {
        handleMulaiChange(fpMulai.selectedDates[0]);
    } else {
        handleMulaiChange(null);
    }

    const btnClearMulai = document.getElementById('btn-clear-mulai');
    if (btnClearMulai) {
        btnClearMulai.addEventListener('click', function () {
            fpMulai.clear();
            handleMulaiChange(null);
        });
    }

    const btnClearSelesai = document.getElementById('btn-clear-selesai');
    if (btnClearSelesai) {
        btnClearSelesai.addEventListener('click', function () {
            fpSelesai.clear();
        });
    }
})();
