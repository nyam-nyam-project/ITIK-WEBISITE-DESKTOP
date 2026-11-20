const filterMapel = document.getElementById('filter-mapel');
if (filterMapel) {
    filterMapel.addEventListener('change', function () {
        if (this.value) {
            window.location.href = this.value;
        }
    });
}
