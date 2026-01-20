<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\InventoryService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AvailabilityController extends Controller
{
    public function __construct(
        private InventoryService $inventoryService
    ) {}

    /**
     * Get availability for tickets on specified dates
     * 
     * GET /api/availability?date=2024-06-01&ticket_ids[]=1&ticket_ids[]=2
     * GET /api/availability?start_date=2024-06-01&end_date=2024-06-30&ticket_ids[]=1
     */
    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required_without:start_date|date|after_or_equal:today',
            'start_date' => 'required_without:date|date|after_or_equal:today',
            'end_date' => 'required_with:start_date|date|after_or_equal:start_date',
            'ticket_ids' => 'required|array|min:1',
            'ticket_ids.*' => 'integer|exists:tickets,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        // Single date or date range
        if ($request->has('date')) {
            $date = Carbon::parse($request->date);
            $availability = $this->getAvailabilityForDate($request->ticket_ids, $date);
            
            return response()->json([
                'success' => true,
                'date' => $date->toDateString(),
                'availability' => $availability,
            ]);
        }

        // Date range
        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        
        $availability = $this->inventoryService->getAvailabilityForDateRange(
            $request->ticket_ids,
            $startDate,
            $endDate
        );

        return response()->json([
            'success' => true,
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
            'availability' => $this->formatAvailability($availability),
        ]);
    }

    /**
     * Get availability for a single date
     */
    private function getAvailabilityForDate(array $ticketIds, Carbon $date): array
    {
        $availability = [];
        
        foreach ($ticketIds as $ticketId) {
            $ticket = \App\Models\Ticket::find($ticketId);
            $availableQuantity = $this->inventoryService->getAvailableQuantity($ticketId, $date);
            
            $availability[] = [
                'ticket_id' => $ticketId,
                'ticket_name' => $ticket->name,
                'price' => $ticket->price,
                'currency' => $ticket->currency,
                'available_quantity' => $availableQuantity,
                'is_available' => $availableQuantity > 0,
                'daily_capacity' => $ticket->daily_capacity,
            ];
        }

        return $availability;
    }

    /**
     * Format date range availability for response
     */
    private function formatAvailability(array $availability): array
    {
        $formatted = [];
        
        foreach ($availability as $date => $tickets) {
            $formatted[$date] = array_map(function ($data) {
                return [
                    'ticket_id' => $data['ticket']->id,
                    'ticket_name' => $data['ticket']->name,
                    'price' => $data['ticket']->price,
                    'currency' => $data['ticket']->currency,
                    'available_quantity' => $data['available'],
                    'is_available' => $data['available'] > 0,
                    'daily_capacity' => $data['ticket']->daily_capacity,
                ];
            }, $tickets);
        }

        return $formatted;
    }
}
