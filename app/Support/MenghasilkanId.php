<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

trait MenghasilkanId
{
    /**
     * Ambil nomor urut tertinggi yang sudah dipakai, lalu tambah satu.
     * Pakai MAX, bukan COUNT, supaya ID tidak bentrok setelah ada baris terhapus.
     */
    protected function idBerikutnya(
        string $tabel,
        string $kolom,
        string $prefix,
        int $panjang = 3
    ): string {
        foreach ([$tabel, $kolom] as $identifier) {
            if (! preg_match('/^[a-z_][a-z0-9_]*$/i', $identifier)) {
                throw new InvalidArgumentException("Identifier tidak valid: {$identifier}");
            }
        }

        $awalPotongan = strlen($prefix) + 1;

        $terakhir = (int) DB::table($tabel)
            ->where($kolom, 'like', $prefix.'%')
            ->selectRaw(
                "MAX(CAST(SUBSTRING(`{$kolom}`, {$awalPotongan}) AS UNSIGNED)) AS nomor"
            )
            ->value('nomor');

        return $prefix.str_pad((string) ($terakhir + 1), $panjang, '0', STR_PAD_LEFT);
    }
}
