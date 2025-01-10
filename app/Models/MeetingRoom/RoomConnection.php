<?php

namespace App\Models\MeetingRoom;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomConnection extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function connectedRoom()
    {
        return $this->belongsTo(Room::class, 'connected_room_id');
    }
}