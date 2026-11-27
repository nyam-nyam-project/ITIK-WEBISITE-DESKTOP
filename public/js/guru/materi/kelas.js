const materiKelasForm = document.querySelector('[data-materi-kelas-form]');

if (materiKelasForm) {
    const mapelSelect = materiKelasForm.querySelector('#id_mapel');
    const classGroups = Array.from(materiKelasForm.querySelectorAll('[data-kelas-options]'));
    const classStatus = materiKelasForm.querySelector('[data-kelas-status]');

    function renderKelas(kelas) {
        classGroups.forEach(group => group.replaceChildren());

        kelas.forEach(item => {
            const group = classGroups.find(element => element.dataset.kelasOptions === item.tingkat);
            if (!group) return;

            const label = document.createElement('label');
            const input = document.createElement('input');
            const text = document.createElement('span');

            input.type = 'checkbox';
            input.name = 'id_kelas[]';
            input.value = item.id_kelas;
            input.id = `id_kelas_${item.id_kelas}`;

            label.className = 'materi-kelas-option';
            label.htmlFor = input.id;
            text.textContent = item.nama_kelas;
            label.append(input, text);
            group.append(label);
        });
    }

    async function muatKelas() {
        classGroups.forEach(group => group.replaceChildren());

        if (!mapelSelect.value) {
            classStatus.textContent = 'Pilih mata pelajaran untuk memuat kelas yang Anda ajar.';
            return;
        }

        classStatus.textContent = 'Memuat kelas...';
        materiKelasForm.setAttribute('aria-busy', 'true');

        try {
            const url = new URL(materiKelasForm.dataset.kelasOptionsUrl, window.location.origin);
            url.searchParams.set('id_mapel', mapelSelect.value);

            const response = await fetch(url, {
                headers: { Accept: 'application/json' },
                credentials: 'same-origin',
            });

            if (!response.ok) {
                throw new Error(`Permintaan kelas gagal (${response.status}).`);
            }

            const result = await response.json();
            renderKelas(result.data);
            classStatus.textContent = result.data.length
                ? ''
                : 'Tidak ada kelas yang Anda ajar untuk mata pelajaran ini pada tahun ajaran aktif.';
        } catch (error) {
            classStatus.textContent = 'Daftar kelas gagal dimuat. Silakan coba pilih mata pelajaran kembali.';
            console.error(error);
        } finally {
            materiKelasForm.removeAttribute('aria-busy');
        }
    }

    materiKelasForm.addEventListener('click', event => {
        const button = event.target.closest('[data-pilih-semua]');
        if (!button) return;

        const group = classGroups.find(element => element.dataset.kelasOptions === button.dataset.pilihSemua);
        if (!group) return;

        group.querySelectorAll('input[type="checkbox"]').forEach(input => {
            input.checked = true;
        });
    });

    if (mapelSelect) {
        mapelSelect.addEventListener('change', muatKelas);
    }
}
