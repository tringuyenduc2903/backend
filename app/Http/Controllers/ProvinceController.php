<?php

namespace App\Http\Controllers;

use App\Models\Province;

class ProvinceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): array
    {
        return $this->getCustomPaginate(
            Province::paginate(request('perPage'))
        );
    }
}
