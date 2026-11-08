(() => {
    document.getElementById('tahun_ajaran').addEventListener('change', function () {
        document.getElementById('filter-kelas').value = '';
        this.form.submit();
    });
})();
