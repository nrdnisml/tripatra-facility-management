<?php

namespace App\Filament\MeetingRoom\Resources\RoomConnectionResource\Pages;

use App\Filament\MeetingRoom\Resources\RoomConnectionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRoomConnections extends ListRecords
{
    protected static string $resource = RoomConnectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
