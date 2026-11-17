<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DashboardKelasPageTest extends TestCase
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

        Schema::create('tp_kuis_kelas', function (Blueprint $table): void {
            $table->string('id_kuis', 50);
            $table->string('id_kelas', 50);
            $table->primary(['id_kuis', 'id_kelas']);
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

    public function test_teacher_with_assignments_sees_their_class_dashboard(): void
    {
        $this->seedBase();
        $this->assign('G001', 'K001', 'MP001');
        $this->assign('G002', 'K002', 'MP002');
        $this->addStudent('S001', 'Alya Kelas Satu', '112233', 'K001');
        $this->addStudent('S002', 'Budi Kelas Dua', '445566', 'K002');
        $this->addQuiz('Q001', 'Kuis Harian', 'MP001');
        $this->addQuiz('Q002', 'Post Test', 'MP001', 'post-test');
        $this->addAttempt('Q001', 'S001', 80);

        $this->actingAs($this->guru('G001'))
            ->get(route('dashboard', ['kelas' => 'K001', 'mapel' => 'MP001']))
            ->assertOk()
            ->assertSee('Ringkasan kelas')
            ->assertSee('X TKJ 1')
            ->assertSee('Teknologi Informasi')
            ->assertSee('Alya Kelas Satu')
            ->assertSee('112233')
            ->assertSee('1 dari 2')
            ->assertSee('Rata-rata kelas')
            ->assertSee('Materi dibaca')
            ->assertSee('Progres kuis per kategori')
            ->assertSee('dashboard-progres-card')
            ->assertSee('1 dari 1')
            ->assertSee('1 lulus')
            ->assertSee('Remedial')
            ->assertDontSee('Budi Kelas Dua')
            ->assertSee('css/guru/dashboard.css')
            ->assertDontSee('<style', false)
            ->assertDontSee('<script>', false)
            ->assertSee(e(route('dashboard', [
                'kelas' => 'K001',
                'mapel' => 'MP001',
                'siswa' => 'S001',
            ])), false);
    }

    public function test_teacher_without_assignments_sees_message_instead_of_empty_table(): void
    {
        $this->addUser('G001', 'Guru Satu', 'guru');

        $this->actingAs($this->guru('G001'))
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Belum ada kelas dan mapel yang diajarkan. Hubungi admin.')
            ->assertDontSee('<table', false)
            ->assertDontSee('Siswa aktif');
    }

    public function test_unassigned_class_filter_falls_back_to_a_class_taught_by_the_teacher(): void
    {
        $this->seedBase();
        $this->assign('G001', 'K001', 'MP001');
        $this->assign('G002', 'K002', 'MP002');
        $this->addStudent('S001', 'Alya Kelas Satu', '112233', 'K001');
        $this->addStudent('S002', 'Budi Kelas Dua', '445566', 'K002');

        $this->actingAs($this->guru('G001'))
            ->get(route('dashboard', ['kelas' => 'K002', 'mapel' => 'MP002']))
            ->assertOk()
            ->assertSee('Alya Kelas Satu')
            ->assertDontSee('Budi Kelas Dua')
            ->assertSee('value="K001" selected', false);
    }

    public function test_student_search_is_limited_to_the_selected_class_and_matches_student_number(): void
    {
        $this->seedBase();
        $this->assign('G001', 'K001', 'MP001');
        $this->assign('G001', 'K002', 'MP001');
        $this->addStudent('S001', 'Alya Kelas Satu', '112233', 'K001');
        $this->addStudent('S002', 'Budi Kelas Dua', '445566', 'K002');

        $this->actingAs($this->guru('G001'))
            ->get(route('dashboard', [
                'kelas' => 'K001',
                'mapel' => 'MP001',
                'cari' => '112233',
            ]))
            ->assertOk()
            ->assertSee('Alya Kelas Satu')
            ->assertSee('112233')
            ->assertDontSee('Budi Kelas Dua');

        $this->actingAs($this->guru('G001'))
            ->get(route('dashboard', [
                'kelas' => 'K001',
                'mapel' => 'MP001',
                'cari' => 'Budi',
            ]))
            ->assertOk()
            ->assertDontSee('Budi Kelas Dua')
            ->assertDontSee('Alya Kelas Satu');
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
            ['id_mapel' => 'MP001', 'kode_mapel' => 'TIK', 'nama_mapel' => 'Teknologi Informasi'],
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

    private function addStudent(string $id, string $name, string $nisn, string $classId): void
    {
        $this->addUser($id, $name, 'siswa', 'aktif', $nisn);
        DB::table('tp_anggota_kelas')->insert([
            'id_kelas' => $classId,
            'id_user' => $id,
        ]);
    }

    private function addUser(
        string $id,
        string $name,
        string $role,
        string $status = 'aktif',
        ?string $nomorInduk = null
    ): void {
        DB::table('tm_user')->insert([
            'id_user' => $id,
            'nomor_induk' => $nomorInduk,
            'nama_lengkap' => $name,
            'role' => $role,
            'status' => $status,
            'password' => 'test-password',
        ]);
    }

    private function addQuiz(
        string $id,
        string $title,
        string $subjectId,
        string $category = 'kuis harian',
        string $classId = 'K001'
    ): void {
        DB::table('tm_kuis')->insert([
            'id_kuis' => $id,
            'judul' => $title,
            'kategori' => $category,
            'tingkat_kesulitan' => 'mudah',
            'id_mapel' => $subjectId,
            'id_tahun_ajaran' => 'TA001',
            'kkm' => 70,
            'id_user' => 'G001',
            'status_publikasi' => 'terbit',
            'is_aktif' => true,
        ]);

        DB::table('tp_kuis_kelas')->insert([
            'id_kuis' => $id,
            'id_kelas' => $classId,
        ]);
    }

    private function addAttempt(string $quizId, string $studentId, float $score): void
    {
        DB::table('tr_mengerjakan')->insert([
            'id_kuis' => $quizId,
            'id_user' => $studentId,
            'nilai' => $score,
            'status_pengerjaan' => 'final',
        ]);
    }

    private function guru(string $id): User
    {
        return User::query()->findOrFail($id);
    }
}
