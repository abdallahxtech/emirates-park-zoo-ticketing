<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Release expired holds every minute
        $schedule->job(new \App\Jobs\ReleaseExpiredHoldsJob)
            ->everyMinute()
            ->name('release-expired-holds')
            ->withoutOverlapping();
        
        // Clean up old audit logs (optional, keep last 90 days)
        $schedule->command('model:prune', ['--model' => 'App\\Models\\AuditLog'])
            ->daily()
            ->at('02:00');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
