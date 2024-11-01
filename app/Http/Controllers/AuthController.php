<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return ResponseHelper::apiResponse(
            true,
            'User registered successfully',
            ['user' => $user, 'access_token' => $token, 'token_type' => 'Bearer'],
            201
        );
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return ResponseHelper::apiResponse(
                false,
                'The provided credentials are incorrect.',
                null,
                401,
                ['email' => ['The provided credentials are incorrect.']]
            );
        }

        // Generate token
        $token = $user->createToken('auth_token')->plainTextToken;

        return ResponseHelper::apiResponse(
            true,
            'Login successful',
            [
                'user' => $user,
                'access_token' => $token, 'token_type' => 'Bearer'
            ]
        );
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return ResponseHelper::apiResponse(true, 'Successfully logged out');
    }
}
