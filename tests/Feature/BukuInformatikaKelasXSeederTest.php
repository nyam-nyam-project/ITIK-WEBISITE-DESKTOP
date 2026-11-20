<?php

namespace Tests\Feature;

use App\Models\Kuis;
use App\Models\Materi;
use App\Models\Soal;
use Database\Seeders\BukuInformatikaKelasXSeeder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class BukuInformatikaKelasXSeederTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->createSchema();
    }

    private function createSchema(): void
    {
        Schema::create('tm_user', function (Blueprint $table) {
            $table->string('id_user', 50)->primary();
            $table->string('nomor_induk', 50)->nullable();
            $table->string('nama_lengkap', 100);
            $table->string('role', 20);
            $table->string('status', 20)->default('aktif');
            $table->string('email', 100)->nullable();
            $table->string('password');
            $table->timestamps();
        });

        Schema::create('tm_tahun_ajaran', function (Blueprint $table) {
            $table->string('id_tahun_ajaran', 50)->primary();
            $table->string('nama', 50);
            $table->boolean('is_aktif')->default(true);
            $table->timestamps();
        });

        Schema::create('tm_mapel', function (Blueprint $table) {
            $table->string('id_mapel', 50)->primary();
            $table->string('kode_mapel', 50);
            $table->string('nama_mapel', 150);
            $table->text('deskripsi')->nullable();
            $table->boolean('is_aktif')->default(true);
            $table->timestamps();
        });

        Schema::create('tm_kelas', function (Blueprint $table) {
            $table->string('id_kelas', 50)->primary();
            $table->string('nama_kelas', 50);
            $table->string('tingkat', 20);
            $table->string('id_tahun_ajaran', 50);
            $table->boolean('is_aktif')->default(true);
            $table->timestamps();
        });

        Schema::create('tp_mapel_kelas', function (Blueprint $table) {
            $table->string('id_mapel', 50);
            $table->string('id_kelas', 50);
            $table->primary(['id_mapel', 'id_kelas']);
        });

        Schema::create('tm_materi', function (Blueprint $table) {
            $table->string('id_materi', 50)->primary();
            $table->string('judul', 150);
            $table->string('bab', 50)->nullable();
            $table->string('kelas', 50)->nullable();
            $table->string('id_mapel', 50);
            $table->text('deskripsi')->nullable();
            $table->longText('isi_materi')->nullable();
            $table->string('link_youtube', 255)->nullable();
            $table->string('id_user', 50);
            $table->boolean('is_aktif')->default(true);
            $table->timestamps();
        });

        Schema::create('tm_kuis', function (Blueprint $table) {
            $table->string('id_kuis', 50)->primary();
            $table->string('judul', 150);
            $table->text('deskripsi')->nullable();
            $table->string('kategori', 50);
            $table->string('tingkat_kesulitan', 20)->default('mudah');
            $table->string('id_mapel', 50);
            $table->string('id_tahun_ajaran', 50);
            $table->unsignedInteger('alokasi_waktu')->nullable();
            $table->decimal('kkm', 5, 2)->nullable();
            $table->dateTime('waktu_mulai')->nullable();
            $table->dateTime('waktu_selesai')->nullable();
            $table->string('id_user', 50);
            $table->string('status_publikasi', 20)->default('draft');
            $table->string('id_publisher', 50)->nullable();
            $table->boolean('is_aktif')->default(true);
            $table->string('tampil_jawaban', 20)->default('setelah_submit');
            $table->timestamps();
        });

        Schema::create('tp_materi_kuis', function (Blueprint $table) {
            $table->string('id_kuis', 50);
            $table->string('id_materi', 50);
            $table->primary(['id_kuis', 'id_materi']);
        });

        Schema::create('tp_kuis_kelas', function (Blueprint $table) {
            $table->string('id_kuis', 50);
            $table->string('id_kelas', 50);
            $table->primary(['id_kuis', 'id_kelas']);
        });

        Schema::create('tm_soal', function (Blueprint $table) {
            $table->string('id_soal', 50)->primary();
            $table->string('id_kuis', 50);
            $table->text('pertanyaan');
            $table->string('tipe', 20)->default('pilihan_ganda');
            $table->string('opsi_a', 255)->nullable();
            $table->string('opsi_b', 255)->nullable();
            $table->string('opsi_c', 255)->nullable();
            $table->string('opsi_d', 255)->nullable();
            $table->string('opsi_e', 255)->nullable();
            $table->string('jawaban', 10)->nullable();
            $table->integer('bobot')->default(1);
            $table->string('tingkat_kesulitan', 20)->default('mudah');
            $table->timestamps();
        });
    }

    public function test_seeder_buku_informatika_kelas_x_berhasil_dijalankan(): void
    {
        $this->seed(BukuInformatikaKelasXSeeder::class);

        // 1. Memastikan 4 materi dari Buku Informatika Kelas X tercipta
        $this->assertSame(4, Materi::count());
        $this->assertTrue(Materi::where('bab', 'Bab 2')->exists());
        $this->assertTrue(Materi::where('bab', 'Bab 3')->exists());
        $this->assertTrue(Materi::where('bab', 'Bab 4')->exists());
        $this->assertTrue(Materi::where('bab', 'Bab 5')->exists());

        // 2. Memastikan setiap materi memiliki Pre-test dan Post-test (total 8 kuis)
        $this->assertSame(8, Kuis::count());
        $this->assertSame(4, Kuis::where('kategori', 'pre-test')->count());
        $this->assertSame(4, Kuis::where('kategori', 'post-test')->count());

        // 3. Memastikan kuis terhubung ke pivot materi dan pivot kelas
        $this->assertSame(8, DB::table('tp_materi_kuis')->count());
        $this->assertGreaterThan(0, DB::table('tp_kuis_kelas')->count());

        // 4. Memastikan butir soal tercipta (total 40 soal: 8 kuis x 5 butir soal)
        $this->assertSame(40, Soal::count());

        // 5. Memastikan variasi tingkat kesulitan soal (mudah, sedang, sulit)
        $this->assertGreaterThan(0, Soal::where('tingkat_kesulitan', 'mudah')->count());
        $this->assertGreaterThan(0, Soal::where('tingkat_kesulitan', 'sedang')->count());
        $this->assertGreaterThan(0, Soal::where('tingkat_kesulitan', 'sulit')->count());
    }
}
