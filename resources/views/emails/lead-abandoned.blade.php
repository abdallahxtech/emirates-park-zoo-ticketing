@extends('emails.layouts.main')

@section('content')
    <div class="text-center">
        <h2>💭 Need help completing your booking?</h2>
    </div>

    <p>Hi {{ $lead->name ?? 'there' }},</p>

    <p>We noticed you started a booking but didn't finish. We've saved your selection for you!</p>

    <div class="text-center" style="margin: 30px 0;">
        <a href="{{ url('/checkout/resume/' . $lead->id) }}" class="btn">Resume Checkout</a>
    </div>

    <p>If you have any questions, feel free to reply to this email or chat with us on WhatsApp.</p>
@endsection
