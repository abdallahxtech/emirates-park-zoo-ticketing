@extends('emails.layouts.main')

@section('content')
    <div class="text-center">
        <h2 style="color: #C6A700;">⭐ VIP Experience Confirmed</h2>
        <p class="lead">{{ $item->product->name }}</p>
        <p>Ref: {{ $booking->booking_id }}</p>
    </div>

    <p>Dear {{ $booking->customer_name }}, prepare for an unforgettable experience!</p>

    <div class="vip-box">
        <div class="vip-title">Experience Details</div>
        <p><strong>Date:</strong> {{ $booking->visit_date->format('l, d M Y') }}</p>
        <p><strong>Time:</strong> {{ $item->time_slot ?? 'Flexible' }}</p>
        <p><strong>Guests:</strong> {{ $item->quantity }}</p>
        
        @if($item->food_preference)
            <hr style="border: 0; border-top: 1px dashed #ffe0b2;">
            <p><strong>🍽️ Food Selection:</strong> {{ $item->food_preference }}</p>
            @if($item->dietary_notes)
                <p><strong>⚠ Dietary Notes:</strong> {{ $item->dietary_notes }}</p>
            @endif
        @endif
    </div>

    <h3>🚀 Arrival Instructions</h3>
    <ul>
        <li>Please arrive <strong>20 minutes</strong> before your scheduled time.</li>
        <li>Proceed to the <strong>VIP Guest Relations</strong> desk at the main entrance.</li>
        <li>Show the QR code below to your host.</li>
    </ul>

    <div class="text-center" style="margin: 30px 0;">
        <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={{ $booking->booking_id }}" alt="QR Code">
        <br><br>
        <a href="{{ url('/bookings/' . $booking->booking_id . '/download') }}" class="btn">📥 Download VIP Pass</a>
    </div>
@endsection
