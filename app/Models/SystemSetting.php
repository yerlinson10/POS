<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    use HasFactory;

    protected $guarded = ['id', 'created_at', 'updated_at'];

    public function options()
    {
        return $this->hasMany(SystemSettingOption::class);
    }

    public function userValues()
    {
        return $this->hasMany(UserSystemSetting::class);
    }
}
