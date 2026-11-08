<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Materi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class GuruMateriController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(
            Materi::query()->with('user:id_user,nama_lengkap')->latest()->paginate(20)
        );
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'judul' => ['required', 'string', 'max:150'],
            'bab' => ['nullable', 'string', 'max:50'],
            'kelas' => ['nullable', 'string', 'max:50'],
            'deskripsi' => ['nullable', 'string'],
            'isi_materi' => ['nullable', 'string', 'max:255'],
            'link_youtube' => ['nullable', 'url', 'max:255'],
        ]);

        $this->ensureUniqueMateri($validated);

        $validated['id_materi'] = $this->nextId();
        $validated['id_user'] = $request->user()->id_user;
        $materi = Materi::create($validated);

        return response()->json([
            'message' => 'Materi berhasil ditambahkan.',
            'data' => $materi,
        ], 201);
    }

    public function show(Materi $materi): JsonResponse
    {
        return response()->json([
            'data' => $materi->load('user:id_user,nama_lengkap'),
        ]);
    }

    public function update(Request $request, Materi $materi): JsonResponse
    {
        $validated = $request->validate([
            'judul' => ['required', 'string', 'max:150'],
            'bab' => ['nullable', 'string', 'max:50'],
            'kelas' => ['nullable', 'string', 'max:50'],
            'deskripsi' => ['nullable', 'string'],
            'isi_materi' => ['nullable', 'string', 'max:255'],
            'link_youtube' => ['nullable', 'url', 'max:255'],
        ]);

        $this->ensureUniqueMateri($validated, $materi);
        $materi->update($validated);

        return response()->json([
            'message' => 'Materi berhasil diperbarui.',
            'data' => $materi->fresh(),
        ]);
    }

    public function destroy(Materi $materi): JsonResponse
    {
        $materi->delete();

        return response()->json([
            'message' => 'Materi berhasil dihapus.',
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function ensureUniqueMateri(array $data, ?Materi $materi = null): void
    {
        $query = Materi::query()
            ->where('judul', $data['judul'])
            ->where('kelas', $data['kelas'] ?? null)
            ->where('bab', $data['bab'] ?? null);

        if ($materi) {
            $query->where('id_materi', '!=', $materi->id_materi);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'judul' => ['Materi dengan kelas, bab, dan judul tersebut sudah ada.'],
            ]);
        }
    }

    private function nextId(): string
    {
        $number = Materi::query()
            ->pluck('id_materi')
            ->map(fn (string $id): int => (int) preg_replace('/^M/', '', $id))
            ->max() ?? 0;

        return 'M'.str_pad((string) ($number + 1), 3, '0', STR_PAD_LEFT);
    }
}
