<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class SalesTrendChart extends ChartWidget
{
    protected static ?string $heading = 'Sales Trend (Last 7 Days)';
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        $data = Trend::model(Booking::class)
            ->between(
                start: now()->subDays(6),
                end: now(),
            )
            ->perDay()
            ->sum('total');

        return [
            'datasets' => [
                [
                    'label' => 'Revenue (AED)',
                    'data' => $data->map(fn(TrendValue $value) => $value->aggregate),
                    'borderColor' => '#00A651',
                    'fill' => true,
                    'backgroundColor' => 'rgba(0, 166, 81, 0.1)',
                ],
            ],
            'labels' => $data->map(fn(TrendValue $value) => $value->date),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
