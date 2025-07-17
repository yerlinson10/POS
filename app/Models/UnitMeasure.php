<?php

namespace App\Models;

use App\Traits\HasAdvancedFilters;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UnitMeasure extends Model
{
    /** @use HasFactory<\Database\Factories\UnitMeasureFactory> */
    use HasFactory, HasAdvancedFilters, SoftDeletes;
    protected $fillable = ['code', 'name'];

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
