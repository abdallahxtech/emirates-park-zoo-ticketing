<x-mail::message>
    # New VIP Booking Confirmed

    **Reference:** {{ $booking->reference }}
    **Customer:** {{ $customer->full_name }} ({{ $customer->phone }})
    **Date:** {{ $items->first()?->visit_date?->format('d M Y') }}
    **Time:** {{ $items->first()?->visit_time?->format('H:i') ?? 'TBD' }}

    ## Items & Preferences

    @foreach($items as $item)
    - **{{ $item->quantity }}x {{ $item->product_name }}**
    @if($item->food_selection)
    - Food: {{ $item->food_selection }}
    @endif
    @if($item->dietary_notes)
    - Notes: {{ $item->dietary_notes }}
    @endif
    @endforeach

    <x-mail::button :url="config('app.url') . '/admin/bookings/' . $booking->id">
        View Booking in Admin
    </x-mail::button>

    Thanks,<br>
    {{ config('app.name') }}
</x-mail::message>