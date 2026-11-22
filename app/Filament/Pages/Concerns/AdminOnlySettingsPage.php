<?php

namespace App\Filament\Pages\Concerns;

use App\Filament\NavigationGroup;
use UnitEnum;

/**
 * Settings pages are grouped under "Settings" and limited to administrators.
 */
trait AdminOnlySettingsPage
{
    public static function canAccess(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return NavigationGroup::Settings;
    }
}
