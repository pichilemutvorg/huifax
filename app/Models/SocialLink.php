<?php

namespace App\Models;

use App\Enums\SocialPlatform;
use App\Models\Concerns\HasDisplayOrder;
use Database\Factories\SocialLinkFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['platform', 'handle', 'url', 'sort_order', 'is_enabled'])]
class SocialLink extends Model
{
    /** @use HasFactory<SocialLinkFactory> */
    use HasDisplayOrder, HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'platform' => SocialPlatform::class,
            'sort_order' => 'integer',
            'is_enabled' => 'boolean',
        ];
    }
}
