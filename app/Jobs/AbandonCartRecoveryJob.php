<?php

namespace App\Jobs;

use App\Models\Lead;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AbandonCartRecoveryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        // Find leads created > 30 mins ago, still 'new', with email
        $leads = Lead::where('status', 'new')
            ->where('created_at', '<', now()->subMinutes(30))
            ->where('created_at', '>', now()->subHours(24)) // Don't spam really old ones
            ->whereNotNull('email')
            ->get();

        foreach ($leads as $lead) {
            // Send Recovery Email
            // \Mail::to($lead->email)->send(new \App\Mail\CompleteYourBooking($lead));
            
            // Mark as contacted/abandoned
            $lead->update(['status' => 'abandoned']);
        }
    }
}
