<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        // Initial values come from the environment; afterwards they are edited in the panel.
        $this->migrator->add('branding.title', config('huifax.branding.title', 'HUIFAX'));
        $this->migrator->add('branding.tagline', config('huifax.branding.tagline'));
        $this->migrator->add('branding.logo_path', null);
    }
};
