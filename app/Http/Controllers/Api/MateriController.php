<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Materi;
use App\Models\Progress;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MateriController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $student = $request->user();

        $materi = $this->visibleMateriQuery($student)
            ->with([
                'progress' => fn (HasMany $query) => $query->where('id_user', $student->id_user),
            ])
            ->orderBy('bab')
            ->orderBy('judul')
            ->paginate(15);

        return response()->json([
            'data' => $materi->getCollection()
                ->map(fn (Materi $item): array => $this->materiData($item, $item->progress->first()))
                ->values(),
            'meta' => [
                'current_page' => $materi->currentPage(),
                'last_page' => $materi->lastPage(),
                'per_page' => $materi->perPage(),
                'total' => $materi->total(),
            ],
        ]);
    }

    public function show(Request $request, string $idMateri): JsonResponse
    {
        $student = $request->user();
        $materi = $this->visibleMateriQuery($student)->findOrFail($idMateri);

        $progress = Progress::firstOrCreate(
            [
                'id_materi' => $materi->id_materi,
                'id_user' => $student->id_user,
            ],
            [
                'id_progress' => 'P'.Str::upper(Str::random(10)),
                'status' => 'sedang belajar',
                'tanggal' => now()->toDateString(),
            ]
        );

        return response()->json([
            'data' => $this->materiData($materi, $progress),
        ]);
    }

    public function complete(Request $request, string $idMateri): JsonResponse
    {
        $student = $request->user();
        $materi = $this->visibleMateriQuery($student)->findOrFail($idMateri);

        $progress = Progress::firstOrCreate(
            [
                'id_materi' => $materi->id_materi,
                'id_user' => $student->id_user,
            ],
            [
                'id_progress' => 'P'.Str::upper(Str::random(10)),
            ]
        );

        $progress->update([
            'status' => 'selesai',
            'tanggal' => now()->toDateString(),
        ]);

        return response()->json([
            'data' => $this->materiData($materi, $progress->refresh()),
        ]);
    }

    /**
     * @return Builder<Materi>
     */
    private function visibleMateriQuery(User $student): Builder
    {
        return Materi::query()
            ->where(function (Builder $query) use ($student): void {
                $query->whereNull('kelas');

                if ($student->kelas !== null) {
                    $query->orWhere('kelas', $student->kelas);
                }
            });
    }

    /**
     * @return array<string, mixed>
     */
    private function materiData(Materi $materi, ?Progress $progress): array
    {
        return [
            'id_materi' => $materi->id_materi,
            'judul' => $materi->judul,
            'bab' => $materi->bab,
            'kelas' => $materi->kelas,
            'deskripsi' => $materi->deskripsi,
            'file_url' => $materi->isi_materi === null ? null : asset($materi->isi_materi),
            'link_youtube' => $materi->link_youtube,
            'status_progress' => $progress?->status ?? 'belum mulai',
            'tanggal_progress' => $progress?->tanggal?->toDateString(),
        ];
    }
}
