<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Facture extends Model
{
    use HasFactory , SoftDeletes;

    const DRAFT = "DRAFT";
    const UNPAID = "UNPAID";
    const PARTIALLY_PAID = "PARTIALLY_PAID";
    const PAID = "PAID";

    const CANCELED = "CANCELED";

    protected $table = 'factures';

    protected $fillable = [
        'order_id',
        'client_id',
        'reference',
        "facture_date",
        'expiration_date',
        'tva',
        'remise_type',
        'remise',
        "paid_amount",
        "bcn",
        'note',
        'total',
        "status"
    ];

    protected $appends = ['statusText'];


    /**
     * Relationships
     */
    public function order()
    {
        return $this->belongsTo(Order::class )->withTrashed();
    }
    
    public function client()
    {
        return $this->belongsTo(Client::class)->withTrashed();
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'facture_product')
                    ->withPivot('price_unitaire', 'quantity')
                    ->withTimestamps()
                    ->withTrashed();
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }


    /**
     * Get the status text based on the status attribute.
     *
     * @return string
    */
    public function getStatusTextAttribute()
    {
        switch ($this->status) {
            case self::CANCELED:
                return trans("facture.statuses.canceled");
            case self::DRAFT:
                return trans("facture.statuses.draft");
            case self::UNPAID:
                return trans("facture.statuses.unpaid");
            case self::PARTIALLY_PAID:
                return trans("facture.statuses.partially_paid");
            case self::PAID:
                return trans("facture.statuses.paid");
            default:
                return "-";
        }
    }

    public static function getStatus($paid_amount, $total_amount)
    {

        if ($paid_amount >= $total_amount) {
            return self::PAID;
        }

        if ($paid_amount > 0) {
            return self::PARTIALLY_PAID;
        }

        return self::UNPAID;
    }

    
}
