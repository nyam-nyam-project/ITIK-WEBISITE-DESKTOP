<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class NavigasiSoalTest extends TestCase
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
            $table->string('kelas', 50)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('password');
            $table->boolean('wajib_ganti_password')->default(false);
            $table->string('dibuat_oleh', 50)->nullable();
            $table->timestamps();
        });

        Schema::create('tm_tahun_ajaran', function (Blueprint $table): void {
            $table->string('id_tahun_ajaran', 50)->primary();
            $table->string('nama', 20)->unique();
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_selesai')->nullable();
            $table->boolean('is_aktif')->default(false);
            $table->timestamps();
        });

        Schema::create('tm_kelas', function (Blueprint $table): void {
            $table->string('id_kelas', 50)->primary();
            $table->string('nama_kelas', 50);
            $table->string('tingkat', 10);
            $table->string('id_tahun_ajaran', 50);
            $table->boolean('is_aktif')->default(true);
            $table->timestamps();
        });

        Schema::create('tp_anggota_kelas', function (Blueprint $table): void {
            $table->bigIncrements('id_anggota_kelas');
            $table->string('id_kelas', 50);
            $table->string('id_user', 50);
            $table->timestamp('created_at')->nullable();
            $table->unique(['id_kelas', 'id_user']);
        });

        Schema::create('tm_mapel', function (Blueprint $table): void {
            $table->string('id_mapel', 50)->primary();
            $table->string('kode_mapel', 20)->unique();
            $table->string('nama_mapel', 100);
            $table->text('deskripsi')->nullable();
            $table->boolean('is_aktif')->default(true);
            $table->timestamps();
        });

        Schema::create('tm_kuis', function (Blueprint $table): void {
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
            $table->string('tampil_jawaban', 30)->default('setelah_submit');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('tp_kuis_kelas', function (Blueprint $table): void {
            $table->string('id_kuis', 50);
            $table->string('id_kelas', 50);
            $table->primary(['id_kuis', 'id_kelas']);
        });

        Schema::create('tm_soal', function (Blueprint $table): void {
            $table->string('id_soal', 50)->primary();
            $table->text('pertanyaan');
            $table->string('tipe', 30)->default('pilihan_ganda');
            $table->string('tingkat_kesulitan', 50)->nullable();
            $table->text('opsi_a')->nullable();
            $table->text('opsi_b')->nullable();
            $table->text('opsi_c')->nullable();
            $table->text('opsi_d')->nullable();
            $table->text('opsi_e')->nullable();
            $table->string('jawaban', 10)->nullable();
            $table->decimal('bobot', 5, 2)->default(50);
            $table->string('id_kuis', 50);
            $table->string('gambar', 255)->nullable();
        });

        Schema::create('tr_sesi_kuis', function (Blueprint $table): void {
            $table->increments('id_sesi');
            $table->string('id_kuis', 50);
            $table->string('id_user', 50);
            $table->dateTime('waktu_mulai');
            $table->dateTime('waktu_selesai')->nullable();
            $table->unsignedTinyInteger('jumlah_pelanggaran')->default(0);
        });

        Schema::create('tr_draft_jawaban', function (Blueprint $table): void {
            $table->increments('id_draft');
            $table->unsignedInteger('id_sesi');
            $table->string('id_soal', 50);
            $table->string('jawaban_dipilih', 10)->nullable();
            $table->text('jawaban_teks')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function test_halaman_kerjakan_tidak_memuat_alert_confirm_atau_prompt_di_script_yang_dimuat(): void
    {
        $siswa = $this->buatSiswaDanKuis();
        $response = $this->actingAs($siswa)->get(route('siswa.kuis.kerjakan', 'Q001'));
        $response->assertOk();

        preg_match_all('/<script[^>]+src="([^"]+)"/i', $response->getContent(), $matches);
        $this->assertNotEmpty($matches[1]);

        foreach ($matches[1] as $src) {
            $path = trim((string) parse_url($src, PHP_URL_PATH), '/');
            $fullPath = public_path($path);
            $this->assertFileExists($fullPath);

            $isiScript = file_get_contents($fullPath);
            $this->assertIsString($isiScript);
            $this->assertDoesNotMatchRegularExpression('/\b(alert|confirm|prompt)\s*\(/', $isiScript);
        }
    }

    public function test_halaman_kerjakan_memuat_tombol_sebelumnya_dan_berikutnya(): void
    {
        $siswa = $this->buatSiswaDanKuis();
        $response = $this->actingAs($siswa)->get(route('siswa.kuis.kerjakan', 'Q001'));

        $response->assertOk();
        $response->assertSee('id="btnSoalSebelumnya"', false);
        $response->assertSee('id="btnSoalBerikutnya"', false);
        $response->assertSee('Sebelumnya');
        $response->assertSee('Berikutnya');
        $response->assertSee('Soal 1 dari 2');
        $response->assertSee('nav-soal-ikon', false);
    }

    public function test_tidak_ada_script_atau_style_inline_di_view_kerjakan(): void
    {
        $isiView = file_get_contents(resource_path('views/siswa/kuis/kerjakan.blade.php'));
        $this->assertIsString($isiView);
        $this->assertDoesNotMatchRegularExpression('/<script(?![^>]*\bsrc=)[^>]*>/i', $isiView);
        $this->assertStringNotContainsString('<style', $isiView);
        $this->assertStringNotContainsString('style=', $isiView);
        $this->assertStringNotContainsString('onclick=', $isiView);
    }

    private function buatSiswaDanKuis(): User
    {
        DB::table('tm_tahun_ajaran')->insert([
            'id_tahun_ajaran' => 'TA001',
            'nama' => '2026/2027',
            'is_aktif' => true,
        ]);
        DB::table('tm_kelas')->insert([
            'id_kelas' => 'K001',
            'nama_kelas' => 'X TKJ 1',
            'tingkat' => 'X',
            'id_tahun_ajaran' => 'TA001',
            'is_aktif' => true,
        ]);
        DB::table('tm_mapel')->insert([
            'id_mapel' => 'MP001',
            'kode_mapel' => 'TIK',
            'nama_mapel' => 'Teknologi Informasi',
            'is_aktif' => true,
        ]);

        $siswa = User::query()->create([
            'id_user' => 'S001',
            'nomor_induk' => '112233',
            'nama_lengkap' => 'Siswa Test',
            'role' => 'siswa',
            'status' => 'aktif',
            'kelas' => 'X TKJ 1',
            'password' => Hash::make('rahasia123'),
            'wajib_ganti_password' => false,
        ]);

        DB::table('tp_anggota_kelas')->insert([
            'id_kelas' => 'K001',
            'id_user' => 'S001',
        ]);

        DB::table('tm_kuis')->insert([
            'id_kuis' => 'Q001',
            'judul' => 'Kuis Navigasi',
            'kategori' => 'kuis harian',
            'tingkat_kesulitan' => 'mudah',
            'id_mapel' => 'MP001',
            'id_tahun_ajaran' => 'TA001',
            'alokasi_waktu' => 30,
            'kkm' => 75,
            'id_user' => 'G001',
            'status_publikasi' => 'terbit',
            'is_aktif' => true,
        ]);

        DB::table('tp_kuis_kelas')->insert([
            'id_kuis' => 'Q001',
            'id_kelas' => 'K001',
        ]);

        DB::table('tm_soal')->insert([
            [
                'id_soal' => 'SOAL1',
                'pertanyaan' => 'Pertanyaan pertama?',
                'tipe' => 'pilihan_ganda',
                'opsi_a' => 'A1',
                'opsi_b' => 'B1',
                'opsi_c' => 'C1',
                'opsi_d' => 'D1',
                'jawaban' => 'A',
                'bobot' => 50,
                'id_kuis' => 'Q001',
            ],
            [
                'id_soal' => 'SOAL2',
                'pertanyaan' => 'Pertanyaan kedua?',
                'tipe' => 'pilihan_ganda',
                'opsi_a' => 'A2',
                'opsi_b' => 'B2',
                'opsi_c' => 'C2',
                'opsi_d' => 'D2',
                'jawaban' => 'B',
                'bobot' => 50,
                'id_kuis' => 'Q001',
            ],
        ]);

        DB::table('tr_sesi_kuis')->insert([
            'id_kuis' => 'Q001',
            'id_user' => 'S001',
            'waktu_mulai' => now()->subMinutes(5),
        ]);

        return $siswa;
    }
}
