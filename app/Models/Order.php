<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory ,SoftDeletes;

    protected $table = 'orders';

    protected $fillable = [
        'client_id',
        'reference',
        'bcn',
        'order_date',
        'expiration_date',
        'tva',
        'remise_type',
        'remise',
        'note',
        'is_published',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class)->withTrashed();
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'order_product')
                    ->withPivot( 'quantity' , 'price_unitaire')
                    ->withTimestamps()
                    ->withTrashed();
    }

    public function facture()
    {
        return $this->hasOne(Facture::class);
    }

    
}
