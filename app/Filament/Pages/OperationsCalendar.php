<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\BookingItem;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Get;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;

class OperationsCalendar extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';
    protected static ?string $navigationGroup = 'Operations';
    protected static ?string $title = 'Daily Operations';

    protected static string $view = 'filament.pages.operations-calendar';

    public static function canAccess(): bool
    {
        // Allow Super Admin, Operations, or Restaurant staff
        return auth()->user()->hasRole(['super_admin', 'operations', 'restaurant']);
    }

    public ?string $date = null;

    public function mount()
    {
        $this->date = now()->format('Y-m-d');
        $this->form->fill([
            'date' => $this->date,
        ]);
    }

    protected function getFormSchema(): array
    {
        return [
            DatePicker::make('date')
                ->label('Select Date')
                ->default(now())
                ->live()
                ->afterStateUpdated(function ($state) {
                    $this->date = $state;
                }),
        ];
    }

    public function getViewData(): array
    {
        $date = $this->date ? Carbon::parse($this->date) : now();

        $items = BookingItem::query()
            ->whereDate('visit_date', $date)
            ->whereHas('booking', function ($q) {
                // Only confirmed bookings
                $q->where('state', \App\Enums\BookingState::CONFIRMED);
            })
            ->with(['booking', 'product'])
            ->orderBy('time_slot')
            ->get();

        // Group by time slot
        $schedule = $items->groupBy('time_slot')->sortKeys();

        // Stats
        $totalGuests = $items->sum('quantity');
        $vipCount = $items->filter(fn($i) => $i->product->base_price > 100)->sum('quantity'); // Simple heuristic or use category

        return [
            'selectedDate' => $date->toFormattedDateString(),
            'schedule' => $schedule,
            'totalGuests' => $totalGuests,
            'vipCount' => $vipCount,
        ];
    }
}
