<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSystemSetting extends Model
{
    use HasFactory;

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
