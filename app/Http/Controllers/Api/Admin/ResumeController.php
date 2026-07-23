<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Resume\StoreResumeRequest;
use App\Http\Requests\Admin\Resume\UpdateResumeRequest;
use App\Http\Requests\Admin\Resume\UpdateResumeStatusRequest;
use App\Http\Resources\Admin\Resume\ResumeListResource;
use App\Http\Resources\Admin\Resume\ResumeResource;
use App\Models\Resume;
use App\Services\ImageUploadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use OpenApi\Attributes as OA;

class ResumeController extends Controller
{
    public function __construct(
        private ImageUploadService $imageUploadService
    ) {
    }

    #[OA\Post(
        path: '/api/admin/resume',
        tags: ['Admin - Resume'],
        summary: 'Create resume',
        description: 'Create a new resume with review and images',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['title', 'description', 'customer_name', 'customer_description'],
                    properties: [
                        new OA\Property(property: 'title', type: 'string', maxLength: 255, example: 'طراحی وب‌سایت فروشگاهی'),
                        new OA\Property(property: 'slug', type: 'string', nullable: true, maxLength: 255, example: 'ecommerce-website-design'),
                        new OA\Property(property: 'description', type: 'string', example: 'توضیحات کامل پروژه'),
                        new OA\Property(property: 'is_published', type: 'boolean', example: true),
                        new OA\Property(property: 'category_id', type: 'integer', nullable: true, example: 1),
                        new OA\Property(property: 'customer_name', type: 'string', maxLength: 255, example: 'علی احمدی'),
                        new OA\Property(property: 'customer_position', type: 'string', nullable: true, maxLength: 255, example: 'مدیر عامل'),
                        new OA\Property(property: 'customer_avatar', type: 'string', format: 'binary', nullable: true, description: 'Image file (max: 2MB, allowed: jpg,jpeg,png,webp)'),
                        new OA\Property(property: 'customer_description', type: 'string', example: 'نظر مشتری درباره پروژه'),
                        new OA\Property(
                            property: 'images',
                            type: 'array',
                            nullable: true,
                            description: 'Array of images',
                            items: new OA\Items(
                                type: 'string',
                                format: 'binary',
                                description: 'Image file (max: 2MB, allowed: jpg,jpeg,png,webp)'
                            )
                        ),
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
                        new OA\Property(property: 'message', type: 'string', example: 'Resume created successfully'),
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/ResumeResource'
                        )
                    ]
                )
            ),
            new OA\Response(response: 422, description: 'Validation error'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    public function store(StoreResumeRequest $request): JsonResponse
    {
        $data = $request->validated();

        return DB::transaction(function () use ($data, $request) {

            $slug = Str::slug($data['slug'] ?? $data['title']);

            $resume = Resume::create([
                'title' => $data['title'],
                'slug' => $slug,
                'description' => $data['description'],
                'is_published' => $data['is_published'] ?? true,
                'category_id' => $data['category_id'] ?? null,
            ]);

            $avatarPath = null;

            if ($request->hasFile('customer_avatar')) {
                $avatarPath = $this->imageUploadService
                    ->upload($request->file('customer_avatar'), 'images/avatars');
            }

            $resume->review()->create([
                'name' => $data['customer_name'],
                'position' => $data['customer_position'] ?? null,
                'avatar' => $avatarPath,
                'description' => $data['customer_description'],
            ]);

            if ($request->hasFile('images')) {

                foreach ($request->file('images') as $index => $image) {

                    $path = $this->imageUploadService->upload($image, 'images/resumes');

                    $resume->images()->create([
                        'image' => $path,
                        'sort_order' => $index,
                    ]);
                }
            }

            $resume->load(['review', 'images', 'category']);
            return response()->json([
                'message' => 'Resume created successfully',
                'data' => new ResumeResource($resume),
            ], 201);
        });
    }

    #[OA\Get(
        path: '/api/admin/resume',
        tags: ['Admin - Resume'],
        summary: 'Get list of resumes',
        description: 'Returns paginated list of resumes with their first image',
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
                schema: new OA\Schema(type: 'integer', example: 6, minimum: 1, maximum: 100)
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
                                ref: '#/components/schemas/ResumeListResource'
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
        $resumes = Resume::query()
            ->with('firstImage')
            ->latest()
            ->paginate(6);

        return ResumeListResource::collection($resumes);
    }

    #[OA\Get(
        path: '/api/admin/resume/{resume}',
        tags: ['Admin - Resume'],
        summary: 'Get single resume',
        description: 'Returns detailed information about a specific resume with its review, images, and category',
        security: [['bearerAuth' => []]],
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
                            ref: '#/components/schemas/ResumeResource'
                        )
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Not found'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    public function show(Resume $resume): ResumeResource
    {
        return new ResumeResource($resume->load(['review', 'images', 'category']));
    }

    #[OA\Post(
        path: '/api/admin/resume/{resume}',
        tags: ['Admin - Resume'],
        summary: 'Update resume',
        description: 'Update an existing resume with its review and images',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'resume',
                in: 'path',
                required: true,
                description: 'Resume ID',
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
                    required: ['title', 'description', 'customer_name', 'customer_description'],
                    properties: [
                        new OA\Property(property: 'title', type: 'string', maxLength: 255, example: 'طراحی وب‌سایت فروشگاهی'),
                        new OA\Property(property: 'slug', type: 'string', nullable: true, maxLength: 255, example: 'ecommerce-website-design'),
                        new OA\Property(property: 'description', type: 'string', example: 'توضیحات کامل پروژه'),
                        new OA\Property(property: 'is_published', type: 'boolean', example: true),
                        new OA\Property(property: 'category_id', type: 'integer', nullable: true, example: 1),
                        new OA\Property(property: 'customer_name', type: 'string', maxLength: 255, example: 'علی احمدی'),
                        new OA\Property(property: 'customer_position', type: 'string', nullable: true, maxLength: 255, example: 'مدیر عامل'),
                        new OA\Property(property: 'customer_avatar', type: 'string', format: 'binary', nullable: true, description: 'Image file (max: 2MB, allowed: jpg,jpeg,png,webp)'),
                        new OA\Property(property: 'customer_description', type: 'string', example: 'نظر مشتری درباره پروژه'),
                        new OA\Property(
                            property: 'images',
                            type: 'array',
                            nullable: true,
                            description: 'Array of images (replaces all existing images)',
                            items: new OA\Items(
                                type: 'string',
                                format: 'binary',
                                description: 'Image file (max: 2MB, allowed: jpg,jpeg,png,webp)'
                            )
                        ),
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
                        new OA\Property(property: 'message', type: 'string', example: 'Resume updated successfully'),
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/ResumeResource'
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
    public function update(UpdateResumeRequest $request, Resume $resume): JsonResponse
    {
        $data = $request->validated();

        return DB::transaction(function () use ($data, $request, $resume) {

            $slug = Str::slug($data['slug'] ?? $data['title']);

            $resume->update([
                'title' => $data['title'],
                'slug' => $slug,
                'description' => $data['description'],
                'is_published' => $data['is_published'] ?? $resume->is_published,
                'category_id' => $data['category_id'] ?? null,
            ]);

            $avatarPath = $resume->review?->avatar;

            if ($request->hasFile('customer_avatar')) {
                $avatarPath = $this->imageUploadService
                    ->replace(
                        $avatarPath,
                        $request->file('customer_avatar'),
                        'images/avatars'
                    );
            }

            $resume->review()->updateOrCreate(
                ['resume_id' => $resume->id],
                [
                    'name' => $data['customer_name'],
                    'position' => $data['customer_position'] ?? null,
                    'avatar' => $avatarPath,
                    'description' => $data['customer_description'],
                ]
            );

            if ($request->hasFile('images')) {

                foreach ($resume->images as $img) {
                    $this->imageUploadService->delete($img->image);
                }

                $resume->images()->delete();

                foreach ($request->file('images') as $index => $image) {

                    $path = $this->imageUploadService
                        ->upload($image, 'images/resumes');

                    $resume->images()->create([
                        'image' => $path,
                        'sort_order' => $index,
                    ]);
                }
            }

            $resume->load(['review', 'images', 'category']);

            return response()->json([
                'message' => 'Resume updated successfully',
                'data' => new ResumeResource($resume),
            ]);
        });
    }

    #[OA\Patch(
        path: '/api/admin/resume/{resume}/status',
        tags: ['Admin - Resume'],
        summary: 'Update resume status',
        description: 'Update the publication status of a resume',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'resume',
                in: 'path',
                required: true,
                description: 'Resume ID',
                schema: new OA\Schema(type: 'integer', example: 1)
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['is_published'],
                properties: [
                    new OA\Property(property: 'is_published', type: 'boolean', example: true)
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Updated',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Resume status updated successfully'),
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/ResumeResource'
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
    public function updateStatus(UpdateResumeStatusRequest $request, Resume $resume): JsonResponse
    {
        $data = $request->validated();

        $resume->update($data);

        $resume->load(['review', 'images', 'category']);

        return response()->json([
            'message' => 'Resume status updated successfully',
            'data' => new ResumeResource($resume),
        ]);
    }

    #[OA\Delete(
        path: '/api/admin/resume/{resume}',
        tags: ['Admin - Resume'],
        summary: 'Delete resume',
        description: 'Delete a resume and all its associated files (review avatar and images)',
        security: [['bearerAuth' => []]],
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
                description: 'Deleted',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Resume deleted successfully'),
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Not found'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    public function destroy(Resume $resume): JsonResponse
    {
        return DB::transaction(function () use ($resume) {

            if ($resume->review?->avatar) {
                $this->imageUploadService->delete($resume->review->avatar);
            }

            foreach ($resume->images as $image) {
                $this->imageUploadService->delete($image->image);
            }

            $resume->delete();

            return response()->json([
                'message' => 'Resume deleted successfully',
            ]);
        });
    }
}