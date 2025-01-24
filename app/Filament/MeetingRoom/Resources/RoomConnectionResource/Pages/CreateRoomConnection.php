<?php

namespace App\Filament\MeetingRoom\Resources\RoomConnectionResource\Pages;

use App\Filament\MeetingRoom\Resources\RoomConnectionResource;
use App\Filament\MeetingRoom\Resources\RoomConnectionResource\Helper\GenerateCustomData;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Models\MeetingRoom\Room;

class CreateRoomConnection extends CreateRecord
{

    protected static string $resource = RoomConnectionResource::class;
    private $helper;

    public function __construct()
    {
        $this->helper = new GenerateCustomData();
    }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data = $this->helper->setRoomNameData($data);
        return $data;
    }
}