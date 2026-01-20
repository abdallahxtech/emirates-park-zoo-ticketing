<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PricingRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'name',
        'rule_type',
        'conditions',
        'adjustment_type',
        'adjustment_value',
        'valid_from',
        'valid_to',
        'is_active',
        'priority',
    ];

    protected $casts = [
        'conditions' => 'array',
        'adjustment_value' => 'decimal:2',
        'valid_from' => 'date',
        'valid_to' => 'date',
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
     * Check if rule applies to given quantity
     */
    public function appliesToQuantity(int $quantity): bool
    {
        if ($this->rule_type !== 'group_discount') {
            return true;
        }

        $minQuantity = $this->conditions['min_quantity'] ?? 1;
        return $quantity >= $minQuantity;
    }

    /**
     * Check if rule applies to given date
     */
    public function appliesToDate(\DateTime $date): bool
    {
        $dayOfWeek = (int) $date->format('w');

        return match($this->rule_type) {
            'weekday' => in_array($dayOfWeek, [1, 2, 3, 4, 5]),
            'weekend' => in_array($dayOfWeek, [0, 6]),
            'seasonal' => true, // Date range handled by valid_from/valid_to
            'group_discount' => true,
            'early_bird' => true,
            default => true,
        };
    }

    /**
     * Apply this pricing rule to a base price
     */
    public function apply(float $basePrice): float
    {
        if ($this->adjustment_type === 'fixed') {
            return $basePrice + (float) $this->adjustment_value;
        }

        // Percentage adjustment
        $percentage = (float) $this->adjustment_value / 100;
        return $basePrice * (1 + $percentage);
    }

    /**
     * Scope to get active rules
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
