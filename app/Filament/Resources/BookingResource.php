<?php

namespace App\Filament\Resources;

use App\Enums\BookingState;
use App\Filament\Resources\BookingResource\Pages;
use App\Models\Booking;
use App\Services\BookingService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class BookingResource extends Resource
{
    protected static ?string $model = Booking::class;
    protected static ?string $navigationIcon = 'heroicon-o-ticket';
    protected static ?string $navigationGroup = 'Bookings';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Booking Information')
                    ->schema([
                        Forms\Components\TextInput::make('reference')
                            ->disabled()
                            ->label('Reference'),
                        Forms\Components\Select::make('state')
                            ->options(collect(BookingState::cases())->mapWithKeys(
                                fn($state) => [$state->value => $state->label()]
                            ))
                            ->disabled()
                            ->label('State'),
                        Forms\Components\DateTimePicker::make('state_changed_at')
                            ->disabled(),
                    ])->columns(3),

                Forms\Components\Section::make('Customer')
                    ->schema([
                        Forms\Components\Select::make('customer_id')
                            ->relationship('customer', 'email')
                            ->searchable()
                            ->required(),
                    ]),

                Forms\Components\Section::make('Pricing')
                    ->schema([
                        Forms\Components\TextInput::make('subtotal')
                            ->numeric()
                            ->prefix('AED')
                            ->disabled(),
                        Forms\Components\TextInput::make('tax')
                            ->numeric()
                            ->prefix('AED')
                            ->disabled(),
                        Forms\Components\TextInput::make('total')
                            ->numeric()
                            ->prefix('AED')
                            ->disabled(),
                    ])->columns(3),

                Forms\Components\Section::make('Visit Details')
                    ->schema([
                        Forms\Components\DatePicker::make('visit_date'),
                        Forms\Components\TimePicker::make('visit_time'),
                    ])->columns(2),

                Forms\Components\Section::make('Notes')
                    ->schema([
                        Forms\Components\Textarea::make('notes')  
                            ->rows(3),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('reference')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('customer.full_name')
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('customer.email')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('state')
                    ->badge()
                    ->color(fn (BookingState $state): string => $state->color())
                    ->formatStateUsing(fn (BookingState $state): string => $state->label()),
                
                Tables\Columns\TextColumn::make('total')
                    ->money('AED')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('visit_date')
                    ->date()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('total_quantity')
                    ->label('Tickets')
                    ->alignCenter(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\IconColumn::make('is_hold_expired')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('state')
                    ->options(collect(BookingState::cases())->mapWithKeys(
                        fn($state) => [$state->value => $state->label()]
                    ))
                    ->multiple(),
                
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from'),
                        Forms\Components\DatePicker::make('created_until'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['created_from'], fn ($q, $date) => $q->whereDate('created_at', '>=', $date))
                            ->when($data['created_until'], fn ($q, $date) => $q->whereDate('created_at', '<=', $date));
                    }),
                
                Tables\Filters\Filter::make('visit_date')
                    ->form([
                        Forms\Components\DatePicker::make('visit_from'),
                        Forms\Components\DatePicker::make('visit_until'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['visit_from'], fn ($q, $date) => $q->whereDate('visit_date', '>=', $date))
                            ->when($data['visit_until'], fn ($q, $date) => $q->whereDate('visit_date', '<=', $date));
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('refund')
                    ->requiresConfirmation()
                    ->icon('heroicon-o-arrow-path')
                    ->color('danger')
                    ->action(function (Booking $record) {
                        $paymentService = app(\App\Services\PaymentService::class);
                        if ($payment = $record->payments->first()) {
                            try {
                                $paymentService->refundPayment($payment, $record->total, 'Admin Refund');
                                \Filament\Notifications\Notification::make()->title('Refund Processed')->success()->send();
                            } catch (\Exception $e) {
                                \Filament\Notifications\Notification::make()->title('Refund Failed: ' . $e->getMessage())->danger()->send();
                            }
                        } else {
                            \Filament\Notifications\Notification::make()->title('No payment found to refund')->warning()->send();
                        }
                    })
                    ->visible(fn (Booking $record) => in_array($record->state, [BookingState::CONFIRMED, BookingState::ISSUED])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Booking Details')
                    ->schema([
                        Infolists\Components\TextEntry::make('reference'),
                        Infolists\Components\TextEntry::make('state')
                            ->badge()
                            ->color(fn (BookingState $state): string => $state->color())
                            ->formatStateUsing(fn (BookingState $state): string => $state->label()),
                        Infolists\Components\TextEntry::make('state_changed_at')
                            ->dateTime(),
                        Infolists\Components\TextEntry::make('created_at')
                            ->dateTime(),
                    ])->columns(2),

                Infolists\Components\Section::make('Customer')
                    ->schema([
                        Infolists\Components\TextEntry::make('customer.full_name'),
                        Infolists\Components\TextEntry::make('customer.email'),
                        Infolists\Components\TextEntry::make('customer.phone'),
                    ])->columns(3),

                Infolists\Components\Section::make('Items')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('items')
                            ->schema([
                                Infolists\Components\TextEntry::make('ticket_name'),
                                Infolists\Components\TextEntry::make('quantity'),
                                Infolists\Components\TextEntry::make('unit_price')
                                    ->money('AED'),
                                Infolists\Components\TextEntry::make('subtotal')
                                    ->money('AED'),
                                Infolists\Components\TextEntry::make('visit_date')
                                    ->date(),
                            ])->columns(5),
                    ]),

                Infolists\Components\Section::make('Payment')
                    ->schema([
                        Infolists\Components\TextEntry::make('total')
                            ->money('AED'),
                        Infolists\Components\TextEntry::make('payment_reference'),
                        Infolists\Components\TextEntry::make('payment_provider'),
                    ])->columns(3),
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
            'view' => Pages\ViewBooking::route('/{record}'),
            'edit' => Pages\EditBooking::route('/{record}/edit'),
        ];
    }
}
