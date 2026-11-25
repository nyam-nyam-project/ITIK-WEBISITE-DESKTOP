<div class="a11y-widget" id="a11y-widget" data-a11y-floating>
    <div class="a11y-panel" id="a11y-panel" role="dialog" aria-label="Pengaturan aksesibilitas" hidden>
        <div class="a11y-panel-header">
            <h2 class="a11y-panel-title">Aksesibilitas</h2>
            <button type="button" class="a11y-reset-btn" id="a11y-reset">Reset</button>
        </div>

        <div class="a11y-group">
            <span class="a11y-group-label">Ukuran Teks</span>
            <div class="a11y-options" role="group" aria-label="Pilih ukuran teks">
                <button type="button" class="a11y-chip" data-a11y-font="kecil" aria-pressed="false">Kecil</button>
                <button type="button" class="a11y-chip" data-a11y-font="normal" aria-pressed="true">Normal</button>
                <button type="button" class="a11y-chip" data-a11y-font="besar" aria-pressed="false">Besar</button>
                <button type="button" class="a11y-chip" data-a11y-font="sangat-besar" aria-pressed="false">Sangat Besar</button>
            </div>
        </div>

        <div class="a11y-group">
            <span class="a11y-group-label">Tema Tampilan</span>
            <div class="a11y-options" role="group" aria-label="Pilih tema tampilan">
                <button type="button" class="a11y-chip" data-a11y-theme="light" aria-pressed="true">Terang</button>
                <button type="button" class="a11y-chip" data-a11y-theme="dark" aria-pressed="false">Gelap</button>
            </div>
        </div>

        <div class="a11y-group">
            <div class="a11y-toggle-row">
                <span class="a11y-group-label">Bacakan Soal</span>
                <button type="button" class="a11y-toggle-btn" id="a11y-bacakan-toggle" aria-pressed="true">Aktif</button>
            </div>
        </div>
    </div>

    <button
        type="button"
        class="a11y-fab"
        id="a11y-toggle"
        aria-expanded="false"
        aria-controls="a11y-panel"
        aria-label="Buka pengaturan aksesibilitas"
    >
        <svg viewBox="0 0 24 24" aria-hidden="true">
            <circle cx="12" cy="5" r="2" />
            <path d="M5 9h14M12 9v6M9 21l3-6 3 6" />
        </svg>
        <span>Aksesibilitas</span>
    </button>
</div>
