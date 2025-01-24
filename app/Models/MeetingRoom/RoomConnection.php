<?php

namespace App\Models\MeetingRoom;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomConnection extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'room_pictures' => 'json',
        'room_layouts' => 'json',
        'connected_rooms' => 'json',
    ];
}