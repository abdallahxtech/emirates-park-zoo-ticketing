<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Ticket;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class GalaxyTicketingService
{
    protected ?string $apiUrl;
    protected ?string $username;
    protected ?string $password;
    protected bool $mockMode;

    public function __construct()
    {
        $this->apiUrl = config('services.galaxy.api_url');
        $this->username = config('services.galaxy.username');
        $this->password = config('services.galaxy.password');
        $this->mockMode = config('services.galaxy.mock_mode', true);
    }

    /**
     * Issue tickets for a confirmed booking
     * 
     * @return Collection<int, Ticket>
     */
    public function issueTickets(Booking $booking): Collection
    {
        if ($this->mockMode) {
            return $this->mockIssueTickets($booking);
        }

        // Real API implementation would go here
        // $response = Http::post(...)
        
        Log::warning("Real Galaxy API not implemented, falling back to mock");
        return $this->mockIssueTickets($booking);
    }

    protected function mockIssueTickets(Booking $booking): Collection
    {
        $tickets = collect();

        foreach ($booking->items as $item) {
            for ($i = 0; $i < $item->quantity; $i++) {
                $code = strtoupper(Str::random(12));
                
                $ticket = Ticket::create([
                    'booking_id' => $booking->id,
                    'booking_item_id' => $item->id,
                    'ticket_type_id' => $item->product->galaxy_id ?? 'STD-' . $item->product->id,
                    'barcode' => $code,
                    'qr_code' => $this->generateQrCodeContent($code),
                    'status' => 'valid',
                    'generated_at' => now(),
                    'metadata' => ['source' => 'mock_generator'],
                ]);

                $tickets->push($ticket);
            }
        }

        $booking->update(['state' => \App\Enums\BookingState::ISSUED]);

        return $tickets;
    }

    public function generateQrCodeContent(string $data): string
    {
        // returns SVG string by default
        return QrCode::size(200)->generate($data);
    }
}
