<?php

namespace App\Http\Controllers;

use Illuminate\Pagination\LengthAwarePaginator;

abstract class Controller
{
    protected function getCustomPaginate(LengthAwarePaginator $paginator): array
    {
        return [
            'data' => $paginator->items(),
            'from' => $paginator->firstItem(),
            'to' => $paginator->lastItem(),
            'total' => $paginator->total(),
            'per_page' => $paginator->perPage(),
            'current_page' => $paginator->currentPage(),
            'total_pages' => $paginator->lastPage(),
        ];
    }
}
