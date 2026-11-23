<?php

namespace App\Models;

use App\Models\Concerns\HasDisplayOrder;
use App\Models\Concerns\HasPublicationWindow;
use Database\Factories\NoticeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['title', 'body', 'starts_at', 'ends_at', 'duration', 'sort_order', 'is_enabled'])]
class Notice extends Model
{
    /** @use HasFactory<NoticeFactory> */
    use HasDisplayOrder, HasFactory, HasPublicationWindow;

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
}
