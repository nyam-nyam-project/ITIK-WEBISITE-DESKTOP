// State Filter Aktif
const filterContainer = document.querySelector('.filter-section');
const activeFilters = {
    kategori: filterContainer.dataset.kategori,
    percobaan: filterContainer.dataset.percobaan,
    kelulusan: filterContainer.dataset.kelulusan,
    tanggal: filterContainer.dataset.tanggal
};

function toggleDropdown(id) {
    const el = document.getElementById(id);
    const isShown = el.classList.contains('show');
    closeAllDropdowns();
    if (!isShown) {
        el.classList.add('show');
    }
}

function closeAllDropdowns() {
    document.querySelectorAll('.pill-dropdown').forEach(d => d.classList.remove('show'));
}

// Tutup dropdown jika klik di luar
document.addEventListener('click', function(e) {
    if (!e.target.closest('.pill-item')) {
        closeAllDropdowns();
    }
});

// 1. Kategori Filter
function applyFilter(key, value) {
    activeFilters[key] = value.toLowerCase().trim();
    closeAllDropdowns();

    const labelEl = document.getElementById('label-' + key);
    const btnEl = document.getElementById('btn-chip-' + key);
    if (value) {
        labelEl.textContent = value.charAt(0).toUpperCase() + value.slice(1);
        btnEl.classList.add('active');
    } else {
        labelEl.textContent = 'Semua';
        btnEl.classList.remove('active');
    }

    runClientFilter();
}

// 2. Percobaan Filter
function showPercobaanInput() {
    document.getElementById('btn-chip-percobaan').classList.remove('u-display-inline-flex');
    document.getElementById('btn-chip-percobaan').classList.add('u-display-none');
    const form = document.getElementById('form-chip-percobaan');
    form.classList.add('show');
    const inp = document.getElementById('input-percobaan');
    inp.focus();
    inp.select();
}

function submitPercobaan() {
    const val = document.getElementById('input-percobaan').value.trim();
    activeFilters.percobaan = val;
    const btn = document.getElementById('btn-chip-percobaan');
    const label = document.getElementById('label-percobaan');
    const form = document.getElementById('form-chip-percobaan');

    form.classList.remove('show');
    btn.classList.remove('u-display-none');
    btn.classList.add('u-display-inline-flex');

    if (val) {
        label.textContent = 'Ke-' + val;
        btn.classList.add('active');
    } else {
        label.textContent = 'Semua';
        btn.classList.remove('active');
    }

    runClientFilter();
}

function resetPercobaanInput() {
    document.getElementById('input-percobaan').value = '';
    submitPercobaan();
}

// 4. Tanggal Filter
function showTanggalInput() {
    document.getElementById('btn-chip-tanggal').classList.remove('u-display-inline-flex');
    document.getElementById('btn-chip-tanggal').classList.add('u-display-none');
    const form = document.getElementById('form-chip-tanggal');
    form.classList.add('show');
    document.getElementById('input-tanggal').focus();
}

function submitTanggal(val) {
    activeFilters.tanggal = val.trim();
    const btn = document.getElementById('btn-chip-tanggal');
    const label = document.getElementById('label-tanggal');
    const form = document.getElementById('form-chip-tanggal');

    form.classList.remove('show');
    btn.classList.remove('u-display-none');
    btn.classList.add('u-display-inline-flex');

    if (val) {
        const parts = val.split('-');
        label.textContent = parts[2] + '/' + parts[1] + '/' + parts[0];
        btn.classList.add('active');
    } else {
        label.textContent = 'Semua';
        btn.classList.remove('active');
    }

    runClientFilter();
}

function resetTanggalInput() {
    document.getElementById('input-tanggal').value = '';
    submitTanggal('');
}

