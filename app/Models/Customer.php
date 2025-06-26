<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    /** @use HasFactory<\Database\Factories\CustomerFactory> */
    use HasFactory;

    protected $fillable = ['first_name', 'last_name', 'email', 'phone', 'address'];

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
}
