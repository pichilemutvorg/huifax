<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        // Audio is played by the playout system (e.g. CasparCG) unless enabled here.
        $this->migrator->add('audio.enabled', false);
        $this->migrator->add('audio.stream_url', null);
        $this->migrator->add('audio.volume', 80);
    }
};
