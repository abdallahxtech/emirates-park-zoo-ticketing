<x-filament-panels::page>
    <div class="space-y-4">
        {{-- Calendar Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold">
                    {{ \Carbon\Carbon::create($currentYear, $currentMonth, 1)->format('F Y') }}
                </h2>
                <p class="mt-1 text-sm text-gray-500">View bookings and reservations</p>
            </div>
            <div class="flex gap-2">
                <x-filament::button
                    wire:click="previousMonth"
                    color="gray"
                    icon="heroicon-o-chevron-left"
                    size="sm"
                >
                    Previous
                </x-filament::button>
                <x-filament::button
                    wire:click="today"
                    color="primary"
                    size="sm"
                >
                    Today
                </x-filament::button>
                <x-filament::button
                    wire:click="nextMonth"
                    color="gray"
                    icon="heroicon-o-chevron-right"
                    icon-position="after"
                    size="sm"
                >
                    Next
                </x-filament::button>
            </div>
        </div>

        {{-- Calendar Grid --}}
        <x-filament::card>
            <div class="grid grid-cols-7 gap-px bg-gray-200 rounded-lg overflow-hidden">
                {{-- Weekday Headers --}}
                @foreach(['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $day)
                    <div class="bg-gray-50 p-3 text-center text-sm font-semibold text-gray-700">
                        {{ $day }}
                    </div>
                @endforeach

                {{-- Calendar Days --}}
                @php
                    $bookings = $this->getBookingsForMonth();
                @endphp
                
                @foreach($this->getCalendarDays() as $day)
                    <div class="bg-white p-2 min-h-[120px] {{ $day['isCurrentMonth'] ? '' : 'opacity-50' }} {{ $day['isToday'] ? 'ring-2 ring-primary-500' : '' }}">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-medium {{ $day['isToday'] ? 'text-primary-600' : 'text-gray-900' }}">
                                {{ $day['day'] }}
                            </span>
                            @if($bookings->has($day['date']))
                                <span class="inline-flex items-center justify-center w-6 h-6 text-xs font-bold text-white bg-primary-600 rounded-full">
                                    {{ $bookings[$day['date']]->count() }}
                                </span>
                            @endif
                        </div>

                        @if($bookings->has($day['date']))
                            <div class="space-y-1">
                                @foreach($bookings[$day['date']]->take(3) as $booking)
                                    <a
                                        href="{{ route('filament.admin.resources.bookings.view', $booking) }}"
                                        class="block p-1 text-xs rounded {{ 
                                            $booking->state->value === 'CONFIRMED' ? 'bg-green-100 text-green-700' :
                                            ($booking->state->value === 'HOLD' ? 'bg-yellow-100 text-yellow-700' : 'bg-blue-100 text-blue-700')
                                        }}"
                                        title="{{ $booking->customer->full_name }}"
                                    >
                                        <div class="font-medium truncate">{{ $booking->reference }}</div>
                                        <div class="truncate">{{ $booking->customer->full_name }}</div>
                                        @if($booking->visit_time)
                                            <div>{{ \Carbon\Carbon::parse($booking->visit_time)->format('H:i') }}</div>
                                        @endif
                                    </a>
                                @endforeach
                                @if($bookings[$day['date']]->count() > 3)
                                    <div class="text-xs text-gray-500 text-center">
                                        +{{ $bookings[$day['date']]->count() - 3 }} more
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </x-filament::card>

        {{-- Legend --}}
        <x-filament::card>
            <h3 class="mb-3 text-sm font-semibold">Legend</h3>
            <div class="flex flex-wrap gap-4">
                <div class="flex items-center gap-2">
                    <div class="w-4 h-4 bg-green-100 border border-green-300 rounded"></div>
                    <span class="text-sm">Confirmed</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-4 h-4 bg-blue-100 border border-blue-300 rounded"></div>
                    <span class="text-sm">Paid</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-4 h-4 bg-yellow-100 border border-yellow-300 rounded"></div>
                    <span class="text-sm">On Hold</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-4 h-4 ring-2 ring-primary-500 rounded"></div>
                    <span class="text-sm">Today</span>
                </div>
            </div>
        </x-filament::card>
    </div>
</x-filament-panels::page>
