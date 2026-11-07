(() => {
    ['tahun_ajaran', 'filter-mapel'].forEach(function (id) {
        document.getElementById(id).addEventListener('change', function () {
            document.getElementById('filter-kuis').value = '';
            this.form.submit();
        });
    });
})();
