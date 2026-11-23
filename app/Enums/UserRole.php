<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum UserRole: string implements HasLabel
{
    /** Everything: content, slides, backgrounds, settings and users. */
    case Admin = 'admin';

    /** Content only: notices, featured programs, emergency contacts and social links. */
    case Editor = 'editor';

    public function getLabel(): string
    {
        return __("admin.roles.{$this->value}");
    }
}
