<?php

namespace Database\Seeders;

use App\Models\Materi;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class MateriSeeder extends Seeder
{
    /**
     * Salin file PDF dummy ke storage dan perbarui path isi_materi
     * untuk materi yang belum punya file PDF.
     *
     * Operasi non-destruktif: data materi yang sudah ada tidak dihapus.
     * Hanya materi yang isi_materi-nya kosong atau file fisiknya tidak
     * ditemukan di disk public yang akan diisi dengan PDF dummy.
     */
    public function run(): void
    {
        $sumber = database_path('seeders/dummy/contoh-materi.pdf');

        if (! file_exists($sumber)) {
            $this->command?->warn('File dummy tidak ditemukan: '.$sumber);

            return;
        }

        // Salin PDF dummy ke storage/app/public/materi/contoh-materi.pdf
        $targetNama = 'contoh-materi.pdf';
        $targetPath = 'materi/'.$targetNama;

        if (! Storage::disk('public')->exists($targetPath)) {
            Storage::disk('public')->put($targetPath, file_get_contents($sumber));
            $this->command?->info('PDF dummy disalin ke: '.$targetPath);
        } else {
            $this->command?->info('PDF dummy sudah ada di: '.$targetPath);
        }

        // Perbarui materi yang isi_materi-nya kosong atau file fisiknya tidak ada
        $jumlahDiperbarui = 0;
        $semuaMateri = Materi::all();

        foreach ($semuaMateri as $materi) {
            $perluDiperbarui = empty($materi->isi_materi)
                || ! Storage::disk('public')->exists($materi->isi_materi);

            if ($perluDiperbarui) {
                $materi->update(['isi_materi' => $targetPath]);
                $jumlahDiperbarui++;
            }
        }

        if ($jumlahDiperbarui > 0) {
            $this->command?->info("$jumlahDiperbarui materi diperbarui dengan PDF dummy.");
        } else {
            $this->command?->info('Semua materi sudah memiliki file PDF.');
        }
    }
}
