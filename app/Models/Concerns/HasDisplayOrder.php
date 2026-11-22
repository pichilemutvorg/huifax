<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;

/**
 * Shared scopes for content that can be toggled and ordered from the admin panel.
 *
 * Expects `is_enabled` and `sort_order` columns.
 */
trait HasDisplayOrder
{
    /**
     * New records go to the end of the list unless a position is given.
     */
    protected static function bootHasDisplayOrder(): void
    {
        static::creating(function ($model) {
            if ($model->sort_order === null) {
                $model->sort_order = (int) static::query()->max('sort_order') + 1;
            }
        });
    }

    /**
     * Only records enabled in the admin panel.
     */
    #[Scope]
    protected function enabled(Builder $query): void
    {
        $query->where('is_enabled', true);
    }

    /**
     * Order by the position set in the admin panel.
     */
    #[Scope]
    protected function ordered(Builder $query): void
    {
        $query->orderBy('sort_order')->orderBy('id');
    }
}
