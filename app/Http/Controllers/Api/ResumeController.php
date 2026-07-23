<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Resume;
use App\Http\Resources\Resume\ResumeListResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use App\Http\Resources\Resume\ResumeResource;
use OpenApi\Attributes as OA;

class ResumeController extends Controller
{
    #[OA\Get(
        path: '/api/resumes',
        tags: ['Resumes'],
        summary: 'Get list of resumes',
        description: 'Returns paginated list of all resumes with their first image',
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
                schema: new OA\Schema(type: 'integer', example: 6, minimum: 1, maximum: 50)
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
                                ref: '#/components/schemas/PublicResumeListResource'
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
            new OA\Response(
                response: 404,
                description: 'Not found'
            ),
        ]
    )]
    public function index(Request $request): AnonymousResourceCollection
    {
        $resumes = Resume::query()
            ->with('firstImage')
            ->latest()
            ->paginate(6);

        return ResumeListResource::collection($resumes);
    }

    #[OA\Get(
        path: '/api/resumes/{resume}',
        tags: ['Resumes'],
        summary: 'Get single resume',
        description: 'Returns detailed information about a specific resume with its review, images, and category',
        parameters: [
            new OA\Parameter(
                name: 'resume',
                in: 'path',
                required: true,
                description: 'Resume ID',
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
                            ref: '#/components/schemas/PublicResumeResource'
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Resume not found',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Resume not found'),
                    ]
                )
            ),
        ]
    )]
    public function show(Resume $resume): ResumeResource
    {
        return new ResumeResource($resume->load(['review', 'images', 'category']));
    }
}