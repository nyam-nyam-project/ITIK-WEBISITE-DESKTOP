<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ViewSeparationTest extends TestCase
{
    public function test_blade_views_contain_no_embedded_styles_scripts_or_event_handlers(): void
    {
        foreach (File::allFiles(resource_path('views')) as $file) {
            if (! str_ends_with($file->getFilename(), '.blade.php')) {
                continue;
            }

            $contents = $file->getContents();
            $this->assertDoesNotMatchRegularExpression('/<style\b|\s(?:style|on[a-z]+)\s*=/i', $contents, $file->getRelativePathname());
            preg_match_all('/<script\b[^>]*>([\s\S]*?)<\/script>/i', $contents, $scripts);
            foreach ($scripts[1] as $script) {
                $this->assertSame('', trim($script), $file->getRelativePathname());
            }
        }
    }

    public function test_extracted_assets_contain_no_blade_expressions(): void
    {
        foreach (['css', 'js'] as $directory) {
            $files = File::allFiles(public_path($directory));
            $this->assertNotEmpty($files);
            foreach ($files as $file) {
                $this->assertDoesNotMatchRegularExpression('/\{\{|\{!!|@php\b|@endif\b/', $file->getContents(), $file->getRelativePathname());
            }
        }
    }

    public function test_local_stylesheet_and_script_references_resolve(): void
    {
        $references = [];
        foreach (File::allFiles(resource_path('views')) as $file) {
            preg_match_all('/asset\([\'"]((?:css|js)\/[^\'"]+)[\'"]\)/', $file->getContents(), $matches);
            foreach ($matches[1] as $asset) {
                $references[$asset] = true;
            }
        }

        $this->assertNotEmpty($references);
        foreach (array_keys($references) as $asset) {
            $this->assertFileExists(public_path($asset));
        }
    }

    public function test_teacher_views_are_grouped_by_role_and_profile_is_shared(): void
    {
        foreach (['kuis', 'materi', 'soal', 'remedial'] as $directory) {
            $this->assertDirectoryDoesNotExist(resource_path('views/'.$directory));
            foreach (['index', 'create', 'edit', 'show'] as $page) {
                if ($directory === 'remedial' && $page === 'edit') {
                    continue;
                }
                $this->assertTrue(view()->exists('guru.'.$directory.'.'.$page));
            }
        }

        $this->assertTrue(view()->exists('guru.remedial.siswa'));
        $this->assertTrue(view()->exists('shared.profil'));
    }
}
