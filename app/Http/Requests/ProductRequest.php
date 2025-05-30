<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{
    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:255',
            'unit' => 'nullable|string|max:50',
            'sale_price' => 'required|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'tax' => 'nullable|numeric|min:0',
            'stock' => 'required|numeric|min:0',
            'reorder_point' => 'nullable|numeric|min:0',
        ];
    }


}