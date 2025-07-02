<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasAdvancedFilters;

class Product extends Model
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory, HasAdvancedFilters;

    protected $fillable = [
        'sku', 'name', 'description', 'category_id',
        'unit_measure_id', 'price', 'stock'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'stock' => 'integer',
        'category_id' => 'integer',
        'unit_measure_id' => 'integer',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function unitMeasure()
    {
        return $this->belongsTo(UnitMeasure::class);
    }

    public function invoiceItems()
    {
        return $this->hasMany(InvoiceItem::class);
    }
}
