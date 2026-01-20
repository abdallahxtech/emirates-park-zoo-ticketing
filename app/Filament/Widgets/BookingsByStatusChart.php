<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use App\Enums\BookingState;
use Filament\Widgets\ChartWidget;

class BookingsByStatusChart extends ChartWidget
{
    protected static ?string $heading = 'Bookings by Status';
    protected static ?int $sort = 4;

    protected function getData(): array
    {
        $data = Booking::select('state', \Illuminate\Support\Facades\DB::raw('count(*) as count'))
            ->groupBy('state')
            ->pluck('count', 'state')
            ->toArray();

        $labels = [];
        $values = [];
        $colors = [];

        foreach ($data as $stateValue => $count) {
             $state = BookingState::tryFrom($stateValue);
             $labels[] = $state ? $state->label() : $stateValue;
             $values[] = $count;
             $colors[] = $state ? $state->color() : 'gray';
        }

        return [
            'datasets' => [
                [
                    'label' => 'Bookings',
                    'data' => $values,
                    'backgroundColor' => $colors, // This depends on if ChartJS supports array of colors for bar/pie
                    // For bar chart, ChartJS typically wants one color per dataset or array matching data length
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
