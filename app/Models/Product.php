<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'short_description',
        'featured_image',
        'base_price',
        'currency',
        'daily_capacity',
        'is_time_slot_based',
        'validity_days',
        'min_quantity',
        'max_quantity',
        'requires_age_verification',
        'is_active',
        'options_config', // Defines available food types, etc.
        'sort_order',
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'daily_capacity' => 'integer',
        'is_time_slot_based' => 'boolean',
        'validity_days' => 'integer',
        'min_quantity' => 'integer',
        'max_quantity' => 'integer',
        'requires_age_verification' => 'boolean',
        'is_active' => 'boolean',
        'options_config' => 'array',
        'sort_order' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
        });
    }

    /**
     * Get the category this product belongs to
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    /**
     * Get pricing rules for this product
     */
    public function pricingRules(): HasMany
    {
        return $this->hasMany(PricingRule::class);
    }

    /**
     * Get active pricing rules
     */
    public function activePricingRules(): HasMany
    {
        return $this->pricingRules()->where('is_active', true);
    }

    /**
     * Get capacity rules for this product
     */
    public function capacityRules(): HasMany
    {
        return $this->hasMany(CapacityRule::class);
    }

    /**
     * Get booking items for this product
     */
    public function bookingItems(): HasMany
    {
        return $this->hasMany(BookingItem::class);
    }

    /**
     * Calculate price for given date and quantity
     */
    public function calculatePrice(\DateTime $date, int $quantity): float
    {
        $price = (float) $this->base_price;

        // Apply pricing rules
        $applicableRules = $this->activePricingRules()
            ->where(function ($query) use ($date) {
                $query->where(function ($q) use ($date) {
                    $q->whereNull('valid_from')
                      ->orWhere('valid_from', '<=', $date->format('Y-m-d'));
                })->where(function ($q) use ($date) {
                    $q->whereNull('valid_to')
                      ->orWhere('valid_to', '>=', $date->format('Y-m-d'));
                });
            })
            ->orderBy('priority', 'desc')
            ->get();

        foreach ($applicableRules as $rule) {
            if ($rule->appliesToQuantity($quantity) && $rule->appliesToDate($date)) {
                $price = $rule->apply($price);
            }
        }

        return $price;
    }

    /**
     * Get capacity for a specific date
     */
    public function getCapacityForDate(\DateTime $date): ?int
    {
        // Check for specific date override first
        $override = $this->capacityRules()
            ->where('is_active', true)
            ->where('date', $date->format('Y-m-d'))
            ->orderBy('priority', 'desc')
            ->first();

        if ($override) {
            return $override->capacity_override;
        }

        // Check for day of week override
        $dayOfWeek = (int) $date->format('w'); // 0 (Sunday) to 6 (Saturday)
        $dayOverride = $this->capacityRules()
            ->where('is_active', true)
            ->where('day_of_week', $dayOfWeek)
            ->orderBy('priority', 'desc')
            ->first();

        if ($dayOverride) {
            return $dayOverride->capacity_override;
        }

        // Return default daily capacity
        return $this->daily_capacity;
    }

    /**
     * Scope to get active products
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to order by sort order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }
}
