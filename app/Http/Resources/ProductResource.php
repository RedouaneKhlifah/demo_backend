<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'sku' => $this->sku,
            'unit' => $this->unit,
            'sale_price' => $this->pivot->price_unitaire,
            'stock' => $this->stock,
            'quantity' => $this->pivot->quantity,
            'deleted_at' => $this->deleted_at,
        ];
    }
    

}
