<?php

namespace Tests\Feature;

use Database\Seeders\MateriSeeder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MateriSeederPdfTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->assertSame('sqlite', DB::connection()->getDriverName());
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());

        // Skema minimal tm_materi
        Schema::create('tm_materi', function (Blueprint $table): void {
            $table->string('id_materi', 50)->primary();
            $table->string('judul', 150);
            $table->string('bab', 10)->nullable();
            $table->string('id_mapel', 50)->nullable();
            $table->text('deskripsi')->nullable();
            $table->text('isi_materi')->nullable();
            $table->string('link_youtube', 255)->nullable();
            $table->string('id_user', 50)->nullable();
            $table->boolean('is_aktif')->default(true);
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('tm_materi');
        parent::tearDown();
    }

    public function test_file_pdf_dummy_ada_di_repositori_dan_valid(): void
    {
        $path = database_path('seeders/dummy/contoh-materi.pdf');

        $this->assertFileExists($path);
        $this->assertGreaterThan(100, filesize($path));

        // Cek header PDF valid
        $header = file_get_contents($path, false, null, 0, 8);
        $this->assertStringStartsWith('%PDF-', $header);
    }

    public function test_seeder_menyalin_pdf_ke_disk_public(): void
    {
        Storage::fake('public');

        // Jalankan seeder
        (new MateriSeeder)->run();

        // File harus ada di disk public
        Storage::disk('public')->assertExists('materi/contoh-materi.pdf');
    }

    public function test_seeder_mengisi_isi_materi_yang_kosong(): void
    {
        Storage::fake('public');

        // Buat materi dengan isi_materi kosong
        DB::table('tm_materi')->insert([
            'id_materi' => 'TEST001',
            'judul' => 'Materi Uji Kosong',
            'isi_materi' => null,
            'is_aktif' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        (new MateriSeeder)->run();

        $materi = DB::table('tm_materi')->where('id_materi', 'TEST001')->first();
        $this->assertSame('materi/contoh-materi.pdf', $materi->isi_materi);
        Storage::disk('public')->assertExists($materi->isi_materi);
    }

    public function test_seeder_tidak_menimpa_isi_materi_yang_filenya_ada(): void
    {
        Storage::fake('public');

        // Buat file yang sudah ada di disk
        Storage::disk('public')->put('materi/file-asli.pdf', '%PDF-1.4 existing');

        DB::table('tm_materi')->insert([
            'id_materi' => 'TEST002',
            'judul' => 'Materi Sudah Ada File',
            'isi_materi' => 'materi/file-asli.pdf',
            'is_aktif' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        (new MateriSeeder)->run();

        // isi_materi harus tetap menunjuk ke file asli, bukan dummy
        $materi = DB::table('tm_materi')->where('id_materi', 'TEST002')->first();
        $this->assertSame('materi/file-asli.pdf', $materi->isi_materi);
    }

    public function test_seeder_mengisi_materi_yang_filenya_tidak_ada_di_storage(): void
    {
        Storage::fake('public');

        // isi_materi terisi tapi file fisiknya tidak ada di disk
        DB::table('tm_materi')->insert([
            'id_materi' => 'TEST003',
            'judul' => 'Materi File Hilang',
            'isi_materi' => 'materi/file-hilang.pdf',
            'is_aktif' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        (new MateriSeeder)->run();

        // Harus diganti dengan dummy
        $materi = DB::table('tm_materi')->where('id_materi', 'TEST003')->first();
        $this->assertSame('materi/contoh-materi.pdf', $materi->isi_materi);
    }
}
