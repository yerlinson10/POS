<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserSystemSetting extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'system_setting_id',
        'value',
    ];

    public function setting()
    {
        return $this->belongsTo(SystemSetting::class, 'system_setting_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
