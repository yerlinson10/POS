<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    /** @use HasFactory<\Database\Factories\InvoiceFactory> */
    use HasFactory;
    protected $fillable = ['customer_id', 'user_id', 'date', 'total_amount', 'status'];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'date' => 'date',
        'customer_id' => 'integer',
        'user_id' => 'integer',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }
}
