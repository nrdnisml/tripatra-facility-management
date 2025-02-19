<?php

namespace App\Filament\MeetingRoom\Resources\BookingResource\FormSchema;

use App\Filament\MeetingRoom\Widgets\Resource\WidgetResource;
use Awcodes\TableRepeater\Components\TableRepeater;
use Awcodes\TableRepeater\Header;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Icetalker\FilamentPicker\Forms\Components\Picker;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;

class Schema
{
    public static function eventSchema()
    {
        $accounts = \App\Helpers\TripatraUser::getAccountNameIds();
        return self::steps($accounts);
    }

    private static function steps($accounts)
    {
        return [
            Step::make('Booking Time & Description')->schema([
                Placeholder::make('Available')
                    ->label('Room Availability')
                    ->content(fn(Get $get) => WidgetResource::getAvailableRooms($get('start_time'), $get('end_time'))->count . ' rooms available for selected time'),
                Fieldset::make('Booking Time')->schema([
                    DateTimePicker::make('start_time')
                        ->native(false)
                        ->displayFormat('d-M-Y h:i A')
                        ->label('Start Date & Time')
                        ->seconds(false)
                        ->required()
                        ->before('end_time')
                        ->live(onBlur: true),
                    DateTimePicker::make('end_time')
                        ->native(false)
                        ->displayFormat('d-M-Y h:i A')
                        ->label('End Date & Time')
                        ->seconds(false)
                        ->required()
                        ->after('start_time')
                        ->live(onBlur: true),
                ])->columns(2),
                Fieldset::make('Booking Details')->schema([
                    TextInput::make('title')
                        ->label('Meeting Description')
                        ->required()
                        ->columnSpanFull(),
                    Select::make('project_id')
                        ->label('Project Name')
                        ->helperText('Keep empty if not applicable')
                        ->options(\App\Models\Project::query()->pluck('project_name', 'id'))
                        ->columnSpanFull()
                        ->searchable(),
                    // Hidden Fields
                    Hidden::make('booked_by')->default(Auth::user()->id),
                    Hidden::make('status')->default('booked'),
                ]),
            ]),
            Step::make('Participants')->schema([
                Fieldset::make('Participants')->schema([
                    Select::make('booking_type')
                        ->label('Meeting For')
                        ->options([
                            'internal' => 'Internal',
                            'eksternal' => 'Eksternal',
                        ])
                        ->default('internal')
                        ->required()
                        ->columns(1),
                    TextInput::make('number_of_participants')
                        ->label('Number of Participants')
                        ->mask(\Filament\Support\RawJs::make(<<<'JS'
                            $input.startsWith('34') || $input.startsWith('37') ? '99' : '99'
                        JS))
                        ->placeholder('0')
                        ->required()
                        ->columns(1),
                    Select::make('internal_participants')
                        ->multiple()
                        ->options($accounts)
                        ->hint('Select internal participants to notify them about the meeting')
                        ->columnSpanFull(),
                    TableRepeater::make('external_participants')
                        ->headers([
                            Header::make('email'),
                            Header::make('company'),
                        ])
                        ->schema([
                            TextInput::make('email')->email(),
                            TextInput::make('company'),
                        ])
                        ->hint('Add external participants if applicable')
                        ->columnSpanFull(),
                ])->columns(2),
            ]),
            Step::make('Select Room')->schema([
                Fieldset::make('Select Meeting Room & Layout')->schema([
                    Select::make('room_id')
                        ->label('Meeting Rooms')
                        ->hint(fn(Get $get) => WidgetResource::getAvailableRooms($get('start_time'), $get('end_time'))->count . ' rooms available')
                        ->options(fn(Get $get) => WidgetResource::getAvailableRooms($get('start_time'), $get('end_time'), currentId: $get('room_id')))
                        ->searchable()
                        ->preload()
                        ->afterStateUpdated(function (Set $set, $state) {
                            $set('room_layouts', \App\Models\MeetingRoom\Room::find($state)->room_layouts);
                        })
                        ->live(onBlur: true)
                        ->required(),
                    Placeholder::make('RoomPreview')
                        ->content(function (Get $get): HtmlString {
                            $selected_room = $get('room_id');
                            if ($selected_room) {
                                $room = \App\Models\MeetingRoom\Room::find($selected_room);
                                $room_pictures = $room->room_pictures;
                                $room_pictures = is_array($room_pictures) ? $room_pictures : [];
                                $room_pictures = array_map(function ($picture) {
                                    $url = env('AZURE_URL_CONTAINER') . $picture;
                                    return "<a href='{$url}' target='_blank'>
                                                <img src='{$url}' class='w-32 h-32 object-cover border border-gray-300 rounded-lg transition-transform transform hover:-translate-y-1 hover:border-gray-500'>
                                            </a>";
                                }, $room_pictures);
                                return new HtmlString("<div class='flex flex-wrap gap-2'>" . implode(' ', $room_pictures) . "</div>");
                            }
                            return new HtmlString("<p>Picture Not Available</p>");
                        })->columnSpanFull(),
                    Picker::make('room_layouts')
                        ->options([
                            'CLASSROOM' => 'Classroom',
                            'U-SHAPE' => 'U-Shape',
                            'ROUND-TABLE' => 'Round Table',
                            'THEATER' => 'Theater',
                        ])
                        ->imageSize(100)
                        ->images([
                            'CLASSROOM' => asset('assets/img/meeting-room/classroom.png'),
                            'U-SHAPE' => asset('assets/img/meeting-room/u-shape.png'),
                            'ROUND-TABLE' => asset('assets/img/meeting-room/round-table.png'),
                            'THEATER' => asset('assets/img/meeting-room/theater.png'),
                        ])
                        ->default(fn(Get $get) =>
                        $get('room_id') ?
                            \App\Models\MeetingRoom\Room::find($get('room_id'))->room_layouts
                            : 'U-SHAPE'),
                ])->columns(1),
            ]),
        ];
    }

