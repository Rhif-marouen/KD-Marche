<x-mail::message>
# Livraison confirmée 🎉

Bonjour {{ $order->user->name }},

Votre commande **#{{ $order->id }}** d'un montant de **{{ number_format($order->total, 2) }}€** a été livrée avec succès le **{{ $deliveryDate }}**.

<x-mail::button :url="route('orders.show', $order->id)">
Voir ma commande
</x-mail::button>

Cordialement,<br>
{{ config('app.name') }}
</x-mail::message>