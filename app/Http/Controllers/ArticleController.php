<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ArticleController extends Controller
{

    public function index(Request $request)
    {
        try {
            $articles = Article::query();

            if ($request->has('keyword')) {
                $articles->where('title', 'like', '%' . $request->keyword . '%')
                    ->orWhere('content', 'like', '%' . $request->keyword . '%');
            }
            if ($request->has('category')) {
                $articles->where('category', $request->category);
            }
            if ($request->has('source')) {
                $articles->where('source', $request->source);
            }
            if ($request->has('date')) {
                $articles->whereDate('published_at', $request->date);
            }

            $paginatedArticles = $articles->paginate(10);

            return ResponseHelper::apiResponse(
                true,
                'Articles retrieved successfully',
                $paginatedArticles
            );
        } catch (\Exception $e) {
            return ResponseHelper::apiResponse(
                false,
                'Failed to retrieve articles',
                null,
                500,
                ['error' => $e->getMessage()]
            );
        }
    }

    public function show($id)
    {
        try {
            $article = Article::findOrFail($id);

            return ResponseHelper::apiResponse(
                true,
                'Article retrieved successfully',
                $article
            );
        } catch (ModelNotFoundException $e) {
            return ResponseHelper::apiResponse(
                false,
                'Article not found',
                null,
                404,
                ['error' => 'Article with the specified ID does not exist.']
            );
        } catch (\Exception $e) {
            return ResponseHelper::apiResponse(
                false,
                'Failed to retrieve the article',
                null,
                500,
                ['error' => $e->getMessage()]
            );
        }
    }
}
