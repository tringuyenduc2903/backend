<?php

namespace App\Http\Controllers;

use App\Http\Requests\CartRequest;
use App\Models\Cart;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Collection
    {
        return $request
            ->user()
            ->carts()
            ->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CartRequest $request): JsonResponse
    {
        $request
            ->user()
            ->carts()
            ->create($request->validated());

        return response()->json('', 201);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CartRequest $request, string $cart_id): JsonResponse
    {
        $this
            ->show($cart_id, $request)
            ->update($request->validated());

        return response()->json('');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $cart_id, Request $request): Cart
    {
        return $request
            ->user()
            ->carts()
            ->findOrFail($cart_id);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $cart_id, Request $request): JsonResponse
    {
        $this
            ->show($cart_id, $request)
            ->delete();

        return response()->json('');
    }
}
