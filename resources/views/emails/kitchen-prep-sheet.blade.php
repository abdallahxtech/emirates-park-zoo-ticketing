@extends('emails.layouts.main')

@section('content')
    <h2 style="color: #d32f2f;">👨‍🍳 Restaurant Prep Sheet</h2>
    <p class="lead">{{ $item->product->name }}</p>

    <div class="vip-box" style="border: 2px solid #d32f2f;">
        <div class="vip-title" style="color: #d32f2f;">Order Details</div>
        <p><strong>Date:</strong> {{ $booking->visit_date->format('l, d M Y') }}</p>
        <p><strong>Serving Time:</strong> {{ $item->time_slot ?? 'N/A' }}</p>
        <p><strong>Pax:</strong> {{ $item->quantity }} Guests</p>
        <hr>
        <p style="font-size: 18px;"><strong>🍽️ {{ $item->food_preference }}</strong></p>
        @if($item->dietary_notes)
            <p style="color: red; font-weight: bold; font-size: 16px;">⚠ NOTE: {{ $item->dietary_notes }}</p>
        @endif
    </div>

    <h3>✅ Kitchen Checklist</h3>
    <ul>
        <li>[ ] Check ingredients for {{ $item->food_preference }}</li>
        <li>[ ] Validate allergy safety ({{ $item->dietary_notes ?? 'None' }})</li>
        <li>[ ] Reserve table/area</li>
    </ul>
@endsection
