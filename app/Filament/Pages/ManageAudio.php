<?php

namespace App\Filament\Pages;

use App\Filament\Pages\Concerns\AdminOnlySettingsPage;
use App\Settings\AudioSettings;
use BackedEnum;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ManageAudio extends SettingsPage
{
    use AdminOnlySettingsPage;

    protected static string $settings = AudioSettings::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSpeakerWave;

    protected static ?int $navigationSort = 3;

    public static function getNavigationLabel(): string
    {
        return __('admin.settings.audio.title');
    }

    public function getTitle(): string
    {
        return __('admin.settings.audio.title');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Toggle::make('enabled')
                    ->label(__('admin.settings.audio.fields.enabled'))
                    ->helperText(__('admin.settings.audio.help.enabled'))
                    ->live(),
                TextInput::make('stream_url')
                    ->label(__('admin.settings.audio.fields.stream_url'))
                    ->url()
                    ->maxLength(500)
                    ->required(fn (Get $get): bool => (bool) $get('enabled')),
                TextInput::make('volume')
                    ->label(__('admin.settings.audio.fields.volume'))
                    ->required()
                    ->integer()
                    ->minValue(0)
                    ->maxValue(100),
            ])
            ->columns(1);
    }
}
