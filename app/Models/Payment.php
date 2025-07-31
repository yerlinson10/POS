<?php

namespace App\Models;

use App\Traits\HasAdvancedFilters;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Payment extends Model
{
    /** @use HasFactory<\Database\Factories\PaymentFactory> */
    use HasFactory, HasAdvancedFilters, SoftDeletes;

    protected $fillable = [
        'type',
        'category',
        'amount',
        'payment_method',
        'payment_date',
        'reference_number',
        'description',
        'payable_type',
        'payable_id',
        'customer_id',
        'supplier_id',
        'customer_debt_id',
        'user_id',
        'pos_session_id',
        'notes',
        'metadata'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
        'metadata' => 'array',
    ];

    // Relaciones
    public function payable()
    {
        return $this->morphTo();
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function customerDebt()
    {
        return $this->belongsTo(CustomerDebt::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function posSession()
    {
        return $this->belongsTo(PosSession::class);
    }

    // Scopes
    public function scopeIncome($query)
    {
        return $query->where('type', 'income');
    }

    public function scopeExpense($query)
    {
        return $query->where('type', 'expense');
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('payment_date', [$startDate, $endDate]);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('payment_date', now()->toDateString());
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('payment_date', now()->month)
                    ->whereYear('payment_date', now()->year);
    }

    // Métodos de utilidad
    public function isIncome()
    {
        return $this->type === 'income';
    }

    public function isExpense()
    {
        return $this->type === 'expense';
    }

    public function getFormattedAmountAttribute()
    {
        return number_format((float)$this->amount, 2);
    }

    public function getCategoryLabelAttribute()
    {
        $categories = [
            'debt_payment' => 'Pago de Deuda',
            'supplier_payment' => 'Pago a Proveedor',
            'other_income' => 'Otro Ingreso',
            'other_expense' => 'Otro Egreso',
            'sale' => 'Venta',
            'refund' => 'Reembolso',
        ];

        return $categories[$this->category] ?? ucfirst(str_replace('_', ' ', $this->category));
    }

    public function getPaymentMethodLabelAttribute()
    {
        $methods = [
            'cash' => 'Efectivo',
            'card' => 'Tarjeta',
            'bank_transfer' => 'Transferencia Bancaria',
            'check' => 'Cheque',
            'other' => 'Otro',
        ];

        return $methods[$this->payment_method] ?? ucfirst(str_replace('_', ' ', $this->payment_method));
    }
}
