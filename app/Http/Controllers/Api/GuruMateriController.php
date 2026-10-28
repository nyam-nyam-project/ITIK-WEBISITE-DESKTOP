<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Materi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class GuruMateriController extends Controller
{
    public function index(): JsonResponse
    {
        $materi = Materi::query()
            ->orderByDesc('created_at')
            ->paginate(15);

        return response()->json([
            'data' => $materi->getCollection()
                ->map(fn (Materi $item): array => $this->materiData($item))
                ->values(),
            'meta' => [
                'current_page' => $materi->currentPage(),
                'last_page' => $materi->lastPage(),
                'per_page' => $materi->perPage(),
                'total' => $materi->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $this->validatedMateri($request);
        $validated['id_materi'] = $this->newMateriId();
        $validated['id_user'] = $request->user()->id_user;

        $materi = Materi::create($validated);

        return response()->json([
            'data' => $this->materiData($materi),
        ], 201);
    }

    public function show(string $materi): JsonResponse
    {
        $materi = Materi::findOrFail($materi);

        return response()->json([
            'data' => $this->materiData($materi),
        ]);
    }

    public function update(Request $request, string $materi): JsonResponse
    {
        $materi = Materi::findOrFail($materi);
        $materi->update($this->validatedMateri($request, $materi));

        return response()->json([
            'data' => $this->materiData($materi->refresh()),
        ]);
    }

    public function destroy(string $materi): Response
    {
        $materi = Materi::findOrFail($materi);

        if (! $materi->delete()) {
            throw new \RuntimeException('Materi could not be deleted.');
        }

        return response()->noContent();
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedMateri(Request $request, ?Materi $materi = null): array
    {
        $judulRule = Rule::unique('materis', 'judul');

        if ($materi !== null) {
            $judulRule->ignore($materi->id_materi, 'id_materi');
        }

        return $request->validate([
            'judul' => ['required', 'string', 'max:150', $judulRule],
            'bab' => ['nullable', 'string', 'max:50'],
            'kelas' => ['nullable', 'string', 'max:50'],
            'deskripsi' => ['nullable', 'string'],
            'isi_materi' => ['nullable', 'string', 'max:255'],
            'link_youtube' => ['nullable', 'url', 'max:255'],
        ]);
    }

    private function newMateriId(): string
    {
        do {
            $idMateri = 'M'.Str::upper(Str::random(10));
        } while (Materi::whereKey($idMateri)->exists());

        return $idMateri;
    }

    /**
     * @return array<string, mixed>
     */
    private function materiData(Materi $materi): array
    {
        return [
            'id_materi' => $materi->id_materi,
            'judul' => $materi->judul,
            'bab' => $materi->bab,
            'kelas' => $materi->kelas,
            'deskripsi' => $materi->deskripsi,
            'isi_materi' => $materi->isi_materi,
            'file_url' => $materi->isi_materi === null ? null : asset($materi->isi_materi),
            'link_youtube' => $materi->link_youtube,
            'id_user' => $materi->id_user,
            'created_at' => $materi->created_at,
            'updated_at' => $materi->updated_at,
        ];
    }
}
