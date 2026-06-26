<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\ArticleStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Articles\StoreArticleRequest;
use App\Http\Requests\Admin\Articles\UpdateArticleRequest;
use App\Http\Requests\Admin\Articles\UpdateArticleStatusRequest;
use App\Http\Requests\Admin\Articles\UpdateArticleThumbnailRequest;
use App\Http\Resources\Admin\Articles\ArticleResource;
use App\Models\Article;
use App\Services\ImageUploadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Str;
use OpenApi\Attributes as OA;

class ArticleController extends Controller
{
    public function __construct(
        private ImageUploadService $imageUploadService
    ) {
    }

    #[OA\Post(
        path: '/api/admin/articles',
        tags: ['Admin - Articles'],
        summary: 'Create article',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['title', 'content', 'thumbnail'],
                    properties: [
                        new OA\Property(property: 'title', type: 'string', example: 'My Article'),
                        new OA\Property(property: 'slug', type: 'string', nullable: true, example: 'my-article'),
                        new OA\Property(property: 'content', type: 'string', example: '<p>Article content here...</p>'),
                        new OA\Property(property: 'thumbnail', type: 'string', format: 'binary', description: 'Thumbnail image file (max: 2MB, allowed: jpg,jpeg,png,webp)'),
                        new OA\Property(property: 'thumbnail_alt', type: 'string', nullable: true, example: 'Article thumbnail description'),
                        new OA\Property(property: 'meta_title', type: 'string', nullable: true, example: 'SEO Title'),
                        new OA\Property(property: 'meta_description', type: 'string', nullable: true, example: 'SEO Description for the article'),
                        new OA\Property(property: 'status', type: 'string', enum: ['draft', 'published', 'archived'], example: 'draft'),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Created',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Article created successfully'),
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/AdminArticleResource'
                        )
                    ]
                )
            ),
            new OA\Response(response: 422, description: 'Validation error'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    public function store(StoreArticleRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Generate slug from title if not provided
        $data['slug'] ??= Str::slug($data['title']);

        $this->handlePublishedAt($data);

        $data['thumbnail'] = $this->imageUploadService->upload(
            $request->file('thumbnail'),
            'images/articles'
        );

        $article = Article::create($data);

        return response()->json([
            'message' => 'Article created successfully',
            'data' => new ArticleResource($article),
        ], 201);
    }

    #[OA\Get(
        path: '/api/admin/articles',
        tags: ['Admin - Articles'],
        summary: 'Get list of articles',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'page',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
            new OA\Parameter(
                name: 'per_page',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer', example: 10, minimum: 1, maximum: 100)
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
                                ref: '#/components/schemas/AdminArticleResource'
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
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = min($request->integer('per_page', 10), 100);

        $articles = Article::query()
            ->latest()
            ->paginate($perPage);

        return ArticleResource::collection($articles);
    }

    #[OA\Get(
        path: '/api/admin/articles/archived',
        tags: ['Admin - Articles'],
        summary: 'Get list of archived articles',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'page',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
            new OA\Parameter(
                name: 'per_page',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer', example: 10, minimum: 1, maximum: 100)
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
                                ref: '#/components/schemas/AdminArticleResource'
                            )
                        ),
                        new OA\Property(
                            property: 'links',
                            type: 'object'
                        ),
                        new OA\Property(
                            property: 'meta',
                            type: 'object'
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    public function archived(Request $request): AnonymousResourceCollection
    {
        $perPage = min($request->integer('per_page', 10), 100);

        $articles = Article::query()
            ->archived()
            ->latest()
            ->paginate($perPage);

        return ArticleResource::collection($articles);
    }

    #[OA\Get(
        path: '/api/admin/articles/{article}',
        tags: ['Admin - Articles'],
        summary: 'Get single article',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'article',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
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
                            ref: '#/components/schemas/AdminArticleResource'
                        )
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Not found'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    public function show(Article $article): ArticleResource
    {
        return new ArticleResource($article);
    }

    #[OA\Put(
        path: '/api/admin/articles/{article}',
        tags: ['Admin - Articles'],
        summary: 'Update article',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'article',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
            new OA\Parameter(
                name: '_method',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string', example: 'PUT')
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'title', type: 'string', example: 'Updated Article Title'),
                        new OA\Property(property: 'slug', type: 'string', nullable: true, example: 'updated-article-slug'),
                        new OA\Property(property: 'content', type: 'string', example: '<p>Updated article content...</p>'),
                        new OA\Property(property: 'thumbnail', type: 'string', format: 'binary', nullable: true, description: 'Thumbnail image file (max: 2MB)'),
                        new OA\Property(property: 'thumbnail_alt', type: 'string', nullable: true, example: 'Updated thumbnail description'),
                        new OA\Property(property: 'meta_title', type: 'string', nullable: true, example: 'Updated SEO Title'),
                        new OA\Property(property: 'meta_description', type: 'string', nullable: true, example: 'Updated SEO Description'),
                        new OA\Property(property: 'status', type: 'string', enum: ['draft', 'published', 'archived'], example: 'published'),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Updated',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Article updated successfully'),
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/AdminArticleResource'
                        )
                    ]
                )
            ),
            new OA\Response(response: 422, description: 'Validation error'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Not found'),
        ]
    )]
    public function update(UpdateArticleRequest $request, Article $article): JsonResponse
    {
        $data = $request->validated();

        // Generate slug from title if not provided
        $data['slug'] ??= Str::slug($data['title']);

        $this->handlePublishedAt($data, $article);

        if ($request->hasFile('thumbnail')) {
            $data['thumbnail'] = $this->imageUploadService->replace(
                $article->thumbnail,
                $request->file('thumbnail'),
                'images/articles'
            );
        }

        $article->update($data);

        return response()->json([
            'message' => 'Article updated successfully',
            'data' => new ArticleResource($article->fresh()),
        ]);
    }

    #[OA\Put(
        path: '/api/admin/articles/{article}/thumbnail',
        tags: ['Admin - Articles'],
        summary: 'Update article thumbnail',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'article',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
            new OA\Parameter(
                name: '_method',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string', example: 'PUT')
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['thumbnail'],
                    properties: [
                        new OA\Property(property: 'thumbnail', type: 'string', format: 'binary', description: 'Thumbnail image file (max: 2MB, allowed: jpg,jpeg,png,webp)'),
                        new OA\Property(property: 'thumbnail_alt', type: 'string', nullable: true, example: 'New thumbnail description'),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Updated',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Thumbnail updated successfully'),
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/AdminArticleResource'
                        )
                    ]
                )
            ),
            new OA\Response(response: 422, description: 'Validation error'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Not found'),
        ]
    )]
    public function updateThumbnail(UpdateArticleThumbnailRequest $request, Article $article): JsonResponse
    {
        $data = $request->validated();

        $data['thumbnail'] = $this->imageUploadService->replace(
            $article->thumbnail,
            $request->file('thumbnail'),
            'images/articles'
        );

        $article->update($data);

        return response()->json([
            'message' => 'Thumbnail updated successfully',
            'data' => new ArticleResource($article->fresh()),
        ]);
    }

    #[OA\Put(
        path: '/api/admin/articles/{article}/status',
        tags: ['Admin - Articles'],
        summary: 'Update article status',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'article',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['status'],
                properties: [
                    new OA\Property(property: 'status', type: 'string', enum: ['draft', 'published', 'archived'], example: 'published')
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Updated',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Status updated successfully'),
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/AdminArticleResource'
                        )
                    ]
                )
            ),
            new OA\Response(response: 422, description: 'Validation error'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Not found'),
        ]
    )]
    public function updateStatus(UpdateArticleStatusRequest $request, Article $article): JsonResponse
    {
        $data = $request->validated();

        $this->handlePublishedAt($data, $article);

        $article->update($data);

        return response()->json([
            'message' => 'Status updated successfully',
            'data' => new ArticleResource($article->refresh()),
        ]);
    }

    #[OA\Delete(
        path: '/api/admin/articles/{article}',
        tags: ['Admin - Articles'],
        summary: 'Delete article',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'article',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Deleted',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Article deleted successfully'),
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Not found'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    public function destroy(Article $article): JsonResponse
    {
        if ($article->thumbnail) {
            $this->imageUploadService->delete($article->thumbnail);
        }

        $article->delete();

        return response()->json([
            'message' => 'Article deleted successfully',
        ]);
    }

    private function handlePublishedAt(array &$data, ?Article $article = null): void
    {
        if (($data['status'] ?? null) === ArticleStatus::PUBLISHED->value) {
            if (!$article || !$article->published_at) {
                $data['published_at'] = now();
            }
        }
    }
}