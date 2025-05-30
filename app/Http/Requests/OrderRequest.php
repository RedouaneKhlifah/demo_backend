<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrderRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            "client_id" => 'required|exists:clients,id',
            'reference' => 'required|string|max:255',
            'order_date' => 'required|date',
            'expiration_date' => 'required|date',
            'tva' => 'required|numeric',
            'remise_type' => 'required|string|max:255 |in:PERCENT,FIXED',
            'remise' => 'nullable|numeric',
            'note' => 'nullable|string',
            'bcn' => 'nullable|string',
            'products' => 'required|array',
            'products.*.id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|numeric|min:0',
            'products.*.sale_price' => 'required|numeric|min:0',
        ];
    }
}