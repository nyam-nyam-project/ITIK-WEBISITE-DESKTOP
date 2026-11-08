(() => {
    // 1. Tambah Anggota Form
    const memberForm = document.getElementById('anggota-form');
    if (memberForm) {
        document.getElementById('pencarian-anggota').hidden = false;
        const searchMember = document.getElementById('cari-anggota');
        const memberLabels = Array.from(document.querySelectorAll('#daftar-anggota .check'));
        const memberInputs = Array.from(memberForm.querySelectorAll('input[type="checkbox"]'));
        const updateMemberCount = function () {
            document.getElementById('jumlah-pilihan').textContent = memberInputs.filter(input => input.checked).length + ' siswa dipilih';
        };
        searchMember.addEventListener('input', function () {
            const term = searchMember.value.trim().toLocaleLowerCase('id');
            memberLabels.forEach(label => { label.hidden = !label.textContent.toLocaleLowerCase('id').includes(term); });
            document.getElementById('anggota-tidak-ditemukan').hidden = memberLabels.some(label => !label.hidden);
        });
        memberForm.addEventListener('change', updateMemberCount);
        updateMemberCount();
    }

    // 2. Pindah Anggota Massal Form
    const pindahForm = document.getElementById('form-pindah-anggota');
    if (pindahForm) {
        const checkSemua = document.getElementById('check-semua-anggota');
        const siswaChecks = Array.from(pindahForm.querySelectorAll('.check-siswa'));
        const infoPilihan = document.getElementById('jumlah-siswa-pindah');

        const updatePindahCount = function () {
            const count = siswaChecks.filter(input => input.checked).length;
            if (infoPilihan) {
                infoPilihan.textContent = count + ' siswa dipilih';
            }
            if (checkSemua && siswaChecks.length > 0) {
                checkSemua.checked = count === siswaChecks.length;
                checkSemua.indeterminate = count > 0 && count < siswaChecks.length;
            }
        };

        if (checkSemua) {
            checkSemua.addEventListener('change', function () {
                siswaChecks.forEach(input => { input.checked = checkSemua.checked; });
                updatePindahCount();
            });
        }

        pindahForm.addEventListener('change', function (e) {
            if (e.target && e.target.classList.contains('check-siswa')) {
                updatePindahCount();
            }
        });

        updatePindahCount();
    }
})();
