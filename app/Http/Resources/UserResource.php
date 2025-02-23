<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\SubscriptionResource;
class UserResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'is_admin' => $this->is_admin,
            'is_active' => $this->is_active,
            'subscription_end' => $this->subscription_end?->toDateString(),
            'created_at' => $this->created_at->toIso8601String(),
           'subscriptions' => SubscriptionResource::collection($this->whenLoaded('subscriptions')),
            'orders' => OrderResource::collection($this->whenLoaded('orders'))
        ];
    }
}