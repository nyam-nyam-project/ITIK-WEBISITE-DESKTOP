<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Mapel;
use App\Models\Materi;
use App\Models\User;
use App\Services\MateriKelasService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Throwable;

class GuruMateriController extends Controller
{
    public function index(): JsonResponse
    {
        $materi = Materi::query()
            ->with(['user:id_user,nama_lengkap', 'kelasTerkait:id_kelas,nama_kelas,tingkat'])
            ->orderByDesc('created_at')
            ->orderByDesc('id_materi')
            ->paginate(20);

        $materi->getCollection()->transform(
            fn (Materi $item): array => $this->materiPayload($item)
        );

        return response()->json($materi);
    }

    public function mapel(Request $request): JsonResponse
    {
        $mapel = Mapel::query()
            ->where('is_aktif', true)
            ->when(
                $request->filled('current_id'),
                fn (Builder $query) => $query->orWhere('id_mapel', $request->string('current_id')->toString())
            )
            ->orderBy('nama_mapel')
            ->get(['id_mapel', 'kode_mapel', 'nama_mapel']);

        return response()->json(['data' => $mapel]);
    }

    public function store(Request $request, MateriKelasService $materiKelasService): JsonResponse
    {
        $user = $request->user();
        abort_unless($user instanceof User, 403);

        $mapelIds = $materiKelasService->mapelIdsUntukGuru($user);
        $idMapel = $request->input('id_mapel');
        $kelasOptions = $materiKelasService->kelasUntukGuru($user, is_string($idMapel) ? $idMapel : null);
        $validated = $request->validate(
            $this->rules($mapelIds->all(), $kelasOptions->pluck('id_kelas')->all()),
            $this->messages(),
        );
        $idKelasDipilih = array_values($validated['id_kelas']);

        $materiKelasService->pastikanBabUnik(
            $validated['bab'] ?? null,
            $validated['id_mapel'],
            $idKelasDipilih,
        );

        $validated['id_materi'] = $this->nextId();
        $validated['id_user'] = $user->id_user;
        $path = null;

        try {
            if ($request->hasFile('file_pdf')) {
                $path = $request->file('file_pdf')->store('materi', 'public');
                if ($path === false) {
                    throw new \RuntimeException('File PDF materi gagal disimpan.');
                }
                $validated['isi_materi'] = $path;
            }

            unset($validated['file_pdf'], $validated['id_kelas']);
            $materi = DB::transaction(function () use ($validated, $idKelasDipilih): Materi {
                $materi = Materi::create($validated);
                $materi->kelasTerkait()->sync($idKelasDipilih);

                return $materi;
            });
        } catch (Throwable $exception) {
            if (is_string($path)) {
                $this->deletePdf($path, $validated['id_materi']);
            }

            throw $exception;
        }

        return response()->json([
            'message' => 'Materi berhasil ditambahkan.',
            'data' => $this->materiPayload($materi),
        ], 201);
    }

    public function show(Materi $materi): JsonResponse
    {
        return response()->json([
            'data' => $this->materiPayload(
                $materi->load(['user:id_user,nama_lengkap', 'kelasTerkait:id_kelas,nama_kelas,tingkat']),
            ),
        ]);
    }

