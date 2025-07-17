<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SystemSetting extends Model
{
    use HasFactory, SoftDeletes;

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
