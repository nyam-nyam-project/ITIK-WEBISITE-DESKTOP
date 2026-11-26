@extends('layouts.guru')
@section('title', 'Detail Nilai Siswa')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/guru/nilai/show.css') }}">
@endpush

@section('content')
@php
    $menungguPenilaian = $mengerjakan->status_validasi === 'menunggu penilaian';
    $bolehMenilaiEssay = auth()->id() === $mengerjakan->kuis->id_user
        && $mengerjakan->status_pengerjaan === 'final'
        && $menungguPenilaian;
    $soalEssay = ($soal ?? collect())->where('tipe', 'essay');
    $totalEssayBelumDinilai = $soalEssay->filter(fn ($item) => $nilaiEssay->get($item->id_soal)?->nilai === null)->count();
@endphp

<div class="page-heading">
    <div>
        <h1>Detail Nilai Siswa</h1>
        <p>Detail hasil pengerjaan kuis siswa dan evaluasi butir soal.</p>
    </div>
    <a href="{{ route('guru.nilai.index') }}" class="btn btn-secondary">
        @include('partials.icon', ['name' => 'arrow-left']) Kembali
    </a>
</div>

@if($menungguPenilaian)
    <div class="essay-pending-summary" role="status">
        <strong>Menunggu penilaian essay</strong>
        <p>{{ $totalEssayBelumDinilai }} dari {{ $soalEssay->count() }} soal essay belum dinilai. Nilai akhir dan kelulusan belum ditentukan sampai seluruh essay dinilai.</p>
        @if(!$bolehMenilaiEssay)
            <p>Penilaian hanya dapat dilakukan oleh pembuat kuis pada percobaan final.</p>
        @endif
    </div>
@endif

<div class="grid grid-2">
    {{-- DATA SISWA --}}
    <section class="card">
        <div class="card-header">
            <h2>Data Siswa</h2>
        </div>
        <div class="siswa-summary-head">
            <span class="siswa-summary-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($mengerjakan->siswa->nama_lengkap ?? 'S', 0, 1)) }}</span>
            <div>
                <div class="siswa-summary-name">{{ $mengerjakan->siswa->nama_lengkap ?? '-' }}</div>
                <div class="muted small">NISN: {{ $mengerjakan->siswa->nomor_induk ?? '-' }}</div>
            </div>
        </div>
        <dl class="identity">
            <div>
                <dt>Kelas</dt>
                <dd>{{ $mengerjakan->siswa->kelas ?? '-' }}</dd>
            </div>
            <div>
                <dt>Judul Kuis</dt>
                <dd>{{ $mengerjakan->kuis->judul ?? '-' }}</dd>
            </div>
            <div>
                <dt>Percobaan</dt>
                <dd>Ke-{{ $mengerjakan->percobaan_ke }}</dd>
            </div>
        </dl>
    </section>

    {{-- DATA KUIS & NILAI --}}
    @php
        $guruLulus = !$menungguPenilaian && $mengerjakan->nilai !== null && $mengerjakan->kuis->kkm !== null && $mengerjakan->nilai >= $mengerjakan->kuis->kkm;
        $guruNilaiClass = $menungguPenilaian || $mengerjakan->nilai === null
            ? 'pending'
            : ($guruLulus ? 'lulus' : 'gagal');
    @endphp
    <section class="card guru-score-card">
        <div class="card-header">
            <h2>Hasil &amp; Kelulusan</h2>
            <span class="badge">{{ ucfirst($mengerjakan->kuis->kategori ?? '-') }}</span>
        </div>
        <div class="guru-score-hero">
            <div class="guru-score-number {{ $guruNilaiClass }}">
                {{ $menungguPenilaian ? '—' : ($mengerjakan->nilai !== null ? number_format($mengerjakan->nilai, 1) : '-') }}
            </div>
            <div class="guru-score-meta">
                @if($menungguPenilaian)
                    <span class="badge badge-muted">Menunggu penilaian</span>
                @elseif($mengerjakan->nilai === null)
                    <span class="badge badge-muted">Belum ada nilai</span>
                @elseif($guruLulus)
                    <span class="badge badge-success">@include('partials.icon', ['name' => 'check']) Lulus</span>
                @else
                    <span class="badge badge-warning">@include('partials.icon', ['name' => 'alert-triangle']) Tidak Lulus</span>
                @endif
                <span class="muted small">KKM Kuis: <strong>{{ $mengerjakan->kuis->kkm !== null ? number_format($mengerjakan->kuis->kkm, 1) : '-' }}</strong></span>
            </div>
        </div>
    </section>
