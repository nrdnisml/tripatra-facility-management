<?php

namespace App\Models\MeetingRoom;

use Guava\Calendar\Contracts\Resourceable;
use Guava\Calendar\ValueObjects\Resource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model implements Resourceable
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'bookable' => 'boolean',
        'mergeable' => 'boolean',
        'facilities' => 'array',
    ];

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