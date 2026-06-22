<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
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

class BannerController extends Controller
{
    public function __construct(
        private ImageUploadService $imageUploadService
    ) {
    }
    public function index(): AnonymousResourceCollection
    {
        $banners = Banner::query()
            ->orderBy('sort_order')
            ->latest()
            ->paginate(10);

        return BannerResource::collection($banners);
    }

    public function store(StoreBannerRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $validated['image'] = $this->imageUploadService->upload(
            $request->file('image'),
            'banners'
        );

        $banner = Banner::create($validated);

        return response()->json([
            'message' => 'بنر با موفقیت افزوده شد.',
            'data' => new BannerResource($banner),
        ]);
    }

    public function show(Banner $banner): BannerResource
    {
        return new BannerResource($banner);
    }

    public function update(UpdateBannerRequest $request, Banner $banner): JsonResponse
    {
        $validated = $request->validated();

        if ($request->hasFile('image')) {
            if ($banner->image) {
                $this->imageUploadService->delete($banner->image);
            }

            $validated['image'] = $this->imageUploadService->upload(
                $request->file('image'),
                'banners'
            );
        }

        $banner->update($validated);

        return response()->json([
            'message' => 'بنر با موفقیت ویرایش شد.',
            'data' => new BannerResource($banner->fresh()),
        ]);
    }

    public function updateImage(UpdateBannerImageRequest $request, Banner $banner): JsonResponse
    {
        $validated = $request->validated();

        if ($banner->image) {
            $this->imageUploadService->delete($banner->image);
        }

        $validated['image'] = $this->imageUploadService->upload(
            $request->file('image'),
            'banners'
        );

        $banner->update($validated);

        return response()->json([
            'message' => 'تصویر بنر با موفقیت ویرایش شد.',
            'data' => new BannerResource($banner->fresh()),
        ]);
    }

    public function updateStatus(UpdateBannerStatusRequest $request, Banner $banner): JsonResponse
    {
        $validated = $request->validated();

        $banner->update($validated);

        return response()->json([
            'message' => 'وضعیف بنر با موفقیت ویرایش شد.',
            'data' => new BannerResource($banner->fresh())
        ]);
    }

    public function reorder(Request $request): JsonResponse
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
            'ترتیب بنرها با موفقیت بروزرسانی شد.',
            'data' => BannerResource::collection($banners)
        ]);
    }

    public function destroy(Banner $banner): JsonResponse
    {
        $this->imageUploadService->delete($banner->image);

        $banner->delete();

        return response()->json([
            'message' => 'بنر با موفقیت حذف شد.',
        ]);
    }
}
