<?php

namespace App\Filament\MeetingRoom\Resources;

use App\Filament\MeetingRoom\Resources\BookingResource\Pages;
use App\Filament\MeetingRoom\Widgets\Form\Schema;
use App\Models\MeetingRoom\Booking;
use App\Models\MeetingRoom\Room;
use Filament\Forms\Components\DatePicker;
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
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\QueryBuilder\Constraints\RelationshipConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\RelationshipConstraint\Operators\IsRelatedToOperator;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BookingResource extends Resource
{
    protected static ?string $model = Booking::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        $accounts = \App\Helpers\TripatraUser::getAccountNameIds();
        return $form
            ->schema(Schema::formSchema())->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(Booking::where('booked_by', auth()->user()->id))
            ->columns([
                TextColumn::make('room.room_name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('room.floor')
                    ->sortable()
                    ->searchable()
                    ->label('Floor'),
                TextColumn::make('bookedBy.name')
                    ->sortable(),
                TextColumn::make('title')
                    ->label('Meeting Description')
                    ->searchable(),
                TextColumn::make('start_time')
                    ->sortable()
                    ->label('Booking Start Date & Time')
                    ->dateTime(format: 'd-M-Y h:i A'),
                TextColumn::make('end_time')
                    ->sortable()
                    ->label('Booking End Date & Time')
                    ->dateTime(format: 'd-M-Y h:i A'),
                TextColumn::make('status')
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'booked' => 'warning',
                        'confirmed' => 'success',
                        'cancelled' => 'danger',
                    })
            ])
            ->filters([
                Tables\Filters\Filter::make('start_time')
                    ->form([
                        DatePicker::make('booked_from'),
                        DatePicker::make('booked_until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['booked_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('start_time', '>=', $date),
                            )
                            ->when(
                                $data['booked_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('start_time', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): ?string {
                        if (!$data['booked_from'] && !$data['booked_until']) {
                            return null;
                        }

                        if ($data['booked_from'] && !$data['booked_until']) {
                            return 'Booked at ' . \Carbon\Carbon::parse($data['booked_from'])->toFormattedDateString();
                        }

                        if (!$data['booked_from'] && $data['booked_until']) {
                            return 'Booked until ' . \Carbon\Carbon::parse($data['booked_until'])->toFormattedDateString();
                        }

                        if ($data['booked_from'] && $data['booked_until']) {
                            $from = 'Booked at ' . \Carbon\Carbon::parse($data['booked_from'])->toFormattedDateString();
                            $until = 'until ' . \Carbon\Carbon::parse($data['booked_until'])->toFormattedDateString();
                            return $from . ' ' . $until;
                        }
                    }),
            ])
            ->filtersTriggerAction(
                fn (\Filament\Tables\Actions\Action $action) => $action
                    ->button()
                    ->label('Filter'),
            )
            ->filtersFormColumns(1)
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->searchOnBlur();;
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
}
