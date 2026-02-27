<?php
// app/Models/Order.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'order_number',
        'user_id',
        'order_date',
        'status',
        'total',
        'notes',
        'processed_by',
        'processed_at',
        'rejected_reason',
        'rejected_at',
        'completed_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'order_date' => 'datetime',
        'processed_at' => 'datetime',
        'rejected_at' => 'datetime',
        'completed_at' => 'datetime',
        'total' => 'decimal:2',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'formatted_total',
        'status_badge',
        'status_text',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            $date = now();
            $prefix = 'ORD-' . $date->format('Ymd');
            $lastOrder = static::where('order_number', 'like', $prefix . '%')
                ->orderBy('id', 'desc')
                ->first();

            if ($lastOrder) {
                $lastNumber = intval(substr($lastOrder->order_number, -4));
                $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
            } else {
                $newNumber = '0001';
            }

            $order->order_number = $prefix . '-' . $newNumber;
            $order->order_date = now();
        });
    }

    /**
     * Get the user that created this order.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the owner that processed this order.
     */
    public function processor()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /**
     * Get the items for this order.
     */
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get the sale associated with this order.
     */
    public function sale()
    {
        return $this->hasOne(Sale::class);
    }

    /**
     * Get formatted total.
     */
    public function getFormattedTotalAttribute()
    {
        return 'Rp ' . number_format($this->total, 0, ',', '.');
    }

    /**
     * Get status badge HTML.
     */
    public function getStatusBadgeAttribute()
    {
        return match ($this->status) {
            'pending' => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Menunggu</span>',
            'accepted' => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">Diterima</span>',
            'completed' => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Selesai</span>',
            'rejected' => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Ditolak</span>',
            default => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Unknown</span>',
        };
    }

    /**
     * Get status text.
     */
    public function getStatusTextAttribute()
    {
        return match ($this->status) {
            'pending' => 'Menunggu Konfirmasi Owner',
            'accepted' => 'Diterima - Sedang Diproses',
            'completed' => 'Pesanan Selesai',
            'rejected' => 'Pesanan Ditolak',
            default => 'Unknown',
        };
    }

    /**
     * Add item to this order.
     */
    public function addItem($menuId, $sauceId, $quantity)
    {
        $menu = Menu::findOrFail($menuId);
        $sauce = Menu::findOrFail($sauceId);

        // Validate that the sauce is available for this menu
        $isAvailable = $menu->availableSauces()
            ->where('sauce_id', $sauce->id)
            ->exists();

        if (!$isAvailable) {
            throw new \Exception("Saus {$sauce->name} tidak tersedia untuk menu {$menu->name}");
        }

        // Additional price is 0 because sauce price is already included in menu price
        $additionalPrice = 0;
        $subtotal = ($menu->price + $additionalPrice) * $quantity;

        // Create order item
        $item = $this->items()->create([
            'menu_id' => $menuId,
            'sauce_id' => $sauceId,
            'quantity' => $quantity,
            'price' => $menu->price,
            'additional_price' => $additionalPrice,
            'subtotal' => $subtotal,
        ]);

        // Update total
        $this->updateTotal();

        return $item;
    }

    /**
     * Update total based on items.
     */
    public function updateTotal()
    {
        $total = $this->items()->sum('subtotal');
        $this->update(['total' => $total]);

        return $total;
    }

    /**
     * Complete order.
     */
    public function complete()
    {
        if ($this->status !== 'accepted') {
            throw new \Exception("Hanya order yang diterima yang dapat diselesaikan");
        }

        $this->status = 'completed';
        $this->completed_at = now();
        $this->save();

        return $this;
    }

    /**
     * Reject order.
     */
    public function reject($reason, $processedBy)
    {
        if ($this->status !== 'pending') {
            throw new \Exception("Order ini sudah diproses sebelumnya");
        }

        $this->status = 'rejected';
        $this->rejected_reason = $reason;
        $this->processed_by = $processedBy;
        $this->rejected_at = now();
        $this->save();

        return $this;
    }

    /**
     * Convert order to sale.
     */
    public function convertToSale()
    {
        if ($this->status !== 'completed') {
            throw new \Exception("Hanya order yang selesai yang dapat dikonversi ke penjualan");
        }

        // Check if already converted
        $existingSale = Sale::where('order_id', $this->id)->first();
        if ($existingSale) {
            return $existingSale;
        }

        // Create sale from order
        $sale = Sale::create([
            'order_id' => $this->id,
            'date' => now(),
            'user_id' => $this->user_id,
            'processed_by' => $this->processed_by,
            'total' => $this->total,
            'notes' => 'Order #' . $this->order_number . ($this->notes ? ' - ' . $this->notes : ''),
            'payment_status' => 'pending',
        ]);

        // Copy items to sale
        foreach ($this->items as $item) {
            SaleItem::create([
                'sale_id' => $sale->id,
                'menu_id' => $item->menu_id,
                'sauce_id' => $item->sauce_id,
                'quantity' => $item->quantity,
                'price' => $item->price,
                'additional_price' => $item->additional_price,
                'subtotal' => $item->subtotal,
            ]);
        }

        return $sale;
    }

    /**
     * Scope for pending orders.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for accepted orders.
     */
    public function scopeAccepted($query)
    {
        return $query->where('status', 'accepted');
    }

    /**
     * Scope for completed orders.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for rejected orders.
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Scope for orders by user.
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for orders today.
     */
    public function scopeToday($query)
    {
        return $query->whereDate('order_date', today());
    }

    /**
     * Scope for orders this week.
     */
    public function scopeThisWeek($query)
    {
        return $query->whereBetween('order_date', [now()->startOfWeek(), now()->endOfWeek()]);
    }

    /**
     * Scope for orders this month.
     */
    public function scopeThisMonth($query)
    {
        return $query->whereMonth('order_date', now()->month)
            ->whereYear('order_date', now()->year);
    }

    /**
     * Calculate required quantity based on batch system (per 5 orders)
     */
    private function calculateRequiredQuantityForBatch($totalOrderQuantity, $recipeQuantity)
    {
        // Hitung berapa batch yang diperlukan berdasarkan total pesanan saus yang sama
        // 1 batch = 5 porsi
        $batches = ceil($totalOrderQuantity / 5);

        // Kebutuhan bahan = jumlah batch * recipe quantity per batch
        return $batches * $recipeQuantity;
    }


    /*
     * Accept order (process by owner) with batch system for sauces.
     */
    public function accept($processedBy)
    {
        if ($this->status !== 'pending') {
            throw new \Exception("Order ini sudah diproses sebelumnya");
        }

        // GROUP BY SAUCE - Hitung total quantity per saus terlebih dahulu
        $sauceQuantities = [];
        foreach ($this->items as $item) {
            if ($item->sauce) {
                $sauceId = $item->sauce->id;
                if (!isset($sauceQuantities[$sauceId])) {
                    $sauceQuantities[$sauceId] = 0;
                }
                $sauceQuantities[$sauceId] += $item->quantity;
            }
        }

        Log::info('Order accept - Sauce quantities:', $sauceQuantities);

        // Check stock availability for menu ingredients (per porsi)
        foreach ($this->items as $item) {
            $menu = $item->menu;

            foreach ($menu->ingredients as $ingredient) {
                $requiredQuantity = $ingredient->pivot->quantity * $item->quantity;

                if ($ingredient->stock < $requiredQuantity) {
                    throw new \Exception("Stok {$ingredient->name} tidak mencukupi untuk menu {$menu->name}. Dibutuhkan: {$requiredQuantity} {$ingredient->unit}, Tersedia: {$ingredient->stock} {$ingredient->unit}");
                }
            }
        }

        // Check stock availability for sauces based on total quantity per sauce (floor division)
        foreach ($sauceQuantities as $sauceId => $totalQuantity) {
            $sauce = Menu::find($sauceId);
            if (!$sauce) continue;

            $batches = intdiv($totalQuantity, 5); // floor: hanya kurangi stok setiap 5 order
            if ($batches > 0) {
                foreach ($sauce->ingredients as $ingredient) {
                    $requiredQuantity = $batches * $ingredient->pivot->quantity;

                    if ($ingredient->stock < $requiredQuantity) {
                        throw new \Exception("Stok {$ingredient->name} tidak mencukupi untuk saus {$sauce->name}. Total pesanan saus: {$totalQuantity} porsi / {$batches} batch. Dibutuhkan: {$requiredQuantity} {$ingredient->unit}, Tersedia: {$ingredient->stock} {$ingredient->unit}");
                    }
                }
            }
        }

        // Reduce ingredient stock for menu
        foreach ($this->items as $item) {
            $menu = $item->menu;

            foreach ($menu->ingredients as $ingredient) {
                $requiredQuantity = $ingredient->pivot->quantity * $item->quantity;
                $ingredient->decreaseStock($requiredQuantity);
            }
        }

        // Reduce stock for sauces based on total quantity per sauce (floor division)
        foreach ($sauceQuantities as $sauceId => $totalQuantity) {
            $sauce = Menu::find($sauceId);
            if (!$sauce) continue;

            $batches = intdiv($totalQuantity, 5);
            if ($batches > 0) {
                foreach ($sauce->ingredients as $ingredient) {
                    $requiredQuantity = $batches * $ingredient->pivot->quantity;
                    $ingredient->decreaseStock($requiredQuantity);

                    Log::info('Sauce stock reduced (global batch)', [
                        'sauce' => $sauce->name,
                        'total_sauce_orders' => $totalQuantity,
                        'batches' => $batches,
                        'ingredient' => $ingredient->name,
                        'required' => $requiredQuantity,
                        'remaining_stock' => $ingredient->stock
                    ]);
                }
            }
        }

        // Update order status
        $this->status = 'accepted';
        $this->processed_by = $processedBy;
        $this->processed_at = now();
        $this->save();

        return $this;
    }
}
