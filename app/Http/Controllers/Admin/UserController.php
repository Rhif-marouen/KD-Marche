<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Applique les middlewares d'authentification et admin pour les méthodes sensibles
     */
    public function __construct()
    {
        $this->middleware(['auth:sanctum', 'admin'])->except(['show', 'update']);
    }

    /**
     * Liste paginée des utilisateurs (Admin only)
     */
    public function index(): JsonResponse
    {
        $this->authorize('viewAny', User::class);
        
        $users = User::with(['subscriptions', 'orders'])
            ->latest()
            ->paginate(10);

        return UserResource::collection($users)->response();
    }

    /**
     * Création d'un utilisateur (Admin only)
     */
    public function store(UserRequest $request): JsonResponse
    {
        $user = User::create($request->validated());

        return (new UserResource($user))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Affichage d'un utilisateur spécifique
     */
    public function show(User $user): JsonResponse
    {
        $this->authorize('view', $user);

        return (new UserResource($user->loadMissing(['subscriptions', 'orders'])))->response();
    }

    /**
     * Mise à jour des informations utilisateur
     */
    public function update(UserRequest $request, User $user): JsonResponse
    {
        $this->authorize('update', $user);

        $user->update($request->validated());

        return (new UserResource($user))->response();
    }

    /**
     * Suppression d'un utilisateur (Admin only)
     */
    public function destroy(User $user): JsonResponse
    {
        $this->authorize('delete', $user);

        $user->delete();

        return response()->json([
            'message' => __('User deleted successfully')
        ]);
    }

    /**
     * Activation d'un abonnement payant
     */
    public function activateSubscription(Request $request, User $user): JsonResponse
    {
        $this->authorize('manageSubscription', $user);

        // Logique d'activation de l'abonnement
        $user->update([
            'is_active' => true,
            'subscription_end' => now()->addYear()
        ]);

        return (new UserResource($user))->response();
    }

    /*
public function activateSubscription(Request $request, User $user)
{
    $this->authorize('manageSubscription', $user);

    // Créer un abonnement lié à l'utilisateur
    $subscription = $user->subscriptions()->create([
        'status' => 'active',
        'amount' => 10.00, // Exemple
        'start_date' => now(),
        'end_date' => now()->addYear(),
        'payment_method' => 'stripe'
    ]);

    return new SubscriptionResource($subscription);
} */
}