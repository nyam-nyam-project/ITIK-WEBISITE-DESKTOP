<?php

namespace Tests\Feature\Api;

use App\Models\Kuis;
use App\Models\Mengerjakan;
use App\Models\Remedial;
use App\Models\Soal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuruManagementApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_can_create_quiz_and_question_and_publish_it(): void
    {
        $teacher = $this->createUser('G001', 'guru');

        $quizResponse = $this->actingAs($teacher, 'sanctum')
            ->postJson('/api/guru/kuis', [
                'judul' => 'Kuis API',
                'kategori' => 'post-test',
                'alokasi_waktu' => 30,
                'kkm' => 70,
                'id_user' => 'G999',
                'status_publikasi' => 'terbit',
            ]);

        $quizResponse->assertCreated()
            ->assertJsonPath('data.judul', 'Kuis API')
            ->assertJsonPath('data.id_user', 'G001')
            ->assertJsonPath('data.status_publikasi', 'draft');
        $quizId = $quizResponse->json('data.id_kuis');

        $this->postJson("/api/guru/kuis/{$quizId}/soal", [
            'tipe_soal' => 'pilihan_ganda',
            'pertanyaan' => 'Pilih jawaban yang benar.',
            'opsi_a' => 'A',
            'opsi_b' => 'B',
            'opsi_c' => 'C',
            'opsi_d' => 'D',
            'jawaban' => 'A',
            'bobot' => 100,
        ])->assertCreated()
            ->assertJsonPath('data.pertanyaan', 'Pilih jawaban yang benar.');

        $this->postJson("/api/guru/kuis/{$quizId}/publish")
            ->assertOk()
            ->assertJsonPath('data.status_publikasi', 'terbit')
            ->assertJsonPath('data.id_publisher', 'G001');

        $this->assertDatabaseHas('kuis', [
            'id_kuis' => $quizId,
            'id_user' => 'G001',
            'status_publikasi' => 'terbit',
        ]);
        $this->assertDatabaseCount('soals', 1);
    }

    public function test_teacher_cannot_publish_quiz_without_questions(): void
    {
        $teacher = $this->createUser('G001', 'guru');
        $quiz = $this->createQuiz($teacher, 'K001', [
            'alokasi_waktu' => 30,
            'kkm' => 70,
        ]);

        $this->actingAs($teacher, 'sanctum')
            ->postJson('/api/guru/kuis/K001/publish')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['publish']);

        $this->assertDatabaseHas('kuis', [
            'id_kuis' => $quiz->id_kuis,
            'status_publikasi' => 'draft',
        ]);
    }

    public function test_teacher_can_update_archive_and_list_quizzes(): void
    {
        $teacher = $this->createUser('G001', 'guru');
        $quiz = $this->createQuiz($teacher, 'K001');

        $this->actingAs($teacher, 'sanctum')
            ->putJson('/api/guru/kuis/K001', [
                'judul' => 'Judul Diperbarui',
                'kategori' => 'post-test',
                'alokasi_waktu' => 45,
                'kkm' => 75,
                'id_user' => 'G999',
            ])
            ->assertOk()
            ->assertJsonPath('data.judul', 'Judul Diperbarui')
            ->assertJsonPath('data.id_user', 'G001');

        $this->getJson('/api/guru/kuis')
            ->assertOk()
            ->assertJsonPath('data.0.id_kuis', $quiz->id_kuis)
            ->assertJsonPath('meta.total', 1);

        $this->deleteJson('/api/guru/kuis/K001')
            ->assertOk()
            ->assertJsonPath('message', 'Kuis berhasil diarsipkan.');

        $this->getJson('/api/guru/kuis')
            ->assertOk()
            ->assertJsonCount(0, 'data')
            ->assertJsonPath('meta.total', 0);

        $this->assertDatabaseHas('kuis', [
            'id_kuis' => $quiz->id_kuis,
            'is_aktif' => false,
        ]);
    }

    public function test_question_routes_only_expose_questions_from_the_requested_quiz(): void
    {
        $teacher = $this->createUser('G001', 'guru');
        $quiz = $this->createQuiz($teacher, 'K001');
        $otherQuiz = $this->createQuiz($teacher, 'K002');
        $question = $this->createQuestion($quiz);

        $this->actingAs($teacher, 'sanctum')
            ->getJson("/api/guru/kuis/{$otherQuiz->id_kuis}/soal/{$question->id_soal}")
            ->assertNotFound();

        $this->getJson("/api/guru/kuis/{$quiz->id_kuis}/soal/{$question->id_soal}")
            ->assertOk()
            ->assertJsonPath('data.id_soal', $question->id_soal);
    }

    public function test_teacher_can_validate_a_finished_score_once(): void
    {
        $teacher = $this->createUser('G001', 'guru');
        $student = $this->createUser('S001', 'siswa');
        $quiz = $this->createQuiz($teacher, 'K001');
        $result = Mengerjakan::create([
            'id_kuis' => $quiz->id_kuis,
            'id_user' => $student->id_user,
            'nilai' => 60,
            'tanggal' => '2026-10-28',
            'percobaan_ke' => 1,
            'status_pengerjaan' => 'final',
            'status_validasi' => 'belum divalidasi',
        ]);

        $this->actingAs($teacher, 'sanctum')
            ->patchJson("/api/guru/nilai/{$result->id_mengerjakan}/validasi")
            ->assertOk()
            ->assertJsonPath('data.status_validasi', 'sudah divalidasi')
            ->assertJsonPath('data.id_validator', 'G001');

        $this->patchJson("/api/guru/nilai/{$result->id_mengerjakan}/validasi")
            ->assertStatus(409);

        $this->assertDatabaseHas('mengerjakans', [
            'id_mengerjakan' => $result->id_mengerjakan,
            'status_validasi' => 'sudah divalidasi',
            'id_validator' => 'G001',
        ]);
    }

    public function test_teacher_can_create_and_assign_a_remedial_quiz_to_validated_students_below_kkm(): void
    {
        $teacher = $this->createUser('G001', 'guru');
        $student = $this->createUser('S001', 'siswa');
        $sourceQuiz = $this->createQuiz($teacher, 'K001', [
            'status_publikasi' => 'terbit',
            'kkm' => 70,
        ]);
        $sourceResult = Mengerjakan::create([
            'id_kuis' => $sourceQuiz->id_kuis,
            'id_user' => $student->id_user,
            'nilai' => 55,
            'tanggal' => '2026-10-28',
            'percobaan_ke' => 1,
            'status_pengerjaan' => 'final',
            'status_validasi' => 'sudah divalidasi',
        ]);

        $response = $this->actingAs($teacher, 'sanctum')
            ->postJson('/api/guru/remedial', [
                'judul' => 'Remedial Kuis',
                'id_kuis_asal' => $sourceQuiz->id_kuis,
                'alokasi_waktu' => 30,
                'kkm' => 70,
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.kategori', 'remedial');
        $remedialQuizId = $response->json('data.id_kuis');
        $remedial = Remedial::where('id_kuis_remedial', $remedialQuizId)->firstOrFail();

        $this->getJson("/api/guru/remedial/{$remedial->id_remedial}/siswa")
            ->assertOk()
            ->assertJsonPath('data.0.hasil.id_mengerjakan', $sourceResult->id_mengerjakan)
            ->assertJsonPath('data.0.sudah_ditugaskan', false);

        $this->postJson("/api/guru/remedial/{$remedial->id_remedial}/tugaskan", [
            'id_mengerjakan' => [$sourceResult->id_mengerjakan],
        ])->assertOk()
            ->assertJsonPath('data.jumlah_ditugaskan', 1);

        $this->getJson("/api/guru/remedial/{$remedial->id_remedial}/siswa")
            ->assertOk()
            ->assertJsonPath('data.0.sudah_ditugaskan', true);

        $this->assertDatabaseHas('remedial_siswa', [
            'id_kuis_remedial' => $remedialQuizId,
            'id_mengerjakan_asal' => $sourceResult->id_mengerjakan,
            'id_guru' => 'G001',
        ]);
    }

    public function test_teacher_api_requires_authentication_and_teacher_role(): void
    {
        $this->getJson('/api/guru/kuis')->assertUnauthorized();

        $student = $this->createUser('S001', 'siswa');
        $this->actingAs($student, 'sanctum')
            ->getJson('/api/guru/kuis')
            ->assertForbidden();
    }

    private function createUser(string $id, string $role): User
    {
        return User::create([
            'id_user' => $id,
            'nomor_induk' => $id.'01',
            'nama_lengkap' => ucfirst($role).' Test',
            'role' => $role,
            'kelas' => $role === 'siswa' ? 'X' : null,
            'email' => strtolower($id).'@example.com',
            'password' => 'password',
        ]);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function createQuiz(User $teacher, string $id, array $attributes = []): Kuis
    {
        return Kuis::create([
            'id_kuis' => $id,
            'judul' => 'Kuis '.$id,
            'kategori' => 'post-test',
            'alokasi_waktu' => 30,
            'kkm' => 70,
            'id_user' => $teacher->id_user,
            'status_publikasi' => 'draft',
            'is_aktif' => true,
            ...$attributes,
        ]);
    }

    private function createQuestion(Kuis $quiz): Soal
    {
        return Soal::create([
            'id_soal' => 'S001',
            'id_kuis' => $quiz->id_kuis,
            'tipe_soal' => 'pilihan_ganda',
            'pertanyaan' => 'Pilih jawaban yang benar.',
            'opsi_a' => 'A',
            'opsi_b' => 'B',
            'opsi_c' => 'C',
            'opsi_d' => 'D',
            'jawaban' => 'A',
            'bobot' => 100,
        ]);
    }
}
