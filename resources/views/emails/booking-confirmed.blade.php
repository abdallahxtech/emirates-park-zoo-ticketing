@extends('emails.layouts.main')

@section('content')
    <div class="text-center">
        <h2 style="color: #4CAF50;">✅ Booking Confirmed</h2>
        <p class="lead">Ref: {{ $booking->booking_id }}</p>
    </div>

    <p>Thank you <strong>{{ $booking->customer_name }}</strong>, your booking is confirmed.</p>
    
    <p>Your entry tickets and experience details are essential for your visit to <strong>{{ config('app.name') }}</strong>.</p>

    <div class="alert alert-success">
        <strong>Visit Date:</strong> {{ $booking->visit_date->format('l, d M Y') }}
    </div>

    <h3>📜 Order Summary</h3>
    <table class="table">
        <thead>
            <tr>
                <th>Item</th>
                <th>Qty</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($booking->items as $item)
            <tr>
                <td>
                    {{ $item->product->name }}
                    @if($item->time_slot) <br><small>🕒 {{ $item->time_slot }}</small> @endif
                </td>
                <td>{{ $item->quantity }}</td>
                <td>AED {{ number_format($item->subtotal, 2) }}</td>
            </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="2">Total Paid</td>
                <td>AED {{ number_format($booking->total_amount, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="text-center" style="margin: 30px 0;">
        <p>Scan this code at the entrance:</p>
        <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={{ $booking->booking_id }}" alt="QR Code">
        <br><br>
        <a href="{{ url('/bookings/' . $booking->booking_id . '/download') }}" class="btn">📥 Download Tickets (PDF)</a>
    </div>

    <p><strong>Need Help?</strong> Contact us at {{ config('app.support_phone') }} or reply to this email.</p>
@endsection
