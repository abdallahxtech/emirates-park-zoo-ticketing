<?php

namespace App\Filament\Pages;

use App\Models\Booking;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

class CalendarView extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-calendar';
    protected static ?string $navigationLabel = 'Calendar';
    protected static ?string $navigationGroup = 'Bookings';
    protected static ?int $navigationSort = 2;
    protected static string $view = 'filament.pages.calendar-view';

    public string $currentMonth;
    public string $currentYear;

    public function mount(): void
    {
        $this->currentMonth = now()->format('m');
        $this->currentYear = now()->format('Y');
    }

    /**
     * Get bookings for the calendar
     */
    public function getBookingsForMonth(): Collection
    {
        $startDate = \Carbon\Carbon::create($this->currentYear, $this->currentMonth, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        return Booking::with(['customer', 'items'])
            ->whereBetween('visit_date', [$startDate, $endDate])
            ->whereIn('state', ['HOLD', 'PENDING_PAYMENT', 'PAID', 'TICKETS_ISSUED', 'CONFIRMED'])
            ->orderBy('visit_date')
            ->orderBy('visit_time')
            ->get()
            ->groupBy(fn($booking) => $booking->visit_date->format('Y-m-d'));
    }

    /**
     * Get calendar days
     */
    public function getCalendarDays(): array
    {
        $startDate = \Carbon\Carbon::create($this->currentYear, $this->currentMonth, 1);
        $endDate = $startDate->copy()->endOfMonth();
        
        $days = [];
        $currentDate = $startDate->copy()->startOfWeek();
        
        while ($currentDate <= $endDate->endOfWeek()) {
            $days[] = [
                'date' => $currentDate->format('Y-m-d'),
                'day' => $currentDate->format('d'),
                'isCurrentMonth' => $currentDate->month == $this->currentMonth,
                'isToday' => $currentDate->isToday(),
            ];
            $currentDate->addDay();
        }

        return $days;
    }

    public function previousMonth(): void
    {
        $date = \Carbon\Carbon::create($this->currentYear, $this->currentMonth, 1)->subMonth();
        $this->currentMonth = $date->format('m');
        $this->currentYear = $date->format('Y');
    }

    public function nextMonth(): void
    {
        $date = \Carbon\Carbon::create($this->currentYear, $this->currentMonth, 1)->addMonth();
        $this->currentMonth = $date->format('m');
        $this->currentYear = $date->format('Y');
    }

    public function today(): void
    {
        $this->currentMonth = now()->format('m');
        $this->currentYear = now()->format('Y');
    }
}
