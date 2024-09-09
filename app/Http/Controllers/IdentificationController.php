<?php

namespace App\Http\Controllers;

use App\Http\Requests\IdentificationRequest;
use App\Models\Identification;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IdentificationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Collection
    {
        return $request
            ->user()
            ->identifications()
            ->latest()
            ->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(IdentificationRequest $request): JsonResponse
    {
        $request
            ->user()
            ->identifications()
            ->create($request->validated());

        return response()->json('', 201);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(IdentificationRequest $request, string $identification_id): JsonResponse
    {
        $this
            ->show($identification_id, $request)
            ->update($request->validated());

        return response()->json('');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $identification_id, Request $request): Identification
    {
        return $request
            ->user()
            ->identifications()
            ->findOrFail($identification_id);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $identification_id, Request $request): JsonResponse
    {
        $identification = $this->show($identification_id, $request);

        if ($identification->default) {
            abort(
                403,
                trans('Default :name cannot be deleted.', ['name' => trans('identification')])
            );
        }

        $identification->delete();

        return response()->json('');
    }
}
