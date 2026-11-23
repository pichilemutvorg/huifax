<?php

namespace App\Filament\Pages;

use App\Filament\NavigationGroup;
use App\Settings\DisplaySettings;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

/**
 * Overview of the rotation as a grid of slide thumbnails, in order and with
 * their timing. Any theme can be previewed without changing the display.
 */
class DisplayPreview extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::Display;

    protected static ?int $navigationSort = 0;

    protected string $view = 'filament.pages.display-preview';

    public string $theme = '';

    /** Changes on "refresh" so the frame reloads with current content. */
    public int $revision = 0;

    public function mount(DisplaySettings $settings): void
    {
        $this->theme = $settings->resolvedTheme();
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.preview.title');
    }

    public function getTitle(): string
    {
        return __('admin.preview.title');
    }

    public function refresh(): void
    {
        $this->revision++;
    }

    /**
     * @return array<string, string>
     */
    public function themeOptions(): array
    {
        return collect(config('huifax.themes'))
            ->mapWithKeys(fn (string $theme) => [$theme => __("admin.themes.{$theme}")])
            ->all();
    }

    public function frameUrl(): string
    {
        $theme = in_array($this->theme, config('huifax.themes'), true) ? $this->theme : null;

        return route('filament.admin.preview.frame', array_filter(['theme' => $theme, 'r' => $this->revision]));
    }
}