</div>

{{-- KHUSUS REMEDIAL --}}
@if($mengerjakan->kuis->kategori === 'remedial')
    <section class="card">
        <div class="card-header">
            <h2>Informasi Remedial</h2>
        </div>
        @if($remedial && $remedial->mengerjakanAsal)
            <dl class="identity">
                <div>
                    <dt>Kuis Asal</dt>
                    <dd>{{ $remedial->mengerjakanAsal->kuis->judul ?? '-' }}</dd>
                </div>
                <div>
                    <dt>Nilai Sebelum Remedial</dt>
                    <dd>{{ $remedial->mengerjakanAsal->nilai }}</dd>
                </div>
                <div>
                    <dt>Nilai Setelah Remedial</dt>
                    <dd><strong class="u-color-var---purple">{{ $menungguPenilaian ? 'Menunggu penilaian' : $mengerjakan->nilai }}</strong></dd>
                </div>
                <div>
                    <dt>Status Remedial</dt>
                    <dd>
                        @if($menungguPenilaian)
                            <span class="badge badge-muted">Menunggu penilaian</span>
                        @elseif($mengerjakan->nilai === null)
                            —
                        @elseif($mengerjakan->kuis->kkm !== null && $mengerjakan->nilai >= $mengerjakan->kuis->kkm)
                            <span class="badge badge-success">Lulus setelah remedial</span>
                        @else
                            <span class="badge badge-warning">Belum lulus setelah remedial</span>
                        @endif
                    </dd>
                </div>
            </dl>
        @else
            <div class="empty">Data nilai asal remedial tidak ditemukan.</div>
        @endif
    </section>
@endif

