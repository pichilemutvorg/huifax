<?php

namespace App\Filament\Resources\ScheduleExceptions;

use App\Filament\NavigationGroup;
use App\Filament\Resources\Concerns\HasSentenceCaseLabels;
use App\Filament\Resources\ScheduleExceptions\Pages\ManageScheduleExceptions;
use App\Models\ScheduleException;
use App\Models\ScheduleSlot;
use BackedEnum;
use Carbon\CarbonImmutable;
use Closure;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\ToggleButtons;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Validation\Rules\Unique;
use UnitEnum;

/**
 * Cancels or moves a single regular airing without touching its weekly slot.
 */
class ScheduleExceptionResource extends Resource
{
    use HasSentenceCaseLabels;

    protected static ?string $model = ScheduleException::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowsRightLeft;

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::Schedule;

    protected static ?int $navigationSort = 4;

    public static function getModelLabel(): string
    {
        return __('admin.schedule_exceptions.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.schedule_exceptions.plural');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('schedule_slot_id')
                    ->label(__('admin.schedule_exceptions.fields.slot'))
                    ->options(fn (): array => ScheduleSlot::query()
                        ->with('program')
                        ->orderBy('start_time')
                        ->get()
                        ->mapWithKeys(fn (ScheduleSlot $slot) => [$slot->id => $slot->label()])
                        ->all())
                    ->searchable()
                    ->required()
                    ->live()
                    ->columnSpanFull(),
                DatePicker::make('date')
                    ->label(__('admin.schedule_exceptions.fields.date'))
                    ->required()
                    ->rule(fn (Get $get): Closure => self::airsOnRule($get('schedule_slot_id')))
                    ->unique(
                        ignoreRecord: true,
                        modifyRuleUsing: fn (Unique $rule, Get $get) => $rule->where('schedule_slot_id', $get('schedule_slot_id')),
                    )
                    ->validationMessages(['unique' => __('admin.schedule_exceptions.validation.duplicate')]),
                // Not stored: whether `rescheduled_to` is set is what tells both apart.
                ToggleButtons::make('action')
                    ->label(__('admin.schedule_exceptions.fields.action'))
                    ->options([
                        'cancel' => __('admin.schedule_exceptions.actions.cancel'),
                        'move' => __('admin.schedule_exceptions.actions.move'),
                    ])
                    ->default('cancel')
                    ->inline()
                    ->live()
                    ->dehydrated(false)
                    ->afterStateHydrated(function (ToggleButtons $component, ?ScheduleException $record): void {
                        if ($record !== null) {
                            $component->state($record->isCancellation() ? 'cancel' : 'move');
                        }
                    })
                    ->afterStateUpdated(function (?string $state, Set $set): void {
                        if ($state !== 'move') {
                            $set('rescheduled_to', null);
                        }
                    }),
                DateTimePicker::make('rescheduled_to')
                    ->label(__('admin.schedule_exceptions.fields.rescheduled_to'))
                    ->seconds(false)
                    ->visible(fn (Get $get): bool => $get('action') === 'move')
                    ->required(fn (Get $get): bool => $get('action') === 'move')
                    // Saved even while hidden, so switching back to "cancel" clears it.
                    ->dehydratedWhenHidden(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('date', 'desc')
            ->modifyQueryUsing(fn ($query) => $query->with('slot.program'))
            ->columns([
                TextColumn::make('slot')
                    ->label(__('admin.schedule_exceptions.fields.slot'))
                    ->state(fn (ScheduleException $record): string => $record->slot->label()),
                TextColumn::make('date')
                    ->label(__('admin.schedule_exceptions.fields.date'))
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('outcome')
                    ->label(__('admin.schedule_exceptions.fields.action'))
                    ->state(fn (ScheduleException $record): string => $record->isCancellation()
                        ? __('admin.schedule_exceptions.outcome.cancelled')
                        : __('admin.schedule_exceptions.outcome.moved', ['date' => $record->rescheduled_to->format('d/m/Y H:i')]))
                    ->badge()
                    ->color(fn (ScheduleException $record): string => $record->isCancellation() ? 'danger' : 'warning'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageScheduleExceptions::route('/'),
        ];
    }

    /**
     * The date must be one on which the slot regularly airs.
     */
    private static function airsOnRule(mixed $slotId): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail) use ($slotId): void {
            $slot = filled($slotId) ? ScheduleSlot::query()->find($slotId) : null;

            if ($slot !== null && filled($value) && ! $slot->airsOn(CarbonImmutable::parse($value))) {
                $fail(__('admin.schedule_exceptions.validation.not_an_airing'));
            }
        };
    }
}
