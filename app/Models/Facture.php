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
        "status"
    ];

    

    protected $appends = ['totals',"total" , 'statusText']; // Adds total to the JSON output

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
                    ->withPivot('price_unitaire', 'quantity' ,'order_id')
                    ->withTimestamps()
                    ->withTrashed();
    }

    /**
     * Calculate total attribute
     */
    public function getTotalAttribute()
    {
        // Ensure products is a collection
        $products = collect($this->products);
    
        // Calculate subtotal (HT) - sum of (unit price * quantity)
        $subtotal = $products->sum(function ($product) {
            return data_get($product, 'sale_price', 0) * data_get($product, 'quantity', 0);
        });
    
        // Calculate TVA amount (TVA% * subtotal)
        $tvaAmount = ($subtotal * $this->tva) / 100;
    
        // Calculate Total TTC before applying the discount
        $totalTTC = $subtotal + $tvaAmount;
    
        // Calculate Remise amount based on type (PERCENT or FIXED)
        $remiseAmount = $this->remise_type === "PERCENT"
            ? ($subtotal * $this->remise) / 100  // Discount applied on subtotal (HT)
            : $this->remise;
    
        // Final total after applying the remise
        return round($totalTTC - $remiseAmount, 2);
    }

    public function getTotalsAttribute()
    {
        // Ensure products is a collection
        $products = collect($this->products);
        
        // Calculate subtotal (HT) - sum of (unit price * quantity)
        $subtotal = $products->sum(function ($product) {
            return data_get($product, 'sale_price', 0) * data_get($product, 'pivot.quantity', 0);
        });
    
    
        // Calculate TVA amount (TVA% * subtotal)
        $tvaAmount = ($subtotal * $this->tva) / 100;
    
        // Calculate Total TTC before applying the discount
        $totalTTC = $subtotal + $tvaAmount;
    
        // Calculate Remise amount based on type (PERCENT or FIXED)
        $remiseAmount = $this->remise_type === "PERCENT"
            ? ($subtotal * $this->remise) / 100  // Discount applied on subtotal (HT)
            : $this->remise;
    
        // Final total after applying the remise
        return round($totalTTC - $remiseAmount, 2);
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

    /**
     * Get the status of the invoice based on the paid amount and total.
     *
     * @return string
    */

    public function getStatusAttribute()
    {
        if ($this->attributes['status']) {
            return $this->attributes['status'];
        }

        if (is_null($this->paid_amount)) {
            return self::DRAFT;
        } elseif ($this->paid_amount == 0) {
            return self::UNPAID;
        } elseif ($this->paid_amount < $this->total) {
            return self::PARTIALLY_PAID;
        } else {
            return self::PAID;
        }
    }


    
}
