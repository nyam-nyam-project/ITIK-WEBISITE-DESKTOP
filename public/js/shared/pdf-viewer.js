(function () {
    const container = document.querySelector('.pdf-viewer-container');
    if (!container) return;

    const url = container.dataset.pdfUrl;
    const wrapper = document.getElementById('pdf-canvas-wrapper');
    const loadingEl = document.getElementById('pdf-loading');
    const statusEl = document.getElementById('pdf-page-status');
    const fullscreenBtn = document.getElementById('pdf-fullscreen');

    pdfjsLib.GlobalWorkerOptions.workerSrc =
        'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

    pdfjsLib
        .getDocument(url)
        .promise.then(function (pdfDoc) {
            const totalPages = pdfDoc.numPages;

            if (statusEl) statusEl.textContent = totalPages + ' halaman';

            const renderPage = function (pageIndex) {
                return pdfDoc.getPage(pageIndex).then(function (page) {
                    const scale = 1.5;
                    const viewport = page.getViewport({ scale: scale });
                    const canvas = document.createElement('canvas');
                    canvas.className = 'pdf-page-canvas';
                    canvas.height = viewport.height;
                    canvas.width = viewport.width;
                    wrapper.appendChild(canvas);

                    return page.render({
                        canvasContext: canvas.getContext('2d'),
                        viewport: viewport,
                    }).promise;
                });
            };

            const renderAll = function (index) {
                if (index > totalPages) {
                    if (loadingEl) loadingEl.style.display = 'none';
                    return;
                }
                renderPage(index).then(function () {
                    renderAll(index + 1);
                });
            };

            renderAll(1);
        })
        .catch(function (err) {
            console.error('PDF.js error:', err);
            if (loadingEl) {
                loadingEl.textContent =
                    'Gagal memuat pratinjau PDF: ' +
                    (err && err.message ? err.message : String(err)) +
                    '. Anda dapat membuka PDF secara langsung di tab baru.';
            }
        });

    if (fullscreenBtn) {
        fullscreenBtn.addEventListener('click', function () {
            if (container.requestFullscreen) {
                container.requestFullscreen();
            } else if (container.webkitRequestFullscreen) {
                container.webkitRequestFullscreen();
            }
        });
    }
}());
