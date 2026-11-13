<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Mapel;
use App\Models\Materi;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class GuruMateriController extends Controller
{
    public function index(): JsonResponse
    {
        $materi = Materi::query()
            ->with('user:id_user,nama_lengkap')
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

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate($this->rules(), $this->messages());

        $this->ensureUniqueMateri($validated);

        $validated['id_materi'] = $this->nextId();
        $validated['id_user'] = $request->user()->id_user;
        $path = null;

        try {
            if ($request->hasFile('file_pdf')) {
                $path = $request->file('file_pdf')->store('materi', 'public');
                if ($path === false) {
                    throw new \RuntimeException('File PDF materi gagal disimpan.');
                }
                $validated['isi_materi'] = $path;
            }

            unset($validated['file_pdf']);
            $materi = Materi::create($validated);
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
            'data' => $this->materiPayload($materi->load('user:id_user,nama_lengkap')),
        ]);
    }

    public function update(Request $request, Materi $materi): JsonResponse
    {
        $validated = $request->validate($this->rules($materi), $this->messages());

        $this->ensureUniqueMateri($validated, $materi);
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

            unset($validated['file_pdf']);
            $materi->update($validated);
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
            'data' => $this->materiPayload($materi->fresh()),
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
    private function rules(?Materi $materi = null): array
    {
        $mapelRules = ['sometimes', 'nullable', 'string'];
        if (Schema::hasColumn('tm_materi', 'id_mapel') && Schema::hasTable('tm_mapel')) {
            $mapelRules[] = Rule::exists('tm_mapel', 'id_mapel')
                ->where(fn (QueryBuilder $query) => $query
                    ->where('is_aktif', true)
                    ->when($materi !== null, fn (QueryBuilder $query) => $query
                        ->orWhere('id_mapel', $materi->id_mapel)));
        } else {
            $mapelRules[] = 'prohibited';
        }

        return [
            'id_mapel' => $mapelRules,
            'judul' => ['required', 'string', 'max:150'],
            'bab' => ['nullable', 'string', 'max:50'],
            'kelas' => ['nullable', 'string', 'max:50'],
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
            'id_mapel.exists' => 'Mata pelajaran tidak tersedia.',
            'file_pdf.mimes' => 'File materi harus berformat PDF.',
            'file_pdf.max' => 'Ukuran file PDF maksimal 10 MB.',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function materiPayload(Materi $materi): array
    {
        return [
            ...$materi->toArray(),
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

    /**
     * @param  array<string, mixed>  $data
     */
    private function ensureUniqueMateri(array $data, ?Materi $materi = null): void
    {
        $idMapel = $data['id_mapel'] ?? $materi?->id_mapel;
        $kelas = array_key_exists('kelas', $data) ? $data['kelas'] : $materi?->kelas;
        $bab = array_key_exists('bab', $data) ? $data['bab'] : $materi?->bab;

        if ($bab !== null && $bab !== '') {
            $babQuery = Materi::query()
                ->where('id_mapel', $idMapel)
                ->where('kelas', $kelas)
                ->where('bab', $bab);

            if ($materi) {
                $babQuery->where('id_materi', '!=', $materi->id_materi);
            }

            if ($babQuery->exists()) {
                throw ValidationException::withMessages([
                    'bab' => ['Bab tersebut sudah digunakan untuk mata pelajaran dan kelas ini.'],
                ]);
            }
        }

        $query = Materi::query()
            ->where('judul', $data['judul'])
            ->where('kelas', $kelas)
            ->where('bab', $bab);

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
