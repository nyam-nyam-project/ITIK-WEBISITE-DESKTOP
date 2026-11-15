<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\DashboardKelasService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DashboardKelasServiceTest extends TestCase
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
            $table->string('password');
            $table->timestamps();
        });

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

        Schema::create('tm_mapel', function (Blueprint $table): void {
            $table->string('id_mapel', 50)->primary();
            $table->string('kode_mapel', 20)->unique();
            $table->string('nama_mapel', 100);
            $table->text('deskripsi')->nullable();
            $table->boolean('is_aktif')->default(true);
            $table->timestamps();
        });

        Schema::create('tr_pengajaran', function (Blueprint $table): void {
            $table->increments('id_pengajaran');
            $table->string('id_user', 50);
            $table->string('id_kelas', 50);
            $table->string('id_mapel', 50);
            $table->timestamp('created_at')->nullable();
            $table->unique(['id_user', 'id_kelas', 'id_mapel']);
        });

        Schema::create('tp_anggota_kelas', function (Blueprint $table): void {
            $table->bigIncrements('id_anggota_kelas');
            $table->string('id_kelas', 50);
            $table->string('id_user', 50);
            $table->timestamp('created_at')->nullable();
            $table->unique(['id_kelas', 'id_user']);
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
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('tr_mengerjakan', function (Blueprint $table): void {
            $table->increments('id_mengerjakan');
            $table->string('id_kuis', 50);
            $table->string('id_user', 50);
            $table->decimal('nilai', 5, 2)->nullable();
            $table->date('tanggal')->nullable();
            $table->integer('percobaan_ke')->default(1);
            $table->string('status_pengerjaan', 20)->default('final');
            $table->string('status_validasi', 50)->default('belum divalidasi');
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
            $table->unique(['id_materi', 'id_user']);
        });

        Schema::create('tr_remedial_siswa', function (Blueprint $table): void {
            $table->increments('id_remedial_siswa');
            $table->string('id_kuis_remedial', 50);
            $table->unsignedInteger('id_mengerjakan_asal');
            $table->string('id_guru', 50);
            $table->date('tanggal_ditugaskan');
            $table->string('status', 20)->default('ditugaskan');
            $table->unique(['id_kuis_remedial', 'id_mengerjakan_asal']);
        });
    }

    public function test_teacher_only_receives_assigned_classes_and_subjects_in_active_year(): void
    {
        $this->seedBase();
        $this->assign('G001', 'K001', 'MP001');
        $this->assign('G001', 'K001', 'MP002');
        $this->assign('G002', 'K002', 'MP001');
        $this->assign('G001', 'K003', 'MP001');

        $classes = app(DashboardKelasService::class)->kelasUntukGuru($this->guru('G001'));

        $this->assertSame(
            [
                ['id_kelas' => 'K001', 'nama_kelas' => 'X TKJ 1', 'tingkat' => 'X', 'id_mapel' => 'MP002', 'nama_mapel' => 'Matematika'],
                ['id_kelas' => 'K001', 'nama_kelas' => 'X TKJ 1', 'tingkat' => 'X', 'id_mapel' => 'MP001', 'nama_mapel' => 'TIK'],
            ],
            $classes->map(fn (object $class): array => (array) $class)->all()
        );

        $service = app(DashboardKelasService::class);
        $guru = $this->guru('G001');
        $this->assertSame(
            ['jumlah_siswa' => 0, 'rata_rata_nilai' => null, 'jumlah_perlu_dibantu' => 0],
            $service->ringkasanKelas($guru, 'K002', 'MP001')
        );
        $this->assertCount(0, $service->daftarSiswa($guru, 'K002', 'MP001'));
    }

    public function test_summary_ignores_null_and_cancelled_scores_and_help_status_is_first(): void
    {
        $this->seedBase();
        $this->assign('G001', 'K001', 'MP001');
        $this->addStudent('S001', 'Alya', 'K001');
        $this->addStudent('S002', 'Budi', 'K001');
        $this->addStudent('S003', 'Cici', 'K001');
        $this->addStudent('S004', 'Dina Nonaktif', 'K001', 'nonaktif');
        $this->addQuiz('Q001', 'Kuis Harian', 'MP001', 70, 'kuis harian');
        $this->addQuiz('Q002', 'Post Test', 'MP001', 80, 'post-test');
        $this->addAttempt('Q001', 'S001', 80, 'final', 1);
        $this->addAttempt('Q001', 'S001', null, 'final', 2);
        $this->addAttempt('Q002', 'S001', 100, 'dibatalkan');
        $this->addAttempt('Q001', 'S002', 60, 'final');
        $this->addAttempt('Q002', 'S002', null, 'final');
        $this->addAttempt('Q001', 'S003', 100, 'dibatalkan');
        $this->addMaterial('M001', 'Dasar Jaringan', 'MP001', 'X TKJ 1');
        $this->addMaterial('M002', 'Perangkat Jaringan', 'MP001', 'X');
        $this->addProgress('P001', 'M001', 'S001', 'sedang belajar');
        $this->addProgress('P002', 'M001', 'S002', 'selesai');

        $service = app(DashboardKelasService::class);
        $guru = $this->guru('G001');
        $summary = $service->ringkasanKelas($guru, 'K001', 'MP001');
        $students = $service->daftarSiswa($guru, 'K001', 'MP001');

        $this->assertSame(3, $summary['jumlah_siswa']);
        $this->assertSame(70.0, $summary['rata_rata_nilai']);
        $this->assertSame(1, $summary['jumlah_perlu_dibantu']);
        $this->assertSame(
            [
                ['id_user' => 'S002', 'nama_lengkap' => 'Budi', 'nomor_induk' => null, 'persentase_materi_dibaca' => 50.0, 'jumlah_kuis_dikerjakan' => 2, 'jumlah_kuis_total' => 2, 'rata_rata_nilai' => 60.0, 'status' => 'Perlu dibantu'],
                ['id_user' => 'S003', 'nama_lengkap' => 'Cici', 'nomor_induk' => null, 'persentase_materi_dibaca' => 0.0, 'jumlah_kuis_dikerjakan' => 0, 'jumlah_kuis_total' => 2, 'rata_rata_nilai' => null, 'status' => 'Belum mulai'],
                ['id_user' => 'S001', 'nama_lengkap' => 'Alya', 'nomor_induk' => null, 'persentase_materi_dibaca' => 50.0, 'jumlah_kuis_dikerjakan' => 1, 'jumlah_kuis_total' => 2, 'rata_rata_nilai' => 80.0, 'status' => 'Lancar'],
            ],
            $students->map(fn (object $student): array => (array) $student)->all()
        );
        $this->assertNotContains('Dina Nonaktif', $students->pluck('nama_lengkap')->all());
    }

    public function test_category_completion_uses_best_score_and_excludes_unattempted_quizzes(): void
    {
        $this->seedBase();
        $this->assign('G001', 'K001', 'MP001');
        $this->addStudent('S001', 'Alya', 'K001');
        $this->addStudent('S002', 'Budi', 'K001');
        $this->addQuiz('Q001', 'Pre Test', 'MP001', 70, 'pre-test');
        $this->addQuiz('Q002', 'Post Test', 'MP001', 80, 'post-test');
        $this->addQuiz('Q003', 'Kuis Harian', 'MP001', 70, 'kuis harian');
        $this->addQuiz('Q004', 'Ulangan', 'MP001', 75, 'ulangan');
        $this->addAttempt('Q001', 'S001', 60, 'final', 1);
        $this->addAttempt('Q001', 'S001', 75, 'final', 2);
        $this->addAttempt('Q001', 'S002', 65, 'final');
        $this->addAttempt('Q002', 'S001', 90, 'final');
        $this->addAttempt('Q002', 'S002', 70, 'final');

        $categories = app(DashboardKelasService::class)
            ->kelulusanPerKategori($this->guru('G001'), 'K001', 'MP001');

        $this->assertSame(
            [
                ['kategori' => 'post-test', 'persentase_lulus' => 50.0, 'jumlah_lulus' => 1, 'jumlah_dinilai' => 2],
                ['kategori' => 'pre-test', 'persentase_lulus' => 50.0, 'jumlah_lulus' => 1, 'jumlah_dinilai' => 2],
                ['kategori' => 'kuis harian', 'persentase_lulus' => 0.0, 'jumlah_lulus' => 0, 'jumlah_dinilai' => 0],
                ['kategori' => 'ulangan', 'persentase_lulus' => 0.0, 'jumlah_lulus' => 0, 'jumlah_dinilai' => 0],
            ],
            $categories->map(fn (object $category): array => (array) $category)->all()
        );
    }

    public function test_remedial_counts_unique_students_per_status_only_in_the_selected_class(): void
    {
        $this->seedBase();
        $this->assign('G001', 'K001', 'MP001');
        $this->assign('G001', 'K002', 'MP001');
        $this->addStudent('S001', 'Alya', 'K001');
        $this->addStudent('S002', 'Budi', 'K002');
        $this->addStudent('S003', 'Cici', 'K001');
        $this->addQuiz('Q001', 'Ulangan', 'MP001', 75, 'ulangan');
        $this->addQuiz('Q002', 'Remedial Satu', 'MP001', 75, 'ulangan');
        $this->addQuiz('Q003', 'Remedial Dua', 'MP001', 75, 'ulangan');
        $attemptInClass = $this->addAttempt('Q001', 'S001', 50, 'final', 1);
        $secondAttemptInClass = $this->addAttempt('Q001', 'S001', 55, 'final', 2);
        $attemptForAnotherStudent = $this->addAttempt('Q001', 'S003', 60);
        $attemptOutOfClass = $this->addAttempt('Q001', 'S002', 40);
        // Alya masih punya tugas remedial terbuka; Cici sudah menyelesaikan semuanya.
        $this->addRemedial('Q002', $attemptInClass, 'G001', 'ditugaskan');
        $this->addRemedial('Q003', $secondAttemptInClass, 'G001', 'ditugaskan');
        $this->addRemedial('Q003', $attemptInClass, 'G001', 'selesai');
        $this->addRemedial('Q002', $attemptForAnotherStudent, 'G001', 'selesai');
        $this->addRemedial('Q002', $attemptOutOfClass, 'G001', 'ditugaskan');

        $counts = app(DashboardKelasService::class)
            ->remedialKelas($this->guru('G001'), 'K001', 'MP001');

        $this->assertSame(['ditugaskan' => 2, 'selesai' => 1, 'belum' => 1], $counts);
    }

    private function seedBase(): void
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

        DB::table('tm_mapel')->insert([
            ['id_mapel' => 'MP001', 'kode_mapel' => 'TIK', 'nama_mapel' => 'TIK'],
            ['id_mapel' => 'MP002', 'kode_mapel' => 'MTK', 'nama_mapel' => 'Matematika'],
        ]);

        $this->addUser('G001', 'Guru Satu', 'guru');
        $this->addUser('G002', 'Guru Dua', 'guru');
    }

    private function assign(string $guruId, string $classId, string $subjectId): void
    {
        DB::table('tr_pengajaran')->insert([
            'id_user' => $guruId,
            'id_kelas' => $classId,
            'id_mapel' => $subjectId,
        ]);
    }

    private function addStudent(string $id, string $name, string $classId, string $status = 'aktif'): void
    {
        $this->addUser($id, $name, 'siswa', $status);
        DB::table('tp_anggota_kelas')->insert([
            'id_kelas' => $classId,
            'id_user' => $id,
        ]);
    }

    private function addUser(string $id, string $name, string $role, string $status = 'aktif'): void
    {
        DB::table('tm_user')->insert([
            'id_user' => $id,
            'nama_lengkap' => $name,
            'role' => $role,
            'status' => $status,
            'password' => 'test-password',
        ]);
    }

    private function addQuiz(string $id, string $title, string $subjectId, float $kkm, string $category): void
    {
        DB::table('tm_kuis')->insert([
            'id_kuis' => $id,
            'judul' => $title,
            'kategori' => $category,
            'tingkat_kesulitan' => 'mudah',
            'id_mapel' => $subjectId,
            'id_tahun_ajaran' => 'TA001',
            'kkm' => $kkm,
            'id_user' => 'G001',
            'status_publikasi' => 'terbit',
            'is_aktif' => true,
        ]);
    }

    private function addAttempt(
        string $quizId,
        string $studentId,
        ?float $score,
        string $status = 'final',
        int $attempt = 1
    ): int {
        return DB::table('tr_mengerjakan')->insertGetId([
            'id_kuis' => $quizId,
            'id_user' => $studentId,
            'nilai' => $score,
            'percobaan_ke' => $attempt,
            'status_pengerjaan' => $status,
        ]);
    }

    private function addMaterial(string $id, string $title, string $subjectId, string $class): void
    {
        DB::table('tm_materi')->insert([
            'id_materi' => $id,
            'judul' => $title,
            'kelas' => $class,
            'id_mapel' => $subjectId,
            'id_user' => 'G001',
        ]);
    }

    private function addProgress(string $id, string $materialId, string $studentId, string $status): void
    {
        DB::table('tr_progress')->insert([
            'id_progress' => $id,
            'id_materi' => $materialId,
            'id_user' => $studentId,
            'status' => $status,
        ]);
    }

    private function addRemedial(string $quizId, int $attemptId, string $teacherId, string $status): void
    {
        DB::table('tr_remedial_siswa')->insert([
            'id_kuis_remedial' => $quizId,
            'id_mengerjakan_asal' => $attemptId,
            'id_guru' => $teacherId,
            'tanggal_ditugaskan' => '2026-11-15',
            'status' => $status,
        ]);
    }

    private function guru(string $id): User
    {
        $guru = new User;
        $guru->id_user = $id;
        $guru->role = 'guru';

        return $guru;
    }
}
