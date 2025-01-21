<?php

namespace App\Models\MeetingRoom;

use Guava\Calendar\Contracts\Resourceable;
use Guava\Calendar\ValueObjects\Resource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Room extends Model implements Resourceable
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'bookable' => 'boolean',
        'mergeable' => 'boolean',
        'facilities' => 'array',
        'room_pictures' => 'json',
        'room_layouts' => 'json',
    ];

    protected static function booted()
    {
        static::deleted(function (Room $room) {
            if ($room->room_pictures) {
                foreach ($room->room_pictures as $roomPicture) {
                    Storage::delete($roomPicture);
                }
            }
        });

        static::updating(function (Room $room) {
            // Convert to array if attachments is null
            $originalAttachments = $room->getOriginal('room_pictures') ?? [];
            $currentAttachments = $room->room_pictures ?? [];

            // Ensure they are arrays
            $originalAttachments = is_array($originalAttachments) ? $originalAttachments : [];
            $currentAttachments = is_array($currentAttachments) ? $currentAttachments : [];
            if (!empty($currentAttachments)) {
                $roomPictureToDelete = array_diff($originalAttachments, $currentAttachments);
                foreach ($roomPictureToDelete as $roomPicture) {
                    Storage::delete($roomPicture);
                }
            }
        });
    }

    public function getFormattedFacilitiesAttribute()
    {
        return collect($this->facilities)
            ->map(fn ($value, $key) => "$key: $value")
            ->values()
            ->toArray();
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function connections()
    {
        return $this->hasMany(RoomConnection::class, 'room_id');
    }

    public function connectedRooms()
    {
        return $this->belongsToMany(Room::class, 'room_connections', 'room_id', 'connected_room_id');
    }

    public function toResource(): array | Resource
    {
        return Resource::make($this->id)
            ->title("[Floor " . $this->floor . " - " . $this->capacity . ' Pax] ' . $this->room_name);
    }
}