{{-- EVALUASI JAWABAN SISWA PER BUTIR SOAL --}}
@if(isset($soal) && $soal->isNotEmpty())
    <section class="card">
        <div class="card-header">
            <div>
                <h2>@include('partials.icon', ['name' => 'file-text']) Evaluasi Jawaban Siswa per Butir Soal</h2>
                <p class="muted">{{ $soalEssay->isNotEmpty() ? 'Evaluasi pilihan ganda serta jawaban essay yang tersimpan untuk percobaan ini.' : 'Daftar butir soal, jawaban yang dipilih siswa, serta kunci jawaban sistem.' }}</p>
            </div>
        </div>

        <div class="eval-stats-guru">
            <div class="eval-stat-guru-box eval-stat-guru-box--benar">
                <div class="eval-stat-guru-num eval-stat-guru-num--benar">{{ $totalBenar ?? 0 }}</div>
                <div class="eval-stat-guru-label">{{ $soalEssay->isNotEmpty() ? 'Jawaban PG Benar' : 'Jawaban Benar' }}</div>
            </div>
            <div class="eval-stat-guru-box eval-stat-guru-box--salah">
                <div class="eval-stat-guru-num eval-stat-guru-num--salah">{{ $totalSalah ?? 0 }}</div>
                <div class="eval-stat-guru-label">{{ $soalEssay->isNotEmpty() ? 'Jawaban PG Salah' : 'Jawaban Salah' }}</div>
            </div>
            <div class="eval-stat-guru-box eval-stat-guru-box--total">
                <div class="eval-stat-guru-num eval-stat-guru-num--total">{{ count($soal) }}</div>
                <div class="eval-stat-guru-label">Total Butir Soal</div>
            </div>
        </div>

        @foreach($soal as $itemSoal)
            @if($itemSoal->tipe === 'essay')
                @php
                    $penilaianEssay = $nilaiEssay->get($itemSoal->id_soal);
                    $nilaiTersimpan = $penilaianEssay?->nilai;
                    $formEssayDikirim = old('essay_soal_id') === $itemSoal->id_soal;
                @endphp
                <div class="soal-guru-item essay">
                    <div class="soal-guru-head">
                        <strong>
                            Soal #{{ $loop->iteration }} — Essay
                            <span class="muted small u-margin-left-6px">(Bobot: {{ $itemSoal->bobot ?? 0 }}%)</span>
                        </strong>
                        <span class="badge badge-muted">{{ $nilaiTersimpan === null ? 'Belum dinilai' : 'Sudah dinilai' }}</span>
                    </div>
                    <div class="essay-question">
                        <div>{{ $itemSoal->pertanyaan }}</div>
                        @if($itemSoal->gambar)
                            <div class="soal-guru-gambar-wrap">
                                <img src="{{ asset('storage/' . $itemSoal->gambar) }}" alt="{{ Str::limit(strip_tags($itemSoal->pertanyaan), 60) }}" class="soal-guru-gambar">
                            </div>
                        @endif
                    </div>
                    <h3 class="essay-label">Jawaban Siswa</h3>
                    @if(!$penilaianEssay)
                        <p class="muted">Snapshot jawaban percobaan ini tidak tersedia.</p>
                    @elseif($penilaianEssay->jawaban_teks === null || $penilaianEssay->jawaban_teks === '')
                        <p class="muted">Tidak dijawab.</p>
                    @else
                        <div class="essay-answer">{{ $penilaianEssay->jawaban_teks }}</div>
                    @endif
                    <p class="essay-score"><strong>Nilai Essay:</strong> {{ $nilaiTersimpan === null ? 'Belum dinilai' : number_format($nilaiTersimpan, 2) . ' / ' . $itemSoal->bobot }}</p>
                    <h3 class="essay-label">Catatan Guru</h3>
                    <div class="essay-comment">{{ $penilaianEssay?->catatan_guru ?? 'Belum ada catatan.' }}</div>

                    @if($bolehMenilaiEssay && $penilaianEssay)
                        <form method="POST" action="{{ route('guru.nilai.essay.store', [$mengerjakan->id_mengerjakan, $itemSoal->id_soal]) }}" class="essay-grade-form">
                            @csrf
                            <input type="hidden" name="essay_soal_id" value="{{ $itemSoal->id_soal }}">
                            <div class="essay-field">
                                <label for="nilai-essay-{{ $itemSoal->id_soal }}">Nilai Essay (0–{{ $itemSoal->bobot }})</label>
                                <input id="nilai-essay-{{ $itemSoal->id_soal }}" type="number" name="nilai" min="0" max="{{ $itemSoal->bobot }}" step="0.01" required value="{{ $formEssayDikirim ? old('nilai') : $nilaiTersimpan }}" @if($formEssayDikirim && $errors->has('nilai')) aria-invalid="true" aria-describedby="nilai-essay-error-{{ $itemSoal->id_soal }}" @endif>
                                @if($formEssayDikirim)
                                    @error('nilai')
                                        <p id="nilai-essay-error-{{ $itemSoal->id_soal }}" class="essay-field-error" role="alert">{{ $message }}</p>
                                    @enderror
                                @endif
                            </div>
                            <div class="essay-field">
                                <label for="catatan-essay-{{ $itemSoal->id_soal }}">Catatan Guru (opsional)</label>
                                <textarea id="catatan-essay-{{ $itemSoal->id_soal }}" name="catatan_guru" rows="3" @if($formEssayDikirim && $errors->has('catatan_guru')) aria-invalid="true" aria-describedby="catatan-essay-error-{{ $itemSoal->id_soal }}" @endif>{{ $formEssayDikirim ? old('catatan_guru') : $penilaianEssay->catatan_guru }}</textarea>
                                @if($formEssayDikirim)
                                    @error('catatan_guru')
                                        <p id="catatan-essay-error-{{ $itemSoal->id_soal }}" class="essay-field-error" role="alert">{{ $message }}</p>
                                    @enderror
                                @endif
                            </div>
                            <button type="submit" class="btn">Simpan Penilaian Essay</button>
                        </form>
                    @endif
                </div>
                @continue
            @endif
            @php
                $jawabanDipilih = $draftJawaban->get($itemSoal->id_soal)?->jawaban_dipilih;
                $kunciBenar = $itemSoal->jawaban ? strtoupper($itemSoal->jawaban) : null;
                $isBenar = $jawabanDipilih && $kunciBenar && strtoupper($jawabanDipilih) === $kunciBenar;
                $opsiMap = [
                    'A' => $itemSoal->opsi_a,
                    'B' => $itemSoal->opsi_b,
                    'C' => $itemSoal->opsi_c,
                    'D' => $itemSoal->opsi_d,
                    'E' => $itemSoal->opsi_e,
                ];
            @endphp

            <div class="soal-guru-item {{ $isBenar ? 'benar' : 'salah' }}">
                <div class="soal-guru-head">
                    <strong>
                        Soal #{{ $loop->iteration }}
                        <span class="muted small u-margin-left-6px">(Bobot: {{ $itemSoal->bobot ?? 0 }}%)</span>
                    </strong>

                    @if($isBenar)
                        <span class="badge badge-success">@include('partials.icon', ['name' => 'check']) Benar (+{{ $itemSoal->bobot ?? 0 }}%)</span>
                    @elseif($jawabanDipilih)
                        <span class="badge badge-warning">@include('partials.icon', ['name' => 'x']) Salah (Siswa pilih: {{ strtoupper($jawabanDipilih) }})</span>
                    @else
                        <span class="badge badge-muted">@include('partials.icon', ['name' => 'circle-x']) Tidak Dijawab</span>
                    @endif
                </div>

                <div class="soal-guru-pertanyaan">
                    <div>{{ $itemSoal->pertanyaan }}</div>
                    @if($itemSoal->gambar)
                        <div class="soal-guru-gambar-wrap">
                            <img src="{{ asset('storage/' . $itemSoal->gambar) }}" alt="{{ Str::limit(strip_tags($itemSoal->pertanyaan), 60) }}" class="soal-guru-gambar">
                        </div>
                    @endif
                </div>

                <div class="opsi-guru-list">
                    @foreach($opsiMap as $huruf => $teksOpsi)
                        @if($teksOpsi)
                            @php
                                $isKunci = $kunciBenar === $huruf;
                                $isDipilih = strtoupper($jawabanDipilih ?? '') === $huruf;
                                $cls = '';
                                if ($isKunci) {
                                    $cls = 'kunci';
                                } elseif ($isDipilih && !$isBenar) {
                                    $cls = 'jawaban-salah';
                                }
                            @endphp

                            <div class="opsi-guru-item {{ $cls }}">
                                <strong>{{ $huruf }}.</strong>
                                <span>{{ $teksOpsi }}</span>

                                @if($isKunci && $isDipilih)
                                    <span class="opsi-guru-badge badge-green">@include('partials.icon', ['name' => 'check']) Jawaban Siswa (Benar)</span>
                                @elseif($isKunci)
                                    <span class="opsi-guru-badge badge-green">@include('partials.icon', ['name' => 'check']) Kunci Jawaban</span>
                                @elseif($isDipilih)
                                    <span class="opsi-guru-badge badge-red">@include('partials.icon', ['name' => 'x']) Pilihan Siswa (Salah)</span>
                                @endif
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        @endforeach
    </section>
@endif

@endsection
