<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReviewRequest;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewCustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): array
    {
        $reviews = fortify_user()->reviews();

        $paginator = $reviews->paginate(request('perPage'));

        $paginator->makeHidden('customer');

        return $this->getCustomPaginate($paginator);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ReviewRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $validated['parent_id'] = $validated['option_id'];
        $validated['images'] = json_encode($validated['images'], JSON_UNESCAPED_UNICODE);

        unset($validated['option_id']);

        $request
            ->user()
            ->reviews()
            ->create($validated);

        return response()->json('', 201);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ReviewRequest $request, string $review_id): JsonResponse
    {
        $validated = $request->validated();

        $validated['images'] = json_encode($validated['images'], JSON_UNESCAPED_UNICODE);

        $request
            ->user()
            ->reviews()
            ->findOrFail($review_id)
            ->update($validated);

        return response()->json('');

    }

    /**
     * Display the specified resource.
     */
    public function show(string $review_id, Request $request): Review
    {
        return $request
            ->user()
            ->reviews()
            ->with([
                'reply',
                'reply.employee',
                'option',
                'option.product',
            ])
            ->findOrFail($review_id);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $review_id, Request $request): JsonResponse
    {
        $request
            ->user()
            ->reviews()
            ->findOrFail($review_id)
            ->delete();

        return response()->json('');
    }
}
