<?php
// app/Models/SaleItem.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'sale_id',
        'menu_id',
        'quantity',
        'price',
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
        'subtotal' => 'decimal:2',
    ];

    /**
     * Get the sale that owns this item.
     */
    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    /**
     * Get the menu associated with this item.
     */
    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }

    /**
     * Get formatted price.
     */
    public function getFormattedPriceAttribute()
    {
        return 'Rp ' . number_format($this->price, 0, ',', '.');
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

        // When deleting a sale item, restore ingredient stock
        static::deleting(function ($item) {
            if ($item->menu && $item->menu->ingredients) {
                foreach ($item->menu->ingredients as $ingredient) {
                    $returnQuantity = $ingredient->pivot->quantity * $item->quantity;
                    $ingredient->increaseStock($returnQuantity);
                }
            }
        });
    }
}
