<?php

namespace App\Models\Concerns;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;

/**
 * Content shown only within an optional publication window.
 *
 * Expects nullable `starts_at` / `ends_at` datetime columns and an
 * `is_enabled` column. A missing start or end leaves that side open.
 */
trait HasPublicationWindow
{
    /**
     * Publication status at the given moment: active, scheduled, expired or disabled.
     */
    public function statusAt(DateTimeInterface $moment): string
    {
        return match (true) {
            ! $this->is_enabled => 'disabled',
            $this->starts_at !== null && $this->starts_at > $moment => 'scheduled',
            $this->ends_at !== null && $this->ends_at <= $moment => 'expired',
            default => 'active',
        };
    }

    /**
     * Records whose publication window includes the given moment.
     */
    #[Scope]
    protected function activeAt(Builder $query, DateTimeInterface $moment): void
    {
        $query
            ->where(fn (Builder $q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', $moment))
            ->where(fn (Builder $q) => $q->whereNull('ends_at')->orWhere('ends_at', '>', $moment));
    }
}
