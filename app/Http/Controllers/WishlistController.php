<?php

namespace App\Http\Controllers;

use App\Http\Requests\WishlistRequest;
use App\Models\Wishlist;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Collection
    {
        return $request
            ->user()
            ->wishlists()
            ->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(WishlistRequest $request): JsonResponse
    {
        $request
            ->user()
            ->wishlists()
            ->create($request->validated());

        return response()->json('', 201);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $wishlist_id, Request $request): JsonResponse
    {
        $this
            ->show($wishlist_id, $request)
            ->delete();

        return response()->json('');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $wishlist_id, Request $request): Wishlist
    {
        return $request
            ->user()
            ->wishlists()
            ->findOrFail($wishlist_id);
    }
}
