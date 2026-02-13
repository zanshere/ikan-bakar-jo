<?php
// app/Models/Sale.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sale extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'date',
        'user_id',
        'total',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'date' => 'date',
        'total' => 'decimal:2',
    ];

    /**
     * Get the user that recorded this sale.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the items for this sale.
     */
    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    /**
     * Get formatted total price.
     */
    public function getFormattedTotalAttribute()
    {
        return 'Rp ' . number_format($this->total, 0, ',', '.');
    }

    /**
     * Add item to this sale
     */
    public function addItem($menuId, $quantity)
    {
        $menu = Menu::findOrFail($menuId);

        // Check stock availability
        foreach ($menu->ingredients as $ingredient) {
            $requiredQuantity = $ingredient->pivot->quantity * $quantity;

            if ($ingredient->stock < $requiredQuantity) {
                throw new \Exception("Stok {$ingredient->name} tidak mencukupi. Dibutuhkan: {$requiredQuantity} {$ingredient->unit}, Tersedia: {$ingredient->stock} {$ingredient->unit}");
            }
        }

        // Reduce ingredient stock
        foreach ($menu->ingredients as $ingredient) {
            $requiredQuantity = $ingredient->pivot->quantity * $quantity;
            $ingredient->decreaseStock($requiredQuantity);
        }

        // Create sale item
        $subtotal = $menu->price * $quantity;

        $item = $this->items()->create([
            'menu_id' => $menuId,
            'quantity' => $quantity,
            'price' => $menu->price,
            'subtotal' => $subtotal,
        ]);

        // Update total
        $this->updateTotal();

        return $item;
    }

    /**
     * Update total based on items
     */
    public function updateTotal()
    {
        $total = $this->items()->sum('subtotal');
        $this->update(['total' => $total]);

        return $total;
    }

    /**
     * Scope for sales in date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * Scope for sales by user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for sales today
     */
    public function scopeToday($query)
    {
        return $query->whereDate('date', today());
    }

    /**
     * Scope for sales this month
     */
    public function scopeThisMonth($query)
    {
        return $query->whereMonth('date', now()->month)
                     ->whereYear('date', now()->year);
    }

    /**
     * Scope for sales this year
     */
    public function scopeThisYear($query)
    {
        return $query->whereYear('date', now()->year);
    }
}
