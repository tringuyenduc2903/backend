<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddressRequest;
use App\Models\Address;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Collection
    {
        return $request
            ->user()
            ->addresses()
            ->latest()
            ->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(AddressRequest $request): JsonResponse
    {
        $request
            ->user()
            ->addresses()
            ->create($request->validated());

        return response()->json('', 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $address_id, Request $request): Address
    {
        return $request
            ->user()
            ->addresses()
            ->findOrFail($address_id);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(AddressRequest $request, string $address_id): JsonResponse
    {
        $request
            ->user()
            ->addresses()
            ->findOrFail($address_id)
            ->update($request->validated());

        return response()->json('');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $address_id, Request $request): JsonResponse
    {
        $address = $request
            ->user()
            ->addresses()
            ->findOrFail($address_id);

        if ($address->default) {
            abort(
                403,
                trans('Default :name cannot be deleted.', ['name' => trans('address')])
            );
        }

        $address->delete();

        return response()->json('');
    }
}
