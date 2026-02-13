<?php
// app/Models/Restock.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Restock extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'date',
        'total',
        'user_id',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'total' => 'decimal:2',
    ];

    protected $appends = [
        'formatted_date',
        'formatted_total',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(RestockItem::class);
    }

    // Scopes
    public function scopeToday($query)
    {
        return $query->whereDate('date', today());
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('date', [now()->startOfWeek(), now()->endOfWeek()]);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('date', now()->month)
                     ->whereYear('date', now()->year);
    }

    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    // Methods
    public function addItem($ingredientId, $quantity, $price)
    {
        $ingredient = Ingredient::findOrFail($ingredientId);

        $item = new RestockItem([
            'ingredient_id' => $ingredientId,
            'quantity' => $quantity,
            'price' => $price,
            'subtotal' => $quantity * $price,
        ]);

        $this->items()->save($item);

        // Update ingredient stock
        $ingredient->increaseStock($quantity);

        // Update restock total
        $this->updateTotal();

        return $item;
    }

    public function updateTotal()
    {
        $total = $this->items()->sum('subtotal');
        $this->total = $total;
        $this->save();
    }

    // Accessors
    public function getFormattedDateAttribute()
    {
        return $this->date->format('d/m/Y');
    }

    public function getFormattedTotalAttribute()
    {
        return 'Rp ' . number_format($this->total, 0, ',', '.');
    }

    // Events
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($restock) {
            if (!$restock->date) {
                $restock->date = today();
            }

            if (!$restock->user_id && auth()->user()) {
                $restock->user_id = auth()->id();
            }
        });

        static::deleting(function ($restock) {
            // Decrease stock when restock is deleted
            foreach ($restock->items as $item) {
                $item->ingredient->decreaseStock($item->quantity);
            }
        });
    }
}
