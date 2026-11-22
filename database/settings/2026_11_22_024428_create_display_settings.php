<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        // Initial values come from the environment; afterwards they are edited in the panel.
        $this->migrator->add('display.poll_interval', (int) config('huifax.display.poll_interval', 30));
        $this->migrator->add('display.reload_at', config('huifax.display.reload_at', '05:00'));
        $this->migrator->add('display.background_interval', 60);
    }
};
