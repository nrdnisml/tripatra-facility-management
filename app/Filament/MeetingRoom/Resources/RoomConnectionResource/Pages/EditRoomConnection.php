<?php

namespace App\Filament\MeetingRoom\Resources\RoomConnectionResource\Pages;

use App\Filament\MeetingRoom\Resources\RoomConnectionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRoomConnection extends EditRecord
{
    protected static string $resource = RoomConnectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}