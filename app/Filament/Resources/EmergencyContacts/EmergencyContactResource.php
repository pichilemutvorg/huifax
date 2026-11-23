<?php

namespace App\Filament\Resources\EmergencyContacts;

use App\Filament\NavigationGroup;
use App\Filament\Resources\Concerns\HasSentenceCaseLabels;
use App\Filament\Resources\EmergencyContacts\Pages\ManageEmergencyContacts;
use App\Models\EmergencyContact;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
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

class EmergencyContactResource extends Resource
{
    use HasSentenceCaseLabels;

    protected static ?string $model = EmergencyContact::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhone;

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::Content;

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getModelLabel(): string
    {
        return __('admin.emergency_contacts.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.emergency_contacts.plural');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('admin.emergency_contacts.fields.name'))
                    ->required()
                    ->maxLength(40),
                TextInput::make('phone')
                    ->label(__('admin.emergency_contacts.fields.phone'))
                    ->required()
                    ->maxLength(20),
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
                TextColumn::make('phone')
                    ->label(__('admin.emergency_contacts.fields.phone'))
                    ->weight('bold')
                    ->searchable(),
                TextColumn::make('name')
                    ->label(__('admin.emergency_contacts.fields.name'))
                    ->searchable(),
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
            'index' => ManageEmergencyContacts::route('/'),
        ];
    }
}
