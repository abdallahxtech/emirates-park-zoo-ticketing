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

        try {
            // Determine transaction items
            $saleItems = $booking->items->map(function ($item) {
                return [
                    'ItemCode' => $item->product->galaxy_id ?? 'STD-'.$item->product->id,
                    'Quantity' => $item->quantity,
                    'UnitPrice' => $item->unit_price,
                    'VisitDate' => $booking->visit_date->format('Y-m-d'),
                ];
            })->toArray();

            // Call Galaxy Gateway API
            $response = \Illuminate\Support\Facades\Http::withBasicAuth($this->username, $this->password)
                ->timeout(30)
                ->post("{$this->apiUrl}/vs/sales/transaction", [
                    'TransactionId' => $booking->reference,
                    'Customer' => [
                        'Email' => $booking->customer_email,
                        'Phone' => $booking->customer_phone,
                    ],
                    'Items' => $saleItems,
                ]);

            if ($response->failed()) {
                Log::error("Galaxy API Error: " . $response->body());
                throw new \Exception("Galaxy API Request Failed");
            }

            $data = $response->json();
            $tickets = collect();

            // Parse response to create tickets (Assuming standard response structure)
            foreach ($data['Tickets'] ?? [] as $ticketData) {
                $tickets->push(Ticket::create([
                    'booking_id' => $booking->id,
                    'booking_item_id' => null, // Would need mapping
                    'ticket_type_id' => $ticketData['ItemCode'] ?? 'UNKNOWN',
                    'barcode' => $ticketData['Barcode'] ?? Str::random(12),
                    'qr_code' => $this->generateQrCodeContent($ticketData['Barcode'] ?? 'ERROR'),
                    'status' => 'valid',
                    'generated_at' => now(),
                    'metadata' => $ticketData,
                ]));
            }

            $booking->update(['state' => \App\Enums\BookingState::ISSUED]);
            return $tickets;

        } catch (\Exception $e) {
            Log::error("Galaxy Integration Failed: " . $e->getMessage());
            // Fallback to mock if configured or critical failure handling
            // For now, re-throw to alert system
            throw $e;
        }
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
