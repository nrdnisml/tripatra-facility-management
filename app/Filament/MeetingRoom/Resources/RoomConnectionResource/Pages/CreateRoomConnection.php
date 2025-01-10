<?php

namespace App\Filament\MeetingRoom\Resources\RoomConnectionResource\Pages;

use App\Filament\MeetingRoom\Resources\RoomConnectionResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateRoomConnection extends CreateRecord
{
    protected static string $resource = RoomConnectionResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}