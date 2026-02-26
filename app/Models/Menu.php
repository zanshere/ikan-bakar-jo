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
    const TYPE_MAIN = 'main';
    const TYPE_SAUCE = 'sauce';

    protected $fillable = [
        'code',
        'name',
        'type',
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
            ->map(function ($code) {
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
        // Jika tipe sauce, return 0 karena biaya akan digabung dengan menu utama
        if ($this->type === self::TYPE_SAUCE) {
            return 0;
        }
        return $this->calculateCost();
    }

    public function getFormattedCostAttribute()
    {
        // Jika tipe sauce, return strip atau pesan khusus
        if ($this->type === self::TYPE_SAUCE) {
            return '<span class="text-gray-400">-</span>';
        }
        return 'Rp ' . number_format($this->calculateCost(), 0, ',', '.');
    }

    public function getProfitAttribute()
    {
        // Jika tipe sauce, return 0 karena profit dihitung dari menu utama
        if ($this->type === self::TYPE_SAUCE) {
            return 0;
        }
        return $this->price - $this->calculateCost();
    }

    public function getFormattedProfitAttribute()
    {
        // Jika tipe sauce, return strip atau pesan khusus
        if ($this->type === self::TYPE_SAUCE) {
            return '<span class="text-gray-400">-</span>';
        }
        return 'Rp ' . number_format($this->profit, 0, ',', '.');
    }

    public function getProfitPercentageAttribute()
    {
        // Jika tipe sauce, return 0
        if ($this->type === self::TYPE_SAUCE) {
            return 0;
        }
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

    /**
     * Get the sauces that can be paired with this main menu.
     */
    public function availableSauces()
    {
        return $this->belongsToMany(Menu::class, 'menu_sauce', 'menu_id', 'sauce_id')
            ->withTimestamps();
    }

    /**
     * Get the main menus that this sauce can be paired with.
     */
    public function mainMenus()
    {
        return $this->belongsToMany(Menu::class, 'menu_sauce', 'sauce_id', 'menu_id')
            ->withTimestamps();
    }

    /**
     * Get the default sauce for this main menu.
     */
    public function defaultSauce()
    {
        return $this->belongsToMany(Menu::class, 'menu_sauce', 'menu_id', 'sauce_id')
            ->wherePivot('is_default', true)
            ->withTimestamps();
    }

    /**
     * Scope to get only main menus.
     */
    public function scopeMainMenus($query)
    {
        return $query->where('type', self::TYPE_MAIN);
    }

    /**
     * Scope to get only sauces.
     */
    public function scopeSauces($query)
    {
        return $query->where('type', self::TYPE_SAUCE);
    }

    /**
     * Calculate the cost of this menu including optional sauce.
     */
    public function calculateCostWithSauce($sauceId = null)
    {
        $totalCost = 0;

        // Calculate cost for main menu ingredients
        foreach ($this->ingredients as $ingredient) {
            $totalCost += $ingredient->price * $ingredient->pivot->quantity;
        }

        // If sauce is selected, calculate its ingredients cost
        if ($sauceId) {
            $sauce = self::find($sauceId);
            if ($sauce) {
                foreach ($sauce->ingredients as $ingredient) {
                    $totalCost += $ingredient->price * $ingredient->pivot->quantity;
                }
            }
        }

        return $totalCost;
    }

    /**
     * Calculate profit for this menu with optional sauce.
     */
    public function calculateProfitWithSauce($sauceId = null)
    {
        $sauce = $sauceId ? self::find($sauceId) : null;

        // Harga saus sudah include di harga menu, jadi tidak perlu additional price
        return $this->price - $this->calculateCostWithSauce($sauceId);
    }

    /**
     * Calculate required quantity based on batch system (per 5 orders)
     */
    public function calculateRequiredQuantityForBatch($orderQuantity, $recipeQuantity)
    {
        // Hitung berapa batch yang diperlukan (1 batch = 5 porsi)
        $batches = ceil($orderQuantity / 5);

        // Kebutuhan bahan = jumlah batch * recipe quantity per batch
        return $batches * $recipeQuantity;
    }

    /**
     * Calculate the cost of this menu including optional sauce with batch system.
     */
    public function calculateCostWithSauceBatch($sauceId = null, $orderQuantity = 1)
    {
        $totalCost = 0;

        // Calculate cost for main menu ingredients (per porsi)
        foreach ($this->ingredients as $ingredient) {
            $totalCost += $ingredient->price * $ingredient->pivot->quantity * $orderQuantity;
        }

        // If sauce is selected, calculate its ingredients cost with batch system
        if ($sauceId) {
            $sauce = self::find($sauceId);
            if ($sauce) {
                foreach ($sauce->ingredients as $ingredient) {
                    // Untuk saus, hitung berdasarkan batch (per 5 porsi)
                    $requiredQuantity = $this->calculateRequiredQuantityForBatch(
                        $orderQuantity,
                        $ingredient->pivot->quantity
                    );
                    $totalCost += $ingredient->price * $requiredQuantity;
                }
            }
        }

        return $totalCost;
    }

    /**
     * Boot method for events.
     */
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
