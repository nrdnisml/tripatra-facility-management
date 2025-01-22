<?php

namespace App\Filament\MeetingRoom\Resources;

use App\Filament\MeetingRoom\Resources\BookingResource\Pages;
use App\Models\MeetingRoom\Room;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BookingResource extends Resource
{
    protected static ?string $model = \App\Models\MeetingRoom\Booking::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        $accounts = \App\Helpers\TripatraUser::getAccountNameIds();
        return $form
            ->schema([
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
            ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('room.room_name'),
                TextColumn::make('bookedBy.name'),
                TextColumn::make('title')->label('Meeting Description'),
                TextColumn::make('start_time')
                    ->dateTime(),
                TextColumn::make('end_time')
                    ->dateTime(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'booked' => 'warning',
                        'confirmed' => 'success',
                        'cancelled' => 'danger',
                    })
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBookings::route('/'),
            'create' => Pages\CreateBooking::route('/create'),
            'edit' => Pages\EditBooking::route('/{record}/edit'),
        ];
    }

    private static function getAvailableRooms($startTime, $endTime): array|\Illuminate\Support\Collection
    {
        $data = Room::query()
            ->where('bookable', true)
            ->when($startTime && $endTime, function ($query) use ($startTime, $endTime) {
                $query->whereDoesntHave('bookings', function ($query) use ($startTime, $endTime) {
                    $query->where('start_time', '<', $endTime)
                        ->where('end_time', '>', $startTime);
                });
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
}
