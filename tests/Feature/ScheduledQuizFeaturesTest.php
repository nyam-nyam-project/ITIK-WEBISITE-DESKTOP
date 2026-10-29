<?php

namespace Tests\Feature;

use App\Models\Kuis;
use App\Models\Mengerjakan;
use App\Models\SesiKuis;
use App\Models\Soal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScheduledQuizFeaturesTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_can_save_integer_kkm_and_optional_attempt_limit(): void
    {
        $teacher = $this->createUser('G001', 'guru');

        $response = $this->actingAs($teacher)->post(route('kuis.store'), [
            'judul' => 'Kuis Terjadwal',
            'kategori' => 'post-test',
            'kkm' => '75',
            'maks_percobaan' => '3',
        ]);

        $response->assertRedirect(route('kuis.index'));
        $this->assertDatabaseHas('kuis', [
            'judul' => 'Kuis Terjadwal',
            'kkm' => 75,
            'maks_percobaan' => 3,
        ]);
    }

    public function test_teacher_cannot_save_a_decimal_kkm(): void
    {
        $teacher = $this->createUser('G001', 'guru');

        $response = $this->actingAs($teacher)->post(route('kuis.store'), [
            'judul' => 'Kuis KKM Desimal',
            'kategori' => 'post-test',
            'kkm' => '75.5',
        ]);

        $response->assertInvalid(['kkm']);
        $this->assertDatabaseMissing('kuis', [
            'judul' => 'Kuis KKM Desimal',
        ]);
    }

    public function test_teacher_cannot_schedule_publication_in_the_past(): void
    {
        $this->freezeTime();
        $teacher = $this->createUser('G001', 'guru');

        $response = $this->actingAs($teacher)->post(route('kuis.store'), [
            'judul' => 'Publikasi Lampau',
            'kategori' => 'post-test',
            'waktu_publish' => now()->subMinute()->format('Y-m-d\TH:i'),
        ]);

        $response->assertInvalid(['waktu_publish']);
        $this->assertDatabaseMissing('kuis', [
            'judul' => 'Publikasi Lampau',
        ]);
    }

    public function test_scheduled_publication_uses_jakarta_local_time(): void
    {
        $this->travelTo('2026-10-16 12:30:00');
        $teacher = $this->createUser('G001', 'guru');

        $response = $this->actingAs($teacher)->post(route('kuis.store'), [
            'judul' => 'Kuis Zona Jakarta',
            'kategori' => 'post-test',
            'waktu_publish' => '2026-10-16T12:45',
        ]);

        $response->assertRedirect(route('kuis.index'));
        $this->assertDatabaseHas('kuis', [
            'judul' => 'Kuis Zona Jakarta',
            'waktu_publish' => '2026-10-16 12:45:00',
        ]);
    }

    public function test_scheduled_quiz_is_published_after_its_scheduled_time(): void
    {
        $this->freezeTime();
        $teacher = $this->createUser('G001', 'guru');
        $quiz = $this->createQuiz($teacher, [
            'waktu_publish' => now()->addHour(),
        ]);
        $this->addMultipleChoiceQuestion($quiz);

        $response = $this->actingAs($teacher)->patch(
            route('kuis.publish', $quiz->id_kuis)
        );

        $response->assertRedirect(route('kuis.show', $quiz->id_kuis));
        $this->assertDatabaseHas('kuis', [
            'id_kuis' => $quiz->id_kuis,
            'status_publikasi' => 'terjadwal',
        ]);

        $this->travelTo(now()->addMinutes(59));
        $this->artisan('app:publish-scheduled-quizzes')
            ->assertSuccessful();
        $this->assertDatabaseHas('kuis', [
            'id_kuis' => $quiz->id_kuis,
            'status_publikasi' => 'terjadwal',
        ]);

        $this->travelTo(now()->addHours(2));
        $this->artisan('app:publish-scheduled-quizzes')
            ->assertSuccessful();

        $this->assertDatabaseHas('kuis', [
            'id_kuis' => $quiz->id_kuis,
            'status_publikasi' => 'terbit',
        ]);
    }

    public function test_student_who_meets_kkm_passes_automatically_and_cannot_retry(): void
    {
        $teacher = $this->createUser('G001', 'guru');
        $student = $this->createUser('S001', 'siswa');
        $quiz = $this->createQuiz($teacher, [
            'status_publikasi' => 'terbit',
            'maks_percobaan' => 3,
            'kkm' => 100,
        ]);
        $question = $this->addMultipleChoiceQuestion($quiz);

        SesiKuis::create([
            'id_kuis' => $quiz->id_kuis,
            'id_user' => $student->id_user,
            'percobaan_ke' => 1,
            'waktu_mulai' => now(),
        ]);

        $this->actingAs($student)
            ->postJson(route('siswa.kuis.jawaban', $quiz->id_kuis), [
                'id_soal' => $question->id_soal,
                'jawaban' => 'A',
            ])
            ->assertOk();

        $this->post(route('siswa.kuis.submit', $quiz->id_kuis))
            ->assertRedirect(route('siswa.kuis.hasil', $quiz->id_kuis));

        $this->assertDatabaseHas('mengerjakans', [
            'id_kuis' => $quiz->id_kuis,
            'id_user' => $student->id_user,
            'nilai' => 100,
            'status_validasi' => 'lulus kkm otomatis',
            'id_validator' => null,
        ]);

        $this->get(route('siswa.kuis.mulai', $quiz->id_kuis))
            ->assertRedirect(route('siswa.kuis.index'));
        $this->assertDatabaseCount('sesi_kuis', 1);
    }

    public function test_student_can_retry_a_failed_quiz_until_the_attempt_limit(): void
    {
        $teacher = $this->createUser('G001', 'guru');
        $student = $this->createUser('S001', 'siswa');
        $quiz = $this->createQuiz($teacher, [
            'status_publikasi' => 'terbit',
            'maks_percobaan' => 2,
        ]);

        SesiKuis::create([
            'id_kuis' => $quiz->id_kuis,
            'id_user' => $student->id_user,
            'percobaan_ke' => 1,
            'waktu_mulai' => now()->subMinutes(20),
            'waktu_selesai' => now()->subMinutes(5),
        ]);
        Mengerjakan::create([
            'id_kuis' => $quiz->id_kuis,
            'id_user' => $student->id_user,
            'nilai' => 40,
            'tanggal' => now()->toDateString(),
            'percobaan_ke' => 1,
            'status_pengerjaan' => 'final',
            'status_validasi' => 'belum divalidasi',
        ]);

        $this->actingAs($student)
            ->get(route('siswa.kuis.hasil', $quiz->id_kuis))
            ->assertOk()
            ->assertSee('Ulangi Kuis');

        $this->get(route('siswa.kuis.mulai', $quiz->id_kuis))
            ->assertRedirect(route('siswa.kuis.kerjakan', $quiz->id_kuis));
        $this->assertDatabaseHas('sesi_kuis', [
            'id_kuis' => $quiz->id_kuis,
            'id_user' => $student->id_user,
            'percobaan_ke' => 2,
        ]);

        SesiKuis::where('id_kuis', $quiz->id_kuis)
            ->where('id_user', $student->id_user)
            ->where('percobaan_ke', 2)
            ->update(['waktu_selesai' => now()]);
        Mengerjakan::create([
            'id_kuis' => $quiz->id_kuis,
            'id_user' => $student->id_user,
            'nilai' => 50,
            'tanggal' => now()->toDateString(),
            'percobaan_ke' => 2,
            'status_pengerjaan' => 'final',
            'status_validasi' => 'belum divalidasi',
        ]);

        $this->get(route('siswa.kuis.mulai', $quiz->id_kuis))
            ->assertRedirect(route('siswa.kuis.hasil', $quiz->id_kuis));
        $this->assertDatabaseCount('sesi_kuis', 2);
    }

    public function test_student_can_retry_without_a_limit_when_teacher_leaves_it_blank(): void
    {
        $teacher = $this->createUser('G001', 'guru');
        $student = $this->createUser('S001', 'siswa');
        $quiz = $this->createQuiz($teacher, [
            'status_publikasi' => 'terbit',
        ]);

        SesiKuis::create([
            'id_kuis' => $quiz->id_kuis,
            'id_user' => $student->id_user,
            'percobaan_ke' => 1,
            'waktu_mulai' => now()->subMinutes(20),
            'waktu_selesai' => now()->subMinutes(5),
        ]);
        Mengerjakan::create([
            'id_kuis' => $quiz->id_kuis,
            'id_user' => $student->id_user,
            'nilai' => 40,
            'tanggal' => now()->toDateString(),
            'percobaan_ke' => 1,
            'status_pengerjaan' => 'final',
            'status_validasi' => 'belum divalidasi',
        ]);

        $this->actingAs($student)
            ->get(route('siswa.kuis.mulai', $quiz->id_kuis))
            ->assertRedirect(route('siswa.kuis.kerjakan', $quiz->id_kuis));

        $this->assertDatabaseHas('sesi_kuis', [
            'id_kuis' => $quiz->id_kuis,
            'id_user' => $student->id_user,
            'percobaan_ke' => 2,
        ]);
    }

    private function createUser(string $id, string $role): User
    {
        return User::create([
            'id_user' => $id,
            'nomor_induk' => $id.'01',
            'email' => strtolower($role).'_'.strtolower($id).'@example.com',
            'nama_lengkap' => ucfirst($role).' Test',
            'role' => $role,
            'password' => 'password',
        ]);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function createQuiz(User $teacher, array $attributes = []): Kuis
    {
        return Kuis::create(array_merge([
            'id_kuis' => 'K001',
            'judul' => 'Kuis Percobaan',
            'kategori' => 'post-test',
            'alokasi_waktu' => 30,
            'kkm' => 70,
            'id_user' => $teacher->id_user,
            'status_publikasi' => 'draft',
            'is_aktif' => true,
        ], $attributes));
    }

    private function addMultipleChoiceQuestion(Kuis $quiz): Soal
    {
        return Soal::create([
            'id_soal' => 'S001',
            'id_kuis' => $quiz->id_kuis,
            'tipe_soal' => 'pilihan_ganda',
            'pertanyaan' => 'Pilih jawaban yang benar.',
            'opsi_a' => 'Jawaban benar',
            'opsi_b' => 'Jawaban salah B',
            'opsi_c' => 'Jawaban salah C',
            'opsi_d' => 'Jawaban salah D',
            'jawaban' => 'A',
            'bobot' => 100,
        ]);
    }
}
