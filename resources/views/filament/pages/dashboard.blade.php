<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Stats Overview --}}
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($this->getStats() as $stat)
                <x-filament::stats.card
                    :color="$stat->getColor()"
                    :chart="$stat->getChart()"
                    :description="$stat->getDescription()"
                    :description-icon="$stat->getDescriptionIcon()"
                    :label="$stat->getLabel()"
                    :value="$stat->getValue()"
                />
            @endforeach
        </div>

        {{-- Quick Actions --}}
        <div class="grid gap-4 md:grid-cols-2">
            <x-filament::card>
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold">Quick Actions</h3>
                        <p class="text-sm text-gray-500">Common tasks</p>
                    </div>
                </div>
                <div class="mt-4 space-y-2">
                    <x-filament::button
                        wire:click="$dispatch('open-modal', { id: 'create-booking' })"
                        color="primary"
                        icon="heroicon-o-plus"
                        class="w-full"
                    >
                        Create New Booking
                    </x-filament::button>
                    <x-filament::button
                        href="{{ route('filament.admin.resources.bookings.index', ['tableFilters[state][values][0]' => 'HOLD']) }}"
                        color="warning"
                        icon="heroicon-o-clock"
                        class="w-full"
                    >
                        View Active Holds
                    </x-filament::button>
                    <x-filament::button
                        href="{{ route('filament.admin.resources.bookings.index', ['tableFilters[state][values][0]' => 'PENDING_PAYMENT']) }}"
                        color="info"
                        icon="heroicon-o-credit-card"
                        class="w-full"
                    >
                        View Pending Payments
                    </x-filament::button>
                </div>
            </x-filament::card>

            <x-filament::card>
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold">System Status</h3>
                        <p class="text-sm text-gray-500">Real-time monitoring</p>
                    </div>
                </div>
                <div class="mt-4 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium">Queue Status</span>
                        <span class="flex items-center text-sm text-green-600">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            Active
                        </span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium">Last Hold Release</span>
                        <span class="text-sm text-gray-600">{{ now()->format('H:i:s') }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium">Active Tickets</span>
                        <span class="text-sm text-gray-600">{{ \App\Models\Ticket::where('is_active', true)->count() }}</span>
                    </div>
                </div>
            </x-filament::card>
        </div>

        {{-- Recent Activity --}}
        <x-filament::card>
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-lg font-semibold">Recent Bookings</h3>
                    <p class="text-sm text-gray-500">Latest transactions</p>
                </div>
                <x-filament::button
                    href="{{ route('filament.admin.resources.bookings.index') }}"
                    color="gray"
                    size="sm"
                >
                    View All
                </x-filament::button>
            </div>
            <div class="overflow-hidden">
                @php
                    $recentBookings = \App\Models\Booking::with('customer')
                        ->latest()
                        ->limit(5)
                        ->get();
                @endphp
                @if($recentBookings->count())
                    <table class="w-full text-sm">
                        <thead class="border-b">
                            <tr class="text-left">
                                <th class="pb-2">Reference</th>
                                <th class="pb-2">Customer</th>
                                <th class="pb-2">Status</th>
                                <th class="pb-2">Amount</th>
                                <th class="pb-2">Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentBookings as $booking)
                                <tr class="border-b last:border-0">
                                    <td class="py-2 font-medium">{{ $booking->reference }}</td>
                                    <td class="py-2">{{ $booking->customer->full_name }}</td>
                                    <td class="py-2">
                                        <x-filament::badge :color="$booking->state->color()">
                                            {{ $booking->state->label() }}
                                        </x-filament::badge>
                                    </td>
                                    <td class="py-2">AED {{ number_format($booking->total, 2) }}</td>
                                    <td class="py-2 text-gray-500">{{ $booking->created_at->diffForHumans() }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p class="text-sm text-gray-500">No recent bookings</p>
                @endif
            </div>
        </x-filament::card>
    </div>
</x-filament-panels::page>
