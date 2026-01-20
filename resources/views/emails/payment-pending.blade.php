@extends('emails.layouts.main')

@section('content')
    <div class="text-center">
        <h2 style="color: #f57c00;">⏳ Payment Pending</h2>
        <p class="lead">Ref: {{ $booking->booking_id }}</p>
    </div>

    <p>Dear {{ $booking->customer_name }},</p>

    <p>We are still waiting for payment confirmation from your bank. If you were charged, please do nothing—this may take a few minutes to sync.</p>

    <div class="alert alert-warning">
        <strong>Status:</strong> Pending Confirmation
    </div>

    <div class="text-center" style="margin-top: 30px;">
        <a href="{{ url('/bookings/' . $booking->booking_id) }}" class="btn" style="background-color: #f57c00;">View Booking Status</a>
    </div>
@endsection
