<?php

namespace App\Models;

use App\Traits\HasAdvancedFilters;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory, HasAdvancedFilters, SoftDeletes;

    protected $fillable = [
        'sku', 'name', 'description', 'category_id',
        'unit_measure_id', 'supplier_id', 'price', 'stock'
    ];
    protected $casts = [
        'price' => 'decimal:2',
        'stock' => 'integer',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function unitMeasure()
    {
        return $this->belongsTo(UnitMeasure::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function invoiceItems()
    {
        return $this->hasMany(InvoiceItem::class);
    }
}
