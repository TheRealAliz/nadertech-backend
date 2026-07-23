<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\ProjectRequest\ProjectRequestResource;
use App\Http\Resources\ProjectRequest\ProjectRequestTypeResource;
use App\Models\ProjectRequest;
use App\Models\ProjectRequestType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;

class ProjectRequestController extends Controller
{
    #[OA\Get(
        path: '/api/admin/requests',
        tags: ['Admin - Project Requests'],
        summary: 'Get list of project requests',
        description: 'Returns paginated list of all project requests with their related services',
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
                                ref: '#/components/schemas/ProjectRequestResource'
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

        $projectResources = ProjectRequest::query()
            ->with('requestType')
            ->latestFirst()
            ->paginate($perPage);

        return ProjectRequestResource::collection($projectResources);
    }

    #[OA\Get(
        path: '/api/admin/requests/{projectRequest}',
        tags: ['Admin - Project Requests'],
        summary: 'Get single project request',
        description: 'Returns detailed information about a specific project request with its related service',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'projectRequest',
                in: 'path',
                required: true,
                description: 'Project Request ID',
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
                            ref: '#/components/schemas/ProjectRequestResource'
                        )
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Not found'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    public function show(ProjectRequest $projectRequest): ProjectRequestResource
    {
        return new ProjectRequestResource($projectRequest);
    }

    #[OA\Get(
        path: '/api/admin/requests/types',
        tags: ['Admin - Project Requests'],
        summary: 'Get project request types',
        description: 'Returns a list of available project request types',
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Success',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/ProjectRequestTypeResource')
                        )
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    public function getTypes()
    {
        $types = ProjectRequestType::all();

        return ProjectRequestTypeResource::collection($types);
    }
}