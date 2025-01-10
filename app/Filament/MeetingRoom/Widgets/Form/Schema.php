<?php

namespace App\Filament\MeetingRoom\Widgets\Form;

use App\Filament\MeetingRoom\Widgets\Resource\WidgetResource;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;

class Schema
{
    public static function eventSchema()
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
                    ->hint(fn (Get $get) => WidgetResource::getAvailableRooms($get('start_time'), $get('end_time'))->count . ' rooms available')
                    ->options(fn (Get $get) => WidgetResource::getAvailableRooms($get('start_time'), $get('end_time')))
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

    public static function widgetSchema()
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
}
