<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\GrafikGuruService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class GrafikGuruTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->assertTrue($this->app->environment('testing'));
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        $this->assertSame('sqlite', DB::connection()->getDriverName());
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());

        Schema::create('tm_tahun_ajaran', function (Blueprint $table): void {
            $table->string('id_tahun_ajaran', 50)->primary();
            $table->string('nama', 20);
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

        Schema::create('tm_user', function (Blueprint $table): void {
            $table->string('id_user', 50)->primary();
            $table->string('nomor_induk', 50)->unique()->nullable();
            $table->string('nama_lengkap', 100);
            $table->enum('role', ['admin', 'guru', 'siswa']);
            $table->string('status', 20)->default('aktif');
            $table->string('kelas', 50)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('password', 255);
            $table->boolean('wajib_ganti_password')->default(false);
            $table->string('dibuat_oleh', 50)->nullable();
            $table->timestamps();
        });

        Schema::create('tm_mapel', function (Blueprint $table): void {
            $table->string('id_mapel', 50)->primary();
            $table->string('kode_mapel', 20)->unique();
            $table->string('nama_mapel', 100);
            $table->text('deskripsi')->nullable();
            $table->boolean('is_aktif')->default(true);
            $table->timestamps();
        });

        Schema::create('tp_mapel_kelas', function (Blueprint $table): void {
            $table->bigIncrements('id_mapel_kelas');
            $table->string('id_mapel', 50);
            $table->string('id_kelas', 50);
            $table->timestamp('created_at')->useCurrent();
            $table->unique(['id_mapel', 'id_kelas']);
        });

        Schema::create('tr_pengajaran', function (Blueprint $table): void {
            $table->increments('id_pengajaran');
            $table->string('id_user', 50);
            $table->string('id_kelas', 50);
            $table->string('id_mapel', 50);
            $table->timestamp('created_at')->nullable()->useCurrent();
        });

        Schema::create('tp_anggota_kelas', function (Blueprint $table): void {
            $table->bigIncrements('id_anggota_kelas');
            $table->string('id_kelas', 50);
            $table->string('id_user', 50);
            $table->timestamp('created_at')->useCurrent();
            $table->unique(['id_kelas', 'id_user']);
        });

        Schema::create('tm_kuis', function (Blueprint $table): void {
            $table->string('id_kuis', 50)->primary();
            $table->string('judul', 150);
            $table->text('deskripsi')->nullable();
            $table->string('kategori', 50);
            $table->string('tingkat_kesulitan', 20);
            $table->string('id_mapel', 50);
            $table->string('id_tahun_ajaran', 50);
            $table->unsignedInteger('alokasi_waktu')->nullable();
            $table->decimal('kkm', 5, 2)->nullable();
            $table->dateTime('waktu_mulai')->nullable();
            $table->dateTime('waktu_selesai')->nullable();
            $table->string('id_publisher', 50)->nullable();
            $table->string('id_user', 50);
            $table->string('status_publikasi', 20)->default('draft');
            $table->boolean('is_aktif')->default(true);
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('tr_mengerjakan', function (Blueprint $table): void {
            $table->increments('id_mengerjakan');
            $table->string('id_kuis', 50);
            $table->string('id_user', 50);
            $table->decimal('nilai', 5, 2)->nullable();
            $table->date('tanggal')->nullable();
            $table->string('status_pengerjaan', 20);
            $table->string('status_validasi', 50)->default('belum divalidasi');
            $table->unsignedInteger('percobaan_ke')->default(1);
            $table->string('id_validator', 50)->nullable();
            $table->unique(['id_kuis', 'id_user', 'percobaan_ke']);
        });

        Schema::create('tm_materi', function (Blueprint $table): void {
            $table->string('id_materi', 50)->primary();
            $table->string('judul', 150);
            $table->string('bab', 50)->nullable();
            $table->string('kelas', 50)->nullable();
            $table->string('id_mapel', 50);
            $table->text('deskripsi')->nullable();
            $table->string('isi_materi', 255)->nullable();
            $table->string('link_youtube', 255)->nullable();
            $table->string('id_user', 50);
            $table->timestamps();
        });

        Schema::create('tr_progress', function (Blueprint $table): void {
            $table->string('id_progress', 50)->primary();
            $table->string('status', 50)->nullable();
            $table->date('tanggal')->nullable();
            $table->string('id_materi', 50);
            $table->string('id_user', 50);
        });

        Schema::create('tp_materi_kuis', function (Blueprint $table): void {
            $table->increments('id_materi_kuis');
            $table->string('id_materi', 50);
            $table->string('id_kuis', 50);
        });
    }

    public function test_guru_without_active_assignments_receives_empty_graph_data(): void
    {
        $data = app(GrafikGuruService::class)->ambilUntuk($this->guru('G001'));

        $this->assertSame([], $data['nilai_per_tingkat']['labels']);
        $this->assertSame([], $data['ketuntasan_per_kuis']['labels']);
        $this->assertSame([], $data['progres_materi']['labels']);
        $this->assertSame([], $data['pre_post_test']);
    }

    public function test_guru_cannot_see_other_classes_students_quizzes_or_scores(): void
    {
        $this->seedYearAndClasses();
        $this->createAssignment('G001', 'K001', 'MP001');
        $this->createAssignment('G002', 'K002', 'MP002');
        $this->createAssignment('G001', 'K003', 'MP003');
        $this->createStudent('S001', 'Siswa Guru Satu', 'K001');
        $this->createStudent('S002', 'Siswa Guru Dua', 'K002');
        $this->createStudent('S003', 'Siswa Tahun Lama', 'K003');
        $this->createQuiz('Q001', 'Kuis Guru Satu', 'MP001');
        $this->createQuiz('Q002', 'Kuis Guru Dua', 'MP002');
        $this->createQuiz('Q003', 'Kuis Tahun Lama', 'MP003', 'mudah', 70, 'kuis harian', 'TA000');
        $this->createAttempt('Q001', 'S001', 80);
        $this->createAttempt('Q002', 'S002', 100);
        $this->createAttempt('Q003', 'S003', 100);

        $data = app(GrafikGuruService::class)->ambilUntuk($this->guru('G001'));

        $this->assertSame(['Kuis Guru Satu'], $data['ketuntasan_per_kuis']['labels']);
        $this->assertSame(80.0, $data['nilai_per_tingkat']['rata_rata'][0]);
        $this->assertNotContains('Kuis Guru Dua', $data['ketuntasan_per_kuis']['labels']);
        $this->assertNotContains('Kuis Tahun Lama', $data['ketuntasan_per_kuis']['labels']);
        $this->assertNotContains('Siswa Guru Dua', $data['nilai_per_tingkat']['nama_siswa_tertinggi']);
        $this->assertNotContains('Siswa Tahun Lama', $data['nilai_per_tingkat']['nama_siswa_tertinggi']);
    }

    public function test_difficulty_summary_uses_best_final_numeric_score_and_names_extremes(): void
    {
        $this->seedYearAndClasses();
        $this->createAssignment('G001', 'K001', 'MP001');
        $this->createStudent('S001', 'Alya', 'K001');
        $this->createStudent('S002', 'Budi', 'K001');
        $this->createQuiz('Q001', 'Kuis Mudah', 'MP001');
        $this->createAttempt('Q001', 'S001', 70, 'final', 1);
        $this->createAttempt('Q001', 'S001', 90, 'final', 2);
        $this->createAttempt('Q001', 'S001', null, 'final', 3);
        $this->createAttempt('Q001', 'S002', 60, 'final', 1);
        $this->createAttempt('Q001', 'S002', 100, 'dibatalkan', 2);

        $data = app(GrafikGuruService::class)->ambilUntuk($this->guru('G001'));
        $mudah = $data['nilai_per_tingkat'];

        $this->assertSame(75.0, $mudah['rata_rata'][0]);
        $this->assertSame(90.0, $mudah['nilai_tertinggi'][0]);
        $this->assertSame('Alya', $mudah['nama_siswa_tertinggi'][0]);
        $this->assertSame(60.0, $mudah['nilai_terendah'][0]);
        $this->assertSame('Budi', $mudah['nama_siswa_terendah'][0]);
    }

    public function test_quiz_completion_uses_each_quiz_kkm_and_best_score(): void
    {
        $this->seedYearAndClasses();
        $this->createAssignment('G001', 'K001', 'MP001');
        $this->createStudent('S001', 'Alya', 'K001');
        $this->createStudent('S002', 'Budi', 'K001');
        $this->createStudent('S003', 'Cici', 'K001');
        $this->createQuiz('Q001', 'KKM Tujuh Puluh', 'MP001', 'mudah', 70);
        $this->createQuiz('Q002', 'KKM Delapan Puluh', 'MP001', 'sedang', 80);
        $this->createAttempt('Q001', 'S001', 60, 'final', 1);
        $this->createAttempt('Q001', 'S001', 75, 'final', 2);
        $this->createAttempt('Q001', 'S002', 40);
        $this->createAttempt('Q002', 'S001', 75);

        $data = app(GrafikGuruService::class)->ambilUntuk($this->guru('G001'));
        $completion = $data['ketuntasan_per_kuis'];

        $this->assertSame(['KKM Tujuh Puluh', 'KKM Delapan Puluh'], $completion['labels']);
        $this->assertSame([1, 0], $completion['lulus']);
        $this->assertSame([1, 1], $completion['belum_lulus']);
        $this->assertSame([1, 2], $completion['belum_mengerjakan']);
    }

    public function test_material_progress_and_linked_pre_post_scores_use_the_assigned_roster(): void
    {
        $this->seedYearAndClasses();
        $this->createAssignment('G001', 'K001', 'MP001');
        $this->createStudent('S001', 'Alya', 'K001');
        $this->createStudent('S002', 'Budi', 'K001');
        $this->createStudent('S003', 'Di luar kelas', 'K002');
        $this->createMateri('M001', 'Dasar Jaringan', 'MP001', 'X');
        $this->createMateri('M002', 'Materi Guru Lain', 'MP002', 'X TKJ 1');
        DB::table('tr_progress')->insert([
            'id_progress' => 'P001',
            'status' => 'selesai',
            'id_materi' => 'M001',
            'id_user' => 'S001',
        ]);
        $this->createQuiz('QPRE', 'Pre-test Jaringan', 'MP001', 'mudah', 70, 'pre-test');
        $this->createQuiz('QPOST', 'Post-test Jaringan', 'MP001', 'mudah', 70, 'post-test');
        DB::table('tp_materi_kuis')->insert([
            ['id_materi' => 'M001', 'id_kuis' => 'QPRE'],
            ['id_materi' => 'M001', 'id_kuis' => 'QPOST'],
        ]);
        $this->createAttempt('QPRE', 'S001', 60);
        $this->createAttempt('QPOST', 'S001', 90);
        $this->createAttempt('QPOST', 'S003', 100);

        $data = app(GrafikGuruService::class)->ambilUntuk($this->guru('G001'));

        $this->assertSame(['Dasar Jaringan'], $data['progres_materi']['labels']);
        $this->assertSame([50.0], $data['progres_materi']['persentase']);
        $this->assertSame(1, count($data['pre_post_test']));
        $this->assertSame('Alya', $data['pre_post_test'][0]['nama_siswa']);
        $this->assertSame(60.0, $data['pre_post_test'][0]['nilai_pre']);
        $this->assertSame(90.0, $data['pre_post_test'][0]['nilai_post']);
    }

    public function test_dashboard_statistics_are_scoped_to_the_guru_assignments(): void
    {
        $this->seedYearAndClasses();
        $this->createAssignment('G001', 'K001', 'MP001');
        $this->createAssignment('G002', 'K002', 'MP002');
        $this->createStudent('S001', 'Siswa Guru Satu', 'K001');
        $this->createStudent('S002', 'Siswa Guru Dua', 'K002');
        $this->createMateri('M001', 'Materi Guru Satu', 'MP001', 'X TKJ 1');
        $this->createMateri('M003', 'Materi Kelas Lain', 'MP001', 'X TKJ 2');
        $this->createMateri('M002', 'Materi Guru Dua', 'MP002', 'X TKJ 2');
        $this->createQuiz('Q001', 'Kuis Guru Satu', 'MP001');
        $this->createQuiz('Q002', 'Kuis Guru Dua', 'MP002');
        $this->createAttempt('Q001', 'S001', 80);
        $this->createAttempt('Q002', 'S002', 100);

        $this->actingAs($this->persistedGuru('G001'))
            ->get(route('dashboard'))
            ->assertOk()
            ->assertViewHas('jumlahSiswa', 1)
            ->assertViewHas('jumlahMateri', 1)
            ->assertViewHas('jumlahKuis', 1)
            ->assertViewHas('rataNilai', 80.0)
            ->assertViewHas('kuisTerbaru', fn ($kuis) => $kuis->pluck('judul')->all() === ['Kuis Guru Satu'])
            ->assertViewHas('materiTerbaru', fn ($materi) => $materi->pluck('judul')->all() === ['Materi Guru Satu'])
            ->assertViewHas('grafik', fn (array $grafik): bool => array_keys($grafik) === [
                'nilai_per_tingkat',
                'ketuntasan_per_kuis',
                'progres_materi',
                'pre_post_test',
            ])
            ->assertSee('id="grafik-nilai-per-tingkat"', false)
            ->assertSee('id="grafik-ketuntasan-per-kuis"', false)
            ->assertSee('id="grafik-progres-materi"', false)
            ->assertSee('id="grafik-pre-post-test"', false)
            ->assertSee('js/vendor/chart.umd.min.js')
            ->assertSee('js/guru/dashboard/grafik.js')
            ->assertSee('css/guru/dashboard.css');
    }

    private function seedYearAndClasses(): void
    {
        DB::table('tm_tahun_ajaran')->insert([
            ['id_tahun_ajaran' => 'TA001', 'nama' => '2026/2027', 'is_aktif' => true],
            ['id_tahun_ajaran' => 'TA000', 'nama' => '2025/2026', 'is_aktif' => false],
        ]);

        DB::table('tm_kelas')->insert([
            ['id_kelas' => 'K001', 'nama_kelas' => 'X TKJ 1', 'tingkat' => 'X', 'id_tahun_ajaran' => 'TA001', 'is_aktif' => true],
            ['id_kelas' => 'K002', 'nama_kelas' => 'X TKJ 2', 'tingkat' => 'X', 'id_tahun_ajaran' => 'TA001', 'is_aktif' => true],
            ['id_kelas' => 'K003', 'nama_kelas' => 'XI TKJ 1', 'tingkat' => 'XI', 'id_tahun_ajaran' => 'TA000', 'is_aktif' => true],
        ]);
    }

    private function createAssignment(string $guruId, string $kelasId, string $mapelId): void
    {
        DB::table('tm_mapel')->insertOrIgnore([
            'id_mapel' => $mapelId,
            'kode_mapel' => $mapelId,
            'nama_mapel' => $mapelId,
        ]);
        DB::table('tp_mapel_kelas')->insertOrIgnore([
            'id_mapel' => $mapelId,
            'id_kelas' => $kelasId,
        ]);
        DB::table('tr_pengajaran')->insert([
            'id_user' => $guruId,
            'id_kelas' => $kelasId,
            'id_mapel' => $mapelId,
        ]);
    }

    private function createStudent(string $id, string $name, string $kelasId): void
    {
        DB::table('tm_user')->insert([
            'id_user' => $id,
            'nama_lengkap' => $name,
            'role' => 'siswa',
            'password' => 'test-password',
        ]);
        DB::table('tp_anggota_kelas')->insert([
            'id_kelas' => $kelasId,
            'id_user' => $id,
        ]);
    }

    private function createQuiz(
        string $id,
        string $title,
        string $mapelId,
        string $difficulty = 'mudah',
        ?float $kkm = 70,
        string $category = 'kuis harian',
        string $yearId = 'TA001'
    ): void {
        DB::table('tm_kuis')->insert([
            'id_kuis' => $id,
            'judul' => $title,
            'kategori' => $category,
            'tingkat_kesulitan' => $difficulty,
            'id_mapel' => $mapelId,
            'id_tahun_ajaran' => $yearId,
            'kkm' => $kkm,
            'id_user' => 'G001',
            'is_aktif' => true,
            'status_publikasi' => 'terbit',
            'created_at' => now(),
        ]);
    }

    private function createAttempt(
        string $quizId,
        string $studentId,
        ?float $score,
        string $status = 'final',
        int $attempt = 1
    ): void {
        DB::table('tr_mengerjakan')->insert([
            'id_kuis' => $quizId,
            'id_user' => $studentId,
            'nilai' => $score,
            'status_pengerjaan' => $status,
            'percobaan_ke' => $attempt,
        ]);
    }

    private function createMateri(string $id, string $title, string $mapelId, ?string $kelas): void
    {
        DB::table('tm_materi')->insert([
            'id_materi' => $id,
            'judul' => $title,
            'bab' => null,
            'id_mapel' => $mapelId,
            'kelas' => $kelas,
            'id_user' => 'G001',
            'created_at' => now(),
        ]);
    }

    private function guru(string $id): User
    {
        $guru = new User;
        $guru->id_user = $id;
        $guru->role = 'guru';
        $guru->status = 'aktif';

        return $guru;
    }

    private function persistedGuru(string $id): User
    {
        DB::table('tm_user')->insert([
            'id_user' => $id,
            'nama_lengkap' => 'Guru '.$id,
            'role' => 'guru',
            'status' => 'aktif',
            'password' => 'test-password',
        ]);

        return User::query()->findOrFail($id);
    }
}
