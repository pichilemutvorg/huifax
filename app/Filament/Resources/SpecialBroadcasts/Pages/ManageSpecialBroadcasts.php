<?php

namespace App\Filament\Resources\SpecialBroadcasts\Pages;

use App\Filament\Resources\SpecialBroadcasts\SpecialBroadcastResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageSpecialBroadcasts extends ManageRecords
{
    protected static string $resource = SpecialBroadcastResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
