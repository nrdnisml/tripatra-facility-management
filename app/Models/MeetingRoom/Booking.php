<?php

namespace App\Models\MeetingRoom;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Guava\Calendar\Contracts\Eventable;
use Guava\Calendar\ValueObjects\Event;
use Illuminate\Database\Eloquent\Builder;

class Booking extends Model implements Eventable
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'confirmed_at' => 'datetime',
        'internal_participants' => 'array',
        'external_participants' => 'array',
        'status' => 'string',
    ];

    // protected static function booted(): void
    // {
    //     static::addGlobalScope('userAccess', function (Builder $builder) {
    //         $roles = auth()->user()->load('roles')->roles()->first();
    //         if ($roles->role_name == 'user' && $roles->module == 'all') {
    //             $builder->where('booked_by', auth()->user()->id);
    //         }
    //         if ($roles->role_name == 'admin' && $roles->module == 'meeting-room') {
    //             $builder;
    //         }
    //     });
    // }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function bookedBy()
    {
        return $this->belongsTo(User::class, 'booked_by');
    }

    public function toEvent(): array | Event
    {
        $title = "{$this->bookedBy->name}" . " - {$this->title}";
        $color = $this->bookedBy->id == auth()->user()->id ? '#D97706' : '#ddd';
        $onClickEvent = $this->bookedBy->id == auth()->user()->id ? 'edit' : 'view';
        $event = Event::make($this)
            ->title(ucwords($title))
            ->start($this->start_time)
            ->end($this->end_time)
            ->backgroundColor($color)
            ->textColor('#ffffff')
            ->displayAuto()
            ->styles([
                'font-size' => '12px',
            ])
            ->action($onClickEvent);

        if ($this->room_id) {
            $event->resourceId($this->room_id);
        }
        return $event;
    }
}
