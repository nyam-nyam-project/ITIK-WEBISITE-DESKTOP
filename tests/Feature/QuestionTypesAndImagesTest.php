<?php

namespace Tests\Feature;

use App\Models\Kuis;
use App\Models\Mengerjakan;
use App\Models\SesiKuis;
use App\Models\Soal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class QuestionTypesAndImagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_can_create_an_essay_question_with_an_image(): void
    {
        Storage::fake('public');
        $teacher = $this->createUser('G001', 'guru');
        $quiz = $this->createQuiz($teacher);

        $this->actingAs($teacher)
            ->get(route('kuis.soal.create', $quiz->id_kuis))
            ->assertOk()
            ->assertSee('Essay / Jawaban Teks')
            ->assertSee('Gambar Pertanyaan');

        $response = $this->actingAs($teacher)->post(
            route('kuis.soal.store', $quiz->id_kuis),
            [
                'tipe_soal' => 'essay',
                'pertanyaan' => 'Jelaskan struktur dokumen HTML.',
                'gambar_pertanyaan' => $this->tinyPng(),
            ],
        );

        $response->assertRedirect(route('kuis.soal.index', $quiz->id_kuis));
        $this->assertDatabaseHas('soals', [
            'id_kuis' => $quiz->id_kuis,
            'tipe_soal' => 'essay',
            'jawaban' => null,
            'bobot' => 0,
        ]);

        $question = Soal::where('id_kuis', $quiz->id_kuis)->firstOrFail();
        Storage::disk('public')->assertExists($question->gambar_pertanyaan);
    }

    public function test_teacher_can_upload_an_image_as_a_multiple_choice_answer(): void
    {
        Storage::fake('public');
        $teacher = $this->createUser('G001', 'guru');
        $quiz = $this->createQuiz($teacher);

        $response = $this->actingAs($teacher)->post(
            route('kuis.soal.store', $quiz->id_kuis),
            [
                'tipe_soal' => 'pilihan_ganda',
                'pertanyaan' => 'Pilih gambar yang benar.',
                'opsi_a' => '',
                'gambar_opsi_a' => $this->tinyPng(),
                'opsi_b' => 'Pilihan B',
                'opsi_c' => 'Pilihan C',
                'opsi_d' => 'Pilihan D',
                'jawaban' => 'A',
                'bobot' => 100,
            ],
        );

        $response->assertRedirect(route('kuis.soal.index', $quiz->id_kuis));
        $question = Soal::where('id_kuis', $quiz->id_kuis)->firstOrFail();
        Storage::disk('public')->assertExists($question->gambar_opsi_a);

        $this->actingAs($teacher)
            ->get(route('kuis.soal.edit', [$quiz->id_kuis, $question->id_soal]))
            ->assertOk()
            ->assertSee('Gambar Opsi A');
    }

    public function test_student_essay_answer_is_saved_without_an_automatic_score(): void
    {
        Storage::fake('public');
        $teacher = $this->createUser('G001', 'guru');
        $student = $this->createUser('S001', 'siswa');
        $quiz = $this->createQuiz($teacher, 'terbit');
        Storage::disk('public')->put('essay-question.png', 'image');
        $question = $this->createEssayQuestion($quiz, 'essay-question.png');

        $this->travelTo(now());
        SesiKuis::create([
            'id_kuis' => $quiz->id_kuis,
            'id_user' => $student->id_user,
            'percobaan_ke' => 1,
            'waktu_mulai' => now(),
        ]);

        $this->actingAs($student)
            ->get(route('siswa.kuis.kerjakan', $quiz->id_kuis))
            ->assertOk()
            ->assertSee('Jawaban teks')
            ->assertSee('Gambar pertanyaan 1');

        $saveResponse = $this->actingAs($student)
            ->postJson(route('siswa.kuis.jawaban', $quiz->id_kuis), [
                'id_soal' => $question->id_soal,
                'jawaban_text' => 'HTML menggunakan elemen yang berstruktur.',
            ]);

        $saveResponse->assertOk()->assertJson(['success' => true]);

        $submitResponse = $this->post(route('siswa.kuis.submit', $quiz->id_kuis));

        $submitResponse->assertRedirect(route('siswa.kuis.hasil', $quiz->id_kuis));
        $this->assertDatabaseHas('mengerjakans', [
            'id_kuis' => $quiz->id_kuis,
            'id_user' => $student->id_user,
            'nilai' => null,
        ]);
        $this->assertDatabaseHas('jawaban_siswa', [
            'id_soal' => $question->id_soal,
            'jawaban_text' => 'HTML menggunakan elemen yang berstruktur.',
        ]);
        $this->assertDatabaseMissing('draft_jawaban', [
            'id_kuis' => $quiz->id_kuis,
            'id_user' => $student->id_user,
        ]);

        $result = Mengerjakan::where('id_kuis', $quiz->id_kuis)
            ->where('id_user', $student->id_user)
            ->firstOrFail();

        $this->actingAs($teacher)
            ->get(route('guru.nilai.show', $result->id_mengerjakan))
            ->assertOk()
            ->assertSee('HTML menggunakan elemen yang berstruktur.');
    }

    public function test_multiple_choice_quiz_keeps_automatic_scoring(): void
    {
        $teacher = $this->createUser('G001', 'guru');
        $student = $this->createUser('S001', 'siswa');
        $quiz = $this->createQuiz($teacher, 'terbit');
        $question = Soal::create([
            'id_soal' => 'S001',
            'id_kuis' => $quiz->id_kuis,
            'tipe_soal' => 'pilihan_ganda',
            'pertanyaan' => 'Pilih jawaban benar.',
            'opsi_a' => 'A',
            'opsi_b' => 'B',
            'opsi_c' => 'C',
            'opsi_d' => 'D',
            'jawaban' => 'A',
            'bobot' => 100,
        ]);

        SesiKuis::create([
            'id_kuis' => $quiz->id_kuis,
            'id_user' => $student->id_user,
            'percobaan_ke' => 1,
            'waktu_mulai' => now(),
        ]);

        $this->actingAs($student)->postJson(
            route('siswa.kuis.jawaban', $quiz->id_kuis),
            [
                'id_soal' => $question->id_soal,
                'jawaban' => 'A',
            ],
        )->assertOk();

        $this->post(route('siswa.kuis.submit', $quiz->id_kuis));

        $this->assertDatabaseHas('mengerjakans', [
            'id_kuis' => $quiz->id_kuis,
            'id_user' => $student->id_user,
            'nilai' => 100,
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

    private function createQuiz(User $teacher, string $status = 'draft'): Kuis
    {
        return Kuis::create([
            'id_kuis' => $status === 'draft' ? 'K001' : 'K002',
            'judul' => 'Kuis Test',
            'kategori' => 'post-test',
            'alokasi_waktu' => 30,
            'kkm' => 70,
            'id_user' => $teacher->id_user,
            'status_publikasi' => $status,
            'is_aktif' => true,
        ]);
    }

    private function createEssayQuestion(
        Kuis $quiz,
        ?string $imagePath = null
    ): Soal {
        return Soal::create([
            'id_soal' => 'S001',
            'id_kuis' => $quiz->id_kuis,
            'tipe_soal' => 'essay',
            'pertanyaan' => 'Jelaskan struktur dokumen HTML.',
            'gambar_pertanyaan' => $imagePath,
            'bobot' => 0,
        ]);
    }

    private function tinyPng(): UploadedFile
    {
        $contents = base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+j35cAAAAASUVORK5CYII=',
            true
        );

        return UploadedFile::fake()->createWithContent('tiny.png', $contents);
    }
}
