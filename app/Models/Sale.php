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
        'order_id',
        'date',
        'user_id',
        'processed_by',
        'total',
        'notes',
        'payment_status',
        'payment_method',
        'cash_received',
        'change',
        'processed_at',
        'completed_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'date' => 'date',
        'total' => 'decimal:2',
        'cash_received' => 'decimal:2',
        'change' => 'decimal:2',
        'processed_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'formatted_total',
        'payment_status_badge',
        'payment_status_text',
        'payment_method_text',
    ];

    /**
     * Get the order associated with this sale.
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the user that placed this order.
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
     * Get the items for this sale.
     */
    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    /**
     * Get formatted total.
     */
    public function getFormattedTotalAttribute()
    {
        return 'Rp ' . number_format($this->total, 0, ',', '.');
    }

    /**
     * Get payment status badge.
     */
    public function getPaymentStatusBadgeAttribute()
    {
        return match($this->payment_status) {
            'pending' => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Belum Bayar</span>',
            'paid' => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Lunas</span>',
            default => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Unknown</span>',
        };
    }

    /**
     * Get payment status text.
     */
    public function getPaymentStatusTextAttribute()
    {
        return match($this->payment_status) {
            'pending' => 'Belum Bayar',
            'paid' => 'Lunas',
            default => 'Unknown',
        };
    }

    /**
     * Get payment method text.
     */
    public function getPaymentMethodTextAttribute()
    {
        return match($this->payment_method) {
            'cash' => 'Tunai',
            'transfer' => 'Transfer Bank',
            default => '-',
        };
    }

    /**
     * Get order number from related order.
     */
    public function getOrderNumberAttribute()
    {
        return $this->order ? $this->order->order_number : null;
    }

    /**
     * Get order status from related order.
     */
    public function getOrderStatusAttribute()
    {
        return $this->order ? $this->order->status : null;
    }

    /**
     * Accept order (process by owner).
     */
    public function accept($processedBy)
    {
        if (!$this->order) {
            throw new \Exception("Sale tidak memiliki order terkait");
        }

        // Accept the order (this will reduce stock)
        $this->order->accept($processedBy);

        // Update sale
        $this->processed_by = $processedBy;
        $this->processed_at = now();
        $this->save();

        return $this;
    }

    /**
     * Complete order and process payment.
     */
    public function complete($paymentMethod, $cashReceived = null)
    {
        if (!$this->order) {
            throw new \Exception("Sale tidak memiliki order terkait");
        }

        if ($this->order->status !== 'accepted') {
            throw new \Exception("Hanya order yang diterima yang dapat diselesaikan");
        }

        // Calculate change for cash payment
        $change = null;
        if ($paymentMethod === 'cash' && $cashReceived !== null) {
            $change = $cashReceived - $this->total;
            if ($change < 0) {
                throw new \Exception("Uang diterima kurang dari total pembayaran");
            }
        }

        // Complete the order
        $this->order->complete();

        // Update sale with payment info
        $this->payment_method = $paymentMethod;
        $this->payment_status = 'paid';
        $this->cash_received = $cashReceived;
        $this->change = $change;
        $this->completed_at = now();
        $this->save();

        return $this;
    }

    /**
     * Reject order.
     */
    public function reject($reason, $processedBy)
    {
        if (!$this->order) {
            throw new \Exception("Sale tidak memiliki order terkait");
        }

        if ($this->order->status !== 'pending') {
            throw new \Exception("Order ini sudah diproses sebelumnya");
        }

        // Reject the order
        $this->order->reject($reason, $processedBy);

        // Update sale
        $this->processed_by = $processedBy;
        $this->processed_at = now();
        $this->save();

        return $this;
    }

    /**
     * Scope for sales with pending payment.
     */
    public function scopePendingPayment($query)
    {
        return $query->where('payment_status', 'pending');
    }

    /**
     * Scope for sales with paid payment.
     */
    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }

    /**
     * Scope for sales by date.
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * Scope for sales today.
     */
    public function scopeToday($query)
    {
        return $query->whereDate('date', today());
    }

    /**
     * Scope for sales this month.
     */
    public function scopeThisMonth($query)
    {
        return $query->whereMonth('date', now()->month)
                     ->whereYear('date', now()->year);
    }
}
