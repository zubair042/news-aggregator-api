<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

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
    public function register(Request $request): JsonResponse
    {
        try {
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
        } catch (ValidationException $e) {
            return ResponseHelper::apiResponse(
                false,
                'Validation failed',
                null,
                422,
                $e->errors()
            );
        } catch (\Exception $e) {
            return ResponseHelper::apiResponse(
                false,
                'An error occurred during registration',
                null,
                500,
                ['error' => $e->getMessage()]
            );
        }
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
    public function login(Request $request): JsonResponse
    {
        try {
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
                ['user' => $user, 'access_token' => $token, 'token_type' => 'Bearer']
            );
        } catch (ValidationException $e) {
            return ResponseHelper::apiResponse(
                false,
                'Validation failed',
                null,
                422,
                $e->errors()
            );
        } catch (\Exception $e) {
            return ResponseHelper::apiResponse(
                false,
                'An error occurred during login',
                null,
                500,
                ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * @OA\Post(
     *     path="/api/logout",
     *     summary="User logout",
     *     description="Logs out the user and invalidates the token",
     *     operationId="logout",
     *     tags={"Authentication"},
     *     security={{"bearerAuth":{}}},
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
    public function logout(Request $request): JsonResponse
    {
        try {
            $request->user()->tokens()->delete();
            return ResponseHelper::apiResponse(true, 'Successfully logged out');
        } catch (\Exception $e) {
            return ResponseHelper::apiResponse(
                false,
                'An error occurred during logout',
                null,
                500,
                ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * @OA\Post(
     *     path="/api/update-password",
     *     summary="Update user password",
     *     description="Allows an authenticated user to update their password by providing the current password and a new password.",
     *     operationId="updatePassword",
     *     tags={"Authentication"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"current_password", "new_password", "new_password_confirmation"},
     *             @OA\Property(property="current_password", type="string", format="password", example="strongPassword123"),
     *             @OA\Property(property="new_password", type="string", format="password", example="newStrongPassword123"),
     *             @OA\Property(property="new_password_confirmation", type="string", format="password", example="newStrongPassword123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Password has been updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Password has been updated successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation failed"),
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="new_password", type="array",
     *                     @OA\Items(type="string", example="The new password must be at least 8 characters.")
     *                 ),
     *                 @OA\Property(property="new_password_confirmation", type="array",
     *                     @OA\Items(type="string", example="The new password confirmation does not match.")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="An error occurred while updating the password",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="An error occurred while updating the password"),
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="error", type="string", example="Unexpected error message")
     *             )
     *         )
     *     )
     * )
     */
    public function updatePassword(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'current_password' => 'required|string',
                'new_password' => 'required|string|min:8|confirmed',
            ]);

            $user = $request->user();

            // Check if the current password is correct
            if (!Hash::check($request->current_password, $user->password)) {
                return ResponseHelper::apiResponse(
                    false,
                    'The current password is incorrect.',
                    null,
                    403,
                    ['current_password' => ['The current password is incorrect.']]
                );
            }

            $user->update([
                'password' => Hash::make($request->new_password),
            ]);

            return ResponseHelper::apiResponse(
                true,
                'Password has been updated successfully'
            );

        } catch (ValidationException $e) {
            return ResponseHelper::apiResponse(
                false,
                'Validation failed',
                null,
                422,
                $e->errors()
            );
        } catch (\Exception $e) {
            return ResponseHelper::apiResponse(
                false,
                'An error occurred while updating the password',
                null,
                500,
                ['error' => $e->getMessage()]
            );
        }
    }
}
