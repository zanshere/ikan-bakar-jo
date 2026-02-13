<?php
// app/Models/RestockItem.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RestockItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'restock_id',
        'ingredient_id',
        'quantity',
        'price',
        'subtotal',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'price' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    protected $appends = [
        'formatted_quantity',
        'formatted_price',
        'formatted_subtotal',
    ];

    // Relationships
    public function restock()
    {
        return $this->belongsTo(Restock::class);
    }

    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class);
    }

    // Accessors
    public function getFormattedQuantityAttribute()
    {
        return number_format($this->quantity, 2, ',', '.') . ' ' . $this->ingredient->unit;
    }

    public function getFormattedPriceAttribute()
    {
        return 'Rp ' . number_format($this->price, 0, ',', '.');
    }

    public function getFormattedSubtotalAttribute()
    {
        return 'Rp ' . number_format($this->subtotal, 0, ',', '.');
    }

    // Events
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($item) {
            if (!$item->subtotal) {
                $item->subtotal = $item->quantity * $item->price;
            }
        });
    }
}
