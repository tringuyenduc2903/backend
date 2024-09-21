<?php

namespace App\Observers;

use App\Models\Customer;
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
        if ($review->reviewable_type === Customer::class) {
            $review->parent_type = Option::class;
        } else {
            /** @var Employee $employee */
            $employee = backpack_user();

            $review->reviewable_id = $employee->id;
            $review->reviewable_type = Employee::class;
        }
    }

    /**
     * Handle the Review "updating" event.
     */
    public function updating(Review $review): void
    {
        if ($review->reviewable_type === Customer::class) {
            return;
        }

        /** @var Employee $employee */
        $employee = backpack_user();

        $review->reviewable_id = $employee->id;
    }
}
