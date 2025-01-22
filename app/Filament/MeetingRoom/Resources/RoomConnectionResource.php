<?php

namespace App\Filament\MeetingRoom\Resources;

use App\Filament\MeetingRoom\Resources\RoomConnectionResource\Pages;
use App\Filament\MeetingRoom\Resources\RoomConnectionResource\RelationManagers;
use App\Models\MeetingRoom\Room;
use App\Models\MeetingRoom\RoomConnection;
use Filament\Forms;
use Filament\Forms\Components\Select;
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
                Select::make('room_id')
                    ->options(Room::query()->pluck('room_name', 'id'))
                    ->searchable()
                    ->multiple()
                    ->required(),
            ]);
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