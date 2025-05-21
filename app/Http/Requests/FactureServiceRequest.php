<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FactureServiceRequest extends FormRequest
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
            'facture_date' => 'required|date',
            'expiration_date' => 'required|date',
            "paid_amount" => 'nullable|numeric',
            'tva' => 'required|numeric',
            'remise_type' => 'required|string|max:255|in:PERCENT,FIXED',
            'remise' => 'nullable|numeric',
            "bcn" => 'nullable|string',
            'note' => 'nullable|string',
            'services' => 'required|array',
            'services.*.service_id' => 'required|exists:services,id',
            'services.*.prix' => 'required|numeric',
            'services.*.quantity' => 'required|numeric|min:1',
        ];
    }
}