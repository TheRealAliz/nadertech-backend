<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Articles\ArticleListResource;
use App\Http\Resources\Articles\ArticleResource;
use App\Models\Article;
use App\Models\ArticleView;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Str;

class ArticleController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $articles = Article::query()
            ->published()
            ->publishedLatest()
            ->paginate(15);

        return ArticleListResource::collection($articles);
    }

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
                60 * 24 * 365
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
