<?php

namespace App\Models;

use App\Models\Concerns\HasDisplayOrder;
use Database\Factories\NoticeFactory;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['title', 'body', 'starts_at', 'ends_at', 'duration', 'sort_order', 'is_enabled'])]
class Notice extends Model
{
    /** @use HasFactory<NoticeFactory> */
    use HasDisplayOrder, HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'duration' => 'integer',
            'sort_order' => 'integer',
            'is_enabled' => 'boolean',
        ];
    }

    /**
     * Notices whose publication window includes the given moment.
     * A missing start or end leaves that side of the window open.
     */
    #[Scope]
    protected function activeAt(Builder $query, DateTimeInterface $moment): void
    {
        $query
            ->where(fn (Builder $q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', $moment))
            ->where(fn (Builder $q) => $q->whereNull('ends_at')->orWhere('ends_at', '>', $moment));
    }
}
