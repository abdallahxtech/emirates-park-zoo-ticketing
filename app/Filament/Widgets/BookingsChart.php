<?php

namespace App\Filament\Widgets;

use App\Enums\BookingState;
use App\Models\Booking;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class BookingsChart extends ChartWidget
{
    protected static ?string $heading = 'Bookings Overview';
    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    public ?string $filter = '7days';

    protected function getFilters(): ?array
    {
        return [
            '7days' => 'Last 7 days',
            '30days' => 'Last 30 days',
            '90days' => 'Last 90 days',
            'year' => 'This year',
        ];
    }

    protected function getData(): array
    {
        $activeFilter = $this->filter;

        $startDate = match ($activeFilter) {
            '7days' => now()->subDays(7),
            '30days' => now()->subDays(30),
            '90days' => now()->subDays(90),
            'year' => now()->startOfYear(),
            default => now()->subDays(7),
        };

        // Get confirmed bookings
        $confirmed = Booking::where('created_at', '>=', $startDate)
            ->where('state', BookingState::CONFIRMED->value)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Get failed bookings
        $failed = Booking::where('created_at', '>=', $startDate)
            ->where('state', BookingState::PAYMENT_FAILED->value)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $dates = [];
        $confirmedData = [];
        $failedData = [];

        $currentDate = $startDate->copy();
        while ($currentDate <= now()) {
            $dateStr = $currentDate->toDateString();
            $dates[] = $currentDate->format('M d');
            
            $confirmedData[] = $confirmed->where('date', $dateStr)->first()->count ?? 0;
            $failedData[] = $failed->where('date', $dateStr)->first()->count ?? 0;
            
            $currentDate->addDay();
        }

        return [
            'datasets' => [
                [
                    'label' => 'Confirmed',
                    'data' => $confirmedData,
                    'backgroundColor' => 'rgba(34, 197, 94, 0.2)',
                    'borderColor' => 'rgb(34, 197, 94)',
                ],
                [
                    'label' => 'Failed',
                    'data' => $failedData,
                    'backgroundColor' => 'rgba(239, 68, 68, 0.2)',
                    'borderColor' => 'rgb(239, 68, 68)',
                ],
            ],
            'labels' => $dates,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
