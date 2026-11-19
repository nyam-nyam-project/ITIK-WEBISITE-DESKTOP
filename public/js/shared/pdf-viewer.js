(function () {
    const container = document.querySelector('.pdf-viewer-container');
    if (!container) return;

    const url = container.dataset.pdfUrl;
    const wrapper = document.getElementById('pdf-canvas-wrapper');
    const loadingEl = document.getElementById('pdf-loading');
    const statusEl = document.getElementById('pdf-page-status');
    const fullscreenBtn = document.getElementById('pdf-fullscreen');
    const prevBtn = document.getElementById('pdf-prev');
    const nextBtn = document.getElementById('pdf-next');
    const pageInfoEl = document.getElementById('pdf-page-info');
    const canvas = document.getElementById('pdf-canvas');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');

    let pdfDoc = null;
    let pageNum = 1;
    let currentRenderTask = null;

    pdfjsLib.GlobalWorkerOptions.workerSrc =
        'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

    function updateNav() {
        if (!pdfDoc) return;
        if (pageInfoEl) {
            pageInfoEl.textContent = 'Halaman ' + pageNum + ' / ' + pdfDoc.numPages;
        }
        if (prevBtn) {
            prevBtn.disabled = pageNum <= 1;
        }
        if (nextBtn) {
            nextBtn.disabled = pageNum >= pdfDoc.numPages;
        }
    }

    function renderPage(num) {
        if (!pdfDoc) return;

        if (currentRenderTask) {
            currentRenderTask.cancel();
            currentRenderTask = null;
        }

        pdfDoc
            .getPage(num)
            .then(function (page) {
                const scale = 1.5;
                const viewport = page.getViewport({ scale: scale });

                canvas.height = viewport.height;
                canvas.width = viewport.width;

                const renderContext = {
                    canvasContext: ctx,
                    viewport: viewport,
                };

                currentRenderTask = page.render(renderContext);

                currentRenderTask.promise
                    .then(function () {
                        currentRenderTask = null;
                        if (loadingEl) loadingEl.style.display = 'none';
                        canvas.classList.remove('u-display-none');
                        canvas.style.display = 'block';
                    })
                    .catch(function (err) {
                        if (err && err.name === 'RenderingCancelledException') {
                            return;
                        }
                        console.error('Render error:', err);
                    });
            })
            .catch(function (err) {
                console.error('GetPage error:', err);
            });

        updateNav();
    }

    pdfjsLib
        .getDocument(url)
        .promise.then(function (doc) {
            pdfDoc = doc;
            if (statusEl) statusEl.textContent = pdfDoc.numPages + ' halaman';
            updateNav();
            renderPage(pageNum);
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

    if (prevBtn) {
        prevBtn.addEventListener('click', function () {
            if (pageNum <= 1) return;
            pageNum--;
            if (wrapper) wrapper.scrollTop = 0;
            if (container) container.scrollTop = 0;
            renderPage(pageNum);
        });
    }

    if (nextBtn) {
        nextBtn.addEventListener('click', function () {
            if (!pdfDoc || pageNum >= pdfDoc.numPages) return;
            pageNum++;
            if (wrapper) wrapper.scrollTop = 0;
            if (container) container.scrollTop = 0;
            renderPage(pageNum);
        });
    }

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
