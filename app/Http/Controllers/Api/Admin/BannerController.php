<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Banner\ReorderBannerRequest;
use App\Http\Requests\Admin\Banner\StoreBannerRequest;
use App\Http\Requests\Admin\Banner\UpdateBannerImageRequest;
use App\Http\Requests\Admin\Banner\UpdateBannerRequest;
use App\Http\Requests\Admin\Banner\UpdateBannerStatusRequest;
use App\Http\Resources\Admin\Banner\BannerResource;
use App\Models\Banner;
use App\Services\ImageUploadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use OpenApi\Attributes as OA;

class BannerController extends Controller
{
    public function __construct(
        private ImageUploadService $imageUploadService
    ) {
    }

    #[OA\Get(
        path: '/api/admin/banners',
        tags: ['Admin - Banners'],
        summary: 'Get list of banners',
        description: 'Returns a paginated list of all banners ordered by sort_order and latest. **Admin access required.**',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'page',
                description: 'Page number',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'integer',
                    default: 1,
                    example: 1
                )
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of banners retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 1),
                                    new OA\Property(property: 'title', type: 'string', nullable: true, example: 'بنر اصلی'),
                                    new OA\Property(property: 'image', type: 'string', example: 'http://localhost/storage/banners/banner-1.jpg'),
                                    new OA\Property(property: 'alt', type: 'string', nullable: true, example: 'توضیح تصویر'),
                                    new OA\Property(property: 'link', type: 'string', nullable: true, example: 'https://example.com'),
                                    new OA\Property(property: 'sort_order', type: 'integer', example: 1),
                                    new OA\Property(property: 'is_active', type: 'boolean', example: true),
                                    new OA\Property(property: 'created_at', type: 'string', format: 'datetime', example: '2026-06-20T10:30:00.000000Z'),
                                    new OA\Property(property: 'updated_at', type: 'string', format: 'datetime', example: '2026-06-20T10:30:00.000000Z'),
                                ],
                                type: 'object'
                            )
                        ),
                        new OA\Property(
                            property: 'links',
                            properties: [
                                new OA\Property(property: 'first', type: 'string', example: 'http://localhost/api/admin/banners?page=1'),
                                new OA\Property(property: 'last', type: 'string', example: 'http://localhost/api/admin/banners?page=5'),
                                new OA\Property(property: 'prev', type: 'string', nullable: true, example: null),
                                new OA\Property(property: 'next', type: 'string', nullable: true, example: 'http://localhost/api/admin/banners?page=2'),
                            ],
                            type: 'object'
                        ),
                        new OA\Property(
                            property: 'meta',
                            properties: [
                                new OA\Property(property: 'current_page', type: 'integer', example: 1),
                                new OA\Property(property: 'from', type: 'integer', example: 1),
                                new OA\Property(property: 'last_page', type: 'integer', example: 5),
                                new OA\Property(property: 'per_page', type: 'integer', example: 10),
                                new OA\Property(property: 'to', type: 'integer', example: 10),
                                new OA\Property(property: 'total', type: 'integer', example: 50),
                            ],
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
                        new OA\Property(property: 'message', type: 'string', example: 'Unauthenticated.')
                    ]
                )
            ),
            new OA\Response(
                response: 403,
                description: 'Forbidden - Admin access required',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'This action is unauthorized.')
                    ]
                )
            )
        ]
    )]
    public function index(): AnonymousResourceCollection
    {
        $banners = Banner::query()
            ->orderBy('sort_order')
            ->latest()
            ->paginate(10);

        return BannerResource::collection($banners);
    }

    #[OA\Post(
        path: '/api/admin/banners',
        tags: ['Admin - Banners'],
        summary: 'Create a new banner',
        description: 'Creates a new banner with image upload. **Admin access required.**',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['image'],
                    properties: [
                        new OA\Property(
                            property: 'title',
                            type: 'string',
                            maxLength: 255,
                            nullable: true,
                            example: 'بنر اصلی',
                            description: 'Banner title (optional)'
                        ),
                        new OA\Property(
                            property: 'image',
                            type: 'string',
                            format: 'binary',
                            description: 'Banner image file (max 2MB, required)'
                        ),
                        new OA\Property(
                            property: 'alt',
                            type: 'string',
                            maxLength: 255,
                            nullable: true,
                            example: 'توضیح تصویر بنر',
                            description: 'Alternative text for image (optional)'
                        ),
                        new OA\Property(
                            property: 'link',
                            type: 'string',
                            maxLength: 255,
                            nullable: true,
                            example: 'https://example.com',
                            description: 'Banner link URL (optional)'
                        ),
                        new OA\Property(
                            property: 'sort_order',
                            type: 'integer',
                            minimum: 0,
                            nullable: true,
                            example: 1,
                            description: 'Display order (optional, default: 0)'
                        ),
                        new OA\Property(
                            property: 'is_active',
                            type: 'boolean',
                            nullable: true,
                            example: true,
                            description: 'Banner status (optional, default: false)'
                        ),
                    ],
                    type: 'object'
                )
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Banner created successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'بنر با موفقیت افزوده شد.'
                        ),
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/AdminBannerResource'
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
                                'image' => ['تصویر بنر الزامی است.'],
                                'image.max' => ['حجم تصویر نباید بیشتر از ۲ مگابایت باشد.']
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthenticated',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Unauthenticated.')
                    ]
                )
            ),
            new OA\Response(
                response: 403,
                description: 'Forbidden - Admin access required',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'This action is unauthorized.')
                    ]
                )
            )
        ]
    )]
    public function store(StoreBannerRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $validated['image'] = $this->imageUploadService->upload(
            $request->file('image'),
            'images/banners'
        );

        $banner = Banner::create($validated);

        return response()->json([
            'message' => 'بنر با موفقیت افزوده شد.',
            'data' => new BannerResource($banner),
        ], 201);
    }

    #[OA\Get(
        path: '/api/admin/banners/{banner}',
        tags: ['Admin - Banners'],
        summary: 'Get a single banner',
        description: 'Returns detailed information about a specific banner. **Admin access required.**',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'banner',
                description: 'Banner ID',
                in: 'path',
                required: true,
                schema: new OA\Schema(
                    type: 'integer',
                    example: 1
                )
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Banner retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/AdminBannerResource'
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Banner not found',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'No query results for model [App\\Models\\Banner] 1')
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthenticated',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Unauthenticated.')
                    ]
                )
            ),
            new OA\Response(
                response: 403,
                description: 'Forbidden - Admin access required',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'This action is unauthorized.')
                    ]
                )
            )
        ]
    )]
    public function show(Banner $banner): BannerResource
    {
        return new BannerResource($banner);
    }

    #[OA\Put(
        path: '/api/admin/banners/{banner}',
        tags: ['Admin - Banners'],
        summary: 'Update a banner',
        description: 'Updates an existing banner. Image is optional. **Admin access required.**',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'banner',
                description: 'Banner ID',
                in: 'path',
                required: true,
                schema: new OA\Schema(
                    type: 'integer',
                    example: 1
                )
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(
                            property: 'title',
                            type: 'string',
                            maxLength: 255,
                            nullable: true,
                            example: 'بنر اصلی ویرایش شده',
                            description: 'Banner title (optional)'
                        ),
                        new OA\Property(
                            property: 'image',
                            type: 'string',
                            format: 'binary',
                            description: 'Banner image file (max 2MB, optional)'
                        ),
                        new OA\Property(
                            property: 'alt',
                            type: 'string',
                            maxLength: 255,
                            nullable: true,
                            example: 'توضیح جدید تصویر بنر',
                            description: 'Alternative text for image (optional)'
                        ),
                        new OA\Property(
                            property: 'link',
                            type: 'string',
                            maxLength: 255,
                            nullable: true,
                            example: 'https://newexample.com',
                            description: 'Banner link URL (optional)'
                        ),
                        new OA\Property(
                            property: 'sort_order',
                            type: 'integer',
                            minimum: 0,
                            nullable: true,
                            example: 2,
                            description: 'Display order (optional)'
                        ),
                        new OA\Property(
                            property: 'is_active',
                            type: 'boolean',
                            nullable: true,
                            example: false,
                            description: 'Banner status (optional)'
                        ),
                    ],
                    type: 'object'
                )
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Banner updated successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'بنر با موفقیت ویرایش شد.'
                        ),
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/AdminBannerResource'
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Banner not found',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'No query results for model [App\\Models\\Banner] 1')
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
                                'image.max' => ['حجم تصویر نباید بیشتر از ۲ مگابایت باشد.']
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthenticated',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Unauthenticated.')
                    ]
                )
            ),
            new OA\Response(
                response: 403,
                description: 'Forbidden - Admin access required',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'This action is unauthorized.')
                    ]
                )
            )
        ]
    )]
    public function update(UpdateBannerRequest $request, Banner $banner): JsonResponse
    {
        $validated = $request->validated();

        if ($request->hasFile('image')) {
            if ($banner->image) {
                $this->imageUploadService->delete($banner->image);
            }

            $validated['image'] = $this->imageUploadService->upload(
                $request->file('image'),
                'images/banners'
            );
        }

        $banner->update($validated);

        return response()->json([
            'message' => 'بنر با موفقیت ویرایش شد.',
            'data' => new BannerResource($banner->fresh()),
        ]);
    }

    #[OA\Put(
        path: '/api/admin/banners/{banner}/image',
        tags: ['Admin - Banners'],
        summary: 'Update banner image only',
        description: 'Updates only the image of an existing banner. **Admin access required.**',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'banner',
                description: 'Banner ID',
                in: 'path',
                required: true,
                schema: new OA\Schema(
                    type: 'integer',
                    example: 1
                )
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['image'],
                    properties: [
                        new OA\Property(
                            property: 'image',
                            type: 'string',
                            format: 'binary',
                            description: 'Banner image file (max 2MB, required)'
                        ),
                    ],
                    type: 'object'
                )
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Banner image updated successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'تصویر بنر با موفقیت ویرایش شد.'
                        ),
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/AdminBannerResource'
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Banner not found',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'No query results for model [App\\Models\\Banner] 1')
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
                                'image' => ['تصویر بنر الزامی است.']
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthenticated',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Unauthenticated.')
                    ]
                )
            ),
            new OA\Response(
                response: 403,
                description: 'Forbidden - Admin access required',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'This action is unauthorized.')
                    ]
                )
            )
        ]
    )]
    public function updateImage(UpdateBannerImageRequest $request, Banner $banner): JsonResponse
    {
        $validated = $request->validated();

        if ($banner->image) {
            $this->imageUploadService->delete($banner->image);
        }

        $validated['image'] = $this->imageUploadService->upload(
            $request->file('image'),
            'images/banners'
        );

        $banner->update($validated);

        return response()->json([
            'message' => 'تصویر بنر با موفقیت ویرایش شد.',
            'data' => new BannerResource($banner->fresh()),
        ]);
    }

    #[OA\Put(
        path: '/api/admin/banners/{banner}/status',
        tags: ['Admin - Banners'],
        summary: 'Update banner status',
        description: 'Updates only the status (active/inactive) of a banner. **Admin access required.**',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'banner',
                description: 'Banner ID',
                in: 'path',
                required: true,
                schema: new OA\Schema(
                    type: 'integer',
                    example: 1
                )
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['is_active'],
                properties: [
                    new OA\Property(
                        property: 'is_active',
                        type: 'boolean',
                        example: true,
                        description: 'Banner status (true = active, false = inactive)'
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Banner status updated successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'وضعیت بنر با موفقیت ویرایش شد.'
                        ),
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/AdminBannerResource'
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Banner not found',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'No query results for model [App\\Models\\Banner] 1')
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
                                'is_active' => ['وضعیت معتبر نیست.']
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthenticated',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Unauthenticated.')
                    ]
                )
            ),
            new OA\Response(
                response: 403,
                description: 'Forbidden - Admin access required',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'This action is unauthorized.')
                    ]
                )
            )
        ]
    )]
    public function updateStatus(UpdateBannerStatusRequest $request, Banner $banner): JsonResponse
    {
        $validated = $request->validated();

        $banner->update($validated);

        return response()->json([
            'message' => 'وضعیت بنر با موفقیت ویرایش شد.',
            'data' => new BannerResource($banner->fresh())
        ]);
    }

    #[OA\Put(
        path: '/api/admin/banners/reorder',
        tags: ['Admin - Banners'],
        summary: 'Reorder banners',
        description: 'Updates the sort order of multiple banners. **Admin access required.**',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                type: 'array',
                items: new OA\Items(
                    properties: [
                        new OA\Property(
                            property: 'id',
                            type: 'integer',
                            example: 1,
                            description: 'Banner ID'
                        ),
                        new OA\Property(
                            property: 'sort_order',
                            type: 'integer',
                            minimum: 0,
                            example: 1,
                            description: 'New sort order'
                        ),
                    ],
                    type: 'object'
                ),
                example: [
                    ['id' => 1, 'sort_order' => 1],
                    ['id' => 3, 'sort_order' => 2],
                    ['id' => 2, 'sort_order' => 3],
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Banners reordered successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'ترتیب بنرها با موفقیت بروزرسانی شد.'
                        ),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                ref: '#/components/schemas/AdminBannerResource'
                            )
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
                                '0.id' => ['شناسه بنر الزامی است.'],
                                '0.sort_order' => ['ترتیب بنر الزامی است.']
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthenticated',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Unauthenticated.')
                    ]
                )
            ),
            new OA\Response(
                response: 403,
                description: 'Forbidden - Admin access required',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'This action is unauthorized.')
                    ]
                )
            )
        ]
    )]
    public function reorder(ReorderBannerRequest $request): JsonResponse
    {
        $items = $request->validated();

        DB::transaction(function () use ($items) {
            collect($items)
                ->sortBy('sort_order')
                ->values()
                ->each(function ($item, $index) {
                    Banner::query()
                        ->where('id', $item['id'])
                        ->update([
                            'sort_order' => $index + 1
                        ]);
                });
        });

        Cache::forget('banners');

        $banners = Banner::query()
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'message' => 'ترتیب بنرها با موفقیت بروزرسانی شد.',
            'data' => BannerResource::collection($banners)
        ]);
    }

    #[OA\Delete(
        path: '/api/admin/banners/{banner}',
        tags: ['Admin - Banners'],
        summary: 'Delete a banner',
        description: 'Deletes a banner and removes its image file. **Admin access required.**',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'banner',
                description: 'Banner ID',
                in: 'path',
                required: true,
                schema: new OA\Schema(
                    type: 'integer',
                    example: 1
                )
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Banner deleted successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'بنر با موفقیت حذف شد.'
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Banner not found',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'No query results for model [App\\Models\\Banner] 1')
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthenticated',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Unauthenticated.')
                    ]
                )
            ),
            new OA\Response(
                response: 403,
                description: 'Forbidden - Admin access required',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'This action is unauthorized.')
                    ]
                )
            )
        ]
    )]
    public function destroy(Banner $banner): JsonResponse
    {
        $this->imageUploadService->delete($banner->image);

        $banner->delete();

        return response()->json([
            'message' => 'بنر با موفقیت حذف شد.',
        ]);
    }
}