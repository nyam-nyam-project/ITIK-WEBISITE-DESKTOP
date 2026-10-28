<?php

namespace App\Services;

use App\Models\Kuis;
use App\Models\Mengerjakan;
use App\Models\Remedial;
use App\Models\RemedialSiswa;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class QuizAvailabilityService
{
    public function isVisibleToStudent(Kuis $kuis, User $student): bool
    {
        return $kuis->materi->every(
            fn ($materi): bool => $materi->kelas === null
                || $materi->kelas === $student->kelas
        );
    }

    public function hasAssignedRemedial(Kuis $kuis, User $student): bool
    {
        return RemedialSiswa::where('id_kuis_remedial', $kuis->id_kuis)
            ->whereHas('mengerjakanAsal', function (Builder $query) use ($student): void {
                $query->where('id_user', $student->id_user);
            })
            ->where('status', 'ditugaskan')
            ->exists();
    }

    /**
     * @param  Collection<int, Kuis>|null  $publishedQuizzes
     * @param  Collection<string, Collection<int, Mengerjakan>>|null  $resultsByQuiz
     */
    public function isUnlocked(
        Kuis $kuis,
        User $student,
        ?Collection $publishedQuizzes = null,
        ?Collection $resultsByQuiz = null
    ): bool {
        $previousLevel = match ($kuis->tingkat_ujian) {
            'sedang' => 'mudah',
            'sulit' => 'sedang',
            default => null,
        };

        if ($previousLevel === null) {
            return $kuis->tingkat_ujian === 'mudah';
        }

        $publishedQuizzes ??= Kuis::where('status_publikasi', 'terbit')
            ->where('is_aktif', true)
            ->with('materi')
            ->get();

        $resultsByQuiz ??= Mengerjakan::where('id_user', $student->id_user)
            ->where('status_pengerjaan', 'final')
            ->get(['id_kuis', 'nilai'])
            ->groupBy('id_kuis');

        $materiIds = $kuis->materi
            ->pluck('id_materi')
            ->map(fn ($id): string => (string) $id)
            ->sort()
            ->values()
            ->all();

        if ($materiIds === []) {
            return false;
        }

        $quizSebelumnya = $publishedQuizzes->filter(
            fn (Kuis $candidate): bool => $candidate->id_kuis !== $kuis->id_kuis
                && $candidate->kategori === $kuis->kategori
                && $candidate->tingkat_ujian === $previousLevel
                && $candidate->materi
                    ->pluck('id_materi')
                    ->map(fn ($id): string => (string) $id)
                    ->sort()
                    ->values()
                    ->all() === $materiIds
        );

        foreach ($quizSebelumnya as $candidate) {
            $attempts = $resultsByQuiz->get($candidate->id_kuis, collect());

            if (
                $candidate->kkm !== null &&
                $attempts->contains(
                    fn (Mengerjakan $attempt): bool => $attempt->nilai !== null
                        && $attempt->nilai >= $candidate->kkm
                )
            ) {
                return true;
            }
        }

        $remedialLinks = Remedial::with('kuisRemedial')
            ->whereIn('id_kuis_asal', $quizSebelumnya->pluck('id_kuis'))
            ->get()
            ->filter(fn (Remedial $remedial): bool => $remedial->kuisRemedial !== null);

        if ($remedialLinks->isEmpty()) {
            return false;
        }

        $assignedRemedialIds = RemedialSiswa::whereIn(
            'id_kuis_remedial',
            $remedialLinks->pluck('id_kuis_remedial')
        )
            ->whereHas('mengerjakanAsal', function (Builder $query) use ($student, $quizSebelumnya): void {
                $query->where('id_user', $student->id_user)
                    ->whereIn('id_kuis', $quizSebelumnya->pluck('id_kuis'));
            })
            ->pluck('id_kuis_remedial');

        $remedialResults = Mengerjakan::where('id_user', $student->id_user)
            ->where('status_pengerjaan', 'final')
            ->whereIn('id_kuis', $assignedRemedialIds)
            ->get(['id_kuis', 'nilai'])
            ->groupBy('id_kuis');

        foreach ($remedialLinks as $remedial) {
            $attempts = $remedialResults->get($remedial->id_kuis_remedial, collect());
            $kkm = $remedial->kuisRemedial->kkm;

            if (
                $assignedRemedialIds->contains($remedial->id_kuis_remedial) &&
                $kkm !== null &&
                $attempts->contains(
                    fn (Mengerjakan $attempt): bool => $attempt->nilai !== null
                        && $attempt->nilai >= $kkm
                )
            ) {
                return true;
            }
        }

        return false;
    }
}
