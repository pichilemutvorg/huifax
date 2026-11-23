<?php

namespace App\Filament\Resources\Backgrounds;

use App\Filament\Forms\Components\PhotoUpload;
use App\Filament\NavigationGroup;
use App\Filament\Resources\Backgrounds\Pages\ManageBackgrounds;
use App\Filament\Resources\Concerns\HasSentenceCaseLabels;
use App\Models\Background;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use UnitEnum;

class BackgroundResource extends Resource
{
    use HasSentenceCaseLabels;

    protected static ?string $model = Background::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::Display;

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getModelLabel(): string
    {
        return __('admin.backgrounds.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.backgrounds.plural');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('admin.backgrounds.fields.name'))
                    ->required()
                    ->maxLength(60),
                PhotoUpload::make('path')
                    ->label(__('admin.backgrounds.fields.image'))
                    ->helperText(__('admin.backgrounds.help.image', ['size' => (int) (config('huifax.uploads.max_size') / 1024)]))
                    ->disk(Background::DISK)
                    ->directory(Background::DIRECTORY)
                    ->required(),
                Toggle::make('is_enabled')
                    ->label(__('admin.backgrounds.fields.is_enabled'))
                    ->helperText(__('admin.backgrounds.help.is_enabled'))
                    ->default(true),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->columns([
                ImageColumn::make('path')
                    ->label(__('admin.backgrounds.fields.image'))
                    ->disk(Background::DISK)
                    ->imageHeight(64),
                TextColumn::make('name')
                    ->label(__('admin.backgrounds.fields.name'))
                    ->searchable(),
                ToggleColumn::make('is_enabled')
                    ->label(__('admin.backgrounds.fields.is_enabled')),
            ])
            ->recordActions([
                EditAction::make(),
                // No bulk delete: deleting one by one fires the model event that removes the file.
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageBackgrounds::route('/'),
        ];
    }
}
