<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', Rule::unique('users')],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([$data]) ;
        $token = $user->createToken('auth-token' , ['user'])->plainTextToken;

        return response()->json([
            'user'  => new UserResource($user),
            'token' => $token,
        ], 201);
    }

    public function login(Request $request)
    {
        $credentials = $request->valdiate([
            'email' => ['required', 'email'],
            'password' => ['required' , 'string'],
        ]) ;

        $user = User::where('email' , $credentials['email'])->first();
        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => 'The provided credentials are incorrect.',
            ]);
        }

        $token = $user->createToken('auth-token' , ['user'])->plainTextToken;

        return response()->json([
            'user' => new UserResource($user),
            'token' => $token,
        ] , 201) ;
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->noContent() ;
    }


    public function me(Request $request)
    {
        return new UserResource($request->user());
    }

}
