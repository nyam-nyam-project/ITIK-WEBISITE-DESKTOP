<?php

namespace App\Services;

use App\Models\Materi;
use Illuminate\Validation\ValidationException;

class KuisPrePostUnikService
{
    /**
     * @param  array<int, string>  $materiIds
     */
    public function pastikanUnikUntukMateri(
        array $materiIds,
        string $kategori,
        ?string $idKuisDikecualikan = null
    ): void {
        if ($materiIds === []) {
            return;
        }

        if (in_array(strtolower(trim($kategori)), ['pre-test', 'post-test'], true) && count($materiIds) > 1) {
            throw ValidationException::withMessages([
                'materi' => 'Pre-test dan post-test hanya boleh dihubungkan ke tepat satu materi.',
            ]);
        }

        $materiList = Materi::query()
            ->whereIn('id_materi', $materiIds)
            ->orderBy('id_materi')
            ->lockForUpdate()
            ->get(['id_materi', 'judul']);

        if ($materiList->count() !== count($materiIds)) {
            throw ValidationException::withMessages([
                'materi' => 'Satu atau beberapa materi yang dipilih tidak tersedia.',
            ]);
        }

        foreach ($materiList as $materi) {
            $sudahAda = $materi->kuis()
                ->where('tm_kuis.is_aktif', true)
                ->whereRaw('LOWER(TRIM(tm_kuis.kategori)) = ?', [$kategori])
                ->when(
                    $idKuisDikecualikan !== null,
                    fn ($query) => $query->where('tm_kuis.id_kuis', '!=', $idKuisDikecualikan)
                )
                ->exists();

            if ($sudahAda) {
                throw ValidationException::withMessages([
                    'materi' => 'Materi "'.$materi->judul.'" sudah memiliki '.ucfirst($kategori).' aktif. Setiap materi hanya boleh memiliki satu '.$kategori.'.',
                ]);
            }
        }
    }
}
