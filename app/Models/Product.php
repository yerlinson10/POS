<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory;

    protected $fillable = [
        'sku', 'name', 'description', 'category_id',
        'unit_measure_id', 'price', 'stock'
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
