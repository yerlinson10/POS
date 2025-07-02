<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    /** @use HasFactory<\Database\Factories\InvoiceFactory> */
    use HasFactory;
    protected $fillable = ['customer_id', 'pos_user_id', 'date', 'total_amount', 'status'];

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
