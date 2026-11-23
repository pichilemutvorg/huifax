<?php

namespace App\Filament\Pages;

use App\Filament\Pages\Concerns\AdminOnlySettingsPage;
use App\Settings\DisplaySettings;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ManageDisplay extends SettingsPage
{
    use AdminOnlySettingsPage;

    protected static string $settings = DisplaySettings::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTv;

    protected static ?int $navigationSort = 2;

    public static function getNavigationLabel(): string
    {
        return __('admin.settings.display.title');
    }

    public function getTitle(): string
    {
        return __('admin.settings.display.title');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('theme')
                    ->label(__('admin.settings.display.fields.theme'))
                    ->helperText(__('admin.settings.display.help.theme'))
                    ->options(fn (): array => collect(config('huifax.themes'))
                        ->mapWithKeys(fn (string $theme) => [$theme => __("admin.themes.{$theme}")])
                        ->all())
                    ->required()
                    ->selectablePlaceholder(false),
                TextInput::make('poll_interval')
                    ->label(__('admin.settings.display.fields.poll_interval'))
                    ->helperText(__('admin.settings.display.help.poll_interval'))
                    ->required()
                    ->integer()
                    ->minValue(10)
                    ->maxValue(3600),
                TextInput::make('background_interval')
                    ->label(__('admin.settings.display.fields.background_interval'))
                    ->required()
                    ->integer()
                    ->minValue(10)
                    ->maxValue(3600),
                TextInput::make('reload_at')
                    ->label(__('admin.settings.display.fields.reload_at'))
                    ->helperText(__('admin.settings.display.help.reload_at'))
                    ->type('time')
                    ->regex('/^([01]\d|2[0-3]):[0-5]\d$/'),
            ])
            ->columns(1);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['reload_at'] = filled($data['reload_at'] ?? null) ? $data['reload_at'] : null;

        return $data;
    }
}
