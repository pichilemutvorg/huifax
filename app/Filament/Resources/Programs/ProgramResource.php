<?php

namespace App\Filament\Resources\Programs;

use App\Filament\NavigationGroup;
use App\Filament\Resources\Concerns\HasSentenceCaseLabels;
use App\Filament\Resources\Programs\Pages\ManagePrograms;
use App\Models\Program;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use UnitEnum;

class ProgramResource extends Resource
{
    use HasSentenceCaseLabels;

    protected static ?string $model = Program::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFilm;

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::Schedule;

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getModelLabel(): string
    {
        return __('admin.programs.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.programs.plural');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('admin.programs.fields.name'))
                    ->required()
                    ->maxLength(80),
                TextInput::make('description')
                    ->label(__('admin.programs.fields.description'))
                    ->helperText(__('admin.programs.help.description'))
                    ->maxLength(120),
                Toggle::make('is_enabled')
                    ->label(__('admin.common.is_enabled'))
                    ->helperText(__('admin.programs.help.is_enabled'))
                    ->default(true),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->columns([
                TextColumn::make('name')
                    ->label(__('admin.programs.fields.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('description')
                    ->label(__('admin.programs.fields.description'))
                    ->placeholder('—')
                    ->wrap(),
                TextColumn::make('slots_count')
                    ->label(__('admin.programs.fields.slots_count'))
                    ->counts('slots'),
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
            'index' => ManagePrograms::route('/'),
        ];
    }
}
