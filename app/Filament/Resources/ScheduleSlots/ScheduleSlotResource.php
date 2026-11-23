<?php

namespace App\Filament\Resources\ScheduleSlots;

use App\Enums\BroadcastTag;
use App\Filament\NavigationGroup;
use App\Filament\Resources\Concerns\HasSentenceCaseLabels;
use App\Filament\Resources\ScheduleSlots\Pages\ManageScheduleSlots;
use App\Models\ScheduleSlot;
use App\Schedule\Weekdays;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class ScheduleSlotResource extends Resource
{
    use HasSentenceCaseLabels;

    protected static ?string $model = ScheduleSlot::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::Schedule;

    protected static ?int $navigationSort = 2;

    public static function getModelLabel(): string
    {
        return __('admin.schedule_slots.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.schedule_slots.plural');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('program_id')
                    ->label(__('admin.schedule_slots.fields.program'))
                    ->relationship('program', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->columnSpanFull(),
                CheckboxList::make('weekdays')
                    ->label(__('admin.schedule_slots.fields.weekdays'))
                    ->options(fn (): array => Weekdays::names())
                    ->columns(4)
                    ->required()
                    ->columnSpanFull(),
                TimePicker::make('start_time')
                    ->label(__('admin.schedule_slots.fields.start_time'))
                    ->seconds(false)
                    ->required(),
                TextInput::make('duration_minutes')
                    ->label(__('admin.schedule_slots.fields.duration_minutes'))
                    ->integer()
                    ->minValue(1)
                    ->maxValue(1440)
                    ->required(),
                Select::make('tag')
                    ->label(__('admin.schedule_slots.fields.tag'))
                    ->options(BroadcastTag::class),
                Toggle::make('is_enabled')
                    ->label(__('admin.common.is_enabled'))
                    ->default(true)
                    ->inline(false),
                DatePicker::make('valid_from')
                    ->label(__('admin.schedule_slots.fields.valid_from'))
                    ->helperText(__('admin.schedule_slots.help.season')),
                DatePicker::make('valid_until')
                    ->label(__('admin.schedule_slots.fields.valid_until'))
                    ->afterOrEqual('valid_from'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('start_time')
            ->columns([
                TextColumn::make('program.name')
                    ->label(__('admin.schedule_slots.fields.program'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('weekdays')
                    ->label(__('admin.schedule_slots.fields.weekdays'))
                    ->state(fn (ScheduleSlot $record): string => Weekdays::summary($record->weekdays ?? [])),
                TextColumn::make('start_time')
                    ->label(__('admin.schedule_slots.fields.start_time'))
                    ->time('H:i')
                    ->sortable(),
                TextColumn::make('duration_minutes')
                    ->label(__('admin.schedule_slots.fields.duration_minutes')),
                TextColumn::make('tag')
                    ->label(__('admin.schedule_slots.fields.tag'))
                    ->badge()
                    ->placeholder('—'),
                TextColumn::make('season')
                    ->label(__('admin.schedule_slots.fields.season'))
                    ->state(fn (ScheduleSlot $record): string => self::season($record)),
                ToggleColumn::make('is_enabled')
                    ->label(__('admin.common.is_enabled')),
            ])
            ->filters([
                SelectFilter::make('program')
                    ->label(__('admin.schedule_slots.fields.program'))
                    ->relationship('program', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageScheduleSlots::route('/'),
        ];
    }

    private static function season(ScheduleSlot $slot): string
    {
        if ($slot->valid_from === null && $slot->valid_until === null) {
            return __('admin.schedule_slots.always');
        }

        return ($slot->valid_from?->format('d/m/Y') ?? '…').' – '.($slot->valid_until?->format('d/m/Y') ?? '…');
    }
}
