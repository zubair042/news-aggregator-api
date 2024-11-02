<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ArticleController extends Controller
{

    /**
     * @OA\Get(
     *     path="/api/articles",
     *     summary="Retrieve a list of articles",
     *     description="Retrieve a list of articles with optional filters like keyword, category, source, and date. Requires Bearer token authentication.",
     *     operationId="getArticles",
     *     tags={"Articles"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="keyword",
     *         in="query",
     *         description="Filter articles by keyword in title or content",
     *         required=false,
     *         @OA\Schema(type="string", example="technology")
     *     ),
     *     @OA\Parameter(
     *         name="category",
     *         in="query",
     *         description="Filter articles by category",
     *         required=false,
     *         @OA\Schema(type="string", example="news")
     *     ),
     *     @OA\Parameter(
     *         name="source",
     *         in="query",
     *         description="Filter articles by source",
     *         required=false,
     *         @OA\Schema(type="string", example="bbc")
     *     ),
     *     @OA\Parameter(
     *         name="date",
     *         in="query",
     *         description="Filter articles by publication date",
     *         required=false,
     *         @OA\Schema(type="string", format="date", example="2024-11-01")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Articles retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Articles retrieved successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(ref="#/components/schemas/Article")
     *                 ),
     *                 @OA\Property(property="total", type="integer", example=50),
     *                 @OA\Property(property="per_page", type="integer", example=10)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to retrieve articles",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Failed to retrieve articles"),
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="error", type="string", example="Internal server error")
     *             )
     *         )
     *     )
     * )
     */
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

    /**
     * @OA\Get(
     *     path="/api/articles/{id}",
     *     summary="Retrieve a specific article",
     *     description="Retrieve a specific article by ID. Requires Bearer token authentication.",
     *     operationId="getArticleById",
     *     tags={"Articles"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the article to retrieve",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Article retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Article retrieved successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Article")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Article not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Article not found"),
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="error", type="string", example="Article with the specified ID does not exist.")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to retrieve the article",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Failed to retrieve the article"),
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="error", type="string", example="Internal server error")
     *             )
     *         )
     *     )
     * )
     */
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
