<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use Filament\Widgets\ChartWidget;

class RevenueChart extends ChartWidget
{
    protected static ?string $heading = 'Revenue Overview';
    protected static ?int $sort = 3;

    public ?string $filter = '30days';

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
            default => now()->subDays(30),
        };

        $revenue = Booking::where('created_at', '>=', $startDate)
            ->whereIn('state', ['PAID', 'TICKETS_ISSUED', 'CONFIRMED'])
            ->selectRaw('DATE(created_at) as date, SUM(total) as revenue')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $dates = [];
        $revenueData = [];

        $currentDate = $startDate->copy();
        while ($currentDate <= now()) {
            $dateStr = $currentDate->toDateString();
            $dates[] = $currentDate->format('M d');
            $revenueData[] = (float) ($revenue->where('date', $dateStr)->first()->revenue ?? 0);
            $currentDate->addDay();
        }

        return [
            'datasets' => [
                [
                    'label' => 'Revenue (AED)',
                    'data' => $revenueData,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.2)',
                    'borderColor' => 'rgb(59, 130, 246)',
                    'fill' => true,
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
