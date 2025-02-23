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
            'price' => $this->price,
            'stock' => $this->stock,
            'category' => $this->category->name,
            'description' => $this->description,
            'image_url' => $this->image_url,
            'created_at' => $this->created_at->toIso8601String()
        ];
    }
}