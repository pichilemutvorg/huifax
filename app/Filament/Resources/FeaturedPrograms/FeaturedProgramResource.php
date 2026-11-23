<?php

namespace App\Filament\Resources\FeaturedPrograms;

use App\Filament\Forms\Components\PhotoUpload;
use App\Filament\NavigationGroup;
use App\Filament\Resources\Concerns\HasSentenceCaseLabels;
use App\Filament\Resources\FeaturedPrograms\Pages\ManageFeaturedPrograms;
use App\Models\FeaturedProgram;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
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

class FeaturedProgramResource extends Resource
{
    use HasSentenceCaseLabels;

    protected static ?string $model = FeaturedProgram::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedStar;

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::Content;

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getModelLabel(): string
    {
        return __('admin.featured_programs.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.featured_programs.plural');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('admin.featured_programs.fields.name'))
                    ->required()
                    ->maxLength(60),
                TextInput::make('label')
                    ->label(__('admin.featured_programs.fields.label'))
                    ->helperText(__('admin.featured_programs.help.label'))
                    ->maxLength(20),
                TextInput::make('tagline')
                    ->label(__('admin.featured_programs.fields.tagline'))
                    ->maxLength(120)
                    ->columnSpanFull(),
                TextInput::make('schedule')
                    ->label(__('admin.featured_programs.fields.schedule'))
                    ->helperText(__('admin.featured_programs.help.schedule'))
                    ->maxLength(60)
                    ->columnSpanFull(),
                PhotoUpload::make('path')
                    ->label(__('admin.featured_programs.fields.image'))
                    ->helperText(__('admin.featured_programs.help.image', ['size' => (int) (config('huifax.uploads.max_size') / 1024)]))
                    ->disk(FeaturedProgram::DISK)
                    ->directory(FeaturedProgram::DIRECTORY)
                    ->required()
                    ->columnSpanFull(),
                DateTimePicker::make('starts_at')
                    ->label(__('admin.notices.fields.starts_at'))
                    ->helperText(__('admin.notices.help.window'))
                    ->seconds(false),
                DateTimePicker::make('ends_at')
                    ->label(__('admin.notices.fields.ends_at'))
                    ->seconds(false)
                    ->after('starts_at'),
                TextInput::make('duration')
                    ->label(__('admin.common.duration'))
                    ->helperText(__('admin.featured_programs.help.duration'))
                    ->integer()
                    ->minValue(5)
                    ->maxValue(300),
                Toggle::make('is_enabled')
                    ->label(__('admin.common.is_enabled'))
                    ->default(true)
                    ->inline(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->columns([
                ImageColumn::make('path')
                    ->label(__('admin.featured_programs.fields.image'))
                    ->disk(FeaturedProgram::DISK)
                    ->imageHeight(64),
                TextColumn::make('name')
                    ->label(__('admin.featured_programs.fields.name'))
                    ->description(fn (FeaturedProgram $record): ?string => $record->schedule)
                    ->searchable(),
                TextColumn::make('label')
                    ->label(__('admin.featured_programs.fields.label'))
                    ->badge()
                    ->placeholder('—'),
                TextColumn::make('status')
                    ->label(__('admin.notices.status.label'))
                    ->state(fn (FeaturedProgram $record): string => $record->statusAt(now()))
                    ->formatStateUsing(fn (string $state): string => __("admin.notices.status.{$state}"))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'scheduled' => 'info',
                        'expired' => 'warning',
                        default => 'gray',
                    }),
                ToggleColumn::make('is_enabled')
                    ->label(__('admin.common.is_enabled')),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageFeaturedPrograms::route('/'),
        ];
    }
}
