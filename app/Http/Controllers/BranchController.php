<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): array
    {
        $branches = Branch::query();

        foreach (['province_id', 'district_id', 'ward_id'] as $column) {
            if ($request->exists($column)) {
                $branches->where($column, request($column));
            }
        }

        $paginator = $branches->paginate(request('perPage'));

        return $this->getCustomPaginate($paginator);
    }

    /**
     * Display the specified resource.
     */
    public function show(Branch $branch): Branch
    {
        return $branch;
    }
}
