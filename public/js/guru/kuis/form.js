const kuisForm = document.querySelector('[data-kuis-form]');

if (kuisForm.dataset.kuisForm === 'create') {
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

if (kuisForm.dataset.kuisForm === 'edit') {
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
