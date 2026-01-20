<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class SalesBySourceChart extends ChartWidget
{
    protected static ?string $heading = 'Sales by Source';
    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $data = Booking::select('source', DB::raw('count(*) as count'))
            ->groupBy('source')
            ->pluck('count', 'source')
            ->toArray();

        // Ensure we have labels even if data is empty or null source
        $labels = array_keys($data);
        $values = array_values($data);
        
        // Fallback for null source
        $labels = array_map(fn($l) => $l ?: 'Unknown', $labels);

        return [
            'datasets' => [
                [
                    'label' => 'Bookings',
                    'data' => $values,
                    'backgroundColor' => [
                        '#00A651', // Brand Green
                        '#36A2EB', // Blue
                        '#FFCE56', // Yellow
                        '#FF6384', // Red
                        '#4BC0C0', // Teal
                    ],
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
