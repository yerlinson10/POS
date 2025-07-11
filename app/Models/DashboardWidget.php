<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DashboardWidget extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'widget_type',
        'title',
        'x',
        'y',
        'width',
        'height',
        'config',
        'filters',
        'advanced_filters',
        'is_active'
    ];

    protected $casts = [
        'config' => 'array',
        'filters' => 'array',
        'advanced_filters' => 'array',
        'is_active' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
