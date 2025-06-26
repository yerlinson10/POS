<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PosSession extends Model
{
    /** @use HasFactory<\Database\Factories\PosSessionFactory> */
    use HasFactory;
    protected $fillable = ['user_id', 'opened_at', 'closed_at', 'initial_cash', 'final_cash'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
