(() => {
    const optionsEntry = document.querySelector('.options-entry[data-jawaban]');
    const answerInput = document.getElementById('hidden-jawaban');
    if (!optionsEntry || !answerInput) return;

    const rows = Array.from(optionsEntry.querySelectorAll('.option-row[data-opsi]'));
    const answerLabel = document.getElementById('label-jawaban-selected');

    const updateAnswerLabel = function (opsi) {
        if (answerLabel) {
            answerLabel.textContent = answerLabel.dataset.answerLabel + opsi;
            answerLabel.classList.add('u-display-block');
        }
    };

    rows.forEach(row => {
        row.addEventListener('click', function () {
            rows.forEach(option => option.classList.remove('selected'));
            row.classList.add('selected');
            answerInput.value = row.dataset.opsi;
            updateAnswerLabel(row.dataset.opsi);
        });

        row.querySelectorAll('input[data-stop-propagation]').forEach(input => {
            input.addEventListener('click', function (event) {
                event.stopPropagation();
            });
        });
    });

    const initialAnswer = optionsEntry.dataset.jawaban;
    if (initialAnswer) {
        const row = rows.find(option => option.dataset.opsi === initialAnswer);
        if (row) {
            row.classList.add('selected');
            updateAnswerLabel(initialAnswer);
        }
    }
})();
