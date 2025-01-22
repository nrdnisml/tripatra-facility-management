<?php

namespace App\Filament\MeetingRoom\Resources\BookingResource\Pages;

use App\Filament\MeetingRoom\Resources\BookingResource;
use App\Models\MeetingRoom\Booking;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class ListBookings extends ListRecords
{
    protected static string $resource = BookingResource::class;

    public Collection $records;

    public function __construct()
    {
        $this->records = Booking::select('status', DB::raw('count(*) as total'))
            ->where('booked_by', auth()->user()->id)
            ->groupBy('status')
            ->pluck('total', 'status');
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
        $bookedTodayCount = Booking::where('status', 'booked')
            ->whereDate('start_time', $today)
            ->count();
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
                    fn ($query) =>
                    $query->where('status', 'confirmed')
                )->badge($this->records['confirmed'] ?? 0),

            'Cancelled' => Tab::make()
                ->modifyQueryUsing(
                    fn ($query) =>
                    $query->where('status', 'cancelled')
                )->badge($this->records['cancelled'] ?? 0),
        ];
    }
}
