<?php

namespace App\Http\Controllers\Api;

use App\Enums\ProjectRequestStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProjectRequest\StoreProjectRequest;
use App\Http\Resources\ProjectRequest\ProjectRequestTypeResource;
use App\Models\ProjectRequest;
use App\Models\ProjectRequestType;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class ProjectRequestController extends Controller
{
    #[OA\Post(
        path: '/api/requests',
        tags: ['Project Requests'],
        summary: 'Submit a new project request',
        description: 'Creates a new project request. User ID is optional (if authenticated, it will be linked automatically).',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['service_id', 'name', 'mobile'],
                properties: [
                    new OA\Property(
                        property: 'type_id',
                        type: 'integer',
                        example: 1,
                        description: 'ID of the requested project type (must exist in project_request_types table)'
                    ),
                    new OA\Property(
                        property: 'name',
                        type: 'string',
                        maxLength: 100,
                        example: 'علی احمدی',
                        description: 'Full name of the requester (max 100 characters)'
                    ),
                    new OA\Property(
                        property: 'mobile',
                        type: 'string',
                        maxLength: 13,
                        example: '09123456789',
                        description: 'Mobile number (max 13 characters)'
                    ),
                    new OA\Property(
                        property: 'email',
                        type: 'string',
                        format: 'email',
                        maxLength: 255,
                        nullable: true,
                        example: 'ali@example.com',
                        description: 'Email address (optional)'
                    ),
                    new OA\Property(
                        property: 'description',
                        type: 'string',
                        nullable: true,
                        example: 'من نیاز به یک وبسایت فروشگاهی دارم...',
                        description: 'Project description and requirements (optional)'
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Project request created successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'درخواست با موفقیت ثبت شد.'
                        ),
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(
                                    property: 'project_request',
                                    properties: [
                                        new OA\Property(property: 'id', type: 'integer', example: 1),
                                        new OA\Property(property: 'type_id', type: 'integer', example: 1),
                                        new OA\Property(property: 'user_id', type: 'integer', nullable: true, example: null),
                                        new OA\Property(property: 'name', type: 'string', example: 'علی احمدی'),
                                        new OA\Property(property: 'mobile', type: 'string', example: '09123456789'),
                                        new OA\Property(property: 'email', type: 'string', nullable: true, example: 'ali@example.com'),
                                        new OA\Property(property: 'description', type: 'string', nullable: true, example: 'من نیاز به یک وبسایت فروشگاهی دارم...'),
                                        new OA\Property(property: 'status', type: 'string', example: 'pending'),
                                        new OA\Property(property: 'created_at', type: 'string', format: 'datetime', example: '2026-06-20T10:30:00.000000Z'),
                                        new OA\Property(property: 'updated_at', type: 'string', format: 'datetime', example: '2026-06-20T10:30:00.000000Z'),
                                    ],
                                    type: 'object'
                                )
                            ],
                            type: 'object'
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'The given data was invalid.'),
                        new OA\Property(
                            property: 'errors',
                            type: 'object',
                            example: [
                                'service_id' => ['انتخاب نوع درخواست الزامی است.'],
                                'name' => ['نام و نام خانوادگی کاربر الزامی می‌باشد.'],
                                'mobile' => ['شماره تماس الزامی می‌باشد.'],
                                'email' => ['فرمت ایمیل صحیح نمی‌باشد.']
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthenticated (optional)',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Unauthenticated.')
                    ]
                )
            ),
        ]
    )]
    public function store(StoreProjectRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $projectRequest = ProjectRequest::create([
            'project_request_type_id' => $validated['type_id'],
            'user_id' => $request->user()?->id ?? null,
            'name' => $validated['name'],
            'mobile' => $validated['mobile'],
            'email' => $validated['email'] ?? null,
            'description' => $validated['description'] ?? null,
            'status' => ProjectRequestStatus::Pending->value,
        ]);

        return response()->json([
            'message' => 'درخواست با موفقیت ثبت شد.',
            'data' => [
                'project_request' => $projectRequest
            ]
        ], 201);
    }

    #[OA\Get(
        path: '/api/requests/types',
        tags: ['Project Requests'],
        summary: 'Get project request types',
        description: 'Retrieve all available project request types',
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful response',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/ProjectRequestTypeResource')
                )
            )
        ]
    )]
    public function getTypes()
    {
        $types = ProjectRequestType::all();

        return ProjectRequestTypeResource::collection($types);
    }
}