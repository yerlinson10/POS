<?php

namespace App\Models;

use App\Traits\HasAdvancedFilters;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Customer extends Model
{
    /** @use HasFactory<\Database\Factories\CustomerFactory> */
    use HasFactory, HasAdvancedFilters, SoftDeletes;

    protected $fillable = ['first_name', 'last_name', 'email', 'phone', 'address'];

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function debts()
    {
        return $this->hasMany(CustomerDebt::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function getFullNameAttribute()
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    // Métodos de utilidad para deudas
    public function getTotalDebtAttribute()
    {
        return $this->debts()->where('status', '!=', 'paid')->sum('remaining_amount');
    }

    public function hasActiveDebts()
    {
        return $this->debts()->where('status', '!=', 'paid')->exists();
    }

    public function getOverdueDebtsAttribute()
    {
        return $this->debts()->overdue()->get();
    }
}
