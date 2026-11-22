<?php

namespace App\Filament\Resources\Slides;

use App\Display\SlideTypeRegistry;
use App\Display\SlideTypes\SlideType;
use App\Filament\NavigationGroup;
use App\Filament\Resources\Concerns\HasSentenceCaseLabels;
use App\Filament\Resources\Slides\Pages\ManageSlides;
use App\Models\Slide;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use UnitEnum;

class SlideResource extends Resource
{
    use HasSentenceCaseLabels;

    protected static ?string $model = Slide::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::Display;

    protected static ?int $navigationSort = 1;

    public static function getModelLabel(): string
    {
        return __('admin.slides.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.slides.plural');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('type')
                    ->label(__('admin.slides.fields.type'))
                    ->options(fn (): array => array_map(fn (SlideType $type) => $type->label(), self::types()->all()))
                    ->required()
                    ->live()
                    ->afterStateUpdated(fn (Set $set) => $set('settings', [])),
                TextInput::make('name')
                    ->label(__('admin.slides.fields.name'))
                    ->helperText(__('admin.slides.help.name'))
                    ->maxLength(60),
                TextInput::make('duration')
                    ->label(__('admin.common.duration'))
                    ->helperText(fn (Get $get): ?string => ($type = self::types()->find((string) $get('type')))
                        ? __('admin.slides.help.duration', ['seconds' => $type->defaultDuration()])
                        : null)
                    ->integer()
                    ->minValue(5)
                    ->maxValue(300),
                Select::make('background_id')
                    ->label(__('admin.slides.fields.background'))
                    ->helperText(__('admin.slides.help.background'))
                    ->relationship('background', 'name')
                    ->searchable()
                    ->preload(),
                // Fields specific to the selected slide type, stored in `settings`.
                Group::make()
                    ->schema(fn (Get $get): array => self::types()->find((string) $get('type'))?->settingsFields() ?? [])
                    ->columnSpanFull(),
                Toggle::make('is_enabled')
                    ->label(__('admin.common.is_enabled'))
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
                TextColumn::make('type')
                    ->label(__('admin.slides.fields.type'))
                    ->formatStateUsing(fn (string $state): string => self::types()->find($state)?->label() ?? $state)
                    ->badge(),
                TextColumn::make('name')
                    ->label(__('admin.slides.fields.name'))
                    ->placeholder('—'),
                TextColumn::make('duration')
                    ->label(__('admin.common.duration'))
                    ->formatStateUsing(fn (?int $state): string => __('admin.common.seconds', ['seconds' => $state]))
                    ->placeholder(__('admin.common.duration_default')),
                TextColumn::make('background.name')
                    ->label(__('admin.slides.fields.background'))
                    ->placeholder('—'),
                ToggleColumn::make('is_enabled')
                    ->label(__('admin.common.is_enabled')),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageSlides::route('/'),
        ];
    }

    private static function types(): SlideTypeRegistry
    {
        return app(SlideTypeRegistry::class);
    }
}
