<?php

namespace App\Filament\MeetingRoom\Widgets\Resource;

use App\Models\MeetingRoom\Booking;
use App\Models\MeetingRoom\Room;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Illuminate\Support\Collection;

class WidgetResource extends BaseWidget
{
    public static function groupResource()
    {
        // Fetch all rooms where 'bookable' is true and order by 'floor' and 'capacity'
        $rooms = Room::query()
            ->where('bookable', true)
            ->orderBy('floor')
            ->orderBy('capacity')
            ->get();

        // Group the rooms by 'floor'
        $groupedRooms = $rooms->groupBy('floor');

        // Format the grouped rooms
        $formattedRooms = $groupedRooms->map(function ($group, $floor) {
            return [
                'id' => 'Floor ' . $floor,
                'title' => 'Floor ' . $floor,
                'children' => $group->map(function ($room) {
                    return [
                        'id' => $room->id,
                        'title' => $room->room_name . " [" . $room->capacity . ' Pax]',
                    ];
                })->values()->toArray(),
            ];
        })->values()->toArray();

        return $formattedRooms;
    }

    public static function getAvailableRooms($startTime, $endTime, $currentId = null): array|Collection
    {
        $data = Room::query()
            ->where('bookable', true)
            ->when($startTime && $endTime, function ($query) use ($startTime, $endTime) {
                $query->whereDoesntHave('bookings', function ($query) use ($startTime, $endTime) {
                    $query->where('start_time', '<', $endTime)
                        ->where('end_time', '>', $startTime);
                });
            })
            ->when($currentId, function ($query) use ($currentId) {
                $query->orWhere('id', $currentId);
            })
            ->get(['id', 'room_name', 'floor', 'capacity']) // Fetch required columns
            ->groupBy(function ($room) {
                return 'Floor ' . $room->floor; // Modify the floor value during grouping
            })
            ->map(function ($groupedRooms) {
                return $groupedRooms->mapWithKeys(function ($room) {
                    return [
                        $room->id => $room->room_name . ' [' . $room->capacity . ' pax]'
                    ];
                });
            })
            ->tap(function ($rooms) {
                // Add a count of the rooms after grouping by floor
                $rooms->count = $rooms->flatten()->count(); // Total count of rooms
            });
        return $data;
    }

    public static function isRoomAvailable($newResourceId, $startTime, $endTime, $bookingId = null): bool
    {
        // Check if the room exists in the bookings table with overlapping time
        $query = Booking::query()
            ->where('room_id', $newResourceId)
            ->where('start_time', '<', $endTime)
            ->where('end_time', '>', $startTime);

        if ($bookingId) {
            $query->where('id', '!=', $bookingId);
        }

        $roomExists = $query->exists();

        // If a room exists with overlapping time, return false; otherwise, true
        return !$roomExists;
    }
}
