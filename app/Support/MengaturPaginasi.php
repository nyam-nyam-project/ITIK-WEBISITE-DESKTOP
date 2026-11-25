<?php

namespace App\Support;

use Illuminate\Http\Request;

trait MengaturPaginasi
{
    protected function batasPerHalaman(Request $request): int
    {
        $perPage = (int) $request->query('per_page', 25);

        return in_array($perPage, [10, 25, 50], true) ? $perPage : 25;
    }
}
