<?php

namespace App\Filament;

use Filament\Support\Contracts\HasLabel;

/**
 * Admin panel navigation groups, in display order.
 */
enum NavigationGroup implements HasLabel
{
    case Content;
    case Schedule;
    case Display;
    case Settings;

    public function getLabel(): string
    {
        return __('admin.navigation.'.strtolower($this->name));
    }
}
