<?php
// app/Models/Ingredient.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Ingredient extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'unit',
        'stock',
        'min_stock',
        'price',
    ];

    protected $casts = [
        'stock' => 'decimal:2',
        'min_stock' => 'decimal:2',
        'price' => 'decimal:2',
    ];

    protected $appends = [
        'formatted_stock',
        'formatted_min_stock',
        'formatted_price',
        'stock_status',
        'stock_status_badge',
        'total_value',
    ];

    // Relationships
    public function menus()
    {
        return $this->belongsToMany(Menu::class, 'menu_ingredient')
                    ->withPivot('quantity')
                    ->withTimestamps();
    }

    public function restockItems()
    {
        return $this->hasMany(RestockItem::class);
    }

    // Scopes
    public function scopeLowStock($query)
    {
        return $query->whereColumn('stock', '<', 'min_stock');
    }

    public function scopeOutOfStock($query)
    {
        return $query->where('stock', '<=', 0);
    }

    public function scopeSufficientStock($query)
    {
        return $query->whereColumn('stock', '>=', 'min_stock');
    }

    public function scopeSearch($query, $search)
    {
        return $query->where('name', 'like', "%{$search}%")
                     ->orWhere('code', 'like', "%{$search}%");
    }

    // Methods
    public function generateCode()
    {
        $date = now()->format('ymd');
        $lastIngredient = self::where('code', 'like', "ING{$date}%")->latest()->first();

        if ($lastIngredient) {
            $lastNumber = intval(substr($lastIngredient->code, -3));
            $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '001';
        }

        return "ING{$date}{$newNumber}";
    }

    public function increaseStock($quantity)
    {
        $this->stock += $quantity;
        $this->save();
    }

    public function decreaseStock($quantity)
    {
        if ($this->stock < $quantity) {
            throw new \Exception("Stock tidak mencukupi. Stock tersedia: {$this->stock}");
        }

        $this->stock -= $quantity;
        $this->save();
    }

    // Accessors
    public function getFormattedStockAttribute()
    {
        return number_format($this->stock, 2, ',', '.') . ' ' . $this->unit;
    }

    public function getFormattedMinStockAttribute()
    {
        return number_format($this->min_stock, 2, ',', '.') . ' ' . $this->unit;
    }

    public function getFormattedPriceAttribute()
    {
        return 'Rp ' . number_format($this->price, 0, ',', '.');
    }

    public function getStockStatusAttribute()
    {
        if ($this->stock <= 0) {
            return 'empty';
        }

        if ($this->stock < $this->min_stock) {
            return 'low';
        }

        return 'sufficient';
    }

    public function getStockStatusBadgeAttribute()
    {
        return match($this->stock_status) {
            'empty' => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Habis</span>',
            'low' => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-amber-100 text-amber-800">Rendah</span>',
            'sufficient' => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Cukup</span>',
            default => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Unknown</span>',
        };
    }

    public function getTotalValueAttribute()
    {
        return $this->stock * $this->price;
    }

    public function getFormattedTotalValueAttribute()
    {
        return 'Rp ' . number_format($this->total_value, 0, ',', '.');
    }

    // Mutators
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = ucwords(strtolower($value));
    }

    public function setUnitAttribute($value)
    {
        $this->attributes['unit'] = strtolower($value);
    }

    public function setMinStockAttribute($value)
    {
        $this->attributes['min_stock'] = max(0, $value);
    }
}
