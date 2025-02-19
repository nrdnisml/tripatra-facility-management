<?php

namespace App\Filament\MeetingRoom\Resources;

use App\Filament\MeetingRoom\Resources\RoomResource\Pages;
use App\Models\MeetingRoom\Room;
use Awcodes\TableRepeater\Components\TableRepeater;
use Awcodes\TableRepeater\Header;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Icetalker\FilamentPicker\Forms\Components\Picker;

class RoomResource extends Resource
{
    protected static ?string $model = Room::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('room_name')
                    ->autocapitalize('words')
                    ->required(),
                Select::make('designation')
                    ->options([
                        'internal' => 'Internal',
                        'eksternal' => 'Eksternal',
                    ])
                    ->default('internal')
                    ->required(),
                Hidden::make('capacity')
                    ->label('Room capacity')
                    ->mask(\Filament\Support\RawJs::make(<<<'JS'
                            $input.startsWith('34') || $input.startsWith('37') ? '99' : '99'
                        JS))
                    ->placeholder('0')
                    ->live()
                    ->afterStateUpdated(fn(Forms\Set $set, $state) => $set('facilities', [
                        'Kursi' => $state,
                        'Meja' => ceil($state / 2),
                        'LCD Proyektor' => 1,
                        'Monitor' => 0,
                    ])),
                TextInput::make('floor')
                    ->label('Room floor')
                    ->mask(\Filament\Support\RawJs::make(<<<'JS'
                            $input.startsWith('34') || $input.startsWith('37') ? '99' : '99'
                        JS))
                    ->placeholder('0')
                    ->required(),
                FileUpload::make('room_pictures')
                    ->multiple()
                    ->disk('azure')
                    ->directory('meeting-room')
                    ->openable()
                    ->reorderable()
                    ->appendFiles()
                    ->panelLayout('grid'),
                // Picker::make('room_layouts')
                //     ->options([
                //         'CLASSROOM' => 'Classroom',
                //         'U-SHAPE' => 'U-Shape',
                //         'ROUND-TABLE' => 'Round Table',
                //         'THEATER' => 'Theater',
                //     ])
                //     ->imageSize(100)
                //     ->images([
                //         'CLASSROOM' => asset('assets/img/meeting-room/classroom.png'),
                //         'U-SHAPE' => asset('assets/img/meeting-room/u-shape.png'),
                //         'ROUND-TABLE' => asset('assets/img/meeting-room/round-table.png'),
                //         'THEATER' => asset('assets/img/meeting-room/theater.png'),
                //     ]),
                // KeyValue::make('room_layouts')
                //     ->keyLabel('Layout Name')
                //     ->valueLabel('Capacity')
                //     ->default([
                //         'Classroom' => 0,
                //         'U-Shape' => 0,
                //         'Round Table' => 0,
                //         'Theater' => 0,
                //     ])
                //     ->editableKeys(false)
                //     ->deletable(false)
                //     ->addable(false),
                TableRepeater::make('room_layouts')
                    ->live()
                    ->defaultItems('4')
                    ->addable(false)
                    ->default([
                        [
                            'layout_name' => 'Classroom',
                            'capacity' => 0,
                        ],
                        [
                            'layout_name' => 'U-Shape',
                            'capacity' => 0,
                        ],
                        [
                            'layout_name' => 'Round Table',
                            'capacity' => 0,
                        ],
                        [
                            'layout_name' => 'Theater',
                            'capacity' => 0,
                        ],
                    ])
                    ->headers([
                        Header::make('Layout Name')->markAsRequired(),
                        Header::make('Capacity')->markAsRequired(),
                    ])
                    ->schema([
                        TextInput::make('layout_name')
                            ->required(),
                        TextInput::make('capacity')
                            ->required(),
                    ])
                    ->columns(1),
                Radio::make('bookable')
                    ->label('Is this room bookable?')
                    ->default(true)
                    ->boolean()
                    ->inline(),
                Radio::make('mergeable')
                    ->label('Is this room connectable?')
                    ->boolean()
                    ->default(false)
                    ->inline()
                    ->required(),
                KeyValue::make('facilities')
                    ->label('Room facilities')
                    ->keyLabel('Property name')
                    ->addActionLabel('Add property')
                    ->default([
                        'Kursi' => 0,
                        'Meja' => 0,
                        'LCD Proyektor' => 0,
                        'Monitor' => 0,
                    ])
                    ->columnSpanFull()

            ])->columns([
                'sm' => 1,
                'xl' => 2
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('room_name')
                    ->sortable()
                    ->searchable(isIndividual: true),
                TextColumn::make('capacity')
                    ->searchable(isIndividual: true)
                    ->suffix(' Persons')
                    ->sortable(),
                TextColumn::make('floor')
                    ->searchable(isIndividual: true)
                    ->sortable(),
                TextColumn::make('designation')
                    ->searchable(isIndividual: true)
                    ->extraAttributes(['class' => 'capitalize'])
                    ->sortable(),
                TextColumn::make('formatted_facilities')
                    ->label('Facilities')
                    ->listWithLineBreaks()
                    ->bulleted()
                    ->limitList(1)
                    ->expandableLimitedList()
                    ->sortable(),
                IconColumn::make('bookable')
                    ->sortable()
                    ->boolean()
                    ->tooltip(fn($record) => $record->bookable ? 'True' : 'False'),
                IconColumn::make('mergeable')
                    ->sortable()
                    ->boolean()
                    ->tooltip(fn($record) => $record->bookable ? 'True' : 'False'),
            ])
            ->filters([
                //
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->striped();
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRooms::route('/'),
            'create' => Pages\CreateRoom::route('/create'),
            'edit' => Pages\EditRoom::route('/{record}/edit'),
        ];
    }
}
