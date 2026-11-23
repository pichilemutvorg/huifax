<?php

namespace App\Filament\Resources\ScheduleSlots\Pages;

use App\Filament\Resources\ScheduleSlots\ScheduleSlotResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageScheduleSlots extends ManageRecords
{
    protected static string $resource = ScheduleSlotResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
