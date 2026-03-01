<?php
// app/Models/OrderItem.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'order_id',
        'menu_id',
        'sauce_id',
        'quantity',
        'price',
        'additional_price',
        'subtotal',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'quantity' => 'integer',
        'price' => 'decimal:2',
        'additional_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'total_price',
        'formatted_price',
        'formatted_additional_price',
        'formatted_total_price',
        'formatted_subtotal',
    ];

    /**
     * Get the order that owns this item.
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the menu associated with this item.
     */
    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }

    /**
     * Get the sauce associated with this item.
     */
    public function sauce()
    {
        return $this->belongsTo(Menu::class, 'sauce_id');
    }

    /**
     * Get total price including sauce.
     */
    public function getTotalPriceAttribute()
    {
        return $this->price + $this->additional_price;
    }

    /**
     * Get formatted price.
     */
    public function getFormattedPriceAttribute()
    {
        return 'Rp ' . number_format($this->price, 0, ',', '.');
    }

    /**
     * Get formatted additional price.
     */
    public function getFormattedAdditionalPriceAttribute()
    {
        return 'Rp ' . number_format($this->additional_price, 0, ',', '.');
    }

    /**
     * Get formatted total price.
     */
    public function getFormattedTotalPriceAttribute()
    {
        return 'Rp ' . number_format($this->total_price, 0, ',', '.');
    }

    /**
     * Get formatted subtotal.
     */
    public function getFormattedSubtotalAttribute()
    {
        return 'Rp ' . number_format($this->subtotal, 0, ',', '.');
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($item) {
            if (empty($item->subtotal)) {
                $item->subtotal = ($item->price + $item->additional_price) * $item->quantity;
            }
        });
    }
}
