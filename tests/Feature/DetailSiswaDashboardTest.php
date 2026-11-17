<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DetailSiswaDashboardTest extends TestCase
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

    public function test_student_from_another_class_is_forbidden(): void
    {
        $this->seedDashboard();
        $this->addClass('K002', 'X TKJ 2');
        $this->addStudent('S002', 'Budi', '223344', 'K002');

        $this->actingAs($this->guru())
            ->get(route('dashboard', [
                'kelas' => 'K001',
                'mapel' => 'MP001',
                'siswa' => 'S002',
            ]))
            ->assertForbidden();
    }

    public function test_ungraded_essay_does_not_create_a_score_bar(): void
    {
        $this->seedDashboard();
        $this->addQuiz('Q001', 'Essay Belum Dinilai', 70);
        $this->addAttempt('Q001', 'S001', null);

        $this->actingAs($this->guru())
            ->get($this->detailUrl())
            ->assertOk()
            ->assertSee('Alya')
            ->assertSee('112233')
            ->assertSee('Belum ada nilai kuis yang dapat ditampilkan.')
            ->assertDontSee('id="grafik-nilai-per-kuis"', false);
    }

    public function test_student_without_quiz_attempts_sees_a_message_instead_of_an_empty_graph(): void
    {
        $this->seedDashboard();
        $this->addQuiz('Q001', 'Kuis Belum Dikerjakan', 70);

        $this->actingAs($this->guru())
            ->get($this->detailUrl())
            ->assertOk()
            ->assertSee('Belum ada kuis yang dikerjakan.')
            ->assertDontSee('id="grafik-nilai-per-kuis"', false);
    }

    public function test_each_quiz_uses_its_own_kkm_for_the_marker_and_pass_state(): void
    {
        $this->seedDashboard();
        $this->addQuiz('Q001', 'Kuis KKM Rendah', 50);
        $this->addQuiz('Q002', 'Kuis KKM Tinggi', 70);
        $this->addAttempt('Q001', 'S001', 60);
        $this->addAttempt('Q002', 'S001', 60);

        $response = $this->actingAs($this->guru())
            ->get($this->detailUrl())
            ->assertOk();

        $quizWithLowerKkm = $this->quizSvgGroup($response->getContent(), 'Q001');
        $quizWithHigherKkm = $this->quizSvgGroup($response->getContent(), 'Q002');

        $this->assertStringContainsString('data-kkm="50"', $quizWithLowerKkm);
        $this->assertStringContainsString('data-lulus="true"', $quizWithLowerKkm);
        $this->assertStringContainsString('y1="100"', $quizWithLowerKkm);
        $this->assertStringContainsString('fill="#15803d"', $quizWithLowerKkm);
        $this->assertStringContainsString('data-kkm="70"', $quizWithHigherKkm);
        $this->assertStringContainsString('data-lulus="false"', $quizWithHigherKkm);
        $this->assertStringContainsString('y1="68"', $quizWithHigherKkm);
        $this->assertStringContainsString('fill="#dc2626"', $quizWithHigherKkm);
        $response
            ->assertSee('2 dari 2 dikerjakan')
            ->assertSee('1 lulus')
            ->assertSee('1 belum')
            ->assertSee('Kuis KKM Rendah')
            ->assertSee('Kuis KKM Tinggi');
    }

    private function seedDashboard(): void
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
        ]);

        $this->addUser('G001', 'Guru Satu', 'guru');
        $this->addStudent('S001', 'Alya', '112233', 'K001');
        DB::table('tr_pengajaran')->insert([
            'id_user' => 'G001',
            'id_kelas' => 'K001',
            'id_mapel' => 'MP001',
        ]);
    }

    private function addClass(string $id, string $name): void
    {
        DB::table('tm_kelas')->insert([
            'id_kelas' => $id,
            'nama_kelas' => $name,
            'tingkat' => 'X',
            'id_tahun_ajaran' => 'TA001',
            'is_aktif' => true,
        ]);
    }

    private function addStudent(string $id, string $name, string $nisn, string $classId): void
    {
        $this->addUser($id, $name, 'siswa', $nisn);
        DB::table('tp_anggota_kelas')->insert([
            'id_kelas' => $classId,
            'id_user' => $id,
        ]);
    }

    private function addUser(string $id, string $name, string $role, ?string $nisn = null): void
    {
        DB::table('tm_user')->insert([
            'id_user' => $id,
            'nomor_induk' => $nisn,
            'nama_lengkap' => $name,
            'role' => $role,
            'status' => 'aktif',
            'password' => 'test-password',
        ]);
    }

    private function addQuiz(string $id, string $title, ?float $kkm, string $classId = 'K001'): void
    {
        DB::table('tm_kuis')->insert([
            'id_kuis' => $id,
            'judul' => $title,
            'kategori' => 'kuis harian',
            'tingkat_kesulitan' => 'mudah',
            'id_mapel' => 'MP001',
            'id_tahun_ajaran' => 'TA001',
            'kkm' => $kkm,
            'id_user' => 'G001',
            'status_publikasi' => 'terbit',
            'is_aktif' => true,
        ]);

        DB::table('tp_kuis_kelas')->insert([
            'id_kuis' => $id,
            'id_kelas' => $classId,
        ]);
    }

    public function test_pending_essay_quiz_displays_as_menunggu_penilaian_not_belum_lulus(): void
    {
        $this->seedDashboard();
        $this->addQuiz('Q001', 'Kuis Essay Pending', 70);
        $this->addAttempt('Q001', 'S001', null);

        $this->actingAs($this->guru())
            ->get($this->detailUrl())
            ->assertOk()
            ->assertSee('1 menunggu penilaian')
            ->assertDontSee('1 belum');
    }

    private function addAttempt(string $quizId, string $studentId, ?float $score): void
    {
        DB::table('tr_mengerjakan')->insert([
            'id_kuis' => $quizId,
            'id_user' => $studentId,
            'nilai' => $score,
            'status_pengerjaan' => 'final',
        ]);
    }

    private function detailUrl(): string
    {
        return route('dashboard', [
            'kelas' => 'K001',
            'mapel' => 'MP001',
            'siswa' => 'S001',
        ]);
    }

    private function quizSvgGroup(string $html, string $quizId): string
    {
        preg_match(
            '/<g\s+data-quiz-id="'.preg_quote($quizId, '/').'".*?<\/g>/s',
            $html,
            $matches
        );

        $this->assertNotEmpty($matches);

        return $matches[0];
    }

    private function guru(): User
    {
        return User::query()->findOrFail('G001');
    }
}
