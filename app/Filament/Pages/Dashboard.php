<?php

namespace App\Filament\Pages;

use App\Enums\BookingState;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Customer;
use App\Models\Ticket;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Widgets\StatsOverviewWidget\Stat;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static string $view = 'filament.pages.dashboard';

    /**
     * Get dashboard stats
     */
    public function getStats(): array
    {
        $today = now()->startOfDay();
        $thisMonth = now()->startOfMonth();

        return [
            // Today's bookings
            Stat::make('Today\'s Bookings', Booking::whereDate('created_at', $today)->count())
                ->description('Bookings created today')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('success')
                ->chart($this->getBookingsChartData(7)),

            // Today's revenue
            Stat::make('Today\'s Revenue', 'AED ' . number_format(
                Booking::whereDate('created_at', $today)
                    ->whereIn('state', [BookingState::PAID->value, BookingState::CONFIRMED->value])
                    ->sum('total'),
                2
            ))
                ->description('Confirmed bookings')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success'),

            // Active holds
            Stat::make('Active Holds', Booking::where('state', BookingState::HOLD->value)
                ->where('hold_expires_at', '>', now())
                ->count())
                ->description('Pending payment')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            // Month's revenue
            Stat::make('Month Revenue', 'AED ' . number_format(
                Booking::where('created_at', '>=', $thisMonth)
                    ->whereIn('state', [BookingState::PAID->value, BookingState::CONFIRMED->value])
                    ->sum('total'),
                2
            ))
                ->description('This month')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('info'),

            // Total customers
            Stat::make('Total Customers', Customer::count())
                ->description('All time')
                ->descriptionIcon('heroicon-m-users')
                ->color('info'),

            // Pending payments
            Stat::make('Pending Payments', Booking::where('state', BookingState::PENDING_PAYMENT->value)->count())
                ->description('Awaiting confirmation')
                ->descriptionIcon('heroicon-m-credit-card')
                ->color('warning'),
        ];
    }

    /**
     * Get bookings chart data for last N days
     */
    private function getBookingsChartData(int $days): array
    {
        $data = [];
        $startDate = now()->subDays($days - 1)->startOfDay();

        for ($i = 0; $i < $days; $i++) {
            $date = $startDate->copy()->addDays($i);
            $data[] = Booking::whereDate('created_at', $date)->count();
        }

        return $data;
    }
}
