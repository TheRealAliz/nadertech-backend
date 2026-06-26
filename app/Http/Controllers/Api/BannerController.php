<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Banner\BannerResource;
use App\Models\Banner;
use OpenApi\Attributes as OA;

class BannerController extends Controller
{
    #[OA\Get(
        path: '/api/banners',
        tags: ['Banners'],
        summary: 'Get list of active banners',
        description: 'Returns list of all active banners ordered by sort_order',
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
                                ref: '#/components/schemas/BannerResource'
                            )
                        )
                    ]
                )
            ),
        ]
    )]
    public function index()
    {
        $banners = Banner::query()
            ->active()
            ->ordered()
            ->get();

        return BannerResource::collection($banners);
    }
}