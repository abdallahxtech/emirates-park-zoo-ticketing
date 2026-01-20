@extends('emails.layouts.main')

@section('content')
    <h2 style="color: #d32f2f;">⚠️ ACTION REQUIRED: VIP Scheduled</h2>
    <p class="lead">{{ $item->product->name }}</p>

    <div class="vip-box">
        <p><strong>Date:</strong> {{ $booking->visit_date->format('Y-m-d') }}</p>
        <p><strong>Time:</strong> {{ $item->time_slot ?? 'N/A' }}</p>
        <p><strong>Guests:</strong> {{ $item->quantity }}</p>
        <p><strong>Contact:</strong> {{ $booking->customer_name }} ({{ $booking->customer_phone }})</p>
    </div>

    <h3>📋 Ops Checklist</h3>
    <ul>
        <li>[ ] Confirm slot capacity in area</li>
        <li>[ ] Assign staff member / host</li>
        <li>[ ] Alert Animal Care Team</li>
        <li>[ ] Prepare Welcome Kit</li>
    </ul>

    <div class="text-center">
        <a href="{{ url('/admin/operations-calendar') }}" class="btn">View Operations Calendar</a>
    </div>
@endsection
