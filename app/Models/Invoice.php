<?php

namespace App\Models;

use App\Traits\HasAdvancedFilters;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Invoice extends Model
{
    /** @use HasFactory<\Database\Factories\InvoiceFactory> */
    use HasFactory, HasAdvancedFilters, SoftDeletes;
    protected $fillable = [
        'customer_id',
        'user_id',
        'pos_session_id',
        'date',
        'total_amount',
        'paid_amount',
        'debt_amount',
        'status',
        'payment_status',
        'due_date',
        'subtotal',
        'discount_type',
        'discount_value',
        'payment_method',
    ];

    protected $casts = [
        'date' => 'datetime',
        'due_date' => 'date',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'debt_amount' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'discount_value' => 'decimal:2',
        'discount_amount' => 'decimal:2',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function posSession()
    {
        return $this->belongsTo(PosSession::class);
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function debt()
    {
        return $this->hasOne(CustomerDebt::class);
    }

    // Métodos de utilidad para manejo de deudas
    public function createDebt($debtAmount, $dueDate = null)
    {
        if ($this->hasDebt()) {
            throw new \Exception('Esta factura ya tiene una deuda asociada');
        }

        return $this->debt()->create([
            'customer_id' => $this->customer_id,
            'user_id' => auth()->id(),
            'original_amount' => $debtAmount,
            'remaining_amount' => $debtAmount,
            'paid_amount' => 0,
            'debt_date' => now()->toDateString(),
            'due_date' => $dueDate,
            'status' => 'pending',
        ]);
    }

    public function hasDebt()
    {
        return $this->debt()->exists();
    }

    public function isFullyPaid()
    {
        return $this->payment_status === 'paid' || $this->paid_amount >= $this->total_amount;
    }

    public function isPartiallyPaid()
    {
        return $this->payment_status === 'partial' && $this->paid_amount > 0 && $this->paid_amount < $this->total_amount;
    }

    public function hasOutstandingDebt()
    {
        return $this->payment_status === 'debt' || $this->debt_amount > 0;
    }
}
