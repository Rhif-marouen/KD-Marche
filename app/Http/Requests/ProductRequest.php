<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->isAdmin();
    }

    public function rules()
{
    $rules = [
        'name' => 'required|string|max:255',
        'price' => 'required|numeric|min:0.01',
        'category_id' => 'required|exists:categories,id',
        'stock' => 'required|integer|min:0',
        'description' => 'required|string',
        'quality' => 'required|in:A,B,C,D,E',
    ];

    // Rendre l'image obligatoire seulement pour la création
    if ($this->isMethod('POST')) {
        $rules['image'] = 'required|image|mimes:jpeg,png,jpg|max:5120';
    } else {
        $rules['image'] = 'sometimes|image|mimes:jpeg,png,jpg|max:5120';
    }

    return $rules;
}
    
}