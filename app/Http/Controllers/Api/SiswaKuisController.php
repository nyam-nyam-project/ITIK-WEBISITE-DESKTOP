<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DraftJawaban;
use App\Models\Kuis;
use App\Models\Mengerjakan;
use App\Models\SesiKuis;
use App\Models\Soal;
use App\Models\User;
use App\Services\QuizAvailabilityService;
use App\Services\QuizSubmissionService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class SiswaKuisController extends Controller
{
    public function __construct(
        private QuizAvailabilityService $quizAvailability,
        private QuizSubmissionService $quizSubmission
    ) {}

    public function index(Request $request): JsonResponse
    {
        $student = $request->user();
        $quizzes = $this->visibleQuizzesQuery($student)
            ->with('materi')
            ->withCount('soal')
            ->orderByDesc('created_at')
            ->orderBy('id_kuis')
            ->paginate(15);

        $studentResults = Mengerjakan::where('id_user', $student->id_user)
            ->where('status_pengerjaan', 'final')
            ->get(['id_kuis', 'nilai', 'percobaan_ke']);
        $resultsByQuiz = $studentResults->groupBy('id_kuis');
        $quizIds = $quizzes->getCollection()->pluck('id_kuis');
        $sessionsByQuiz = SesiKuis::where('id_user', $student->id_user)
            ->whereIn('id_kuis', $quizIds)
            ->get(['id_kuis', 'percobaan_ke', 'waktu_mulai', 'waktu_selesai'])
            ->groupBy('id_kuis');
        $publishedQuizzes = Kuis::where('status_publikasi', 'terbit')
            ->where('is_aktif', true)
            ->with('materi')
            ->get();

        return response()->json([
            'data' => $quizzes->getCollection()
                ->map(fn (Kuis $quiz): array => $this->quizData(
                    $quiz,
                    $student,
                    $resultsByQuiz->get($quiz->id_kuis, collect()),
                    $sessionsByQuiz->get($quiz->id_kuis, collect()),
                    $publishedQuizzes,
                    $studentResults
                ))
                ->values(),
            'meta' => [
                'current_page' => $quizzes->currentPage(),
                'last_page' => $quizzes->lastPage(),
                'per_page' => $quizzes->perPage(),
                'total' => $quizzes->total(),
            ],
        ]);
    }

    public function show(Request $request, string $idKuis): JsonResponse
    {
        $student = $request->user();
        $quiz = $this->visibleQuizzesQuery($student)
            ->with(['materi', 'soal'])
            ->withCount('soal')
            ->findOrFail($idKuis);
        $allResults = Mengerjakan::where('id_user', $student->id_user)
            ->where('status_pengerjaan', 'final')
            ->get(['id_kuis', 'nilai', 'percobaan_ke']);
        $results = $allResults->where('id_kuis', $quiz->id_kuis)->values();
        $sessions = SesiKuis::where('id_user', $student->id_user)
            ->where('id_kuis', $quiz->id_kuis)
            ->get(['id_kuis', 'percobaan_ke', 'waktu_mulai', 'waktu_selesai']);

        return response()->json([
            'data' => $this->quizData($quiz, $student, $results, $sessions, null, $allResults),
        ]);
    }

    public function start(Request $request, string $idKuis): JsonResponse
    {
        $student = $request->user();
        $quiz = $this->visibleQuizzesQuery($student)
            ->with('materi')
            ->withCount('soal')
            ->findOrFail($idKuis);

        if ($quiz->kategori === 'remedial') {
            if (! $this->quizAvailability->hasAssignedRemedial($quiz, $student)) {
                abort(Response::HTTP_NOT_FOUND);
            }
        } elseif (! $this->quizAvailability->isUnlocked($quiz, $student)) {
            return response()->json([
                'message' => 'Tingkat ujian ini belum terbuka. Selesaikan tingkat sebelumnya dengan nilai minimal KKM.',
            ], Response::HTTP_FORBIDDEN);
        }

        $passed = $quiz->kategori !== 'remedial' &&
            Mengerjakan::where('id_kuis', $quiz->id_kuis)
                ->where('id_user', $student->id_user)
                ->where('status_pengerjaan', 'final')
                ->whereNotNull('nilai')
                ->get(['nilai'])
                ->contains(fn (Mengerjakan $result): bool => $quiz->kkm !== null && $result->nilai >= $quiz->kkm);

        if ($passed) {
            return response()->json([
                'message' => 'Kamu sudah lulus ujian ini.',
            ], Response::HTTP_CONFLICT);
        }

        if ($quiz->soal_count === 0) {
            return response()->json([
                'message' => 'Kuis belum memiliki soal.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($quiz->alokasi_waktu === null || $quiz->alokasi_waktu < 1) {
            return response()->json([
                'message' => 'Alokasi waktu ujian belum diatur.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (
            $quiz->kategori === 'ulangan' &&
            (($quiz->waktu_mulai && now()->lt($quiz->waktu_mulai)) ||
                ($quiz->waktu_selesai && now()->gte($quiz->waktu_selesai)))
        ) {
            return response()->json([
                'message' => 'Ujian sedang tidak berada dalam jendela waktu pengerjaan.',
            ], Response::HTTP_CONFLICT);
        }

        $session = DB::transaction(function () use ($quiz, $student): ?SesiKuis {
            User::whereKey($student->id_user)->lockForUpdate()->firstOrFail();

            $session = SesiKuis::where('id_kuis', $quiz->id_kuis)
                ->where('id_user', $student->id_user)
                ->whereNull('waktu_selesai')
                ->latest('id_sesi')
                ->lockForUpdate()
                ->first();

            if ($session !== null) {
                return $session;
            }

            $previousAttempt = (int) (SesiKuis::where('id_kuis', $quiz->id_kuis)
                ->where('id_user', $student->id_user)
                ->max('percobaan_ke') ?? 0);
            $attempt = $previousAttempt + 1;

            if ($quiz->maks_percobaan !== null && $attempt > $quiz->maks_percobaan) {
                return null;
            }

            return SesiKuis::create([
                'id_kuis' => $quiz->id_kuis,
                'id_user' => $student->id_user,
                'percobaan_ke' => $attempt,
                'waktu_mulai' => now(),
            ]);
        });

        if ($session === null) {
            return response()->json([
                'message' => 'Batas maksimal percobaan ujian sudah tercapai.',
            ], Response::HTTP_CONFLICT);
        }

        $deadline = $this->deadline($quiz, $session);

        if (now()->greaterThanOrEqualTo($deadline)) {
            return response()->json([
                'message' => 'Waktu pengerjaan sesi ini sudah habis.',
            ], Response::HTTP_CONFLICT);
        }

        $draftAnswers = DraftJawaban::where('id_kuis', $quiz->id_kuis)
            ->where('id_user', $student->id_user)
            ->get()
            ->keyBy('id_soal');

        return response()->json([
            'data' => [
                'id_kuis' => $quiz->id_kuis,
                'id_sesi' => $session->id_sesi,
                'percobaan_ke' => $session->percobaan_ke,
                'waktu_mulai' => $session->waktu_mulai->toIso8601String(),
                'batas_waktu' => $deadline->toIso8601String(),
                'sisa_detik' => max(0, now()->diffInSeconds($deadline, false)),
                'soal' => $quiz->soal
                    ->sortBy('id_soal')
                    ->map(fn (Soal $question): array => $this->questionData(
                        $question,
                        $draftAnswers->get($question->id_soal)
                    ))
                    ->values(),
            ],
        ]);
    }

    public function saveAnswer(Request $request, string $idKuis): JsonResponse
    {
        $student = $request->user();
        $quiz = $this->visibleQuizzesQuery($student)->findOrFail($idKuis);
        $session = $this->activeSession($quiz, $student);
        $deadline = $this->deadline($quiz, $session);

        if (now()->greaterThanOrEqualTo($deadline)) {
            return response()->json([
                'message' => 'Waktu pengerjaan ujian sudah habis.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $validated = $request->validate([
            'id_soal' => ['required', 'string'],
            'jawaban' => ['nullable', 'in:A,B,C,D,E'],
            'jawaban_text' => ['nullable', 'string', 'max:10000'],
        ]);
        $question = Soal::where('id_soal', $validated['id_soal'])
            ->where('id_kuis', $quiz->id_kuis)
            ->firstOrFail();

        if (
            ($question->tipe_soal === 'essay' && $request->filled('jawaban')) ||
            ($question->tipe_soal === 'pilihan_ganda' && $request->filled('jawaban_text'))
        ) {
            throw ValidationException::withMessages([
                'jawaban' => 'Format jawaban tidak sesuai dengan tipe soal.',
            ]);
        }

        $draft = DraftJawaban::updateOrCreate(
            [
                'id_kuis' => $quiz->id_kuis,
                'id_user' => $student->id_user,
                'id_soal' => $question->id_soal,
            ],
            [
                'jawaban_dipilih' => $validated['jawaban'] ?? null,
                'jawaban_text' => $validated['jawaban_text'] ?? null,
                'updated_at' => now(),
            ]
        );

        return response()->json([
            'data' => [
                'id_soal' => $question->id_soal,
                'tersimpan' => true,
                'updated_at' => $draft->updated_at?->toIso8601String(),
            ],
        ]);
    }

    public function submit(Request $request, string $idKuis): JsonResponse
    {
        $student = $request->user();
        $quiz = $this->visibleQuizzesQuery($student)->findOrFail($idKuis);
        $result = $this->quizSubmission->submit($quiz, $student);

        if ($result === null) {
            return response()->json([
                'message' => 'Tidak ada sesi ujian aktif.',
            ], Response::HTTP_CONFLICT);
        }

        $session = SesiKuis::where('id_kuis', $quiz->id_kuis)
            ->where('id_user', $student->id_user)
            ->where('percobaan_ke', $result->percobaan_ke)
            ->firstOrFail();

        return response()->json([
            'data' => $this->resultData($quiz, $result, $session),
        ]);
    }

    public function result(Request $request, string $idKuis): JsonResponse
    {
        $student = $request->user();
        $quiz = $this->visibleQuizzesQuery($student)->findOrFail($idKuis);
        $result = Mengerjakan::where('id_kuis', $quiz->id_kuis)
            ->where('id_user', $student->id_user)
            ->where('status_pengerjaan', 'final')
            ->orderByDesc('percobaan_ke')
            ->firstOrFail();
        $session = SesiKuis::where('id_kuis', $quiz->id_kuis)
            ->where('id_user', $student->id_user)
            ->where('percobaan_ke', $result->percobaan_ke)
            ->first();

        return response()->json([
            'data' => $this->resultData($quiz, $result, $session),
        ]);
    }

    /**
     * @return Builder<Kuis>
     */
    private function visibleQuizzesQuery(User $student): Builder
    {
        return Kuis::query()
            ->where('status_publikasi', 'terbit')
            ->where('is_aktif', true)
            ->whereDoesntHave('materi', function (Builder $query) use ($student): void {
                $query->whereNotNull('kelas');

                if ($student->kelas === null) {
                    return;
                }

                $query->where('kelas', '!=', $student->kelas);
            })
            ->where(function (Builder $query) use ($student): void {
                $query->where('kategori', '!=', 'remedial')
                    ->orWhereHas('remedialSiswa', function (Builder $query) use ($student): void {
                        $query->where('status', 'ditugaskan')
                            ->whereHas('mengerjakanAsal', function (Builder $query) use ($student): void {
                                $query->where('id_user', $student->id_user);
                            });
                    });
            });
    }

    /**
     * @param  iterable<Mengerjakan>  $results
     * @param  iterable<SesiKuis>  $sessions
     * @param  iterable<Kuis>|null  $publishedQuizzes
     * @param  iterable<Mengerjakan>|null  $allResults
     * @return array<string, mixed>
     */
    private function quizData(
        Kuis $quiz,
        User $student,
        iterable $results,
        iterable $sessions,
        ?iterable $publishedQuizzes = null,
        ?iterable $allResults = null
    ): array {
        $results = collect($results);
        $sessions = collect($sessions);
        $allResults = $allResults === null
            ? $results->groupBy('id_kuis')
            : collect($allResults)->groupBy('id_kuis');
        $publishedQuizzes = $publishedQuizzes === null
            ? null
            : collect($publishedQuizzes);
        $passed = $quiz->kkm !== null && $results->contains(
            fn (Mengerjakan $result): bool => $result->nilai !== null
                && $result->nilai >= $quiz->kkm
        );
        $unlocked = $quiz->kategori === 'remedial'
            ? $this->quizAvailability->hasAssignedRemedial($quiz, $student)
            : $this->quizAvailability->isUnlocked(
                $quiz,
                $student,
                $publishedQuizzes,
                $allResults
            );
        $activeSession = $sessions->first(fn (SesiKuis $session): bool => $session->waktu_selesai === null);
        $attemptsUsed = max(
            (int) ($results->max('percobaan_ke') ?? 0),
            (int) ($sessions->max('percobaan_ke') ?? 0)
        );

        $status = ! $unlocked
            ? 'terkunci'
            : ($passed
                ? 'lulus'
                : ($activeSession !== null ? 'sedang_dikerjakan' : 'tersedia'));

        return [
            'id_kuis' => $quiz->id_kuis,
            'judul' => $quiz->judul,
            'deskripsi' => $quiz->deskripsi,
            'kategori' => $quiz->kategori,
            'kategori_label' => $quiz->kategori_label,
            'tingkat_ujian' => $quiz->tingkat_ujian,
            'tingkat_ujian_label' => $quiz->tingkat_ujian_label,
            'tingkat_sebelumnya_label' => $quiz->tingkat_sebelumnya_label,
            'alokasi_waktu_menit' => $quiz->alokasi_waktu,
            'kkm' => $quiz->kkm,
            'maks_percobaan' => $quiz->maks_percobaan,
            'jumlah_soal' => $quiz->soal_count ?? $quiz->soal->count(),
            'status' => $status,
            'bisa_mulai' => $unlocked && ! $passed,
            'percobaan_terpakai' => $attemptsUsed,
            'materi' => $quiz->materi
                ->map(fn ($materi): array => [
                    'id_materi' => $materi->id_materi,
                    'judul' => $materi->judul,
                ])
                ->values(),
        ];
    }

    private function activeSession(Kuis $quiz, User $student): SesiKuis
    {
        return SesiKuis::where('id_kuis', $quiz->id_kuis)
            ->where('id_user', $student->id_user)
            ->whereNull('waktu_selesai')
            ->latest('id_sesi')
            ->firstOrFail();
    }

    private function deadline(Kuis $quiz, SesiKuis $session): Carbon
    {
        $deadline = $session->waktu_mulai->copy()->addMinutes($quiz->alokasi_waktu);

        if (
            $quiz->kategori === 'ulangan' &&
            $quiz->waktu_selesai &&
            $quiz->waktu_selesai->lessThan($deadline)
        ) {
            return $quiz->waktu_selesai->copy();
        }

        return $deadline;
    }

    /**
     * @return array<string, mixed>
     */
    private function questionData(Soal $question, ?DraftJawaban $draft): array
    {
        return [
            'id_soal' => $question->id_soal,
            'tipe_soal' => $question->tipe_soal,
            'pertanyaan' => $question->pertanyaan,
            'gambar_pertanyaan_url' => $question->gambar_pertanyaan === null
                ? null
                : asset('storage/'.$question->gambar_pertanyaan),
            'opsi' => collect(['a', 'b', 'c', 'd', 'e'])
                ->filter(fn (string $option): bool => $question->{'opsi_'.$option} !== null
                    || $question->{'gambar_opsi_'.$option} !== null)
                ->mapWithKeys(fn (string $option): array => [strtoupper($option) => [
                    'teks' => $question->{'opsi_'.$option},
                    'gambar_url' => $question->{'gambar_opsi_'.$option} === null
                        ? null
                        : asset('storage/'.$question->{'gambar_opsi_'.$option}),
                ]])
                ->all(),
            'jawaban_tersimpan' => $question->tipe_soal === 'essay'
                ? $draft?->jawaban_text
                : $draft?->jawaban_dipilih,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function resultData(Kuis $quiz, Mengerjakan $result, ?SesiKuis $session): array
    {
        $duration = $session?->durasi_detik;

        if ($duration === null && $session?->waktu_selesai !== null) {
            $duration = max(
                0,
                $session->waktu_selesai->getTimestamp() - $session->waktu_mulai->getTimestamp()
            );
        }

        return [
            'id_kuis' => $quiz->id_kuis,
            'percobaan_ke' => $result->percobaan_ke,
            'nilai' => $result->nilai,
            'kkm' => $quiz->kkm,
            'status' => $result->nilai === null
                ? 'menunggu_koreksi'
                : ($quiz->kkm !== null && $result->nilai >= $quiz->kkm ? 'lulus' : 'tidak_lulus'),
            'durasi_detik' => $duration,
            'waktu_mulai' => $session?->waktu_mulai?->toIso8601String(),
            'waktu_selesai' => $session?->waktu_selesai?->toIso8601String(),
        ];
    }
}
