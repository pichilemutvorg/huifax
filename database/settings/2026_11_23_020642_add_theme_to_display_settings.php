<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('display.theme', config('huifax.display.theme', 'huifax'));
    }

    public function down(): void
    {
        $this->migrator->delete('display.theme');
    }
};
