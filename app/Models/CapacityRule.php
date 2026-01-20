<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CapacityRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'date',
        'day_of_week',
        'date_from',
        'date_to',
        'time_slot_start',
        'time_slot_end',
        'capacity_override',
        'reason',
        'is_active',
        'priority',
    ];

    protected $casts = [
        'date' => 'date',
        'day_of_week' => 'integer',
        'date_from' => 'date',
        'date_to' => 'date',
        'capacity_override' => 'integer',
        'is_active' => 'boolean',
        'priority' => 'integer',
    ];

    /**
     * Get the product this rule belongs to
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Scope to get active rules
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get rules for a specific date
     */
    public function scopeForDate($query, \DateTime $date)
    {
        return $query->where(function ($q) use ($date) {
            $q->where('date', $date->format('Y-m-d'))
              ->orWhere('day_of_week', (int) $date->format('w'))
              ->orWhere(function ($q2) use ($date) {
                  $q2->where('date_from', '<=', $date->format('Y-m-d'))
                     ->where('date_to', '>=', $date->format('Y-m-d'));
              });
        });
    }
}
