<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Faq\StoreFaqRequest;
use App\Http\Requests\Admin\Faq\UpdateFaqRequest;
use App\Http\Resources\Admin\Faq\FaqResource;
use App\Models\Faq;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;

class FaqController extends Controller
{
    #[OA\Post(
        path: '/api/admin/faqs',
        tags: ['Admin - FAQs'],
        summary: 'Create FAQ',
        description: 'Create a new FAQ entry',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['question', 'answer'],
                properties: [
                    new OA\Property(property: 'question', type: 'string', maxLength: 255, example: 'چگونه می‌توانم ثبت‌نام کنم؟'),
                    new OA\Property(property: 'answer', type: 'string', example: 'برای ثبت‌نام، لطفاً به صفحه ثبت‌نام مراجعه کنید و فرم را تکمیل نمایید.'),
                    new OA\Property(property: 'sort_order', type: 'integer', nullable: true, example: 1),
                    new OA\Property(property: 'is_active', type: 'boolean', example: true),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'FAQ created successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'FAQ created successfully'),
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/AdminFaqResource'
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'The question field is required.'),
                        new OA\Property(
                            property: 'errors',
                            type: 'object'
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthenticated',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Unauthenticated.'),
                    ]
                )
            ),
            new OA\Response(
                response: 403,
                description: 'Forbidden',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Forbidden.'),
                    ]
                )
            ),
        ]
    )]
    public function store(StoreFaqRequest $request): JsonResponse
    {
        $data = $request->validated();

        $faq = Faq::create($data);

        return response()->json([
            'message' => 'FAQ created successfully',
            'data' => new FaqResource($faq),
        ], 201);
    }

    #[OA\Get(
        path: '/api/admin/faqs',
        tags: ['Admin - FAQs'],
        summary: 'List FAQs',
        description: 'Get all FAQs ordered by latest',
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'FAQs list',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/AdminFaqResource')
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthenticated',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Unauthenticated.'),
                    ]
                )
            ),
            new OA\Response(
                response: 403,
                description: 'Forbidden',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Forbidden.'),
                    ]
                )
            ),
        ]
    )]
    public function index(): AnonymousResourceCollection
    {
        $faqs = Faq::query()
            ->latest()
            ->get();

        return FaqResource::collection($faqs);
    }

    #[OA\Get(
        path: '/api/admin/faqs/{faq}',
        tags: ['Admin - FAQs'],
        summary: 'Get FAQ details',
        description: 'Get detailed information about a specific FAQ',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'faq',
                in: 'path',
                required: true,
                description: 'FAQ ID',
                schema: new OA\Schema(type: 'integer', example: 1)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'FAQ details',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/AdminFaqResource'
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'FAQ not found',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'FAQ not found.'),
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthenticated',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Unauthenticated.'),
                    ]
                )
            ),
            new OA\Response(
                response: 403,
                description: 'Forbidden',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Forbidden.'),
                    ]
                )
            ),
        ]
    )]
    public function show(Faq $faq): FaqResource
    {
        return new FaqResource($faq);
    }

    #[OA\Put(
        path: '/api/admin/faqs/{faq}',
        tags: ['Admin - FAQs'],
        summary: 'Update FAQ',
        description: 'Update an existing FAQ entry',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'faq',
                in: 'path',
                required: true,
                description: 'FAQ ID',
                schema: new OA\Schema(type: 'integer', example: 1)
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'question', type: 'string', maxLength: 255, example: 'سوال به‌روز شده'),
                    new OA\Property(property: 'answer', type: 'string', example: 'پاسخ به‌روز شده برای سوال'),
                    new OA\Property(property: 'sort_order', type: 'integer', nullable: true, example: 2),
                    new OA\Property(property: 'is_active', type: 'boolean', example: false),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'FAQ updated successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'FAQ updated successfully'),
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/AdminFaqResource'
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'The question field is required.'),
                        new OA\Property(
                            property: 'errors',
                            type: 'object'
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'FAQ not found',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'FAQ not found.'),
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthenticated',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Unauthenticated.'),
                    ]
                )
            ),
            new OA\Response(
                response: 403,
                description: 'Forbidden',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Forbidden.'),
                    ]
                )
            ),
        ]
    )]
    public function update(UpdateFaqRequest $request, Faq $faq): JsonResponse
    {
        $data = $request->validated();

        $faq->update($data);

        return response()->json([
            'message' => 'FAQ updated successfully',
            'data' => new FaqResource($faq),
        ]);
    }

    #[OA\Delete(
        path: '/api/admin/faqs/{faq}',
        tags: ['Admin - FAQs'],
        summary: 'Delete FAQ',
        description: 'Delete a specific FAQ entry',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'faq',
                in: 'path',
                required: true,
                description: 'FAQ ID',
                schema: new OA\Schema(type: 'integer', example: 1)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'FAQ deleted successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'FAQ deleted successfully'),
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'FAQ not found',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'FAQ not found.'),
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthenticated',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Unauthenticated.'),
                    ]
                )
            ),
            new OA\Response(
                response: 403,
                description: 'Forbidden',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Forbidden.'),
                    ]
                )
            ),
        ]
    )]
    public function destroy(Faq $faq): JsonResponse
    {
        $faq->delete();

        return response()->json([
            'message' => 'FAQ deleted successfully',
        ]);
    }
}