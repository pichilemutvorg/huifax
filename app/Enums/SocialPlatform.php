<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum SocialPlatform: string implements HasLabel
{
    case Facebook = 'facebook';
    case Instagram = 'instagram';
    case X = 'x';
    case YouTube = 'youtube';
    case WhatsApp = 'whatsapp';
    case TikTok = 'tiktok';
    case Threads = 'threads';
    case Bluesky = 'bluesky';
    case Mastodon = 'mastodon';
    case Website = 'website';

    /**
     * Human-readable platform name, shown on screen next to the handle.
     */
    public function label(): string
    {
        return match ($this) {
            self::Facebook => 'Facebook',
            self::Instagram => 'Instagram',
            self::X => 'X',
            self::YouTube => 'YouTube',
            self::WhatsApp => 'WhatsApp',
            self::TikTok => 'TikTok',
            self::Threads => 'Threads',
            self::Bluesky => 'Bluesky',
            self::Mastodon => 'Mastodon',
            self::Website => __('display.social_platforms.website'),
        };
    }

    public function getLabel(): string
    {
        return $this->label();
    }
}
