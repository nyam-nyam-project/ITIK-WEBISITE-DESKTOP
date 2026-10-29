<?php

namespace Tests\Feature\Api;

use App\Models\Kuis;
use App\Models\Materi;
use App\Models\Mengerjakan;
use App\Models\Remedial;
use App\Models\RemedialSiswa;
use App\Models\Soal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiswaKuisControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_list_quizzes_with_adaptive_level_status(): void
    {
        $teacher = $this->createTeacher();
        $student = $this->createStudent('S001', 'X');
        $material = $this->createMaterial($teacher, 'M001', 'X');
        $easyQuiz = $this->createQuiz($teacher, 'K001');
        $mediumQuiz = $this->createQuiz($teacher, 'K002', 'sedang');
        $otherClassQuiz = $this->createQuiz($teacher, 'K003');
        $easyQuiz->materi()->sync([$material->id_materi]);
        $mediumQuiz->materi()->sync([$material->id_materi]);
        $otherClassMaterial = $this->createMaterial($teacher, 'M002', 'XI');
        $otherClassQuiz->materi()->sync([$otherClassMaterial->id_materi]);

        $this->actingAs($student, 'sanctum')
            ->getJson('/api/kuis')
            ->assertOk()
            ->assertJsonPath('data.0.status', 'tersedia')
            ->assertJsonFragment([
                'id_kuis' => 'K002',
                'status' => 'terkunci',
                'bisa_mulai' => false,
            ])
            ->assertJsonMissing(['id_kuis' => 'K003']);
    }

    public function test_student_can_access_the_next_level_after_passing_the_previous_level(): void
    {
        $teacher = $this->createTeacher();
        $student = $this->createStudent('S001', 'X');
        $material = $this->createMaterial($teacher, 'M001', 'X');
        $easyQuiz = $this->createQuiz($teacher, 'K001');
        $mediumQuiz = $this->createQuiz($teacher, 'K002', 'sedang');
        $easyQuiz->materi()->sync([$material->id_materi]);
        $mediumQuiz->materi()->sync([$material->id_materi]);
        $this->createMultipleChoiceQuestion($mediumQuiz);
        Mengerjakan::create([
            'id_kuis' => $easyQuiz->id_kuis,
            'id_user' => $student->id_user,
            'nilai' => 75,
            'tanggal' => '2026-10-28',
            'percobaan_ke' => 1,
            'status_pengerjaan' => 'final',
            'status_validasi' => 'lulus kkm otomatis',
        ]);

        $this->actingAs($student, 'sanctum')
            ->getJson('/api/kuis')
            ->assertOk()
            ->assertJsonFragment([
                'id_kuis' => 'K002',
                'status' => 'tersedia',
                'bisa_mulai' => true,
            ]);

        $this->getJson('/api/kuis/K002')
            ->assertOk()
            ->assertJsonPath('data.status', 'tersedia')
            ->assertJsonPath('data.bisa_mulai', true);

        $this->postJson('/api/kuis/K002/mulai')
            ->assertOk()
            ->assertJsonPath('data.id_kuis', 'K002');
    }

    public function test_student_without_a_class_cannot_see_class_specific_quizzes(): void
    {
        $teacher = $this->createTeacher();
        $student = $this->createStudent('S001', null);
        $material = $this->createMaterial($teacher, 'M001', 'X');
        $quiz = $this->createQuiz($teacher, 'K001');
        $quiz->materi()->sync([$material->id_materi]);

        $this->actingAs($student, 'sanctum')
            ->getJson('/api/kuis')
            ->assertOk()
            ->assertJsonCount(0, 'data');

        $this->getJson('/api/kuis/K001')->assertNotFound();
    }

    public function test_student_can_start_quiz_without_receiving_answer_keys(): void
    {
        $this->travelTo('2026-10-28 01:00:00');
        $teacher = $this->createTeacher();
        $student = $this->createStudent('S001', 'X');
        $quiz = $this->createQuiz($teacher, 'K001', 'mudah', [
            'alokasi_waktu' => 120,
        ]);
        $this->createMultipleChoiceQuestion($quiz);
        $essay = $this->createEssayQuestion($quiz);

        $this->actingAs($student, 'sanctum')
            ->postJson('/api/kuis/K001/mulai')
            ->assertOk()
            ->assertJsonPath('data.id_kuis', 'K001')
            ->assertJsonPath('data.percobaan_ke', 1)
            ->assertJsonPath('data.sisa_detik', 7200)
            ->assertJsonMissingPath('data.soal.0.jawaban')
            ->assertJsonMissingPath('data.soal.0.bobot')
            ->assertJsonFragment([
                'id_soal' => $essay->id_soal,
                'tipe_soal' => 'essay',
            ]);

        $this->assertDatabaseHas('sesi_kuis', [
            'id_kuis' => $quiz->id_kuis,
            'id_user' => $student->id_user,
            'percobaan_ke' => 1,
        ]);
    }

    public function test_student_cannot_start_a_quiz_without_questions(): void
    {
        $teacher = $this->createTeacher();
        $student = $this->createStudent('S001', 'X');
        $quiz = $this->createQuiz($teacher, 'K001');

        $this->actingAs($student, 'sanctum')
            ->postJson('/api/kuis/K001/mulai')
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Kuis belum memiliki soal.');

        $this->assertDatabaseCount('sesi_kuis', 0);
    }

    public function test_student_cannot_start_a_locked_difficulty(): void
    {
        $teacher = $this->createTeacher();
        $student = $this->createStudent('S001', 'X');
        $material = $this->createMaterial($teacher, 'M001', 'X');
        $this->createQuiz($teacher, 'K001');
        $mediumQuiz = $this->createQuiz($teacher, 'K002', 'sedang');
        $mediumQuiz->materi()->sync([$material->id_materi]);
        $this->createMultipleChoiceQuestion($mediumQuiz);

        $this->actingAs($student, 'sanctum')
            ->postJson('/api/kuis/K002/mulai')
            ->assertForbidden();

        $this->assertDatabaseCount('sesi_kuis', 0);
    }

    public function test_student_can_save_answer_and_submit_score_and_duration(): void
    {
        $this->travelTo('2026-10-28 01:00:00');
        $teacher = $this->createTeacher();
        $student = $this->createStudent('S001', 'X');
        $quiz = $this->createQuiz($teacher, 'K001', 'mudah', [
            'alokasi_waktu' => 60,
        ]);
        $question = $this->createMultipleChoiceQuestion($quiz);
        $essay = $this->createEssayQuestion($quiz);
        $this->actingAs($student, 'sanctum')
            ->postJson('/api/kuis/K001/mulai')
            ->assertOk();

        $this->postJson('/api/kuis/K001/jawaban', [
            'id_soal' => $question->id_soal,
            'jawaban' => 'A',
        ])->assertOk()
            ->assertJsonPath('data.tersimpan', true);

        $this->postJson('/api/kuis/K001/jawaban', [
            'id_soal' => $essay->id_soal,
            'jawaban_text' => 'Konsep jaringan menghubungkan perangkat.',
        ])->assertOk();

        $this->travelTo('2026-10-28 01:02:00');

        $this->postJson('/api/kuis/K001/submit')
            ->assertOk()
            ->assertJsonPath('data.nilai', '100.00')
            ->assertJsonPath('data.durasi_detik', 120)
            ->assertJsonPath('data.status', 'lulus');

        $this->assertDatabaseHas('mengerjakans', [
            'id_kuis' => $quiz->id_kuis,
            'id_user' => $student->id_user,
            'nilai' => 100,
        ]);
        $this->assertDatabaseMissing('draft_jawaban', [
            'id_kuis' => $quiz->id_kuis,
            'id_user' => $student->id_user,
        ]);
        $this->assertDatabaseHas('jawaban_siswa', [
            'id_soal' => $essay->id_soal,
            'jawaban_text' => 'Konsep jaringan menghubungkan perangkat.',
        ]);

        $this->getJson('/api/kuis/K001/hasil')
            ->assertOk()
            ->assertJsonPath('data.nilai', '100.00')
            ->assertJsonPath('data.durasi_detik', 120);
    }

    public function test_student_cannot_submit_an_answer_to_a_question_from_another_quiz(): void
    {
        $teacher = $this->createTeacher();
        $student = $this->createStudent('S001', 'X');
        $quiz = $this->createQuiz($teacher, 'K001');
        $otherQuiz = $this->createQuiz($teacher, 'K002');
        $this->createMultipleChoiceQuestion($quiz);
        $question = $this->createMultipleChoiceQuestion($otherQuiz);

        $this->actingAs($student, 'sanctum')
            ->postJson('/api/kuis/K001/mulai')
            ->assertOk();

        $this->postJson('/api/kuis/K001/jawaban', [
            'id_soal' => $question->id_soal,
            'jawaban' => 'A',
        ])->assertNotFound();

        $this->assertDatabaseMissing('draft_jawaban', [
            'id_kuis' => $quiz->id_kuis,
            'id_user' => $student->id_user,
        ]);
    }

    public function test_student_cannot_submit_an_answer_in_the_wrong_question_format(): void
    {
        $teacher = $this->createTeacher();
        $student = $this->createStudent('S001', 'X');
        $quiz = $this->createQuiz($teacher, 'K001');
        $question = $this->createMultipleChoiceQuestion($quiz);
        $this->actingAs($student, 'sanctum')
            ->postJson('/api/kuis/K001/mulai')
            ->assertOk();

        $this->postJson('/api/kuis/K001/jawaban', [
            'id_soal' => $question->id_soal,
            'jawaban_text' => 'Seharusnya pilihan ganda.',
        ])->assertUnprocessable()
            ->assertJsonPath('errors.jawaban.0', 'Format jawaban tidak sesuai dengan tipe soal.');

        $this->assertDatabaseCount('draft_jawaban', 0);
    }

    public function test_student_cannot_save_an_answer_after_the_server_deadline(): void
    {
        $this->travelTo('2026-10-28 01:00:00');
        $teacher = $this->createTeacher();
        $student = $this->createStudent('S001', 'X');
        $quiz = $this->createQuiz($teacher, 'K001', 'mudah', [
            'alokasi_waktu' => 1,
        ]);
        $question = $this->createMultipleChoiceQuestion($quiz);
        $this->actingAs($student, 'sanctum')
            ->postJson('/api/kuis/K001/mulai')
            ->assertOk();

        $this->travelTo('2026-10-28 01:01:00');

        $this->postJson('/api/kuis/K001/jawaban', [
            'id_soal' => $question->id_soal,
            'jawaban' => 'A',
        ])->assertUnprocessable();

        $this->assertDatabaseCount('draft_jawaban', 0);
    }

    public function test_student_can_start_only_an_assigned_remedial_quiz(): void
    {
        $teacher = $this->createTeacher();
        $student = $this->createStudent('S001', 'X');
        $originQuiz = $this->createQuiz($teacher, 'K001');
        $remedialQuiz = $this->createQuiz($teacher, 'K002', 'mudah', [
            'kategori' => 'remedial',
        ]);
        $failedAttempt = Mengerjakan::create([
            'id_kuis' => $originQuiz->id_kuis,
            'id_user' => $student->id_user,
            'nilai' => 60,
            'tanggal' => '2026-10-28',
            'percobaan_ke' => 1,
            'status_pengerjaan' => 'final',
            'status_validasi' => 'belum divalidasi',
        ]);
        Remedial::create([
            'id_kuis_remedial' => $remedialQuiz->id_kuis,
            'id_kuis_asal' => $originQuiz->id_kuis,
        ]);
        $this->createMultipleChoiceQuestion($remedialQuiz);

        $this->actingAs($student, 'sanctum')
            ->postJson('/api/kuis/K002/mulai')
            ->assertNotFound();

        RemedialSiswa::create([
            'id_kuis_remedial' => $remedialQuiz->id_kuis,
            'id_mengerjakan_asal' => $failedAttempt->id_mengerjakan,
            'id_guru' => $teacher->id_user,
            'tanggal_ditugaskan' => '2026-10-28',
            'status' => 'ditugaskan',
        ]);

        $this->postJson('/api/kuis/K002/mulai')
            ->assertOk()
            ->assertJsonPath('data.id_kuis', 'K002');
    }

    public function test_student_cannot_exceed_the_quiz_attempt_limit(): void
    {
        $teacher = $this->createTeacher();
        $student = $this->createStudent('S001', 'X');
        $quiz = $this->createQuiz($teacher, 'K001', 'mudah', [
            'maks_percobaan' => 1,
        ]);
        $this->createMultipleChoiceQuestion($quiz);
        $this->actingAs($student, 'sanctum')
            ->postJson('/api/kuis/K001/mulai')
            ->assertOk();

        $this->postJson('/api/kuis/K001/submit')->assertOk();

        $this->postJson('/api/kuis/K001/mulai')->assertConflict();

        $this->assertDatabaseCount('sesi_kuis', 1);
    }

    public function test_student_cannot_read_another_students_result(): void
    {
        $teacher = $this->createTeacher();
        $student = $this->createStudent('S001', 'X');
        $otherStudent = $this->createStudent('S002', 'X');
        $quiz = $this->createQuiz($teacher, 'K001');
        Mengerjakan::create([
            'id_kuis' => $quiz->id_kuis,
            'id_user' => $otherStudent->id_user,
            'nilai' => 100,
            'tanggal' => '2026-10-28',
            'percobaan_ke' => 1,
            'status_pengerjaan' => 'final',
            'status_validasi' => 'lulus kkm otomatis',
        ]);

        $this->actingAs($student, 'sanctum')
            ->getJson('/api/kuis/K001/hasil')
            ->assertNotFound();
    }

    public function test_teacher_cannot_access_student_quiz_endpoints(): void
    {
        $teacher = $this->createTeacher();

        $this->actingAs($teacher, 'sanctum')
            ->getJson('/api/kuis')
            ->assertForbidden();
    }

    public function test_student_quiz_endpoints_require_authentication(): void
    {
        $this->getJson('/api/kuis')->assertUnauthorized();
        $this->postJson('/api/kuis/K001/mulai')->assertUnauthorized();
    }

    private function createTeacher(): User
    {
        return User::create([
            'id_user' => 'G001',
            'nomor_induk' => 'G00101',
            'nama_lengkap' => 'Guru Test',
            'role' => 'guru',
            'email' => 'guru@example.com',
            'password' => 'password',
        ]);
    }

    private function createStudent(string $id, ?string $kelas): User
    {
        return User::create([
            'id_user' => $id,
            'nomor_induk' => $id.'01',
            'nama_lengkap' => 'Siswa Test',
            'role' => 'siswa',
            'kelas' => $kelas,
            'email' => strtolower($id).'@example.com',
            'password' => 'password',
        ]);
    }

    private function createMaterial(User $teacher, string $id, string $kelas): Materi
    {
        return Materi::create([
            'id_materi' => $id,
            'judul' => 'Materi '.$id,
            'kelas' => $kelas,
            'id_user' => $teacher->id_user,
        ]);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function createQuiz(
        User $teacher,
        string $id,
        string $level = 'mudah',
        array $attributes = []
    ): Kuis {
        return Kuis::create(array_merge([
            'id_kuis' => $id,
            'judul' => 'Kuis '.$id,
            'kategori' => 'post-test',
            'tingkat_ujian' => $level,
            'alokasi_waktu' => 30,
            'kkm' => 75,
            'id_user' => $teacher->id_user,
            'status_publikasi' => 'terbit',
            'is_aktif' => true,
        ], $attributes));
    }

    private function createMultipleChoiceQuestion(Kuis $quiz): Soal
    {
        return Soal::create([
            'id_soal' => 'S-'.$quiz->id_kuis,
            'id_kuis' => $quiz->id_kuis,
            'tipe_soal' => 'pilihan_ganda',
            'pertanyaan' => 'Pilih jawaban yang benar.',
            'opsi_a' => 'Benar',
            'opsi_b' => 'Salah B',
            'opsi_c' => 'Salah C',
            'opsi_d' => 'Salah D',
            'jawaban' => 'A',
            'bobot' => 100,
        ]);
    }

    private function createEssayQuestion(Kuis $quiz): Soal
    {
        return Soal::create([
            'id_soal' => 'E-'.$quiz->id_kuis,
            'id_kuis' => $quiz->id_kuis,
            'tipe_soal' => 'essay',
            'pertanyaan' => 'Jelaskan konsep jaringan.',
            'bobot' => 0,
        ]);
    }
}
