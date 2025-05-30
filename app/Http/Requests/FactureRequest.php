<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FactureRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'payments' => 'sometimes|array',
            'payments.*.id' => 'nullable|exists:payments,id',
            'payments.*.amount' => 'required|numeric|min:0',
            'payments.*.type' => 'required|in:cash,bank_transfer,credit_card,check,mobile_payment,other',
            'payments.*.payment_date' => 'required|date',
        ];
    }
}