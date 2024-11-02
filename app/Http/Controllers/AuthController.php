<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/register",
     *     summary="Register a new user",
     *     description="Create a new user and return an authentication token",
     *     operationId="register",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email","password","password_confirmation"},
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="johndoe@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="strongPassword123"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="strongPassword123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User registered successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="User registered successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="user", ref="#/components/schemas/User"),
     *                 @OA\Property(property="access_token", type="string", example="token"),
     *                 @OA\Property(property="token_type", type="string", example="Bearer")
     *             )
     *         )
     *     )
     * )
     */
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

    /**
     * @OA\Post(
     *     path="/api/login",
     *     summary="User login",
     *     description="Authenticate user and return an access token",
     *     operationId="login",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", format="email", example="johndoe@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="strongPassword123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Login successful"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="user", ref="#/components/schemas/User"),
     *                 @OA\Property(property="access_token", type="string", example="token"),
     *                 @OA\Property(property="token_type", type="string", example="Bearer")
     *             )
     *         )
     *     )
     * )
     */
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

    /**
     * @OA\Post(
     *     path="/api/logout",
     *     summary="User logout",
     *     description="Logs out the user and invalidates the token",
     *     operationId="logout",
     *     tags={"Authentication"},
     *     @OA\Response(
     *         response=200,
     *         description="Successfully logged out",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Successfully logged out")
     *         )
     *     )
     * )
     */
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return ResponseHelper::apiResponse(true, 'Successfully logged out');
    }
}
