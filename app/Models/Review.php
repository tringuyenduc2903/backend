<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use RalphJSmit\Helpers\Laravel\Concerns\HasFactory;

class Review extends Model
{
    use CrudTrait;
    use HasFactory;
    use SwitchTimezoneTrait;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'reviewable_id',
        'reviewable_type',
        'parent_id',
        'parent_type',
        'content',
        'rate',
        'images',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'reviewable_id',
        'reviewable_type',
        'parent_id',
        'parent_type',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function reviewable(): MorphTo
    {
        return $this->morphTo();
    }

    public function parent(): MorphTo
    {
        return $this->morphTo();
    }

    public function reply(): MorphOne
    {
        return $this->morphOne(Review::class, 'parent')
            ->where('reviewable_type', Employee::class)
            ->where('parent_type', Review::class);
    }

    public function customer(): BelongsTo
    {
        return $this
            ->belongsTo(
                Customer::class,
                'reviewable_id'
            )
            ->select([
                'id',
                'name',
                'gender',
                'timezone',
                'deleted_at',
                'created_at',
                'updated_at',
            ]);
    }

    public function employee(): BelongsTo
    {
        return $this
            ->belongsTo(
                Employee::class,
                'reviewable_id'
            )
            ->select([
                'id',
                'name',
                'deleted_at',
                'created_at',
                'updated_at',
            ]);
    }

    public function option(): BelongsTo
    {
        return $this->belongsTo(Option::class, 'parent_id');
    }

    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */

    protected function getImagesAttribute(string|array|null $images = null): string|array|null
    {
        if (backpack_auth()->check()) {
            return $images;
        }

        $items = json_decode($images);

        foreach ($items as &$item) {
            $item = image_preview(
                review_image_url($item ?? ''),
                match ($this->reviewable_type) {
                    Customer::class => $this->customer->name,
                    Employee::class => $this->employee->name,
                    default => '',
                }
            );
        }

        return array_values($items);
    }
}
