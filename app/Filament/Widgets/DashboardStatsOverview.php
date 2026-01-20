<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;

class DashboardStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $today = Carbon::today();
        
        $todayRevenue = Booking::whereDate('created_at', $today)
            ->whereIn('state', ['confirmed', 'completed', 'issued'])
            ->sum('total_amount');
            
        $todayBookings = Booking::whereDate('created_at', $today)
            ->whereIn('state', ['confirmed', 'completed', 'issued'])
            ->count();
            
        $pendingPayments = Booking::where('state', 'payment_failed')->count();

        return [
            Stat::make('Today\'s Revenue', 'AED ' . number_format($todayRevenue, 2))
                ->description('Sales processed today')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success')
                ->chart([7, 2, 10, 3, 15, 4, 17]), // Mock chart for UI feel

            Stat::make('Today\'s Bookings', $todayBookings)
                ->description('Confirmed bookings')
                ->descriptionIcon('heroicon-m-ticket')
                ->color('primary'),

            Stat::make('Failed Payments', $pendingPayments)
                ->description('Requires attention')
                ->descriptionIcon('heroicon-m-exclamation-circle')
                ->color('danger'),
        ];
    }
}
