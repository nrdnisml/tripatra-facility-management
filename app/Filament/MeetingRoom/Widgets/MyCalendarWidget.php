<?php

namespace App\Filament\MeetingRoom\Widgets;

use App\Filament\MeetingRoom\Widgets\Form\Schema;
use App\Filament\MeetingRoom\Widgets\Resource\WidgetResource;
use App\Models\MeetingRoom\Booking;
use Carbon\Carbon;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use \Guava\Calendar\Widgets\CalendarWidget;
use Illuminate\Database\Eloquent\Model;
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
                ->steps(Schema::eventSchema())
                ->closeModalByClickingAway(false)
                ->modalWidth(\Filament\Support\Enums\MaxWidth::SevenExtraLarge),
        ];
    }

    public function getEvents(array $fetchInfo = []): Collection | array
    {
        return collect()
            ->push(...Booking::query()->get());
    }

    public function getResources(): Collection|array
    {
        return WidgetResource::groupResource();
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
            $this->editAction()
                ->before(function (EditAction $action, array $data, Booking $record) {
                    if (!WidgetResource::isRoomAvailable(
                        $data['room_id'],
                        $data['start_time'],
                        $data['end_time'],
                        $record->id
                    )) {
                        Notification::make()
                            ->title('Action Failed')
                            ->body('The room is not available at the selected time.')
                            ->danger()
                            ->send();
                        $action->cancel();
                    }
                })
                ->closeModalByClickingAway(false),
            $this->deleteAction(),
        ];
    }

    public function getSchema(?string $model = null): ?array
    {
        return Schema::widgetSchema();
    }

    public function getDateClickContextMenuActions(): array
    {
        return $this->getDateContextMenuActions();
    }

    public function getDateSelectContextMenuActions(): array
    {
        return $this->getDateContextMenuActions();
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

                if (!is_numeric($newIdResource) && $newIdResource != null) {
                    Notification::make()
                        ->title('Action Failed')
                        ->body('Please select a specific room. Moving to a floor level is not allowed.')
                        ->danger()
                        ->send();
                    return false;
                }

                if (!WidgetResource::isRoomAvailable($newIdResource, $startsAt, $endsAt)) {
                    Notification::make()
                        ->title('Action Failed')
                        ->body('The room is not available at the selected time.')
                        ->danger()
                        ->send();
                    return false;
                }

                if (is_numeric($newIdResource) && $newIdResource) {
                    $record->update([
                        'room_id' => $newIdResource,
                    ]);
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

    public function onEventResize(array $info = []): bool
    {
        parent::onEventResize($info);

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


        if ($delta = data_get($info, 'endDelta')) {
            $endsAt = $record->end_time;
            $endsAt->addSeconds(data_get($delta, 'seconds'));

            if (!WidgetResource::isRoomAvailable(
                $record->room_id,
                $record->start_time,
                $endsAt,
                $record->id
            )) {
                Notification::make()
                    ->title('Action Failed')
                    ->body('The room is not available at the selected time.')
                    ->danger()
                    ->send();
                return false;
            }

            $record->update([
                'end_time' => $endsAt,
            ]);
        }

        Notification::make()
            ->title('Booking duration changed!')
            ->success()
            ->send();

        return true;
    }

    private function getDateContextMenuActions()
    {
        $data = [
            \Guava\Calendar\Actions\CreateAction::make('ctxCreateMeeting')
                ->closeModalByClickingAway(false)
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
                            'internal_participants' => auth()->user()->id,
                            'status' => 'booked',
                            'booking_type' => 'internal',
                            'booked_by' => auth()->user()->id,
                        ]);
                    }
                }),
        ];
        return $data;
    }
}
