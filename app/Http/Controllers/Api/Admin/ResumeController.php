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

            $resume->load(['review', 'images', 'category']);

            return response()->json([
                'message' => 'Resume created successfully',
                'data' => new ResumeResource($resume),
            ], 201);
        });
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $resumes = Resume::query()
            ->with('firstImage')
            ->latest()
            ->paginate(6);

        return ResumeListResource::collection($resumes);
    }

    public function show(Resume $resume): ResumeResource
    {
        return new ResumeResource($resume->load(['review', 'images', 'category']));
    }

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