<?php

namespace App\Http\Controllers;

use App\Models\Social;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SocialController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Collection
    {
        return $request
            ->user()
            ->socials()
            ->latest()
            ->get();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $social_id, Request $request): JsonResponse
    {
        $this
            ->show($social_id, $request)
            ->delete();

        return response()->json('');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $social_id, Request $request): Social
    {
        return $request
            ->user()
            ->socials()
            ->findOrFail($social_id);
    }
}
