<?php

namespace App\Filament\MeetingRoom\Widgets\Form;

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
use Icetalker\FilamentPicker\Forms\Components\Picker;
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
        $availableRooms = 0;
        return [
            Step::make('Booking Time')->schema([
                Placeholder::make('Available')
                    ->label('Room Availability Related to Selected Date & Time : ' . $availableRooms),
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

                ])->columns(2)
            ]),
            Step::make('Meeting Participants')->schema([
                Fieldset::make('Participants')->schema([
                    TextInput::make('number_of_participants')
                        ->label('Number of Participants')
                        ->required()
                        ->columnSpanFull(),
                    Select::make('internal_participants')
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
                        ]),
                ])->columns(1),
            ]),
            Step::make('Select Room')->schema([
                Fieldset::make('Select Meeting Room & Layout')->schema([
                    Select::make('room_id')
                        ->label('Meeting Rooms')
                        ->hint(fn (Get $get) => WidgetResource::getAvailableRooms($get('start_time'), $get('end_time'))->count . ' rooms available')
                        ->options(fn (Get $get) => WidgetResource::getAvailableRooms($get('start_time'), $get('end_time')))
                        ->searchable()
                        ->preload()
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
                            'CLASSROOM' => 'CLASSROOM',
                            'U-SHAPE' => 'U-SHAPE',
                            'ROUND TABLE' => 'ROUND TABLE',
                            'THEATER' => 'THEATER',
                        ])
                        ->icons([
                            'CLASSROOM' => 'heroicon-o-home',
                            'U-SHAPE' => 'heroicon-o-paper-airplane',
                            'ROUND TABLE' => 'heroicon-o-truck',
                            'THEATER' => 'heroicon-o-truck',
                        ])
                        ->imageSize(100)
                        ->images([
                            'CLASSROOM' => 'https://source.unsplash.com/random/100x100',
                            'U-SHAPE' => 'https://source.unsplash.com/random/100x100/?airplane',
                            'ROUND TABLE' => 'https://source.unsplash.com/random/100x100?truck',
                            'THEATER' => 'https://source.unsplash.com/random/100x100?truck',
                        ])
                        ->default('THEATER'),
                ])->columns(1),
            ]),
            Step::make('Booking Details')->schema([
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
                    // Hidden Fields
                    Hidden::make('booked_by')->default(auth()->user()->id),
                    Hidden::make('status')->default('booked'),
                ])
            ])
        ];
    }

    /*************  ✨ Codeium Command ⭐  *************/
    /**
     * Get the schema of the form.
     *
     * @return array
     */
    /******  1e3f8a03-b6d3-4269-98de-e20d84cab24a  *******/
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
                    ->hint(fn (Get $get) => WidgetResource::getAvailableRooms($get('start_time'), $get('end_time'))->count . ' rooms available')
                    ->options(fn (Get $get) => WidgetResource::getAvailableRooms($get('start_time'), $get('end_time'), $get('room_id')))
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
            TextInput::make('title')
                ->label('Meeting Description')
                ->required(),
            // Hidden Fields
            Hidden::make('booked_by')->default(auth()->user()->id),
            Hidden::make('status')->default('booked'),
        ];
    }
}
