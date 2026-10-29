<?php

namespace Tests\Feature;

use App\Models\Kuis;
use App\Models\Materi;
use App\Models\Mengerjakan;
use App\Models\Remedial;
use App\Models\RemedialSiswa;
use App\Models\SesiKuis;
use App\Models\Soal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdaptiveExamTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_sees_exam_categories_in_the_standard_order(): void
    {
        $teacher = $this->createUser('G001', 'guru');

        $response = $this->actingAs($teacher)->get(route('kuis.create'));

        $response->assertOk()
            ->assertSee('Pre-test')
            ->assertSee('Post-test')
            ->assertSee('Kuis Harian')
            ->assertSee('Ulangan')
            ->assertSee('Remedial')
            ->assertDontSee('Pass-test');
    }

    public function test_teacher_can_create_a_pretest_with_a_difficulty_level(): void
    {
        $teacher = $this->createUser('G001', 'guru');
        $materi = $this->createMateri($teacher, 'M001');

        $response = $this->actingAs($teacher)->post(route('kuis.store'), [
            'judul' => 'Pre-test Dasar',
            'kategori' => 'pre-test',
            'tingkat_ujian' => 'sedang',
            'kkm' => 75,
            'materi' => [$materi->id_materi],
        ]);

        $response->assertRedirect(route('kuis.index'));
        $this->assertDatabaseHas('kuis', [
            'judul' => 'Pre-test Dasar',
            'kategori' => 'pre-test',
            'tingkat_ujian' => 'sedang',
        ]);
    }

    public function test_teacher_cannot_create_a_later_difficulty_without_materials(): void
    {
        $teacher = $this->createUser('G001', 'guru');

        $response = $this->actingAs($teacher)->post(route('kuis.store'), [
            'judul' => 'Ujian Sedang Tanpa Materi',
            'kategori' => 'post-test',
            'tingkat_ujian' => 'sedang',
            'kkm' => 75,
        ]);

        $response->assertInvalid(['materi']);
        $this->assertDatabaseMissing('kuis', [
            'judul' => 'Ujian Sedang Tanpa Materi',
        ]);
    }

    public function test_medium_quiz_unlocks_after_a_passing_attempt_on_matching_easy_quiz(): void
    {
        $teacher = $this->createUser('G001', 'guru');
        $student = $this->createUser('S001', 'siswa');
        $materi = $this->createMateri($teacher, 'M001');
        $easyQuiz = $this->createQuiz($teacher, 'K001');
        $mediumQuiz = $this->createQuiz($teacher, 'K002', 'sedang');
        $differentMaterialQuiz = $this->createQuiz($teacher, 'K003', 'sedang');
        $easyQuiz->materi()->sync([$materi->id_materi]);
        $mediumQuiz->materi()->sync([$materi->id_materi]);

        $this->actingAs($student)
            ->get(route('siswa.kuis.index'))
            ->assertOk()
            ->assertSee('Terbuka setelah lulus KKM tingkat Mudah');

        $this->get(route('siswa.kuis.mulai', $mediumQuiz->id_kuis))
            ->assertRedirect(route('siswa.kuis.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseCount('sesi_kuis', 0);

        Mengerjakan::create([
            'id_kuis' => $easyQuiz->id_kuis,
            'id_user' => $student->id_user,
            'nilai' => 75,
            'tanggal' => now()->toDateString(),
            'percobaan_ke' => 1,
            'status_pengerjaan' => 'final',
            'status_validasi' => 'lulus kkm otomatis',
        ]);

        $this->get(route('siswa.kuis.mulai', $mediumQuiz->id_kuis))
            ->assertRedirect(route('siswa.kuis.kerjakan', $mediumQuiz->id_kuis));

        $this->get(route('siswa.kuis.mulai', $differentMaterialQuiz->id_kuis))
            ->assertRedirect(route('siswa.kuis.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('sesi_kuis', [
            'id_kuis' => $mediumQuiz->id_kuis,
            'id_user' => $student->id_user,
            'percobaan_ke' => 1,
        ]);
    }

    public function test_score_below_kkm_does_not_unlock_the_next_difficulty(): void
    {
        $teacher = $this->createUser('G001', 'guru');
        $student = $this->createUser('S001', 'siswa');
        $materi = $this->createMateri($teacher, 'M001');
        $easyQuiz = $this->createQuiz($teacher, 'K001');
        $mediumQuiz = $this->createQuiz($teacher, 'K002', 'sedang');
        $easyQuiz->materi()->sync([$materi->id_materi]);
        $mediumQuiz->materi()->sync([$materi->id_materi]);

        Mengerjakan::create([
            'id_kuis' => $easyQuiz->id_kuis,
            'id_user' => $student->id_user,
            'nilai' => 74,
            'tanggal' => now()->toDateString(),
            'percobaan_ke' => 1,
            'status_pengerjaan' => 'final',
            'status_validasi' => 'belum divalidasi',
        ]);

        $this->actingAs($student)
            ->get(route('siswa.kuis.mulai', $mediumQuiz->id_kuis))
            ->assertRedirect(route('siswa.kuis.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseCount('sesi_kuis', 0);
    }

    public function test_passing_an_assigned_remedial_unlocks_the_next_difficulty(): void
    {
        $teacher = $this->createUser('G001', 'guru');
        $student = $this->createUser('S001', 'siswa');
        $materi = $this->createMateri($teacher, 'M001');
        $easyQuiz = $this->createQuiz($teacher, 'K001');
        $mediumQuiz = $this->createQuiz($teacher, 'K002', 'sedang');
        $remedialQuiz = $this->createQuiz($teacher, 'K003', 'mudah', [
            'kategori' => 'remedial',
        ]);
        $easyQuiz->materi()->sync([$materi->id_materi]);
        $mediumQuiz->materi()->sync([$materi->id_materi]);
        $failedAttempt = Mengerjakan::create([
            'id_kuis' => $easyQuiz->id_kuis,
            'id_user' => $student->id_user,
            'nilai' => 60,
            'tanggal' => now()->toDateString(),
            'percobaan_ke' => 1,
            'status_pengerjaan' => 'final',
            'status_validasi' => 'belum divalidasi',
        ]);
        Remedial::create([
            'id_kuis_remedial' => $remedialQuiz->id_kuis,
            'id_kuis_asal' => $easyQuiz->id_kuis,
        ]);
        RemedialSiswa::create([
            'id_kuis_remedial' => $remedialQuiz->id_kuis,
            'id_mengerjakan_asal' => $failedAttempt->id_mengerjakan,
            'id_guru' => $teacher->id_user,
            'tanggal_ditugaskan' => now()->toDateString(),
            'status' => 'ditugaskan',
        ]);

        $this->actingAs($student)
            ->get(route('siswa.kuis.mulai', $mediumQuiz->id_kuis))
            ->assertRedirect(route('siswa.kuis.index'));

        Mengerjakan::create([
            'id_kuis' => $remedialQuiz->id_kuis,
            'id_user' => $student->id_user,
            'nilai' => 75,
            'tanggal' => now()->toDateString(),
            'percobaan_ke' => 1,
            'status_pengerjaan' => 'final',
            'status_validasi' => 'lulus kkm otomatis',
        ]);

        $this->get(route('siswa.kuis.mulai', $mediumQuiz->id_kuis))
            ->assertRedirect(route('siswa.kuis.kerjakan', $mediumQuiz->id_kuis));
    }

    public function test_unlinked_quizzes_without_materials_do_not_unlock_each_other(): void
    {
        $teacher = $this->createUser('G001', 'guru');
        $student = $this->createUser('S001', 'siswa');
        $easyQuiz = $this->createQuiz($teacher, 'K001');
        $mediumQuiz = $this->createQuiz($teacher, 'K002', 'sedang');

        Mengerjakan::create([
            'id_kuis' => $easyQuiz->id_kuis,
            'id_user' => $student->id_user,
            'nilai' => 100,
            'tanggal' => now()->toDateString(),
            'percobaan_ke' => 1,
            'status_pengerjaan' => 'final',
            'status_validasi' => 'lulus kkm otomatis',
        ]);

        $this->actingAs($student)
            ->get(route('siswa.kuis.mulai', $mediumQuiz->id_kuis))
            ->assertRedirect(route('siswa.kuis.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseCount('sesi_kuis', 0);
    }

    public function test_exam_duration_is_recorded_without_changing_the_answer_score(): void
    {
        $this->travelTo('2026-10-28 00:00:00');
        $teacher = $this->createUser('G001', 'guru');
        $student = $this->createUser('S001', 'siswa');
        $quiz = $this->createQuiz($teacher, 'K001', 'mudah', [
            'status_publikasi' => 'terbit',
            'alokasi_waktu' => 120,
        ]);
        $question = Soal::create([
            'id_soal' => 'S001',
            'id_kuis' => $quiz->id_kuis,
            'tipe_soal' => 'pilihan_ganda',
            'pertanyaan' => 'Pilih jawaban yang benar.',
            'opsi_a' => 'Benar',
            'opsi_b' => 'Salah',
            'opsi_c' => 'Salah',
            'opsi_d' => 'Salah',
            'jawaban' => 'A',
            'bobot' => 100,
        ]);
        SesiKuis::create([
            'id_kuis' => $quiz->id_kuis,
            'id_user' => $student->id_user,
            'percobaan_ke' => 1,
            'waktu_mulai' => now(),
        ]);

        $this->actingAs($student)
            ->get(route('siswa.kuis.kerjakan', $quiz->id_kuis))
            ->assertOk()
            ->assertSee('Waktu tersisa')
            ->assertSee('120 menit')
            ->assertSee('Tingkat: Mudah');

        $this->postJson(route('siswa.kuis.jawaban', $quiz->id_kuis), [
            'id_soal' => $question->id_soal,
            'jawaban' => 'A',
        ])->assertOk();

        $this->travelTo('2026-10-28 00:02:00');

        $this->post(route('siswa.kuis.submit', $quiz->id_kuis))
            ->assertRedirect(route('siswa.kuis.hasil', $quiz->id_kuis));

        $this->assertDatabaseHas('mengerjakans', [
            'id_kuis' => $quiz->id_kuis,
            'id_user' => $student->id_user,
            'nilai' => 100,
        ]);
        $this->assertDatabaseHas('sesi_kuis', [
            'id_kuis' => $quiz->id_kuis,
            'id_user' => $student->id_user,
            'durasi_detik' => 120,
        ]);

        $this->get(route('siswa.kuis.hasil', $quiz->id_kuis))
            ->assertOk()
            ->assertSee('Durasi pengerjaan')
            ->assertSee('2 menit 0 detik');
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

    private function createMateri(User $teacher, string $id): Materi
    {
        return Materi::create([
            'id_materi' => $id,
            'judul' => 'Materi '.$id,
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
        array $attributes = [],
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
}
