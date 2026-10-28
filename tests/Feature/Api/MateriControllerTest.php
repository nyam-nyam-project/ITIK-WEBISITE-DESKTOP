<?php

namespace Tests\Feature\Api;

use App\Models\Materi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MateriControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_list_only_materials_for_their_class_and_general_materials(): void
    {
        $this->createTeacher();
        $student = $this->createStudent('S001', 'X');
        $this->createMateri('M001', 'Materi Kelas X', 'X');
        $this->createMateri('M002', 'Materi Kelas XI', 'XI');
        $this->createMateri('M003', 'Materi Umum', null);

        $this->actingAs($student, 'sanctum')
            ->getJson('/api/materi')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonFragment(['id_materi' => 'M001'])
            ->assertJsonFragment(['id_materi' => 'M003'])
            ->assertJsonMissing(['id_materi' => 'M002']);
    }

    public function test_student_cannot_access_material_for_another_class(): void
    {
        $this->createTeacher();
        $student = $this->createStudent('S001', 'X');
        $this->createMateri('M001', 'Materi Kelas XI', 'XI');

        $this->actingAs($student, 'sanctum')
            ->getJson('/api/materi/M001')
            ->assertNotFound();
    }

    public function test_opening_material_creates_progress_for_the_authenticated_student(): void
    {
        $this->createTeacher();
        $student = $this->createStudent('S001', 'X');
        $this->createMateri('M001', 'Materi Kelas X', 'X');

        $this->actingAs($student, 'sanctum')
            ->getJson('/api/materi/M001')
            ->assertOk()
            ->assertJsonPath('data.id_materi', 'M001')
            ->assertJsonPath('data.status_progress', 'sedang belajar');

        $this->assertDatabaseHas('progress', [
            'id_materi' => 'M001',
            'id_user' => 'S001',
            'status' => 'sedang belajar',
        ]);
    }

    public function test_student_can_mark_visible_material_as_complete(): void
    {
        $this->createTeacher();
        $student = $this->createStudent('S001', 'X');
        $this->createMateri('M001', 'Materi Kelas X', 'X');

        $this->actingAs($student, 'sanctum')
            ->postJson('/api/materi/M001/selesai')
            ->assertOk()
            ->assertJsonPath('data.status_progress', 'selesai');

        $this->assertDatabaseHas('progress', [
            'id_materi' => 'M001',
            'id_user' => 'S001',
            'status' => 'selesai',
        ]);
    }

    public function test_non_student_role_cannot_access_material_api(): void
    {
        $teacher = $this->createTeacher();

        $this->actingAs($teacher, 'sanctum')
            ->getJson('/api/materi')
            ->assertForbidden();
    }

    public function test_material_api_requires_authentication(): void
    {
        $this->getJson('/api/materi')->assertUnauthorized();
    }

    private function createStudent(string $id, string $kelas): User
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

    private function createMateri(string $id, string $judul, ?string $kelas): Materi
    {
        return Materi::create([
            'id_materi' => $id,
            'judul' => $judul,
            'kelas' => $kelas,
            'deskripsi' => 'Deskripsi '.$judul,
            'id_user' => 'G001',
        ]);
    }

    private function createTeacher(): User
    {
        return User::create([
            'id_user' => 'G001',
            'nomor_induk' => 'G00100',
            'nama_lengkap' => 'Guru Pemilik Materi',
            'role' => 'guru',
            'email' => 'pemilik@example.com',
            'password' => 'password',
        ]);
    }
}
