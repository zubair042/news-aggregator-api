<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Models\Article;
use App\Models\UserPreference;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;

class UserPreferencesController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/preferences",
     *     summary="Set user preferences",
     *     description="Save preferred sources, categories, and authors for the authenticated user.",
     *     operationId="setPreferences",
     *     tags={"User Preferences"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="preferred_sources", type="array", @OA\Items(type="string"), example={"source1", "source2"}),
     *             @OA\Property(property="preferred_categories", type="array", @OA\Items(type="string"), example={"news", "technology"}),
     *             @OA\Property(property="preferred_authors", type="array", @OA\Items(type="string"), example={"author1", "author2"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Preferences saved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Preferences saved successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="preferred_sources", type="array", @OA\Items(type="string")),
     *                 @OA\Property(property="preferred_categories", type="array", @OA\Items(type="string")),
     *                 @OA\Property(property="preferred_authors", type="array", @OA\Items(type="string"))
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to save preferences",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Failed to save preferences"),
     *             @OA\Property(property="error", type="string", example="Error message")
     *         )
     *     )
     * )
     */
    public function setPreferences(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'preferred_sources' => 'nullable|array',
                'preferred_categories' => 'nullable|array',
                'preferred_authors' => 'nullable|array',
            ]);

            $preferences = UserPreference::updateOrCreate(
                ['user_id' => Auth::id()],
                [
                    'preferred_sources' => $request->preferred_sources,
                    'preferred_categories' => $request->preferred_categories,
                    'preferred_authors' => $request->preferred_authors,
                ]
            );

            return ResponseHelper::apiResponse(
                true,
                'Preferences saved successfully',
                $preferences
            );
        } catch (\Exception $e) {
            return ResponseHelper::apiResponse(
                false,
                'Failed to save preferences',
                null,
                500,
                ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * @OA\Get(
     *     path="/api/preferences",
     *     summary="Get user preferences",
     *     description="Retrieve the preferred sources, categories, and authors of the authenticated user.",
     *     operationId="getPreferences",
     *     tags={"User Preferences"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Preferences retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Preferences retrieved successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="preferred_sources", type="array", @OA\Items(type="string")),
     *                 @OA\Property(property="preferred_categories", type="array", @OA\Items(type="string")),
     *                 @OA\Property(property="preferred_authors", type="array", @OA\Items(type="string"))
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No preferences found for this user",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="No preferences found for this user")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to retrieve preferences",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Failed to retrieve preferences"),
     *             @OA\Property(property="error", type="string", example="Error message")
     *         )
     *     )
     * )
     */
    public function getPreferences(): JsonResponse
    {
        try {
            $preferences = UserPreference::where('user_id', Auth::id())->first();

            if (!$preferences) {
                return ResponseHelper::apiResponse(
                    false,
                    'No preferences found for this user',
                    null,
                    404
                );
            }

            return ResponseHelper::apiResponse(
                true,
                'Preferences retrieved successfully',
                $preferences
            );
        } catch (\Exception $e) {
            return ResponseHelper::apiResponse(
                false,
                'Failed to retrieve preferences',
                null,
                500,
                ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * @OA\Get(
     *     path="/api/personalized-feed",
     *     summary="Get personalized feed",
     *     description="Retrieve a personalized feed of articles based on user preferences.",
     *     operationId="personalizedFeed",
     *     tags={"User Preferences"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Personalized feed retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Personalized feed retrieved successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="title", type="string", example="Article title"),
     *                         @OA\Property(property="content", type="string", example="Article content"),
     *                         @OA\Property(property="category", type="string", example="news"),
     *                         @OA\Property(property="source", type="string", example="source name"),
     *                         @OA\Property(property="published_at", type="string", format="date-time", example="2024-11-01T12:34:56Z")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No preferences set for personalized feed",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="No preferences set for personalized feed.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to retrieve personalized feed",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Failed to retrieve personalized feed"),
     *             @OA\Property(property="error", type="string", example="Error message")
     *         )
     *     )
     * )
     */
    public function personalizedFeed(): JsonResponse
    {
        try {
            $preferences = UserPreference::where('user_id', Auth::id())->first();

            if (!$preferences) {
                return ResponseHelper::apiResponse(
                    false,
                    'No preferences set for personalized feed.',
                    null,
                    404
                );
            }

            $query = Article::query();

            // Filter articles based on user preferences
            if (!empty($preferences->preferred_sources)) {
                $query->whereIn('source', $preferences->preferred_sources);
            }

            if (!empty($preferences->preferred_categories)) {
                $query->whereIn('category', $preferences->preferred_categories);
            }

            if (!empty($preferences->preferred_authors)) {
                $query->whereIn('author', $preferences->preferred_authors);
            }

            $articles = $query->paginate(10);

            return ResponseHelper::apiResponse(
                true,
                'Personalized feed retrieved successfully',
                $articles
            );
        } catch (\Exception $e) {
            return ResponseHelper::apiResponse(
                false,
                'Failed to retrieve personalized feed',
                null,
                500,
                ['error' => $e->getMessage()]
            );
        }
    }
}
