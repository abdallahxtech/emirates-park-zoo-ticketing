<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TextLocalService
{
    protected string $apiKey;
    protected string $sender;
    protected string $baseUrl = 'https://api.txtlocal.com/send/';

    public function __construct()
    {
        $this->apiKey = config('services.textlocal.key', env('TEXTLOCAL_API_KEY'));
        $this->sender = config('services.textlocal.sender', 'EmiratesZoo');
    }

    public function sendBookingConfirmation($booking): bool
    {
        $message = "Welcome to Emirates Park Zoo! Your booking #{$booking->booking_id} is confirmed for {$booking->visit_date->format('d M Y')}. Download tickets: " . url('/bookings/' . $booking->booking_id . '/download');
        
        return $this->send($booking->customer_phone, $message);
    }

    public function send($number, $message): bool
    {
        if (config('app.env') !== 'production') {
            Log::info("TextLocal SMS Mock to {$number}: {$message}");
            return true;
        }

        try {
            $response = Http::post($this->baseUrl, [
                'apikey' => $this->apiKey,
                'numbers' => $this->formatNumber($number),
                'sender' => $this->sender,
                'message' => $message,
            ]);

            $result = $response->json();
            
            if (($result['status'] ?? '') === 'success') {
                return true;
            }
            
            Log::error('TextLocal Error: ' . json_encode($result));
            return false;
        } catch (\Exception $e) {
            Log::error('TextLocal Exception: ' . $e->getMessage());
            return false;
        }
    }

    protected function formatNumber($number): string
    {
        // Remove +, spaces, dashes
        $number = preg_replace('/[^0-9]/', '', $number);
        
        // Ensure UAE prefix (971) if missing and starts with 5
        if (str_starts_with($number, '05')) {
             $number = '971' . substr($number, 1);
        } elseif (str_starts_with($number, '5')) {
             $number = '971' . $number;
        }

        return $number;
    }
}
