<?php

namespace App\Filament\Resources\Concerns;

use Illuminate\Support\Str;

/**
 * Spanish (like most languages other than English) does not capitalize every
 * word in titles: "Números de emergencia", not "Números De Emergencia".
 */
trait HasSentenceCaseLabels
{
    public static function getTitleCaseModelLabel(): string
    {
        return Str::ucfirst(static::getModelLabel());
    }

    public static function getTitleCasePluralModelLabel(): string
    {
        return Str::ucfirst(static::getPluralModelLabel());
    }
}
