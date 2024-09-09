<?php

namespace App\Http\Controllers;

use App\Models\District;

class WardController extends Controller
{
    /**
     * Display the specified resource.
     */
    public function show(District $ward): array
    {
        return $this->getCustomPaginate(
            $ward->wards()->paginate(request('perPage'))
        );
    }
}
