<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemSettingOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'system_setting_id',
        'value',
        'default',
        'label',
        'description',
    ];

    public function setting()
    {
        return $this->belongsTo(SystemSetting::class, 'system_setting_id');
    }
}
