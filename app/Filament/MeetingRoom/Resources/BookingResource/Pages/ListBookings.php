<?php

namespace App\Filament\MeetingRoom\Resources\BookingResource\Pages;

use App\Filament\MeetingRoom\Resources\BookingResource;
use App\Helpers\UserHelper;
use App\Models\MeetingRoom\Booking;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class ListBookings extends ListRecords
{
    protected static string $resource = BookingResource::class;

    public Collection $records;

    public function __construct()
    {
        $user_role = UserHelper::getUserRoleName('meeting-room');
        $query = Booking::select('status', DB::raw('count(*) as total'))
            ->groupBy('status');

        if ($user_role !== 'admin') {
            $query->where('booked_by', Auth::user()->id);
        }

        $this->records = $query->pluck('total', 'status');
    }
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        $today = \Carbon\Carbon::today();
        // Calculate the count for today's bookings
        $bookedTodayCountQuery = Booking::where('status', 'booked')
            ->whereDate('start_time', $today);
        if (UserHelper::getUserRoleName('meeting-room') !== 'admin') {
            $bookedTodayCountQuery->where('booked_by', Auth::user()->id);
        }
        $bookedTodayCount = $bookedTodayCountQuery->count();
        return [
            "Unconfirmed Bookings for Today" => Tab::make()->modifyQueryUsing(
                function ($query) {
                    $today = \Carbon\Carbon::today();
                    return $query->where('status', 'booked')
                        ->whereDate('start_time', $today);
                }
            )->badge($bookedTodayCount ?? 0),

            'Booked' => Tab::make()->modifyQueryUsing(
                function ($query) {
                    return $query->where('status', 'booked');
                }
            )->badge($this->records['booked'] ?? 0),

            'Confirmed' => Tab::make()
                ->modifyQueryUsing(
                    fn($query) =>
                    $query->where('status', 'confirmed')
                )->badge($this->records['confirmed'] ?? 0),

            'Cancelled' => Tab::make()
                ->modifyQueryUsing(
                    fn($query) =>
                    $query->where('status', 'cancelled')
                )->badge($this->records['cancelled'] ?? 0),
        ];
    }
}
