<?php

namespace Tests\Feature;

use App\Models\Kuis;
use App\Models\Mapel;
use App\Models\TahunAjaran;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class KuisAutoPublishTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->createSchema();

        User::create([
            'id_user' => 'U001',
            'nomor_induk' => '198001',
            'nama_lengkap' => 'Guru Penguji',
            'email' => 'guru@sekolah.sch.id',
            'password' => bcrypt('password123'),
            'role' => 'guru',
            'status' => 'aktif',
            'wajib_ganti_password' => false,
        ]);

        TahunAjaran::create([
            'id_tahun_ajaran' => 'TA01',
            'nama' => '2026/2027 Ganjil',
            'is_aktif' => true,
        ]);

        Mapel::create([
            'id_mapel' => 'MP01',
            'nama_mapel' => 'Dasar TIK',
            'is_aktif' => true,
        ]);
    }

    private function createSchema(): void
    {
        Schema::dropIfExists('kuis');
        Schema::dropIfExists('mapel');
        Schema::dropIfExists('tahun_ajaran');
        Schema::dropIfExists('user');

        Schema::create('user', function (Blueprint $table) {
            $table->string('id_user', 50)->primary();
            $table->string('nomor_induk', 50)->nullable();
            $table->string('nama_lengkap', 100);
            $table->string('role', 20);
            $table->string('status', 20)->default('aktif');
            $table->string('email', 100)->nullable();
            $table->string('password', 255);
            $table->boolean('wajib_ganti_password')->default(false);
            $table->timestamps();
        });

        Schema::create('tahun_ajaran', function (Blueprint $table) {
            $table->string('id_tahun_ajaran', 50)->primary();
            $table->string('nama', 100);
            $table->boolean('is_aktif')->default(true);
            $table->string('tampil_jawaban', 20)->default('setelah_submit');
            $table->timestamps();
        });

        Schema::create('mapel', function (Blueprint $table) {
            $table->string('id_mapel', 50)->primary();
            $table->string('nama_mapel', 150);
            $table->boolean('is_aktif')->default(true);
            $table->string('tampil_jawaban', 20)->default('setelah_submit');
            $table->timestamps();
        });

        Schema::create('kuis', function (Blueprint $table) {
            $table->string('id_kuis', 50)->primary();
            $table->string('judul', 150);
            $table->text('deskripsi')->nullable();
            $table->string('kategori', 50);
            $table->string('tingkat_kesulitan', 20)->default('sedang');
            $table->string('id_mapel', 50)->nullable();
            $table->string('id_tahun_ajaran', 50)->nullable();
            $table->unsignedInteger('alokasi_waktu')->nullable();
            $table->decimal('kkm', 5, 2)->nullable();
            $table->dateTime('waktu_mulai')->nullable();
            $table->dateTime('waktu_selesai')->nullable();
            $table->string('id_user', 50)->nullable();
            $table->string('status_publikasi', 20)->default('draft');
            $table->string('id_publisher', 50)->nullable();
            $table->boolean('is_aktif')->default(true);
            $table->string('tampil_jawaban', 20)->default('setelah_submit');
            $table->timestamp('created_at')->nullable()->useCurrent();
        });
    }

    public function test_kuis_otomatis_terbit_saat_waktu_mulai_tiba(): void
    {
        $kuis = Kuis::create([
            'id_kuis' => 'KTEST1',
            'judul' => 'Ulangan Auto Terbit',
            'kategori' => 'ulangan',
            'tingkat_kesulitan' => 'sedang',
            'id_mapel' => 'MP01',
            'id_tahun_ajaran' => 'TA01',
            'id_user' => 'U001',
            'alokasi_waktu' => 60,
            'kkm' => 75,
            'waktu_mulai' => Carbon::now()->subMinutes(5),
            'waktu_selesai' => Carbon::now()->addHour(),
            'status_publikasi' => 'draft',
            'is_aktif' => true,
        ]);

        $this->assertSame('draft', $kuis->status_publikasi);

        $this->artisan('kuis:auto-publish')
            ->assertSuccessful();

        $kuis->refresh();
        $this->assertSame('terbit', $kuis->status_publikasi);
    }

    public function test_kuis_otomatis_kembali_draft_saat_waktu_selesai_lewat(): void
    {
        $kuis = Kuis::create([
            'id_kuis' => 'KTEST2',
            'judul' => 'Ulangan Auto Selesai',
            'kategori' => 'ulangan',
            'tingkat_kesulitan' => 'sedang',
            'id_mapel' => 'MP01',
            'id_tahun_ajaran' => 'TA01',
            'id_user' => 'U001',
            'alokasi_waktu' => 60,
            'kkm' => 75,
            'waktu_mulai' => Carbon::now()->subHours(2),
            'waktu_selesai' => Carbon::now()->subMinutes(10),
            'status_publikasi' => 'terbit',
            'is_aktif' => true,
        ]);

        $this->assertSame('terbit', $kuis->status_publikasi);

        $this->artisan('kuis:auto-publish')
            ->assertSuccessful();

        $kuis->refresh();
        $this->assertSame('draft', $kuis->status_publikasi);
    }
}
