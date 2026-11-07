(() => {
    const memberForm = document.getElementById('anggota-form');
    if (!memberForm) return;

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
})();
