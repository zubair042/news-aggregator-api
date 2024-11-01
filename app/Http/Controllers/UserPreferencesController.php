<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Models\Article;
use App\Models\UserPreference;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserPreferencesController extends Controller
{
    public function setPreferences(Request $request)
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

    public function getPreferences()
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

    public function personalizedFeed()
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
