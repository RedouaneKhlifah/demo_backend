<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;

class FactureService extends Model
{
    use HasFactory, SoftDeletes;

    const DRAFT = "DRAFT";
    const UNPAID = "UNPAID";
    const PARTIALLY_PAID = "PARTIALLY_PAID";
    const PAID = "PAID";

    protected $table = 'facture_services';

    protected $fillable = [
        'client_id',
        'reference',
        "facture_date",
        'expiration_date',
        'tva',
        'remise_type',
        'remise',
        "paid_amount",
        'note',
        "bcn",
    ];

    protected $appends = ['totals', "total", 'statusText', "status"]; 

    /**
     * Relationships
     */
    public function client()
    {
        return $this->belongsTo(Client::class)->withTrashed();
    }

    public function services()
    {
        return $this->belongsToMany(Service::class, 'facture_service_service')
                    ->withPivot('prix', 'quantity')
                    ->withTimestamps()
                    ->withTrashed();
    }

    /**
     * Calculate total attribute
     */
    public function getTotalAttribute()
    {
        // Ensure services is a collection
        $services = collect($this->services);
    
        // Calculate subtotal (HT) - sum of (unit price * quantity)
        $subtotal = $services->sum(function ($service) {
            return data_get($service, 'prix', 0) * data_get($service, 'quantity', 0);
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
        // Ensure services is a collection
        $services = collect($this->services);


        
        // Calculate subtotal (HT) - sum of (unit price * quantity)
        $subtotal = $services->sum(function ($service) {
            return data_get($service, 'prix', 0) * data_get($service, 'quantity', 0);
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
    
    public function getStatusTextAttribute()
    {
        if(is_null($this->paid_amount)){
            return trans("facture.statuses.draft");
        }elseif($this->paid_amount == 0){
            return trans("facture.statuses.unpaid");
        }elseif($this->paid_amount < $this->total){ 
            return trans("facture.statuses.partially_paid");
        }else{
            return trans("facture.statuses.paid");
        }
    }

    public function getStatusAttribute()
    {
        if(is_null($this->paid_amount)){
            return self::DRAFT;
        }elseif($this->paid_amount == 0){
            return self::UNPAID;
        }elseif($this->paid_amount < $this->total){ 
            return self::PARTIALLY_PAID;
        }else{
            return self::PAID;
        }
    }
}