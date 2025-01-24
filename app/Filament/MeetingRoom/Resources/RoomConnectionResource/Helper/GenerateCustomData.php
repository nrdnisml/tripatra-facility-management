<?php

namespace App\Filament\MeetingRoom\Resources\RoomConnectionResource\Helper;

use App\Models\MeetingRoom\Room;

class GenerateCustomData
{
    public function setRoomNameData(array $data): array
    {
        $collection = collect($data['connected_rooms']);
        $roomIds = $collection->pluck('connected_rooms')->all();

        $roomNames = Room::whereIn('id', $roomIds)->pluck('room_name')->toArray();
        $formattedRoomNames = implode(' - ', $roomNames) . ' (Connecting Rooms)';

        $data['room_name'] = $formattedRoomNames;
        return $data;
    }
}