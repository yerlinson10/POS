<?php

namespace App\Models;

use App\Traits\HasAdvancedFilters;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CustomerDebt extends Model
{
    /** @use HasFactory<\Database\Factories\CustomerDebtFactory> */
    use HasFactory, HasAdvancedFilters, SoftDeletes;

    protected $fillable = [
        'customer_id',
        'invoice_id',
        'user_id',
        'original_amount',
        'remaining_amount',
        'paid_amount',
        'debt_date',
        'due_date',
        'status',
        'notes'
    ];

    protected $casts = [
        'original_amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'debt_date' => 'date',
        'due_date' => 'date',
    ];

    // Relaciones
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopePartial($query)
    {
        return $query->where('status', 'partial');
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'overdue')->where('due_date', '<', now());
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    // Métodos de utilidad
    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isPartial()
    {
        return $this->status === 'partial';
    }

    public function isPaid()
    {
        return $this->status === 'paid';
    }

    public function isOverdue()
    {
        return $this->status === 'overdue' || ($this->due_date && $this->due_date < now()->toDateString() && !$this->isPaid());
    }

    public function addPayment($amount, $paymentMethod = 'cash', $description = null)
    {
        if ($amount > $this->remaining_amount) {
            throw new \Exception('El monto a pagar no puede ser mayor a la deuda pendiente');
        }

        // Actualizar montos
        $this->update([
            'paid_amount' => $this->paid_amount + $amount,
            'remaining_amount' => $this->remaining_amount - $amount,
        ]);

        // Actualizar estado
        if ($this->remaining_amount <= 0) {
            $this->update(['status' => 'paid', 'remaining_amount' => 0]);
        } else {
            $this->update(['status' => 'partial']);
        }

        $this->refresh();

        // Crear registro de pago
        $payment = Payment::create([
            'type' => 'income',
            'category' => 'debt_payment',
            'amount' => $amount,
            'payment_method' => $paymentMethod,
            'payment_date' => now()->toDateString(),
            'description' => $description ?? "Pago de deuda - {$this->customer->full_name}",
            'customer_id' => $this->customer_id,
            'customer_debt_id' => $this->id,
            'user_id' => auth()->id(),
        ]);

        // Actualizar el monto pagado en la factura
        $this->invoice->increment('paid_amount', $amount);
        
        // Actualizar estado de pago de la factura
        if ($this->invoice->paid_amount >= $this->invoice->total_amount) {
            $this->invoice->update(['payment_status' => 'paid']);
        } else {
            $this->invoice->update(['payment_status' => 'partial']);
        }

        return $payment;
    }

    // Métodos de utilidad para días vencidos se pueden calcular en el servicio o controlador cuando sea necesario
}
