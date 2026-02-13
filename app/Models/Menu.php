<?php
// app/Models/Menu.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class Menu extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'price',
        'description',
        'image',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    protected $appends = [
        'formatted_price',
        'formatted_cost',
        'profit',
        'formatted_profit',
        'profit_percentage',
        'status_badge',
    ];

    // Relationships
    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function ingredients()
    {
        return $this->belongsToMany(Ingredient::class, 'menu_ingredient')
                    ->withPivot('quantity')
                    ->withTimestamps();
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where('name', 'like', "%{$search}%")
                     ->orWhere('code', 'like', "%{$search}%");
    }

    // Methods
    public static function generateUniqueCode()
    {
        $date = now()->format('His');
        $prefix = "MENU{$date}";

        // Gunakan pendekatan berbeda: cari nomor yang tersedia
        // Ambil semua kode dengan prefix hari ini
        $existingCodes = self::where('code', 'like', $prefix . '%')
                            ->pluck('code')
                            ->map(function($code) {
                                return intval(substr($code, -3));
                            })
                            ->toArray();

        // Jika tidak ada kode hari ini, mulai dari 1
        if (empty($existingCodes)) {
            return $prefix . '001';
        }

        // Cari nomor terkecil yang tersedia (1-999)
        for ($i = 1; $i <= 999; $i++) {
            $number = str_pad($i, 3, '0', STR_PAD_LEFT);
            if (!in_array($i, $existingCodes)) {
                return $prefix . $number;
            }
        }

        // Jika semua nomor 1-999 sudah terpakai (sangat jarang), gunakan timestamp
        return $prefix . now()->format('His');
    }

    // Method lama untuk kompatibilitas
    public function generateCode()
    {
        return self::generateUniqueCode();
    }

    public function calculateCost()
    {
        $totalCost = 0;

        foreach ($this->ingredients as $ingredient) {
            $totalCost += $ingredient->price * $ingredient->pivot->quantity;
        }

        return $totalCost;
    }

    // Accessors
    public function getFormattedPriceAttribute()
    {
        return 'Rp ' . number_format($this->price, 0, ',', '.');
    }

    public function getStatusBadgeAttribute()
    {
        if ($this->is_active) {
            return '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Aktif</span>';
        }

        return '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Nonaktif</span>';
    }

    public function getCostAttribute()
    {
        return $this->calculateCost();
    }

    public function getFormattedCostAttribute()
    {
        return 'Rp ' . number_format($this->calculateCost(), 0, ',', '.');
    }

    public function getProfitAttribute()
    {
        return $this->price - $this->calculateCost();
    }

    public function getFormattedProfitAttribute()
    {
        return 'Rp ' . number_format($this->profit, 0, ',', '.');
    }

    public function getProfitPercentageAttribute()
    {
        if ($this->price == 0) return 0;

        return round(($this->profit / $this->price) * 100, 2);
    }

    // Mutators
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = ucwords(strtolower($value));
    }

    public function setDescriptionAttribute($value)
    {
        $this->attributes['description'] = $value ?: null;
    }

    // Boot method untuk events
    protected static function boot()
    {
        parent::boot();

        // Event creating: generate code sebelum insert
        static::creating(function ($menu) {
            if (empty($menu->code)) {
                $menu->code = self::generateUniqueCode();
            }
        });
    }
}
