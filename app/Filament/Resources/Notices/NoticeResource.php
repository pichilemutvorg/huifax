<?php

namespace App\Filament\Resources\Notices;

use App\Filament\NavigationGroup;
use App\Filament\Resources\Concerns\HasSentenceCaseLabels;
use App\Filament\Resources\Notices\Pages\ManageNotices;
use App\Models\Notice;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use UnitEnum;

class NoticeResource extends Resource
{
    use HasSentenceCaseLabels;

    protected static ?string $model = Notice::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMegaphone;

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::Content;

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'title';

    public static function getModelLabel(): string
    {
        return __('admin.notices.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.notices.plural');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label(__('admin.notices.fields.title'))
                    ->required()
                    ->maxLength(120)
                    ->columnSpanFull(),
                Textarea::make('body')
                    ->label(__('admin.notices.fields.body'))
                    ->rows(4)
                    ->maxLength(400)
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
                    ->helperText(__('admin.notices.help.duration'))
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
                TextColumn::make('title')
                    ->label(__('admin.notices.fields.title'))
                    ->searchable()
                    ->wrap(),
                TextColumn::make('status')
                    ->label(__('admin.notices.status.label'))
                    ->state(fn (Notice $record): string => $record->statusAt(now()))
                    ->formatStateUsing(fn (string $state): string => __("admin.notices.status.{$state}"))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'scheduled' => 'info',
                        'expired' => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('starts_at')
                    ->label(__('admin.notices.fields.starts_at'))
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('—'),
                TextColumn::make('ends_at')
                    ->label(__('admin.notices.fields.ends_at'))
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('—'),
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
            'index' => ManageNotices::route('/'),
        ];
    }
}
