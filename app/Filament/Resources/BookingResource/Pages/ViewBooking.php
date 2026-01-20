<?php

namespace App\Filament\Resources\BookingResource\Pages;

use App\Filament\Resources\BookingResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewBooking extends ViewRecord
{
    protected static string $resource = BookingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\Action::make('resend_confirmation')
                ->label('Resend Email')
                ->icon('heroicon-o-envelope')
                ->action(function () {
                     // Logic to resend confirmation email
                     \App\Jobs\IssueTicketsJob::dispatch($this->record);
                     \Filament\Notifications\Notification::make()
                        ->title('Confirmation email queued')
                        ->success()
                        ->send();
                })
                ->requiresConfirmation(),
        ];
    }
}
