<?php

namespace App\Filament\Pages;

use App\Filament\Pages\Concerns\AdminOnlySettingsPage;
use App\Settings\BrandingSettings;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Storage;

class ManageBranding extends SettingsPage
{
    use AdminOnlySettingsPage;

    protected static string $settings = BrandingSettings::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSparkles;

    protected static ?int $navigationSort = 1;

    public static function getNavigationLabel(): string
    {
        return __('admin.settings.branding.title');
    }

    public function getTitle(): string
    {
        return __('admin.settings.branding.title');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label(__('admin.settings.branding.fields.title'))
                    ->helperText(__('admin.settings.branding.help.title'))
                    ->required()
                    ->maxLength(60),
                TextInput::make('tagline')
                    ->label(__('admin.settings.branding.fields.tagline'))
                    ->helperText(__('admin.settings.branding.help.tagline'))
                    ->maxLength(120),
                FileUpload::make('logo_path')
                    ->label(__('admin.settings.branding.fields.logo'))
                    ->helperText(__('admin.settings.branding.help.logo'))
                    ->image()
                    ->acceptedFileTypes(['image/png', 'image/svg+xml', 'image/webp'])
                    ->disk('public')
                    ->directory('branding')
                    ->maxSize(2048),
            ])
            ->columns(1);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Remove the previous logo file when it is replaced or cleared.
        $previous = app(BrandingSettings::class)->logo_path;

        if ($previous && $previous !== ($data['logo_path'] ?? null)) {
            Storage::disk('public')->delete($previous);
        }

        return $data;
    }
}
