<?php

namespace App\Console\Commands;

use App\Models\Kuis;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:publish-scheduled-quizzes')]
#[Description('Publish quizzes whose scheduled publication time has arrived')]
class PublishScheduledQuizzes extends Command
{
    public function handle(): int
    {
        $publishedCount = Kuis::query()
            ->where('status_publikasi', 'terjadwal')
            ->where('is_aktif', true)
            ->whereNotNull('waktu_publish')
            ->where('waktu_publish', '<=', now())
            ->update([
                'status_publikasi' => 'terbit',
            ]);

        $this->info("Published {$publishedCount} scheduled quiz(es).");

        return self::SUCCESS;
    }
}
