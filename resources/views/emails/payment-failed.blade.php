@extends('emails.layouts.main')

@section('content')
    <div class="text-center">
        <h2 style="color: #d32f2f;">❌ Payment Failed</h2>
        <p class="lead">Ref: {{ $booking->booking_id }}</p>
    </div>

    <p>Dear {{ $booking->customer_name }},</p>

    <p>Unfortunately, your payment could not be completed. No funds have been captured.</p>

    <h3>Booking Summary</h3>
    <table class="table">
        <tr class="total-row">
            <td colspan="2">Total Amount Due</td>
            <td>AED {{ number_format($booking->total_amount, 2) }}</td>
        </tr>
    </table>

    <div class="text-center" style="margin-top: 30px;">
        <a href="{{ url('/checkout/retry/' . $booking->booking_id) }}" class="btn" style="background-color: #d32f2f;">Try Payment Again</a>
    </div>
    
    <p class="text-center small" style="margin-top: 20px;">
        If you continue experiencing issues, please contact your bank or calling us at {{ config('app.support_phone') }}.
    </p>
@endsection
