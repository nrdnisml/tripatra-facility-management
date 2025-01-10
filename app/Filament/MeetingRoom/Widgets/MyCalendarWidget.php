<?php

namespace App\Filament\MeetingRoom\Widgets;

use App\Models\MeetingRoom\Booking;
use App\Models\MeetingRoom\Room;
use Carbon\Carbon;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Guava\Calendar\ValueObjects\Event;
use \Guava\Calendar\Widgets\CalendarWidget;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;

class MyCalendarWidget extends CalendarWidget
{

    protected string $calendarView = 'resourceTimelineDay';
    protected string | \Closure | HtmlString | null $heading = 'Todays Meeting Room Bookings';
    protected bool $eventClickEnabled = true;
    protected bool $eventDragEnabled = true;
    protected bool $dateSelectEnabled = true;
    protected bool $eventResizeEnabled = true;


    public function getHeaderActions(): array
    {
        return [
            \Guava\Calendar\Actions\CreateAction::make('CreateMeeting')
                ->model(Booking::class)
                ->form($this->customSchema()),
        ];
    }

    public function getEvents(array $fetchInfo = []): Collection | array
    {
        return collect()
            ->push(...Booking::query()->get());
    }

    public function getResources(): Collection|array
    {
        return $this->groupResource();
    }

    public function getOptions(): array
    {
        return [
            'slotMinTime' => '08:00:00',
            'slotMaxTime' => '21:00:00',
            'slotWidth' => '98',
        ];
    }


    public function getEventClickContextMenuActions(): array
    {
        return [
            $this->editAction(),
            $this->deleteAction(),
        ];
    }

    public function getSchema(?string $model = null): ?array
    {
        $accounts = \App\Helpers\TripatraUser::getAccountNameIds();
        return  [
            Group::make([
                DateTimePicker::make('start_time')
                    ->label('Start Date & Time')
                    ->seconds(false)
                    ->live(onBlur: true)
                    ->required(),
                DateTimePicker::make('end_time')
                    ->label('End Date & Time')
                    ->seconds(false)
                    ->live(onBlur: true)
                    ->required(),
                Select::make('room_id')
                    ->label('Meeting Rooms')
                    ->hint(fn (Get $get) => self::getAvailableRooms($get('start_time'), $get('end_time'))->count . ' rooms available')
                    ->options(fn (Get $get) => self::getAvailableRooms($get('start_time'), $get('end_time'), $get('room_id')))
                    ->searchable()
                    ->preload()
                    ->required(),
            ])->columns(3),
            Group::make([
                Select::make('booking_type')
                    ->label('Meeting Type')
                    ->options([
                        'internal' => 'Internal',
                        'eksternal' => 'Eksternal',
                    ])
                    ->default('internal')
                    ->required(),
                Select::make('project_id')
                    ->label('Project Name')
                    ->helperText('Keep empty if not applicable')
                    ->options(\App\Models\Project::query()->pluck('project_name', 'id'))
                    ->searchable(),

            ])->columns(2),
            Select::make('booked_for')
                ->label('Participants')
                ->multiple()
                ->options($accounts),
            TextInput::make('title')
                ->label('Meeting Description')
                ->required(),
            // Hidden Fields
            Hidden::make('booked_by')->default(auth()->user()->id),
            Hidden::make('status')->default('booked'),
        ];
    }

