@extends('emails.layouts.main')

@section('content')
    <div class="text-center">
        <h2>👋 Thank you for visiting!</h2>
    </div>

    <p>Dear {{ $booking->customer_name }},</p>

    <p>We hope you had a wild time at <strong>{{ config('app.name') }}</strong> today! 🦒🐘</p>

    <p>We would love to hear about your experience to help us improve.</p>

    <div class="text-center" style="margin: 30px 0;">
        <a href="https://g.page/r/review-link" class="btn">Rate Your Experience</a>
    </div>

    <h3>🎁 Special for you</h3>
    <p>Upgrade to an <strong>Annual Pass</strong> within 7 days and get your today's ticket price deducted! Visit the ticketing counter for details.</p>

    <p class="text-center">
        <a href="#">Follow us on Instagram</a>
    </p>
@endsection
