<?php

namespace App\Http;

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="News Aggregator API",
 *     description="API documentation for the News Aggregator application",
 *     @OA\Contact(
 *         email="support@example.com"
 *     ),
 *     @OA\License(
 *         name="Apache 2.0",
 *         url="http://www.apache.org/licenses/LICENSE-2.0.html"
 *     )
 * )
 *
 * @OA\Server(
 *     url="http://localhost:8000",
 *     description="Local server"
 * )
 *
 * @OA\SecurityScheme(
 *      securityScheme="bearerAuth",
 *      type="http",
 *      scheme="bearer",
 *      bearerFormat="JWT",
 *      description="Use the token from the login endpoint"
 *  )
 *
 * @OA\Schema(
 *      schema="User",
 *      type="object",
 *      @OA\Property(property="id", type="integer", example=1),
 *      @OA\Property(property="name", type="string", example="John Doe"),
 *      @OA\Property(property="email", type="string", format="email", example="johndoe@example.com"),
 *      @OA\Property(property="created_at", type="string", format="date-time", example="2024-11-01T12:34:56Z"),
 *      @OA\Property(property="updated_at", type="string", format="date-time", example="2024-11-01T12:34:56Z")
 *  )
 *
 * @OA\Schema(
 *      schema="Article",
 *      type="object",
 *      @OA\Property(property="id", type="integer", example=1),
 *      @OA\Property(property="title", type="string", example="Breaking News"),
 *      @OA\Property(property="content", type="string", example="Detailed article content."),
 *      @OA\Property(property="category", type="string", example="news"),
 *      @OA\Property(property="source", type="string", example="bbc"),
 *      @OA\Property(property="published_at", type="string", format="date-time", example="2024-11-01T12:34:56Z")
 *  )
 *
 * @OA\Schema(
 *      schema="Preferences",
 *      type="object",
 *      @OA\Property(property="user_id", type="integer", example=1),
 *      @OA\Property(property="preferred_sources", type="array",
 *          @OA\Items(type="string"),
 *          example={"bbc", "cnn", "reuters"}
 *      ),
 *      @OA\Property(
 *          property="preferred_categories", type="array",
 *          @OA\Items(type="string"),
 *          example={"news", "sports", "technology"}
 *      ),
 *      @OA\Property(
 *          property="preferred_authors", type="array",
 *          @OA\Items(type="string"),
 *          example={"author1", "author2", "author3"}
 *      )
 *  )
 */
class SwaggerConfig
{
    // This file contains only Swagger annotations.
}
