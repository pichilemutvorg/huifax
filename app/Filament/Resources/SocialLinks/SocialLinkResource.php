<?php

namespace App\Filament\Resources\SocialLinks;

use App\Enums\SocialPlatform;
use App\Filament\NavigationGroup;
use App\Filament\Resources\Concerns\HasSentenceCaseLabels;
use App\Filament\Resources\SocialLinks\Pages\ManageSocialLinks;
use App\Models\SocialLink;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use UnitEnum;

class SocialLinkResource extends Resource
{
    use HasSentenceCaseLabels;

    protected static ?string $model = SocialLink::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAtSymbol;

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::Content;

    protected static ?int $navigationSort = 4;

    protected static ?string $recordTitleAttribute = 'handle';

    public static function getModelLabel(): string
    {
        return __('admin.social_links.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.social_links.plural');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('platform')
                    ->label(__('admin.social_links.fields.platform'))
                    ->options(SocialPlatform::class)
                    ->required(),
                TextInput::make('handle')
                    ->label(__('admin.social_links.fields.handle'))
                    ->helperText(__('admin.social_links.help.handle'))
                    ->required()
                    ->maxLength(60),
                TextInput::make('url')
                    ->label(__('admin.social_links.fields.url'))
                    ->helperText(__('admin.social_links.help.url'))
                    ->url()
                    ->maxLength(255),
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
                TextColumn::make('platform')
                    ->label(__('admin.social_links.fields.platform'))
                    ->badge(),
                TextColumn::make('handle')
                    ->label(__('admin.social_links.fields.handle'))
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
            'index' => ManageSocialLinks::route('/'),
        ];
    }
}
