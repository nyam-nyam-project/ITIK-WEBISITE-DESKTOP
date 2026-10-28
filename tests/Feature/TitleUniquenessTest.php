<?php

namespace Tests\Feature;

use App\Models\Kuis;
use App\Models\Materi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TitleUniquenessTest extends TestCase
{
    use RefreshDatabase;

    public function test_material_title_must_be_unique_when_creating(): void
    {
        $user = $this->createGuru();

        Materi::create([
            'id_materi' => 'M001',
            'judul' => 'Pengenalan HTML',
            'id_user' => $user->id_user,
        ]);

        $response = $this->actingAs($user)->post(route('materi.store'), [
            'judul' => 'Pengenalan HTML',
        ]);

        $response->assertInvalid([
            'judul' => 'Judul materi sudah digunakan.',
        ]);
        $this->assertDatabaseCount('materis', 1);
    }

    public function test_material_can_keep_its_title_when_updating(): void
    {
        $user = $this->createGuru();
        $materi = Materi::create([
            'id_materi' => 'M001',
            'judul' => 'Pengenalan HTML',
            'id_user' => $user->id_user,
        ]);

        $response = $this->actingAs($user)->put(
            route('materi.update', $materi->id_materi),
            ['judul' => 'Pengenalan HTML'],
        );

        $response->assertRedirect(route('materi.index'));
        $this->assertDatabaseCount('materis', 1);
    }

    public function test_quiz_title_must_be_unique_when_creating(): void
    {
        $user = $this->createGuru();

        Kuis::create([
            'id_kuis' => 'K001',
            'judul' => 'Kuis HTML',
            'kategori' => 'post-test',
            'id_user' => $user->id_user,
            'status_publikasi' => 'draft',
        ]);

        $response = $this->actingAs($user)->post(route('kuis.store'), [
            'judul' => 'Kuis HTML',
            'kategori' => 'post-test',
        ]);

        $response->assertInvalid([
            'judul' => 'Judul kuis sudah digunakan.',
        ]);
        $this->assertDatabaseCount('kuis', 1);
    }

    public function test_quiz_can_keep_its_title_when_updating(): void
    {
        $user = $this->createGuru();
        $kuis = Kuis::create([
            'id_kuis' => 'K001',
            'judul' => 'Kuis HTML',
            'kategori' => 'post-test',
            'id_user' => $user->id_user,
            'status_publikasi' => 'draft',
        ]);

        $response = $this->actingAs($user)->put(
            route('kuis.update', $kuis->id_kuis),
            [
                'judul' => 'Kuis HTML',
                'kategori' => 'post-test',
            ],
        );

        $response->assertRedirect(route('kuis.index'));
        $this->assertDatabaseCount('kuis', 1);
    }

    public function test_quiz_can_reference_an_existing_material(): void
    {
        $user = $this->createGuru();
        $materi = Materi::create([
            'id_materi' => 'M001',
            'judul' => 'Pengenalan HTML',
            'id_user' => $user->id_user,
        ]);

        $response = $this->actingAs($user)->post(route('kuis.store'), [
            'judul' => 'Kuis HTML',
            'kategori' => 'post-test',
            'materi' => [$materi->id_materi],
        ]);

        $response->assertRedirect(route('kuis.index'));
        $this->assertDatabaseHas('materi_kuis', [
            'id_materi' => $materi->id_materi,
        ]);
    }

    private function createGuru(): User
    {
        return User::create([
            'id_user' => 'G001',
            'nomor_induk' => 'G00101',
            'email' => 'guru_test@example.com',
            'nama_lengkap' => 'Guru Test',
            'role' => 'guru',
            'password' => 'password',
        ]);
    }
}
