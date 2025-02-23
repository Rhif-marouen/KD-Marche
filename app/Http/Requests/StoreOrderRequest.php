<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOrderRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Autoriser tous les utilisateurs authentifiés
    }

    public function rules()
    {
        return [
            'items' => [
                'required',
                'array',
                'min:1',
                function ($attribute, $value, $fail) {
                    foreach ($value as $item) {
                        if ($item['quantity'] < 3) {
                            $fail("Minimum 3 unités requis pour le produit ".$item['product_id']);
                        }
                    }
                }
            ],
            'items.*.product_id' => [
                'required',
                Rule::exists('products', 'id')->where('stock', '>', 0)
            ],
            'items.*.quantity' => 'required|integer|min:3'
        ];
    }

    public function messages()
    {
        return [
            'items.*.product_id.exists' => 'Le produit sélectionné est indisponible',
            'items.*.quantity.min' => 'La quantité minimale par produit est de 3'
        ];
    }
}