@extends('emails.layouts.main')

@section('content')
    <div class="text-center">
        <h2>🛠️ New Booking Confirmed</h2>
        <p>Ref: {{ $booking->booking_id }}</p>
    </div>

    <table class="table">
        <tr>
            <th>Total Amount</th>
            <td>AED {{ number_format($booking->total_amount, 2) }} (Paid)</td>
        </tr>
        <tr>
            <th>Customer</th>
            <td>{{ $booking->customer_name }}<br>{{ $booking->customer_phone }}</td>
        </tr>
    </table>

    <div class="text-center">
        <a href="{{ url('/admin/bookings/' . $booking->id) }}" class="btn">View in Admin Panel</a>
    </div>
@endsection