    public static function formSchema()
    {
        $accounts = \App\Helpers\TripatraUser::getAccountNameIds();
        return  [
            \Filament\Forms\Components\Wizard::make(
                self::steps($accounts)
            )->skippable()
        ];
    }

    public static function widgetSchema()
    {
        $accounts = \App\Helpers\TripatraUser::getAccountNameIds();
        return  [
            TextInput::make('title')
                ->label('Meeting Description')
                ->required(),
            Group::make([
                DateTimePicker::make('start_time')
                    ->native(false)
                    ->displayFormat('d-M-Y h:i A')
                    ->label('Start Date & Time')
                    ->seconds(false)
                    ->required()
                    ->before('end_time')
                    ->live(onBlur: true),
                DateTimePicker::make('end_time')
                    ->native(false)
                    ->displayFormat('d-M-Y h:i A')
                    ->label('End Date & Time')
                    ->seconds(false)
                    ->required()
                    ->after('start_time')
                    ->live(onBlur: true),
                Select::make('room_id')
                    ->label('Meeting Rooms')
                    ->hint(fn(Get $get) => WidgetResource::getAvailableRooms($get('start_time'), $get('end_time'))->count . ' rooms available')
                    ->options(fn(Get $get) => WidgetResource::getAvailableRooms($get('start_time'), $get('end_time'), currentId: $get('room_id')))
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
            Select::make('internal_participants')
                ->label('Participants')
                ->multiple()
                ->options($accounts),
            TableRepeater::make('external_participants')
                ->headers([
                    Header::make('email'),
                    Header::make('company'),
                ])
                ->schema([
                    TextInput::make('email')->email(),
                    TextInput::make('company'),
                ])
                ->hint('Add external participants if applicable')
                ->columnSpanFull(),
            // Hidden Fields
            Hidden::make('booked_by')->default(Auth::user()->id),
            Hidden::make('status')->default('booked'),
        ];
    }
}