// Reset Semua Filter
function resetAllFilters() {
    activeFilters.kategori = '';
    activeFilters.percobaan = '';
    activeFilters.kelulusan = '';
    activeFilters.tanggal = '';

    document.getElementById('label-kategori').textContent = 'Semua';
    document.getElementById('btn-chip-kategori').classList.remove('active');

    document.getElementById('label-percobaan').textContent = 'Semua';
    document.getElementById('btn-chip-percobaan').classList.remove('active');
    document.getElementById('input-percobaan').value = '';

    document.getElementById('label-kelulusan').textContent = 'Semua';
    document.getElementById('btn-chip-kelulusan').classList.remove('active');

    document.getElementById('label-tanggal').textContent = 'Semua';
    document.getElementById('btn-chip-tanggal').classList.remove('active');
    document.getElementById('input-tanggal').value = '';

    const url = new URL(window.location);
    url.search = '';
    window.history.pushState({}, '', url);

    runClientFilter();
}

// Filter Real-Time pada Baris Tabel
function runClientFilter() {
    const rows = document.querySelectorAll('.data-row');
    let visibleCount = 0;

    rows.forEach((row) => {
        const kat = (row.dataset.kategori || '').toLowerCase();
        const perc = row.dataset.percobaan || '';
        const lulus = row.dataset.lulus || '';
        const tgl = row.dataset.tanggal || '';

        let match = true;

        if (activeFilters.kategori && kat !== activeFilters.kategori) {
            match = false;
        }
        if (activeFilters.percobaan && perc !== activeFilters.percobaan) {
            match = false;
        }
        if (activeFilters.kelulusan && lulus !== activeFilters.kelulusan) {
            match = false;
        }
        if (activeFilters.tanggal && tgl !== activeFilters.tanggal) {
            match = false;
        }

        if (match) {
            row.classList.remove('u-display-none');
            visibleCount++;
            const numCell = row.querySelector('.row-num');
            if (numCell) numCell.textContent = visibleCount;
        } else {
            row.classList.add('u-display-none');
        }
    });

    const hasActive = activeFilters.kategori || activeFilters.percobaan || activeFilters.kelulusan || activeFilters.tanggal;
    const resetBtn = document.getElementById('pill-reset-wrapper');
    if (resetBtn) {
        resetBtn.classList.toggle('u-display-none', !hasActive);
        resetBtn.classList.toggle('u-display-inline-block', !!hasActive);
    }

    const countInfo = document.getElementById('info-count-row');
    if (countInfo) {
        countInfo.textContent = 'Menampilkan ' + visibleCount + ' data pengerjaan kuis siswa';
    }
}

document.querySelectorAll('[data-toggle-dropdown]').forEach(el => {
    el.addEventListener('click', function() {
        toggleDropdown(el.dataset.toggleDropdown);
    });
});

document.querySelectorAll('[data-filter-key]').forEach(el => {
    el.addEventListener('click', function() {
        applyFilter(el.dataset.filterKey, el.dataset.filterValue);
    });
});

document.querySelectorAll('[data-show-percobaan]').forEach(el => {
    el.addEventListener('click', showPercobaanInput);
});

document.querySelectorAll('[data-submit-percobaan-on-enter]').forEach(el => {
    el.addEventListener('keydown', function(event) {
        if (event.key === 'Enter') submitPercobaan();
    });
});

document.querySelectorAll('[data-submit-percobaan]').forEach(el => {
    el.addEventListener('click', submitPercobaan);
});

document.querySelectorAll('[data-reset-percobaan]').forEach(el => {
    el.addEventListener('click', resetPercobaanInput);
});

document.querySelectorAll('[data-show-tanggal]').forEach(el => {
    el.addEventListener('click', showTanggalInput);
});

document.querySelectorAll('[data-submit-tanggal]').forEach(el => {
    el.addEventListener('change', function() {
        submitTanggal(el.value);
    });
});

document.querySelectorAll('[data-reset-tanggal]').forEach(el => {
    el.addEventListener('click', resetTanggalInput);
});

document.querySelectorAll('[data-reset-filters]').forEach(el => {
    el.addEventListener('click', resetAllFilters);
});

document.addEventListener('DOMContentLoaded', function() {
    if (activeFilters.kategori || activeFilters.percobaan || activeFilters.kelulusan || activeFilters.tanggal) {
        runClientFilter();
    }
});
