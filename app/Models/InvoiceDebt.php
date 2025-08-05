<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InvoiceDebt extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'customer_debt_id',
        'debt_amount',
        'notes',
    ];

    protected $casts = [
        'debt_amount' => 'decimal:2',
    ];

    // Relaciones
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function customerDebt()
    {
        return $this->belongsTo(CustomerDebt::class);
    }
}
