<?php

namespace App\Models;

use App\Traits\HasAdvancedFilters;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PosSession extends Model
{
    /** @use HasFactory<\Database\Factories\PosSessionFactory> */
    use HasFactory, HasAdvancedFilters;
    protected $fillable = ['user_id', 'opened_at', 'closed_at', 'initial_cash', 'final_cash'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
