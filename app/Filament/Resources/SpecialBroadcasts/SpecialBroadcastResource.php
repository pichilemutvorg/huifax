<?php

namespace App\Filament\Resources\SpecialBroadcasts;

use App\Enums\BroadcastTag;
use App\Filament\NavigationGroup;
use App\Filament\Resources\Concerns\HasSentenceCaseLabels;
use App\Filament\Resources\SpecialBroadcasts\Pages\ManageSpecialBroadcasts;
use App\Models\SpecialBroadcast;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class SpecialBroadcastResource extends Resource
{
    use HasSentenceCaseLabels;

    protected static ?string $model = SpecialBroadcast::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedStar;

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::Schedule;

    protected static ?int $navigationSort = 3;

    public static function getModelLabel(): string
    {
        return __('admin.special_broadcasts.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.special_broadcasts.plural');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('program_id')
                    ->label(__('admin.special_broadcasts.fields.program'))
                    ->relationship('program', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->columnSpanFull(),
                DateTimePicker::make('starts_at')
                    ->label(__('admin.special_broadcasts.fields.starts_at'))
                    ->seconds(false)
                    ->required(),
                TextInput::make('duration_minutes')
                    ->label(__('admin.special_broadcasts.fields.duration_minutes'))
                    ->integer()
                    ->minValue(1)
                    ->maxValue(1440)
                    ->required(),
                Select::make('tag')
                    ->label(__('admin.special_broadcasts.fields.tag'))
                    ->options(BroadcastTag::class),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('starts_at', 'desc')
            ->columns([
                TextColumn::make('program.name')
                    ->label(__('admin.special_broadcasts.fields.program'))
                    ->searchable(),
                TextColumn::make('starts_at')
                    ->label(__('admin.special_broadcasts.fields.starts_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('duration_minutes')
                    ->label(__('admin.special_broadcasts.fields.duration_minutes')),
                TextColumn::make('tag')
                    ->label(__('admin.special_broadcasts.fields.tag'))
                    ->badge()
                    ->placeholder('—'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageSpecialBroadcasts::route('/'),
        ];
    }
}
