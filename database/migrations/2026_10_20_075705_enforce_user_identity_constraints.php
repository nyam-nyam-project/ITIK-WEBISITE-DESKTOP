<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $hasMissingIdentity = DB::table('users')
            ->where(function (Builder $query): void {
                $query
                    ->whereNull('nomor_induk')
                    ->orWhereRaw('TRIM(nomor_induk) = ?', [''])
                    ->orWhereNull('email')
                    ->orWhereRaw('TRIM(email) = ?', ['']);
            })
            ->exists();

        if ($hasMissingIdentity) {
            throw new RuntimeException(
                'Cannot enforce user identity constraints: each user must have a non-empty email and student number.'
            );
        }

        $hasDuplicateEmails = DB::table('users')
            ->select('email')
            ->groupBy('email')
            ->havingRaw('COUNT(*) > 1')
            ->exists();

        if ($hasDuplicateEmails) {
            throw new RuntimeException(
                'Cannot enforce user identity constraints: duplicate email addresses must be resolved first.'
            );
        }

        Schema::table('users', function (Blueprint $table) {
            $table->string('nomor_induk', 50)->nullable(false)->change();
            $table->string('email', 100)->nullable(false)->change();
        });

        if (! Schema::hasIndex('users', ['email'], 'unique')) {
            Schema::table('users', function (Blueprint $table) {
                $table->unique('email');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasIndex('users', ['email'], 'unique')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropUnique(['email']);
            });
        }

        Schema::table('users', function (Blueprint $table) {
            $table->string('nomor_induk', 50)->nullable()->change();
            $table->string('email', 100)->nullable()->change();
        });
    }
};
