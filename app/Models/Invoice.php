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
        'status',
        'subtotal',
        'discount_type',
        'discount_value',
        'payment_method',
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
}
