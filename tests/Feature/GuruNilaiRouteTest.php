<?php

namespace Tests\Feature;

use Tests\TestCase;

class GuruNilaiRouteTest extends TestCase
{
    public function test_web_and_api_guru_nilai_routes_have_distinct_names(): void
    {
        $this->assertSame('/guru/nilai', route('guru.nilai.index', [], false));
        $this->assertSame('/guru/nilai/123', route('guru.nilai.show', ['mengerjakan' => '123'], false));
        $this->assertSame('/guru/nilai/123/validasi', route('guru.nilai.validasi', ['mengerjakan' => '123'], false));

        $this->assertSame('/api/guru/nilai', route('api.guru.nilai.index', [], false));
        $this->assertSame('/api/guru/nilai/123', route('api.guru.nilai.show', ['mengerjakan' => '123'], false));
        $this->assertSame('/api/guru/nilai/123/validasi', route('api.guru.nilai.validasi', ['mengerjakan' => '123'], false));
    }
}
