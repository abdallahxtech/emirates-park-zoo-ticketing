<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;
use App\Enums\BookingState;

class OperationsCalendar extends FullCalendarWidget
{
    public function fetchEvents(array $fetchInfo): array
    {
        return Booking::query()
            ->whereIn('state', [BookingState::CONFIRMED, BookingState::ISSUED])
            ->whereDate('visit_date', '>=', $fetchInfo['start'])
            ->whereDate('visit_date', '<=', $fetchInfo['end'])
            ->get()
            ->map(function (Booking $booking) {
                return [
                    'id'    => $booking->id,
                    'title' => "{$booking->customer->full_name} ({$booking->total_quantity})",
                    'start' => $booking->visit_date->format('Y-m-d') . ' ' . ($booking->visit_time ? $booking->visit_time->format('H:i:s') : '00:00:00'),
                    'url'   => \App\Filament\Resources\BookingResource::getUrl('edit', ['record' => $booking]),
                    'shouldOpenInNewTab' => true,
                    'extendedProps' => [
                        'status' => $booking->state->label(),
                    ],
                    'backgroundColor' => match ($booking->state) {
                        BookingState::CONFIRMED => '#10b981', // green-500
                        BookingState::ISSUED => '#3b82f6',    // blue-500
                        default => '#6b7280',
                    },
                    'borderColor' => match ($booking->state) {
                        BookingState::CONFIRMED => '#059669', // green-600
                        BookingState::ISSUED => '#2563eb',    // blue-600
                        default => '#4b5563',
                    },
                ];
            })
            ->toArray();
    }

    public static function canView(): bool
    {
        return true;
    }
}
