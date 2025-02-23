<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class AdminUserController extends Controller
{
   // Mettre à jour un utilisateur (activation/rôle)
public function updateUser(Request $request, $id)
{
    $user = User::findOrFail($id);

    $validator = Validator::make($request->all(), [
        'is_active' => 'sometimes|boolean',
        'is_admin' => 'sometimes|boolean'
    ]);

    if ($validator->fails()) {
        return response()->json($validator->errors(), 422);
    }

    $user->update($request->all());
    return response()->json($user);
}

// Supprimer un utilisateur
public function deleteUser($id)
{
    $user = User::findOrFail($id);
    if ($user->is_admin) {
        return response()->json(['error' => 'Cannot delete admin'], 403);
    }
    $user->delete();
    return response()->json(['message' => 'Utilisateur supprimé']);
}
}