    private function customSchema()
    {
        $accounts = \App\Helpers\TripatraUser::getAccountNameIds();
        return  [
            Fieldset::make('Booking Time')->schema([
                DateTimePicker::make('start_time')
                    ->label('Start Date & Time')
                    ->seconds(false)
                    ->required()
                    ->live(onBlur: true),
                DateTimePicker::make('end_time')
                    ->label('End Date & Time')
                    ->seconds(false)
                    ->required()
                    ->live(onBlur: true),
            ])->columns(2),
            Fieldset::make('Select Meeting Room & Add Participants')->schema([
                Select::make('room_id')
                    ->label('Meeting Rooms')
                    ->hint(fn (Get $get) => self::getAvailableRooms($get('start_time'), $get('end_time'))->count . ' rooms available')
                    ->options(fn (Get $get) => self::getAvailableRooms($get('start_time'), $get('end_time')))
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('booked_for')
                    ->label('Participants')
                    ->multiple()
                    ->options($accounts),
            ])->columns(2),
            Fieldset::make('Booking Details')->schema([
                Select::make('project_id')
                    ->label('Project Name')
                    ->helperText('Keep empty if not applicable')
                    ->options(\App\Models\Project::query()->pluck('project_name', 'id'))
                    ->searchable(),
                Select::make('booking_type')
                    ->label('Meeting Type')
                    ->options([
                        'internal' => 'Internal',
                        'eksternal' => 'Eksternal',
                    ])
                    ->default('internal')
                    ->required(),
                TextInput::make('title')
                    ->label('Meeting Description')
                    ->required()
                    ->columnSpanFull(),

            ])->columns(2),

            // Hidden Fields
            Hidden::make('booked_by')->default(auth()->user()->id),
            Hidden::make('status')->default('booked'),
        ];
    }

    private function groupResource()
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

    private static function getAvailableRooms($startTime, $endTime, $currentId = null): array|Collection
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

    public function getDateClickContextMenuActions(): array
    {
        return $this->getDateContextMenuActions();
    }

    public function getDateSelectContextMenuActions(): array
    {
        return $this->getDateContextMenuActions();
    }

    private function getDateContextMenuActions()
    {
        $data = [
            \Guava\Calendar\Actions\CreateAction::make('ctxCreateMeeting')
                ->model(Booking::class)
                ->mountUsing(function (\Filament\Forms\Form $form, array $arguments) {
                    $roomId = data_get($arguments, 'resource.id');
                    if (!is_numeric($roomId)) {
                        // Prevent the next action
                        return;
                    }

                    $date = data_get($arguments, 'dateStr');
                    $startsTime = Carbon::make(data_get($arguments, 'startStr', $date));
                    $endsTime = Carbon::make(data_get($arguments, 'endStr', $date));

                    if ($endsTime->diffInMinutes($startsTime) == 0) {
                        $endsTime->addMinutes(30);
                    }
                    if ($startsTime && $endsTime) {
                        $form->fill([
                            'room_id' => $roomId,
                            'start_time' => Carbon::make($startsTime),
                            'end_time' => Carbon::make($endsTime),
                            'booked_for' => auth()->user()->id,
                            'status' => 'booked',
                            'booking_type' => 'internal',
                            'booked_by' => auth()->user()->id,
                        ]);
                    }
                }),
        ];
        return $data;
    }

    public function onEventDrop(array $info = []): bool
    {
        parent::onEventDrop($info);

        if (in_array($this->getEventModel(), [Booking::class])) {
            $record = $this->getEventRecord();

            if (!auth()->user()->can('update', $record)) {
                // If not authorized, show a notification and return
                Notification::make()
                    ->title('Action Failed')
                    ->body('You do not have permission to move this booking.')
                    ->danger()
                    ->send();
                return false;
            }

            if ($delta = data_get($info, 'delta')) {
                $startsAt = $record->start_time;
                $endsAt = $record->end_time;
                $startsAt->addSeconds(data_get($delta, 'seconds'));
                $endsAt->addSeconds(data_get($delta, 'seconds'));
                $newIdResource = data_get($info, 'newResource.id');
                if (is_numeric($newIdResource) && $newIdResource) {
                    $record->update([
                        'room_id' => $newIdResource,
                    ]);
                } else if (!is_numeric($newIdResource) && $newIdResource != null) {
                    Notification::make()
                        ->title('Action Failed')
                        ->body('Please select a specific room. Moving to a floor level is not allowed.')
                        ->danger()
                        ->send();
                    return false;
                }

                $record->update([
                    'start_time' => $startsAt,
                    'end_time' => $endsAt,
                ]);

                Notification::make()
                    ->title('Booking data moved!')
                    ->success()
                    ->send();
            }

            return true;
        }

        return false;
    }
}
