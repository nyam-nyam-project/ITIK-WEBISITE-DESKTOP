(() => {
    const typeSelect = document.querySelector('[data-soal-type]');
    if (typeSelect) {
        const pgGroups = typeSelect.form.querySelectorAll('[data-pg-only]');
        const updateType = function () {
            const isEssay = typeSelect.value === 'essay';
            pgGroups.forEach(group => {
                group.hidden = isEssay;
                group.querySelectorAll('input, select, textarea').forEach(input => {
                    input.disabled = isEssay;
                    input.required = !isEssay && input.hasAttribute('data-pg-required');
                });
            });
        };

        typeSelect.addEventListener('change', updateType);
        updateType();
    }

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
            if (answerInput.disabled) return;

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
