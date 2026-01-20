<?php

namespace App\Services;

use App\Models\Booking;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected ?string $apiUrl;
    protected ?string $apiKey;
    protected ?string $senderId;

    public function __construct()
    {
        // Future config for provider (e.g. Twilio or Meta)
        $this->apiUrl = config('services.whatsapp.api_url');
        $this->apiKey = config('services.whatsapp.api_key');
    }

    /**
     * Send booking confirmation via WhatsApp
     */
    public function sendConfirmation(Booking $booking): bool
    {
        $message = "Hello {$booking->customer_name}, your booking #{$booking->booking_id} at Emirates Park Zoo is confirmed! Access your tickets here: " . url('/tickets/' . $booking->booking_id);
        
        return $this->sendMessage($booking->customer_phone, $message);
    }

    /**
     * Send generic message
     */
    public function sendMessage(string $phone, string $message): bool
    {
        Log::info("WhatsApp Message to {$phone}: {$message}");

        // if mock mode or no credentials
        if (empty($this->apiKey)) {
            return true;
        }

        // Example implementation for a generic provider
        /*
        try {
            Http::withToken($this->apiKey)->post($this->apiUrl, [
                'to' => $phone,
                'message' => $message
            ]);
            return true;
        } catch (\Exception $e) {
            Log::error("WhatsApp failed: " . $e->getMessage());
            return false;
        }
        */
        
        return true;
    }
}
