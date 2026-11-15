(() => {
    const section = document.querySelector('[data-grafik]');

    if (!section) {
        return;
    }

    if (typeof Chart === 'undefined') {
        console.error('Chart.js tidak tersedia untuk grafik dashboard guru.');
        return;
    }

    let grafik;

    try {
        grafik = JSON.parse(section.dataset.grafik);
    } catch (error) {
        console.error('Data grafik dashboard guru tidak valid.', error);
        return;
    }

    const buatGrafik = (idCanvas, konfigurasi) => {
        const canvas = document.getElementById(idCanvas);

        if (!canvas) {
            console.error(`Canvas grafik dashboard tidak ditemukan: ${idCanvas}`);
            return;
        }

        new Chart(canvas, konfigurasi);
    };

    const opsiUmum = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
            },
        },
    };

    const nilai = grafik.nilai_per_tingkat;

    buatGrafik('grafik-nilai-per-tingkat', {
        type: 'bar',
        data: {
            labels: nilai.labels,
            datasets: [
                {
                    label: 'Rata-rata',
                    data: nilai.rata_rata,
                    backgroundColor: '#7160ef',
                },
                {
                    label: 'Tertinggi',
                    data: nilai.nilai_tertinggi,
                    backgroundColor: '#22a06b',
                },
                {
                    label: 'Terendah',
                    data: nilai.nilai_terendah,
                    backgroundColor: '#e17b32',
                },
            ],
        },
        options: {
            ...opsiUmum,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                },
            },
            plugins: {
                ...opsiUmum.plugins,
                tooltip: {
                    callbacks: {
                        label(context) {
                            const namaSiswa = context.datasetIndex === 1
                                ? nilai.nama_siswa_tertinggi[context.dataIndex]
                                : context.datasetIndex === 2
                                    ? nilai.nama_siswa_terendah[context.dataIndex]
                                    : null;

                            return `${context.dataset.label}: ${context.formattedValue}${namaSiswa ? ` - ${namaSiswa}` : ''}`;
                        },
                    },
                },
            },
        },
    });

    const ketuntasan = grafik.ketuntasan_per_kuis;

    buatGrafik('grafik-ketuntasan-per-kuis', {
        type: 'bar',
        data: {
            labels: ketuntasan.labels,
            datasets: [
                {
                    label: 'Lulus',
                    data: ketuntasan.lulus,
                    backgroundColor: '#22a06b',
                },
                {
                    label: 'Belum lulus',
                    data: ketuntasan.belum_lulus,
                    backgroundColor: '#e17b32',
                },
                {
                    label: 'Belum mengerjakan',
                    data: ketuntasan.belum_mengerjakan,
                    backgroundColor: '#94a3b8',
                },
            ],
        },
        options: {
            ...opsiUmum,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0,
                    },
                },
            },
        },
    });

    const progres = grafik.progres_materi;

    buatGrafik('grafik-progres-materi', {
        type: 'bar',
        data: {
            labels: progres.labels,
            datasets: [
                {
                    label: 'Siswa selesai (%)',
                    data: progres.persentase,
                    backgroundColor: '#3b82f6',
                },
            ],
        },
        options: {
            ...opsiUmum,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                },
            },
        },
    });

    const prePost = grafik.pre_post_test;

    buatGrafik('grafik-pre-post-test', {
        type: 'bar',
        data: {
            labels: prePost.map((baris) => `${baris.judul_materi} - ${baris.nama_siswa}`),
            datasets: [
                {
                    label: 'Pre-test',
                    data: prePost.map((baris) => baris.nilai_pre),
                    backgroundColor: '#94a3b8',
                },
                {
                    label: 'Post-test',
                    data: prePost.map((baris) => baris.nilai_post),
                    backgroundColor: '#7160ef',
                },
            ],
        },
        options: {
            ...opsiUmum,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                },
            },
        },
    });
})();
