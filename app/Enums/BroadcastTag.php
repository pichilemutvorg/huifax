<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * Optional mark shown next to an airing on screen.
 */
enum BroadcastTag: string implements HasLabel
{
    case Live = 'live';
    case Premiere = 'premiere';
    case Rerun = 'rerun';

    public function getLabel(): string
    {
        // Same wording as on screen, where the display reads it from its UI strings.
        return __("display.ui.tags.{$this->value}");
    }
}