    public function update(
        Request $request,
        Materi $materi,
        MateriKelasService $materiKelasService,
    ): JsonResponse {
        $user = $request->user();
        abort_unless($user instanceof User, 403);

        $mapelIds = $materiKelasService->mapelIdsUntukGuru($user);
        $idMapel = $request->input('id_mapel', $materi->id_mapel);
        $kelasOptions = $materiKelasService->kelasUntukGuru($user, is_string($idMapel) ? $idMapel : null);
        if (! $request->has('id_kelas')) {
            $request->merge(['id_kelas' => $materi->kelasTerkait()->pluck('tm_kelas.id_kelas')->all()]);
        }
        $validated = $request->validate(
            $this->rules($mapelIds->all(), $kelasOptions->pluck('id_kelas')->all(), $materi),
            $this->messages(),
        );
        $idKelasDipilih = array_values($validated['id_kelas']);

        $materiKelasService->pastikanBabUnik(
            $validated['bab'] ?? $materi->bab,
            $validated['id_mapel'] ?? $materi->id_mapel,
            $idKelasDipilih,
            $materi,
        );

        $oldPath = $materi->isi_materi;
        $newPath = null;

        try {
            if ($request->hasFile('file_pdf')) {
                $newPath = $request->file('file_pdf')->store('materi', 'public');
                if ($newPath === false) {
                    throw new \RuntimeException('File PDF materi gagal disimpan.');
                }
                $validated['isi_materi'] = $newPath;
            }

            unset($validated['file_pdf'], $validated['id_kelas']);
            DB::transaction(function () use ($materi, $validated, $idKelasDipilih): void {
                $materi->update($validated);
                $materi->kelasTerkait()->sync($idKelasDipilih);
            });
        } catch (Throwable $exception) {
            if (is_string($newPath)) {
                $this->deletePdf($newPath, $materi->id_materi);
            }

            throw $exception;
        }

        if ($newPath !== null && $oldPath && $oldPath !== $newPath) {
            $this->deletePdf($oldPath, $materi->id_materi);
        }

        return response()->json([
            'message' => 'Materi berhasil diperbarui.',
            'data' => $this->materiPayload(
                $materi->fresh()->load(['user:id_user,nama_lengkap', 'kelasTerkait:id_kelas,nama_kelas,tingkat']),
            ),
        ]);
    }

    public function destroy(Materi $materi): JsonResponse
    {
        $path = $materi->isi_materi;
        $materi->delete();

        if ($path) {
            $this->deletePdf($path, $materi->id_materi);
        }

        return response()->json([
            'message' => 'Materi berhasil dihapus.',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function rules(array $mapelIds, array $kelasIds, ?Materi $materi = null): array
    {
        return [
            'id_mapel' => [
                $materi === null ? 'required' : 'sometimes',
                'string',
                Rule::in($mapelIds),
            ],
            'judul' => ['required', 'string', 'max:150'],
            'bab' => ['nullable', 'string', 'max:50'],
            'id_kelas' => ['required', 'array', 'min:1'],
            'id_kelas.*' => ['required', 'string', 'distinct:strict', Rule::in($kelasIds)],
            'deskripsi' => ['nullable', 'string'],
            'file_pdf' => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
            'link_youtube' => ['nullable', 'url', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    private function messages(): array
    {
        return [
            'id_mapel.in' => 'Mata pelajaran tidak tersedia atau bukan penugasan Anda pada tahun ajaran aktif.',
            'id_kelas.required' => 'Pilih minimal satu kelas.',
            'id_kelas.min' => 'Pilih minimal satu kelas.',
            'id_kelas.*.in' => 'Kelas yang dipilih tidak Anda ajar untuk mata pelajaran ini pada tahun ajaran aktif.',
            'file_pdf.mimes' => 'File materi harus berformat PDF.',
            'file_pdf.max' => 'Ukuran file PDF maksimal 10 MB.',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function materiPayload(Materi $materi): array
    {
        $payload = $materi->toArray();
        unset($payload['kelas']);

        $kelas = $materi->kelasTerkait;

        return [
            ...$payload,
            'id_kelas' => $kelas->pluck('id_kelas')->all(),
            'kelas' => $kelas->pluck('nama_kelas')->all(),
            'pdf_url' => $materi->pdfUrl(),
        ];
    }

    private function deletePdf(string $path, string $materiId): void
    {
        $disk = Storage::disk('public');
        if ($disk->exists($path) && ! $disk->delete($path)) {
            Log::warning('Gagal menghapus file PDF materi.', [
                'id_materi' => $materiId,
                'path' => $path,
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
