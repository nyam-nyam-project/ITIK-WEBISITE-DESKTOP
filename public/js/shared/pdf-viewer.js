const url = document.querySelector('.pdf-viewer-container').dataset.pdfUrl;
pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

let pdfDoc = null,
    pageNum = 1,
    pageRendering = false,
    pageNumPending = null,
    scale = 1.3,
    canvas = document.getElementById('pdf-canvas'),
    ctx = canvas.getContext('2d'),
    loadingEl = document.getElementById('pdf-loading'),
    prevBtn = document.getElementById('prev-page'),
    nextBtn = document.getElementById('next-page'),
    pageNumEl = document.getElementById('page_num'),
    pageCountEl = document.getElementById('page_count');

function renderPage(num) {
    pageRendering = true;
    pdfDoc.getPage(num).then(function(page) {
        const viewport = page.getViewport({ scale: scale });
        canvas.height = viewport.height;
        canvas.width = viewport.width;

        const renderContext = {
            canvasContext: ctx,
            viewport: viewport
        };
        const renderTask = page.render(renderContext);

        renderTask.promise.then(function() {
            pageRendering = false;
            if (loadingEl) loadingEl.classList.add('u-display-none');
            canvas.classList.remove('u-display-none');
            canvas.classList.add('u-display-block');

            if (pageNumPending !== null) {
                renderPage(pageNumPending);
                pageNumPending = null;
            }
        });
    });

    pageNumEl.textContent = num;
    prevBtn.disabled = num <= 1;
    nextBtn.disabled = num >= pdfDoc.numPages;
}

function queueRenderPage(num) {
    if (pageRendering) {
        pageNumPending = num;
    } else {
        renderPage(num);
    }
}

prevBtn.addEventListener('click', function() {
    if (pageNum <= 1) return;
    pageNum--;
    queueRenderPage(pageNum);
});

nextBtn.addEventListener('click', function() {
    if (pageNum >= pdfDoc.numPages) return;
    pageNum++;
    queueRenderPage(pageNum);
});

pdfjsLib.getDocument(url).promise.then(function(pdfDoc_) {
    pdfDoc = pdfDoc_;
    pageCountEl.textContent = pdfDoc.numPages;
    renderPage(pageNum);
}).catch(function(err) {
    if (loadingEl) {
        loadingEl.textContent = 'Gagal memuat pratinjau PDF. Anda dapat membuka PDF secara langsung di tab baru.';
    }
});
