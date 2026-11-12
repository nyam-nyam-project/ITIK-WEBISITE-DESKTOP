<?php

namespace Tests\Feature;

use App\Models\Mapel;
use App\Models\Materi;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ApiGuruMateriTest extends TestCase
{
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
            $table->unique(['kelas', 'bab', 'judul']);
            $table->foreign('id_mapel')->references('id_mapel')->on('mapel');
            $table->foreign('id_user')->references('id_user')->on('users');
        });

        Schema::create('progress', function (Blueprint $table): void {
            $table->string('id_progress', 50)->primary();
            $table->string('id_materi', 50);
            $table->string('id_user', 50);
            $table->string('status', 30)->nullable();
            $table->date('tanggal')->nullable();
            $table->foreign('id_materi')->references('id_materi')->on('materis');
            $table->foreign('id_user')->references('id_user')->on('users');
        });

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
            'is_aktif' => false,
        ]);

        Storage::fake('public');
    }

    public function test_teacher_can_create_materi_with_a_pdf_upload(): void
    {
        $pdf = UploadedFile::fake()->create('algoritma.pdf', 32, 'application/pdf');

        $response = $this->actingAs($this->teacher(), 'sanctum')
            ->post('/api/guru/materi', [
                'id_mapel' => 'MP001',
                'judul' => 'Algoritma',
                'file_pdf' => $pdf,
            ], ['Accept' => 'application/json']);

        $response
            ->assertCreated()
            ->assertJsonPath('data.id_mapel', 'MP001')
            ->assertJsonPath('data.pdf_url', Storage::disk('public')->url('materi/'.$pdf->hashName()));

        $materi = Materi::findOrFail('M001');
        $this->assertSame('MP001', $materi->id_mapel);
        $this->assertStringStartsWith('materi/', $materi->isi_materi);
        Storage::disk('public')->assertExists($materi->isi_materi);
    }

    public function test_teacher_can_create_materi_with_all_web_form_fields(): void
    {
        $this->actingAs($this->teacher(), 'sanctum')
            ->postJson('/api/guru/materi', [
                'id_mapel' => 'MP001',
                'judul' => 'Materi Jaringan',
                'bab' => 'Bab 1',
                'kelas' => 'X RPL 1',
                'deskripsi' => 'Pengenalan jaringan komputer.',
                'link_youtube' => 'https://www.youtube.com/watch?v=abc123',
            ])
            ->assertCreated()
            ->assertJsonPath('data.id_mapel', 'MP001')
            ->assertJsonPath('data.judul', 'Materi Jaringan')
            ->assertJsonPath('data.bab', 'Bab 1')
            ->assertJsonPath('data.kelas', 'X RPL 1')
            ->assertJsonPath('data.deskripsi', 'Pengenalan jaringan komputer.')
            ->assertJsonPath('data.link_youtube', 'https://www.youtube.com/watch?v=abc123');
    }

    public function test_update_replaces_pdf_safely_and_preserves_it_when_no_new_file_is_sent(): void
    {
        Storage::disk('public')->put('materi/original.pdf', '%PDF-1.4 original');
        $materi = $this->createMateri(['isi_materi' => 'materi/original.pdf']);
        $replacement = UploadedFile::fake()->create('replacement.pdf', 48, 'application/pdf');

        $this->actingAs($this->teacher(), 'sanctum')
            ->post('/api/guru/materi/'.$materi->id_materi, [
                '_method' => 'PUT',
                'id_mapel' => 'MP001',
                'judul' => 'Perubahan tidak valid',
                'file_pdf' => UploadedFile::fake()->create('invalid.txt', 12, 'text/plain'),
            ], ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('file_pdf');

        $this->assertSame('Materi', $materi->fresh()->judul);
        Storage::disk('public')->assertExists('materi/original.pdf');

        $response = $this->actingAs($this->teacher(), 'sanctum')
            ->post('/api/guru/materi/'.$materi->id_materi, [
                '_method' => 'PUT',
                'id_mapel' => 'MP001',
                'judul' => 'Materi diperbarui',
                'file_pdf' => $replacement,
            ], ['Accept' => 'application/json']);

        $response->assertOk()->assertJsonPath('data.pdf_url', $materi->fresh()->pdfUrl());
        $materi->refresh();
        $this->assertNotSame('materi/original.pdf', $materi->isi_materi);
        Storage::disk('public')->assertMissing('materi/original.pdf');
        Storage::disk('public')->assertExists($materi->isi_materi);

        $savedPath = $materi->isi_materi;
        $this->actingAs($this->teacher(), 'sanctum')
            ->post('/api/guru/materi/'.$materi->id_materi, [
                '_method' => 'PUT',
                'id_mapel' => 'MP001',
                'judul' => 'Materi diperbarui lagi',
            ], ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJsonPath('data.pdf_url', $materi->pdfUrl());

        $this->assertSame($savedPath, $materi->fresh()->isi_materi);
        Storage::disk('public')->assertExists($savedPath);
    }

    public function test_api_requires_an_active_subject_and_rejects_invalid_pdf_uploads(): void
    {
        $teacher = $this->teacher();

        $this->actingAs($teacher, 'sanctum')
            ->post('/api/guru/materi', [
                'id_mapel' => 'MP002',
                'judul' => 'Mapel tidak aktif',
            ], ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('id_mapel');

        $this->actingAs($teacher, 'sanctum')
            ->post('/api/guru/materi', [
                'id_mapel' => 'MP001',
                'judul' => 'File bukan PDF',
                'file_pdf' => UploadedFile::fake()->create('materi.txt', 10, 'text/plain'),
            ], ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('file_pdf');

        $this->actingAs($teacher, 'sanctum')
            ->post('/api/guru/materi', [
                'id_mapel' => 'MP001',
                'judul' => 'File terlalu besar',
                'file_pdf' => UploadedFile::fake()->create('besar.pdf', 10241, 'application/pdf'),
            ], ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('file_pdf');

        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_teacher_can_create_materi_without_a_subject(): void
    {
        $this->actingAs($this->teacher(), 'sanctum')
            ->post('/api/guru/materi', [
                'judul' => 'Materi tanpa mapel',
            ], ['Accept' => 'application/json'])
            ->assertCreated()
            ->assertJsonPath('data.id_mapel', null)
            ->assertJsonPath('data.judul', 'Materi tanpa mapel');

        $this->assertDatabaseHas('materis', [
            'id_materi' => 'M001',
            'id_mapel' => null,
            'judul' => 'Materi tanpa mapel',
        ]);
    }

    public function test_api_returns_active_subjects_and_pdf_urls(): void
    {
        $materi = $this->createMateri();
        Storage::disk('public')->put('materi/materi.pdf', '%PDF-1.4');
        $materi->update(['isi_materi' => 'materi/materi.pdf']);

        $this->actingAs($this->teacher(), 'sanctum')
            ->getJson('/api/guru/mapel')
            ->assertOk()
            ->assertJsonPath('data.0.id_mapel', 'MP001')
            ->assertJsonPath('data.0.kode_mapel', 'TIK')
            ->assertJsonMissing(['id_mapel' => 'MP002']);

        $this->actingAs($this->teacher(), 'sanctum')
            ->getJson('/api/guru/materi')
            ->assertOk()
            ->assertJsonPath('data.0.pdf_url', $materi->pdfUrl());
    }

    public function test_student_materi_response_includes_the_pdf_url(): void
    {
        $materi = $this->createMateri([
            'link_youtube' => 'https://www.youtube.com/watch?v=abc123',
        ]);
        Storage::disk('public')->put('materi/materi.pdf', '%PDF-1.4');
        $materi->update(['isi_materi' => 'materi/materi.pdf']);
        $student = User::create([
            'id_user' => 'S001',
            'nomor_induk' => 'S001',
            'nama_lengkap' => 'Siswa',
            'role' => 'siswa',
            'status' => 'aktif',
            'password' => 'password',
        ]);

        $this->actingAs($student, 'sanctum')
            ->getJson('/api/siswa/materi')
            ->assertOk()
            ->assertJsonPath('data.0.pdf_url', $materi->pdfUrl())
            ->assertJsonPath('data.0.link_youtube', $materi->link_youtube);
    }

    private function teacher(): User
    {
        return User::firstOrCreate(
            ['id_user' => 'G001'],
            [
                'nomor_induk' => 'G001',
                'nama_lengkap' => 'Guru',
                'role' => 'guru',
                'status' => 'aktif',
                'password' => 'password',
            ]
        );
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function createMateri(array $attributes = []): Materi
    {
        return Materi::create(array_replace([
            'id_materi' => 'M001',
            'id_mapel' => 'MP001',
            'judul' => 'Materi',
            'id_user' => $this->teacher()->id_user,
        ], $attributes));
    }
}
