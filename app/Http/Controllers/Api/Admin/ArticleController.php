<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\ArticleStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Articles\StoreArticleRequest;
use App\Http\Requests\Admin\Articles\UpdateArticleRequest;
use App\Http\Requests\Admin\Articles\UpdateArticleStatusRequest;
use App\Http\Requests\Admin\Articles\UpdateArticleThumbnailRequest;
use App\Http\Resources\Admin\ArticleResource;
use App\Models\Article;
use App\Services\ImageUploadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Str;

class ArticleController extends Controller
{
    public function __construct(
        private ImageUploadService $imageUploadService
    ) {
    }

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

    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = min($request->integer('per_page', 10), 100);

        $articles = Article::query()
            ->latest()
            ->paginate($perPage);

        return ArticleResource::collection($articles);
    }

    public function archived(Request $request): AnonymousResourceCollection
    {
        $perPage = min($request->integer('per_page', 10), 100);

        $articles = Article::query()
            ->archived()
            ->latest()
            ->paginate($perPage);

        return ArticleResource::collection($articles);
    }

    public function show(Article $article): ArticleResource
    {
        return new ArticleResource($article);
    }

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
