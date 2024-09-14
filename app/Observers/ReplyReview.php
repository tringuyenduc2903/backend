<?php

namespace App\Observers;

use App\Models\Employee;
use App\Models\Option;
use App\Models\Review;

class ReplyReview
{
    /**
     * Handle the Review "creating" event.
     */
    public function creating(Review $review): void
    {
        if (fortify_auth()->check()) {
            $review->parent_type = Option::class;

            return;
        } elseif (backpack_auth()->guest()) {
            return;
        }

        /** @var Employee $employee */
        $employee = backpack_user();

        $review->reviewable_id = $employee->id;
        $review->reviewable_type = Employee::class;
    }

    /**
     * Handle the Review "updating" event.
     */
    public function updating(Review $review): void
    {
        if (backpack_auth()->guest() || fortify_auth()->check()) {
            return;
        }

        /** @var Employee $employee */
        $employee = backpack_user();

        $review->reviewable_id = $employee->id;
    }
}
