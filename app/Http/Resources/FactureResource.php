<?php

namespace App\Http\Resources;

use App\Http\Resources\OrderResource;
use App\Models\Facture;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FactureResource extends JsonResource
{

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        // Access the loaded products relation directly as Eloquent models
        $products = $this->whenLoaded('products');

        // Calculate subtotal directly from the Eloquent models
        $subtotal = collect($products)->sum(function ($product) {
            return $product->pivot->price_unitaire * $product->pivot->quantity;
        });

        // Calculate TVA (tax)
        $tva = $this->tva && $this->tva > 0 ? ($subtotal * $this->tva / 100) : 0;

        $discount = $this->remise;

        if ($this->remise_type === 'PERCENT') {
            $discount = $subtotal * ($this->remise) / 100;
        }

        // Prevent discount from exceeding subtotal
        $discount = min($discount, $subtotal);

        // Final total
        $total = round($subtotal + $tva - $discount, 2);
        


        return [
            'id' => $this->id,
            'client' => $this->client,
            'reference' => $this->reference,
            'bcn' => $this->bcn,
            'facture_date' => $this->facture_date,
            'expiration_date' => $this->expiration_date,
            'tva' => $this->tva,
            'remise_type' => $this->remise_type,
            'remise' => $this->remise,
            'note' => $this->note,
            'total' => $total,
            'paid_amount' => $this->paid_amount,
            'status' => $this->status,
            'statusText' => $this->statusText,
            'products' => ProductResource::collection($products),
            'payments' => $this->whenLoaded('payments'),
        ];
    }
}
