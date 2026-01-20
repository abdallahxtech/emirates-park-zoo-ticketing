@extends('emails.layouts.main')

@section('content')
<h2>Complete your booking!</h2>

<p>Hi {{ $name }},</p>

<p>We noticed you left some tickets in your cart. Don't worry, we've saved your spot!</p>

<p>Click the button below to secure your tickets for Emirates Park Zoo & Resort before they run out.</p>

<div style="text-align: center; margin: 30px 0;">
    <a href="{{ $url }}" class="button">Complete Booking</a>
</div>

<p>If you have any questions, feel free to reply to this email.</p>

<p>See you soon,<br>
The Emirates Park Zoo Team</p>
@endsection
