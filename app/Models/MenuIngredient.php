<?php
// app/Models/MenuIngredient.php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class MenuIngredient extends Pivot
{
    protected $table = 'menu_ingredient';

    protected $casts = [
        'quantity' => 'decimal:2',
    ];

    protected $appends = [
        'formatted_quantity',
    ];

    // Relationships
    public function menu()
    {
        return $this->belongsTo(Menu::class);
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

    public function getRequiredStockForQuantity($quantity)
    {
        return $this->quantity * $quantity;
    }

    public function getCostAttribute()
    {
        return $this->quantity * $this->ingredient->price;
    }

    public function getFormattedCostAttribute()
    {
        return 'Rp ' . number_format($this->cost, 0, ',', '.');
    }
}
