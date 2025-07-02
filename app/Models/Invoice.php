<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    /** @use HasFactory<\Database\Factories\InvoiceFactory> */
    use HasFactory;
    protected $fillable = [
        'customer_id',
        'user_id',
        'date',
        'total_amount',
        'status',
        'subtotal',
        'discount_type',
        'discount_value',
    ];

    protected $casts = [
        'date' => 'datetime',
        'total_amount' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'discount_value' => 'decimal:2',
        'discount_amount' => 'decimal:2',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function posUser()
    {
        return $this->belongsTo(User::class, 'pos_user_id');
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }
}
