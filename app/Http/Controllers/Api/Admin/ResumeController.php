<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Resume\StoreResumeRequest;
use App\Http\Resources\Admin\Resume\ResumeResource;
use App\Models\Resume;
use App\Services\ImageUploadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ResumeController extends Controller
{
    public function __construct(
        private ImageUploadService $imageUploadService
    ) {
    }

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

            $resume->load(['review', 'images']);

            return response()->json([
                'message' => 'Resume created successfully',
                'data' => new ResumeResource($resume),
            ], 201);
        });
    }
}