<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DraftJawaban;
use App\Models\Kuis;
use App\Models\Materi;
use App\Models\Mengerjakan;
use App\Models\Progress;
use App\Models\RemedialSiswa;
use App\Models\SesiKuis;
use App\Models\Soal;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SiswaController extends Controller
{
    public function materi(Request $request): JsonResponse
    {
        $user = $request->user();

        $materi = Materi::query()
            ->with([
                'progress' => fn ($query) => $query->where('id_user', $user->id_user),
            ])
            ->orderBy('id_materi')
            ->paginate(20);

        return response()->json($materi);
    }

    public function showMateri(Request $request, Materi $materi): JsonResponse
    {
        $progress = Progress::firstOrCreate(
            [
                'id_materi' => $materi->id_materi,
                'id_user' => $request->user()->id_user,
            ],
            [
                'id_progress' => 'P'.Str::upper(Str::random(10)),
                'status' => 'sedang belajar',
                'tanggal' => now()->toDateString(),
            ]
        );

        return response()->json([
            'data' => $materi,
            'progress' => $progress,
        ]);
    }

    public function selesaikanMateri(Request $request, Materi $materi): JsonResponse
    {
        $progress = Progress::firstOrCreate(
            [
                'id_materi' => $materi->id_materi,
                'id_user' => $request->user()->id_user,
            ],
            [
                'id_progress' => 'P'.Str::upper(Str::random(10)),
                'tanggal' => now()->toDateString(),
            ]
        );

        $progress->update([
            'status' => 'selesai',
            'tanggal' => now()->toDateString(),
        ]);

        return response()->json([
            'message' => 'Materi berhasil ditandai selesai.',
            'data' => $progress->fresh(),
        ]);
    }

    public function kuis(Request $request): JsonResponse
    {
        $user = $request->user();

        $kuis = Kuis::query()
            ->where('status_publikasi', 'terbit')
            ->where('is_aktif', true)
            ->where(function (Builder $query) use ($user): void {
                $query->where('kategori', '!=', 'remedial')
                    ->orWhereHas('remedialSiswa', function (Builder $query) use ($user): void {
                        $query->where('status', 'ditugaskan')
                            ->whereHas('mengerjakanAsal', function (Builder $query) use ($user): void {
                                $query->where('id_user', $user->id_user);
                            });
                    });
            })
            ->with('materi:id_materi,judul,bab,kelas')
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json($kuis);
    }

    public function mulaiKuis(Request $request, Kuis $kuis): JsonResponse
    {
        $user = $request->user();
        $this->ensureKuisIsAvailable($kuis, $user);

        if (!$kuis->alokasi_waktu || $kuis->alokasi_waktu < 1) {
            throw ValidationException::withMessages([
                'alokasi_waktu' => ['Alokasi waktu kuis belum diatur.'],
            ]);
        }

        if ($kuis->kategori === 'ulangan') {
            if ($kuis->waktu_mulai && now()->lt($kuis->waktu_mulai)) {
                throw ValidationException::withMessages([
                    'kuis' => ['Kuis belum dapat dikerjakan.'],
                ]);
            }

            if ($kuis->waktu_selesai && now()->gte($kuis->waktu_selesai)) {
                throw ValidationException::withMessages([
                    'kuis' => ['Waktu pengerjaan kuis sudah berakhir.'],
                ]);
            }
        }

        $sesi = DB::transaction(function () use ($kuis, $user): SesiKuis {
            $sesi = SesiKuis::query()
                ->where('id_kuis', $kuis->id_kuis)
                ->where('id_user', $user->id_user)
                ->whereNull('waktu_selesai')
                ->latest('id_sesi')
                ->lockForUpdate()
                ->first();

            if ($sesi) {
                return $sesi;
            }

            $percobaanTerakhir = SesiKuis::query()
                ->where('id_kuis', $kuis->id_kuis)
                ->where('id_user', $user->id_user)
                ->max('percobaan_ke');

            return SesiKuis::create([
                'id_kuis' => $kuis->id_kuis,
                'id_user' => $user->id_user,
                'percobaan_ke' => ($percobaanTerakhir ?? 0) + 1,
                'waktu_mulai' => now(),
            ]);
        });

        $batasWaktu = $this->batasWaktu($kuis, $sesi);

        if (now()->greaterThanOrEqualTo($batasWaktu)) {
            throw ValidationException::withMessages([
                'kuis' => ['Waktu pengerjaan kuis sudah habis.'],
            ]);
        }

        return response()->json([
            'message' => 'Sesi kuis siap dikerjakan.',
            'data' => [
                'id_sesi' => $sesi->id_sesi,
                'percobaan_ke' => $sesi->percobaan_ke,
                'waktu_mulai' => $sesi->waktu_mulai,
                'batas_waktu' => $batasWaktu,
            ],
        ]);
    }

    public function detailKuis(Request $request, Kuis $kuis): JsonResponse
    {
        $user = $request->user();
        $this->ensureKuisIsAvailable($kuis, $user);

        $sesi = SesiKuis::query()
            ->where('id_kuis', $kuis->id_kuis)
            ->where('id_user', $user->id_user)
            ->whereNull('waktu_selesai')
            ->latest('id_sesi')
            ->firstOrFail();

        $batasWaktu = $this->batasWaktu($kuis, $sesi);

        if (now()->greaterThanOrEqualTo($batasWaktu)) {
            throw ValidationException::withMessages([
                'kuis' => ['Waktu pengerjaan kuis sudah habis.'],
            ]);
        }

        $soal = $kuis->soal()
            ->select([
                'id_soal',
                'id_kuis',
                'pertanyaan',
                'opsi_a',
                'opsi_b',
                'opsi_c',
                'opsi_d',
                'opsi_e',
            ])
            ->orderBy('id_soal')
            ->get();

        $draftJawaban = DraftJawaban::query()
            ->where('id_kuis', $kuis->id_kuis)
            ->where('id_user', $user->id_user)
            ->get(['id_soal', 'jawaban_dipilih'])
            ->keyBy('id_soal');

        return response()->json([
            'data' => [
                'id_kuis' => $kuis->id_kuis,
                'judul' => $kuis->judul,
                'alokasi_waktu' => $kuis->alokasi_waktu,
                'sesi' => $sesi,
                'batas_waktu' => $batasWaktu,
                'soal' => $soal,
                'draft_jawaban' => $draftJawaban,
            ],
        ]);
    }

    public function simpanJawaban(Request $request, Kuis $kuis): JsonResponse
    {
        $validated = $request->validate([
            'id_soal' => ['required', 'string'],
            'jawaban' => ['nullable', 'in:A,B,C,D,E'],
        ]);
        $user = $request->user();
        $this->ensureKuisIsAvailable($kuis, $user);

        $sesi = SesiKuis::query()
            ->where('id_kuis', $kuis->id_kuis)
            ->where('id_user', $user->id_user)
            ->whereNull('waktu_selesai')
            ->latest('id_sesi')
            ->first();

        if (!$sesi) {
            return response()->json([
                'message' => 'Sesi kuis tidak ditemukan.',
            ], 404);
        }

        if (now()->greaterThanOrEqualTo($this->batasWaktu($kuis, $sesi))) {
            throw ValidationException::withMessages([
                'kuis' => ['Waktu pengerjaan sudah habis.'],
            ]);
        }

        $soal = Soal::query()
            ->where('id_soal', $validated['id_soal'])
            ->where('id_kuis', $kuis->id_kuis)
            ->first();

        if (!$soal) {
            return response()->json([
                'message' => 'Soal tidak ditemukan dalam kuis ini.',
            ], 404);
        }

        $draft = DraftJawaban::updateOrCreate(
            [
                'id_kuis' => $kuis->id_kuis,
                'id_user' => $user->id_user,
                'id_soal' => $soal->id_soal,
            ],
            [
                'jawaban_dipilih' => $validated['jawaban'] ?? null,
                'updated_at' => now(),
            ]
        );

        return response()->json([
            'message' => 'Jawaban tersimpan.',
            'data' => [
                'id_draft' => $draft->id_draft,
                'id_soal' => $draft->id_soal,
                'jawaban_dipilih' => $draft->jawaban_dipilih,
            ],
        ]);
    }

    public function submitKuis(Request $request, Kuis $kuis): JsonResponse
    {
        $user = $request->user();
        $this->ensureKuisIsAvailable($kuis, $user);

        $hasil = DB::transaction(function () use ($kuis, $user): ?Mengerjakan {
            $sesi = SesiKuis::query()
                ->where('id_kuis', $kuis->id_kuis)
                ->where('id_user', $user->id_user)
                ->whereNull('waktu_selesai')
                ->latest('id_sesi')
                ->lockForUpdate()
                ->first();

            if (!$sesi) {
                return null;
            }

            $soal = $kuis->soal()->orderBy('id_soal')->get();

            if ($soal->isEmpty()) {
                throw ValidationException::withMessages([
                    'kuis' => ['Kuis belum memiliki soal.'],
                ]);
            }

            $draftJawaban = DraftJawaban::query()
                ->where('id_kuis', $kuis->id_kuis)
                ->where('id_user', $user->id_user)
                ->get()
                ->keyBy('id_soal');

            $totalBobot = $soal->sum(fn (Soal $item): float => (float) $item->bobot);
            $bobotBenar = 0.0;

            foreach ($soal as $item) {
                $jawabanSiswa = $draftJawaban->get($item->id_soal)?->jawaban_dipilih;

                if ($jawabanSiswa && strtoupper($jawabanSiswa) === strtoupper($item->jawaban)) {
                    $bobotBenar += (float) $item->bobot;
                }
            }

            $nilai = $totalBobot > 0
                ? round(($bobotBenar / $totalBobot) * 100, 2)
                : 0;

            $hasil = Mengerjakan::create([
                'id_kuis' => $kuis->id_kuis,
                'id_user' => $user->id_user,
                'nilai' => $nilai,
                'tanggal' => now()->toDateString(),
                'percobaan_ke' => $sesi->percobaan_ke,
                'status_pengerjaan' => 'final',
                'status_validasi' => 'belum divalidasi',
            ]);

            if ($kuis->kategori === 'remedial') {
                RemedialSiswa::query()
                    ->where('id_kuis_remedial', $kuis->id_kuis)
                    ->whereHas('mengerjakanAsal', fn (Builder $query) => $query->where('id_user', $user->id_user))
                    ->where('status', 'ditugaskan')
                    ->update(['status' => 'selesai']);
            }

            $sesi->update(['waktu_selesai' => now()]);

            DraftJawaban::query()
                ->where('id_kuis', $kuis->id_kuis)
                ->where('id_user', $user->id_user)
                ->delete();

            return $hasil;
        });

        if (!$hasil) {
            return response()->json([
                'message' => 'Pengerjaan kuis ini sudah selesai atau belum dimulai.',
            ], 409);
        }

        return response()->json([
            'message' => 'Kuis berhasil dikumpulkan.',
            'data' => $hasil,
        ], 201);
    }

    public function nilai(Request $request): JsonResponse
    {
        $nilai = Mengerjakan::query()
            ->with('kuis:id_kuis,judul,kategori,kkm')
            ->where('id_user', $request->user()->id_user)
            ->where('status_pengerjaan', 'final')
            ->orderByDesc('tanggal')
            ->orderByDesc('id_mengerjakan')
            ->paginate(20);

        return response()->json($nilai);
    }

    public function remedial(Request $request): JsonResponse
    {
        $tugas = RemedialSiswa::query()
            ->with([
                'kuisRemedial:id_kuis,judul,deskripsi,alokasi_waktu,kkm,status_publikasi,is_aktif',
                'mengerjakanAsal.kuis:id_kuis,judul,kkm',
            ])
            ->whereHas('mengerjakanAsal', function (Builder $query) use ($request): void {
                $query->where('id_user', $request->user()->id_user);
            })
            ->orderByDesc('tanggal_ditugaskan')
            ->paginate(20);

        return response()->json($tugas);
    }

    private function ensureKuisIsAvailable(Kuis $kuis, User $user): void
    {
        abort_unless($kuis->status_publikasi === 'terbit' && $kuis->is_aktif, 404);

        if ($kuis->kategori !== 'remedial') {
            return;
        }

        $hasAssignment = RemedialSiswa::query()
            ->where('id_kuis_remedial', $kuis->id_kuis)
            ->where('status', 'ditugaskan')
            ->whereHas('mengerjakanAsal', fn (Builder $query) => $query->where('id_user', $user->id_user))
            ->exists();

        abort_unless($hasAssignment, 403, 'Kamu tidak memiliki tugas remedial untuk kuis ini.');
    }

    private function batasWaktu(Kuis $kuis, SesiKuis $sesi): \Illuminate\Support\Carbon
    {
        $batasWaktu = $sesi->waktu_mulai->copy()->addMinutes($kuis->alokasi_waktu);

        if (
            $kuis->kategori === 'ulangan' &&
            $kuis->waktu_selesai &&
            $kuis->waktu_selesai->lessThan($batasWaktu)
        ) {
            return $kuis->waktu_selesai->copy();
        }

        return $batasWaktu;
    }
}
