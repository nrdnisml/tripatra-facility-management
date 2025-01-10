<?php

namespace App\Filament\MeetingRoom\Resources\RoomResource\RelationManagers;

use App\Models\MeetingRoom\Room;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ConnectedRoomsRelationManager extends RelationManager
{
    protected static string $relationship = 'connections';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Hidden::make('room_id')->default($this->ownerRecord->id),
                Forms\Components\Select::make('connected_room_id')
                    ->label('Room Name')
                    ->options(Room::query()
                        ->where('floor', $this->ownerRecord->floor)
                        ->where('id', '!=', $this->ownerRecord->id)
                        ->pluck('room_name', 'id'))
                    ->searchable()
                    ->multiple()
                    ->required()
            ])->columns(1);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('room.room_name')
            ->columns([
                Tables\Columns\TextColumn::make('room.room_name'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
            ])
            ->actions([
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}