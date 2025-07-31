<?php

namespace App\Models;

use App\Traits\HasAdvancedFilters;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Supplier extends Model
{
    /** @use HasFactory<\Database\Factories\SupplierFactory> */
    use HasFactory, HasAdvancedFilters, SoftDeletes;

    protected $fillable = [
        'company_name',
        'contact_name',
        'email',
        'phone',
        'address',
        'city',
        'country',
        'tax_id',
        'notes',
        'current_debt',
        'is_active'
    ];

    protected $casts = [
        'current_debt' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    // Relaciones
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeWithDebt($query)
    {
        return $query->where('current_debt', '>', 0);
    }

    // Métodos de utilidad
    public function hasDebt()
    {
        return $this->current_debt > 0;
    }

    public function addDebt($amount, $description = null)
    {
        $this->increment('current_debt', $amount);
        
        // Registrar el pago como egreso
        $this->payments()->create([
            'type' => 'expense',
            'category' => 'supplier_payment',
            'amount' => $amount,
            'payment_date' => now()->toDateString(),
            'description' => $description ?? "Compra a proveedor {$this->company_name}",
            'user_id' => auth()->id(),
        ]);
    }

    public function payDebt($amount, $paymentMethod = 'cash', $description = null)
    {
        if ($amount > $this->current_debt) {
            throw new \Exception('El monto a pagar no puede ser mayor a la deuda actual');
        }

        $this->decrement('current_debt', $amount);

        // Registrar el pago como egreso
        $this->payments()->create([
            'type' => 'expense',
            'category' => 'supplier_payment',
            'amount' => $amount,
            'payment_method' => $paymentMethod,
            'payment_date' => now()->toDateString(),
            'description' => $description ?? "Pago a proveedor {$this->company_name}",
            'user_id' => auth()->id(),
        ]);
    }
}
