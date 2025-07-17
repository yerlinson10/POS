<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SystemSettingOption extends Model
{
    use HasFactory, SoftDeletes;

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
