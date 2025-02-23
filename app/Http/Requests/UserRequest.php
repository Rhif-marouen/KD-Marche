<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
class UserRequest extends FormRequest
{
    public function authorize()
{
    return $this->user() && $this->user()->isAdmin();
}

    // app/Http/Requests/UserRequest.php
public function rules()
{
    $userId = $this->route('user')?->id;

    return [
        'name' => 'required|string|max:255', 
        'email' => 'required|email|unique:users,email,' . $userId,
        'password' => $this->isMethod('POST') 
            ? 'required|min:8|confirmed' 
            : 'nullable|min:8|confirmed',
        'is_admin' => 'sometimes|boolean'
    ];
}
}