<?php

namespace Tests\Feature;

use App\Models\Kuis;
use App\Models\Materi;
use Database\Seeders\KuisLengkapMateriSeeder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class KuisLengkapMateriSeederTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->assertSame('sqlite', DB::connection()->getDriverName());
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());

        Schema::create('tm_user', function (Blueprint $table): void {
            $table->string('id_user', 50)->primary();
            $table->string('nomor_induk', 50)->nullable()->unique();
            $table->string('nama_lengkap', 100);
            $table->string('role', 20);
            $table->string('status', 20)->default('aktif');
            $table->string('email', 100)->nullable();
            $table->string('password');
            $table->timestamps();
        });

        Schema::create('tm_tahun_ajaran', function (Blueprint $table): void {
            $table->string('id_tahun_ajaran', 50)->primary();
            $table->string('nama', 20)->unique();
            $table->boolean('is_aktif')->default(false);
            $table->timestamps();
        });

        Schema::create('tm_kelas', function (Blueprint $table): void {
            $table->string('id_kelas', 50)->primary();
            $table->string('nama_kelas', 50);
            $table->string('tingkat', 20)->nullable();
            $table->string('id_tahun_ajaran', 50);
            $table->boolean('is_aktif')->default(true);
            $table->timestamps();
        });

        Schema::create('tm_mapel', function (Blueprint $table): void {
            $table->string('id_mapel', 50)->primary();
            $table->string('kode_mapel', 20)->unique();
            $table->string('nama_mapel', 100);
            $table->boolean('is_aktif')->default(true);
            $table->timestamps();
        });

        Schema::create('tm_materi', function (Blueprint $table): void {
            $table->string('id_materi', 50)->primary();
            $table->string('judul', 150);
            $table->string('bab', 20)->nullable();
            $table->string('id_mapel', 50);
            $table->text('deskripsi')->nullable();
            $table->text('isi_materi')->nullable();
            $table->string('link_youtube', 255)->nullable();
            $table->string('id_user', 50)->nullable();
            $table->boolean('is_aktif')->default(true);
            $table->timestamps();
        });

        Schema::create('tp_materi_kelas', function (Blueprint $table): void {
            $table->string('id_materi', 50);
            $table->string('id_kelas', 50);
            $table->primary(['id_materi', 'id_kelas']);
        });

        Schema::create('tm_kuis', function (Blueprint $table): void {
            $table->string('id_kuis', 50)->primary();
            $table->string('judul', 150);
            $table->text('deskripsi')->nullable();
            $table->string('kategori', 30);
            $table->string('tingkat_kesulitan', 30)->nullable();
            $table->string('id_mapel', 50);
            $table->string('id_tahun_ajaran', 50);
            $table->integer('alokasi_waktu')->default(15);
            $table->decimal('kkm', 5, 2)->default(75);
            $table->dateTime('waktu_mulai')->nullable();
            $table->dateTime('waktu_selesai')->nullable();
            $table->string('id_user', 50)->nullable();
            $table->string('status_publikasi', 20)->default('draft');
            $table->string('tampil_jawaban', 30)->default('setelah_submit');
            $table->string('id_publisher', 50)->nullable();
            $table->boolean('is_aktif')->default(true);
            $table->string('diarsipkan_bersama', 50)->nullable();
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('tp_materi_kuis', function (Blueprint $table): void {
            $table->increments('id_materi_kuis');
            $table->string('id_materi', 50);
            $table->string('id_kuis', 50);
        });

        Schema::create('tp_kuis_kelas', function (Blueprint $table): void {
            $table->string('id_kuis', 50);
            $table->string('id_kelas', 50);
            $table->primary(['id_kuis', 'id_kelas']);
        });

        Schema::create('tm_soal', function (Blueprint $table): void {
            $table->string('id_soal', 50)->primary();
            $table->string('id_kuis', 50);
            $table->string('tipe', 30)->default('pilihan_ganda');
            $table->text('pertanyaan');
            $table->text('opsi_a')->nullable();
            $table->text('opsi_b')->nullable();
            $table->text('opsi_c')->nullable();
            $table->text('opsi_d')->nullable();
            $table->text('opsi_e')->nullable();
            $table->string('jawaban', 10)->nullable();
            $table->integer('bobot')->default(25);
            $table->string('tingkat_kesulitan', 30)->nullable();
            $table->string('gambar', 255)->nullable();
        });

        DB::table('tm_user')->insert([
            'id_user' => 'U001',
            'nomor_induk' => '19800101',
            'nama_lengkap' => 'Guru Uji',
            'role' => 'guru',
            'status' => 'aktif',
            'password' => Hash::make('password'),
        ]);

        DB::table('tm_tahun_ajaran')->insert([
            'id_tahun_ajaran' => 'TA001',
            'nama' => '2026/2027',
            'is_aktif' => true,
        ]);

        DB::table('tm_kelas')->insert([
            'id_kelas' => 'KLS001',
            'nama_kelas' => 'X TKJ 1',
            'tingkat' => 'X',
            'id_tahun_ajaran' => 'TA001',
            'is_aktif' => true,
        ]);

        DB::table('tm_mapel')->insert([
            'id_mapel' => 'MP003',
            'kode_mapel' => 'MTK',
            'nama_mapel' => 'Matematika',
            'is_aktif' => true,
        ]);

        DB::table('tm_materi')->insert([
            'id_materi' => 'M004',
            'judul' => 'Eksponen',
            'bab' => 'Bab 1',
            'id_mapel' => 'MP003',
            'id_user' => 'U001',
            'is_aktif' => true,
        ]);

        DB::table('tp_materi_kelas')->insert([
            'id_materi' => 'M004',
            'id_kelas' => 'KLS001',
        ]);
    }

    public function test_seeder_melengkapi_empat_kategori_kuis_dan_soal_serta_idempoten(): void
    {
        // Buat 1 pre-test yang sudah ada terlebih dahulu
        DB::table('tm_kuis')->insert([
            'id_kuis' => 'EXISTING-PRE',
            'judul' => 'Pre-test Eksisting',
            'kategori' => 'pre-test',
            'id_mapel' => 'MP003',
            'id_tahun_ajaran' => 'TA001',
            'id_user' => 'U001',
            'status_publikasi' => 'terbit',
            'is_aktif' => true,
        ]);
        DB::table('tp_materi_kuis')->insert([
            'id_materi' => 'M004',
            'id_kuis' => 'EXISTING-PRE',
        ]);

        (new KuisLengkapMateriSeeder)->run();

        // Hanya 3 kategori baru yang dibuat (post-test, kuis harian, ulangan); pre-test eksisting tetap terjaga
        $this->assertSame(4, Kuis::count());
        $this->assertDatabaseHas('tm_kuis', ['id_kuis' => 'EXISTING-PRE']);

        $materi = Materi::findOrFail('M004');
        $kategoriTautan = $materi->kuis()->pluck('kategori')->all();
        $this->assertEqualsCanonicalizing(
            ['pre-test', 'post-test', 'kuis harian', 'ulangan'],
            $kategoriTautan
        );

        // 3 kuis baru × 4 soal = 12 soal, masing-masing tertaut ke kelas KLS001
        $this->assertSame(12, DB::table('tm_soal')->count());
        $this->assertSame(3, DB::table('tp_kuis_kelas')->where('id_kelas', 'KLS001')->count());

        // Ulangan tetap terbit setelah sinkronisasiStatus()
        Kuis::sinkronisasiStatus();
        $this->assertDatabaseHas('tm_kuis', [
            'kategori' => 'ulangan',
            'status_publikasi' => 'terbit',
        ]);

        // Jalankan ulang seeder harus idempoten (jumlah tetap)
        (new KuisLengkapMateriSeeder)->run();
        $this->assertSame(4, Kuis::count());
        $this->assertSame(12, DB::table('tm_soal')->count());
    }
}
