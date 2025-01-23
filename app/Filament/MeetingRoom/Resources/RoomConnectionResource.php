<?php

namespace App\Filament\MeetingRoom\Resources;

use App\Filament\MeetingRoom\Resources\RoomConnectionResource\Pages;
use App\Filament\MeetingRoom\Resources\RoomConnectionResource\RelationManagers;
use App\Models\MeetingRoom\Room;
use App\Models\MeetingRoom\RoomConnection;
use Awcodes\TableRepeater\Components\TableRepeater;
use Awcodes\TableRepeater\Header;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RoomConnectionResource extends Resource
{
    protected static ?string $model = RoomConnection::class;

    protected static ?string $navigationIcon = 'heroicon-o-link';

    protected static ?string $navigationParentItem = 'Rooms';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TableRepeater::make('create_room_connection')
                    ->headers([
                        Header::make('Meeting Room'),
                    ])
                    ->schema([
                        Select::make('connected_rooms')
                            ->options(function () {
                                return Room::orderBy('floor')->get()->mapWithKeys(function ($room) {
                                    return [$room->id => $room->room_name . ' (floor ' . $room->floor . ')'];
                                })->toArray();
                            })
                            ->required(),
                    ])
                    ->columns(1),
                TextInput::make('capacity')
                    ->label('Total capacity')
                    ->mask(\Filament\Support\RawJs::make(<<<'JS'
                            $input.startsWith('34') || $input.startsWith('37') ? '99' : '99'
                        JS))
                    ->placeholder('0')
                    ->required(),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
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
            'index' => Pages\ListRoomConnections::route('/'),
            'create' => Pages\CreateRoomConnection::route('/create'),
            'edit' => Pages\EditRoomConnection::route('/{record}/edit'),
        ];
    }
}
