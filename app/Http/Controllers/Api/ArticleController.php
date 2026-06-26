<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Articles\ArticleListResource;
use App\Http\Resources\Articles\ArticleResource;
use App\Models\Article;
use App\Models\ArticleView;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Str;
use OpenApi\Attributes as OA;

class ArticleController extends Controller
{
    #[OA\Get(
        path: '/api/articles',
        tags: ['Articles'],
        summary: 'Get list of published articles',
        description: 'Returns paginated list of published articles with latest first',
        parameters: [
            new OA\Parameter(
                name: 'page',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Success',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                ref: '#/components/schemas/ArticleListResource'
                            )
                        ),
                        new OA\Property(
                            property: 'links',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'first', type: 'string'),
                                new OA\Property(property: 'last', type: 'string'),
                                new OA\Property(property: 'prev', type: 'string', nullable: true),
                                new OA\Property(property: 'next', type: 'string', nullable: true),
                            ]
                        ),
                        new OA\Property(
                            property: 'meta',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'current_page', type: 'integer'),
                                new OA\Property(property: 'from', type: 'integer'),
                                new OA\Property(property: 'last_page', type: 'integer'),
                                new OA\Property(property: 'path', type: 'string'),
                                new OA\Property(property: 'per_page', type: 'integer'),
                                new OA\Property(property: 'to', type: 'integer'),
                                new OA\Property(property: 'total', type: 'integer'),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Not found'),
        ]
    )]
    public function index(): AnonymousResourceCollection
    {
        $articles = Article::query()
            ->published()
            ->publishedLatest()
            ->paginate(15);

        return ArticleListResource::collection($articles);
    }

    #[OA\Get(
        path: '/api/articles/{article}',
        tags: ['Articles'],
        summary: 'Get single article',
        description: 'Returns detailed article data with view tracking. The view is tracked uniquely by user or visitor ID.',
        parameters: [
            new OA\Parameter(
                name: 'article',
                in: 'path',
                required: true,
                description: 'Article Slug',
                schema: new OA\Schema(type: 'string', example: 'first-article')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Success',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/ArticleResource'
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Not found',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Article not found'),
                    ]
                )
            ),
        ]
    )]
    public function show(Article $article): ArticleResource
    {
        $userId = auth()->id();
        $visitorId = request()->cookie('visitor_id');

        // Create visitor id if not exists
        if (!$visitorId) {
            $visitorId = (string) Str::uuid();

            cookie()->queue(cookie(
                'visitor_id',
                $visitorId,
                60 * 24 * 365 // 1 year
            ));
        }

        // 1. ALWAYS log the view
        ArticleView::create([
            'article_id' => $article->id,
            'user_id' => $userId,
            'visitor_id' => $userId ? null : $visitorId,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        // 2. ONLY increase if it's first time (unique logic)
        $exists = ArticleView::where('article_id', $article->id)
            ->when($userId, fn($q) => $q->where('user_id', $userId))
            ->when(!$userId, fn($q) => $q->where('visitor_id', $visitorId))
            ->exists();

        if (!$exists) {
            $article->incrementViews();
        }

        return new ArticleResource($article);
    }
}