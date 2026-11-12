<?php

namespace Tests\Feature;

use App\Models\Kuis;
use App\Models\Mapel;
use App\Models\Materi;
use App\Models\Soal;
use App\Models\TahunAjaran;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ApiGuruKuisTest extends TestCase
{
    private User $guru;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function (Blueprint $table): void {
            $table->string('id_user', 50)->primary();
            $table->string('nomor_induk', 50)->nullable()->unique();
            $table->string('nama_lengkap', 100);
            $table->string('role', 20);
            $table->string('status', 20)->default('aktif');
            $table->string('kelas', 50)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('password');
            $table->boolean('wajib_ganti_password')->default(false);
            $table->timestamps();
        });

        Schema::create('tahun_ajaran', function (Blueprint $table): void {
            $table->string('id_tahun_ajaran', 50)->primary();
            $table->string('nama', 20)->unique();
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_selesai')->nullable();
            $table->boolean('is_aktif')->default(false);
            $table->timestamps();
        });

        Schema::create('mapel', function (Blueprint $table): void {
            $table->string('id_mapel', 50)->primary();
            $table->string('kode_mapel', 20)->unique();
            $table->string('nama_mapel', 100);
            $table->boolean('is_aktif')->default(true);
            $table->timestamps();
        });

        Schema::create('materis', function (Blueprint $table): void {
            $table->string('id_materi', 50)->primary();
            $table->string('judul', 150);
            $table->string('bab', 50)->nullable();
            $table->string('kelas', 50)->nullable();
            $table->string('id_mapel', 50)->nullable();
            $table->text('deskripsi')->nullable();
            $table->string('isi_materi', 255)->nullable();
            $table->string('link_youtube', 255)->nullable();
            $table->string('id_user', 50);
            $table->timestamps();
            $table->foreign('id_mapel')->references('id_mapel')->on('mapel');
            $table->foreign('id_user')->references('id_user')->on('users');
        });

        Schema::create('kuis', function (Blueprint $table): void {
            $table->string('id_kuis', 50)->primary();
            $table->string('judul', 150);
            $table->text('deskripsi')->nullable();
            $table->string('kategori', 50);
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
            $table->foreign('id_mapel')->references('id_mapel')->on('mapel');
            $table->foreign('id_tahun_ajaran')->references('id_tahun_ajaran')->on('tahun_ajaran');
            $table->foreign('id_user')->references('id_user')->on('users');
            $table->foreign('id_publisher')->references('id_user')->on('users');
        });

        Schema::create('materi_kuis', function (Blueprint $table): void {
            $table->increments('id_materi_kuis');
            $table->string('id_materi', 50);
            $table->string('id_kuis', 50);
            $table->unique(['id_materi', 'id_kuis']);
            $table->foreign('id_materi')->references('id_materi')->on('materis');
            $table->foreign('id_kuis')->references('id_kuis')->on('kuis');
        });

        Schema::create('remedial', function (Blueprint $table): void {
            $table->increments('id_remedial');
            $table->string('id_kuis_remedial', 50)->unique();
            $table->string('id_kuis_asal', 50);
            $table->foreign('id_kuis_remedial')->references('id_kuis')->on('kuis');
            $table->foreign('id_kuis_asal')->references('id_kuis')->on('kuis');
        });

        Schema::create('soal', function (Blueprint $table): void {
            $table->string('id_soal', 50)->primary();
            $table->string('id_kuis', 50);
            $table->text('pertanyaan');
            foreach (['a', 'b', 'c', 'd', 'e'] as $option) {
                $table->string('opsi_'.$option, 255)->nullable();
            }
            $table->string('jawaban', 5);
            $table->decimal('bobot', 5, 2);
            $table->string('tingkat_kesulitan', 20)->nullable();
            $table->foreign('id_kuis')->references('id_kuis')->on('kuis');
        });

        TahunAjaran::create([
            'id_tahun_ajaran' => 'TA001',
            'nama' => '2026/2027',
            'is_aktif' => true,
        ]);
        Mapel::create([
            'id_mapel' => 'MP001',
            'kode_mapel' => 'TIK',
            'nama_mapel' => 'Teknologi Informasi',
            'is_aktif' => true,
        ]);
        Mapel::create([
            'id_mapel' => 'MP002',
            'kode_mapel' => 'IPA',
            'nama_mapel' => 'Ilmu Pengetahuan Alam',
            'is_aktif' => true,
        ]);
        $this->guru = User::create([
            'id_user' => 'G001',
            'nomor_induk' => 'G001',
            'nama_lengkap' => 'Guru',
            'role' => 'guru',
            'status' => 'aktif',
            'password' => 'password',
        ]);
        Materi::create([
            'id_materi' => 'M001',
            'judul' => 'Materi TIK',
            'id_mapel' => 'MP001',
            'id_user' => $this->guru->id_user,
        ]);
        Materi::create([
            'id_materi' => 'M002',
            'judul' => 'Materi IPA',
            'id_mapel' => 'MP002',
            'id_user' => $this->guru->id_user,
        ]);
    }

    public function test_teacher_can_create_update_archive_quiz_and_manage_its_questions(): void
    {
        $this->actingAs($this->guru, 'sanctum');

        $this->postJson('/api/guru/kuis', [
            ...$this->quizPayload(),
            'materi' => ['M001'],
        ])
            ->assertCreated()
            ->assertJsonPath('data.id_kuis', 'K001')
            ->assertJsonPath('data.id_tahun_ajaran', 'TA001')
            ->assertJsonPath('data.id_mapel', 'MP001')
            ->assertJsonPath('data.materi.0.id_materi', 'M001');

        $quiz = Kuis::findOrFail('K001');
        $this->putJson('/api/guru/kuis/K001', [
            ...$this->quizPayload(['judul' => 'Kuis diubah']),
            'materi' => ['M001'],
        ])
            ->assertOk()
            ->assertJsonPath('data.judul', 'Kuis diubah')
            ->assertJsonPath('data.id_tahun_ajaran', 'TA001');

        $this->postJson('/api/guru/kuis/K001/soal', $this->questionPayload())
            ->assertCreated()
            ->assertJsonPath('data.id_soal', 'S001')
            ->assertJsonPath('data.jawaban', 'A');

        $this->putJson('/api/guru/kuis/K001/soal/S001', $this->questionPayload([
            'pertanyaan' => 'Pertanyaan diperbarui?',
            'bobot' => 80,
        ]))
            ->assertOk()
            ->assertJsonPath('data.pertanyaan', 'Pertanyaan diperbarui?')
            ->assertJsonPath('data.bobot', '80.00');

        $this->getJson('/api/guru/kuis/K001')
            ->assertOk()
            ->assertJsonPath('data.soal.0.id_soal', 'S001');

        $this->deleteJson('/api/guru/kuis/K001/soal/S001')
            ->assertOk();
        $this->assertDatabaseMissing('soal', ['id_soal' => 'S001']);

        $this->deleteJson('/api/guru/kuis/K001')
            ->assertOk();
        $this->assertDatabaseHas('kuis', [
            'id_kuis' => 'K001',
            'is_aktif' => false,
        ]);
        $this->assertSame('K001', $quiz->fresh()->id_kuis);
    }

    public function test_quiz_validates_a_provided_subject_and_rejects_mismatched_material(): void
    {
        $this->actingAs($this->guru, 'sanctum')
            ->postJson('/api/guru/kuis', $this->quizPayload([
                'id_mapel' => 'NOT-A-SUBJECT',
            ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('id_mapel');

        $this->actingAs($this->guru, 'sanctum')
            ->postJson('/api/guru/kuis', [
                ...$this->quizPayload(),
                'materi' => ['M002'],
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('materi');

        $this->assertDatabaseCount('kuis', 0);
    }

    public function test_teacher_can_create_quiz_without_a_subject(): void
    {
        $payload = $this->quizPayload();
        unset($payload['id_mapel']);

        $this->actingAs($this->guru, 'sanctum')
            ->postJson('/api/guru/kuis', $payload)
            ->assertCreated()
            ->assertJsonPath('data.id_kuis', 'K001')
            ->assertJsonPath('data.id_mapel', null);

        $this->assertDatabaseHas('kuis', [
            'id_kuis' => 'K001',
            'id_mapel' => null,
        ]);
    }

    public function test_teacher_can_publish_a_quiz_after_questions_reach_one_hundred_weight(): void
    {
        $quiz = $this->createQuiz();
        Soal::create([
            'id_soal' => 'S001',
            'id_kuis' => $quiz->id_kuis,
            ...$this->questionPayload(),
        ]);

        $this->actingAs($this->guru, 'sanctum')
            ->postJson('/api/guru/kuis/K001/publish')
            ->assertOk()
            ->assertJsonPath('data.status_publikasi', 'terbit');
    }

    private function quizPayload(array $overrides = []): array
    {
        return array_replace([
            'judul' => 'Kuis API',
            'deskripsi' => 'Deskripsi kuis',
            'kategori' => 'kuis harian',
            'id_mapel' => 'MP001',
            'alokasi_waktu' => 30,
            'kkm' => 75,
        ], $overrides);
    }

    private function questionPayload(array $overrides = []): array
    {
        return array_replace([
            'pertanyaan' => 'Pertanyaan?',
            'opsi_a' => 'Jawaban benar',
            'opsi_b' => 'Pilihan B',
            'opsi_c' => 'Pilihan C',
            'opsi_d' => 'Pilihan D',
            'jawaban' => 'A',
            'bobot' => 100,
            'tingkat_kesulitan' => 'mudah',
        ], $overrides);
    }

    private function createQuiz(): Kuis
    {
        $this->actingAs($this->guru, 'sanctum')
            ->postJson('/api/guru/kuis', $this->quizPayload())
            ->assertCreated();

        return Kuis::findOrFail('K001');
    }
}
