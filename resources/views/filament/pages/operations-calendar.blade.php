<x-filament-panels::page>
    <div class="space-y-6">
        {{ $this->form }}

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <x-filament::section>
                <div class="text-xl font-bold">{{ $totalGuests }}</div>
                <div class="text-sm text-gray-500">Total Guests Expected</div>
            </x-filament::section>
            
            <x-filament::section>
                <div class="text-xl font-bold text-primary-600">{{ $vipCount }}</div>
                <div class="text-sm text-gray-500">VIP Guests</div>
            </x-filament::section>
        </div>

        @if($schedule->isEmpty())
            <div class="p-6 text-center text-gray-500 bg-white rounded-lg shadow">
                No bookings found for {{ $selectedDate }}
            </div>
        @else
            <div class="space-y-8">
                @foreach($schedule as $slot => $items)
                    <div class="bg-white rounded-xl shadow ring-1 ring-gray-950/5 p-6">
                        <h3 class="text-lg font-bold mb-4 flex items-center gap-2">
                            <x-heroicon-o-clock class="w-5 h-5 text-gray-400"/>
                            {{ $slot ?: 'Any Time' }}
                        </h3>

                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2">Customer</th>
                                        <th class="px-4 py-2">Experience</th>
                                        <th class="px-4 py-2">Guests</th>
                                        <th class="px-4 py-2">Food / Notes</th>
                                        <th class="px-4 py-2">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach($items as $item)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-2 font-medium">
                                                {{ $item->booking->customer_name }}<br>
                                                <span class="text-xs text-gray-500">{{ $item->booking->booking_id }}</span>
                                            </td>
                                            <td class="px-4 py-2">
                                                {{ $item->product->name }}
                                            </td>
                                            <td class="px-4 py-2">
                                                {{ $item->quantity }}
                                                @if($item->guest_names)
                                                    <div class="text-xs text-gray-400 mt-1">
                                                        {{ implode(', ', $item->guest_names) }}
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="px-4 py-2">
                                                @if($item->food_preference)
                                                    <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-yellow-50 text-yellow-700 ring-1 ring-inset ring-yellow-600/20">
                                                        {{ $item->food_preference }}
                                                    </span>
                                                @endif
                                                @if($item->dietary_notes)
                                                    <div class="text-xs text-red-600 mt-1">
                                                        ⚠ {{ $item->dietary_notes }}
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="px-4 py-2">
                                                <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-green-50 text-green-700 ring-1 ring-inset ring-green-600/20">
                                                    Confirmed
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-filament-panels::page>
