<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory , SoftDeletes;
    protected $fillable = [
        'name',
        'sku',
        'unit',
        'sale_price',
        'cost_price',
        'description',
        'tax',
        'stock',
        'reorder_point',
    ];

    protected $casts = [
        'sale_price'         => 'decimal:2',
        'cost_price'         => 'decimal:2',
        'tax'                => 'decimal:2',
        'stock'              => 'decimal:2',
        'reorder_point'      => 'decimal:2',
    ];

    /**
     * Get all of the product's images.
     */
    public function images()
    {
        return $this->morphMany(Image::class, 'imageable');
    }


    public function order()
    {
        return $this->belongsToMany(Order::class);
    }

}