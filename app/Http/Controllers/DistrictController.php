<?php

namespace App\Http\Controllers;

use App\Models\Province;

class DistrictController extends Controller
{
    /**
     * Display the specified resource.
     */
    public function show(Province $district): array
    {
        return $this->getCustomPaginate(
            $district->districts()->paginate(request('perPage'))
        );
    }
}
