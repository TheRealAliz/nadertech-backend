<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProjectRequests\ProjectServiceResource;
use App\Models\ProjectService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProjectServiceController extends Controller
{
    public function index(): JsonResponse
    {
        $services = ProjectService::query()
            ->orderBy('parent_id')
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'data' => [
                'services' => ProjectServiceResource::collection($services),
            ]
        ]);
    }

    public function show(ProjectService $projectService): JsonResponse
    {
        $projectService->load('children');

        return response()->json([
            'data' => [
                'service' => new ProjectServiceResource($projectService),
            ]
        ]);
    }

    public function tree(): JsonResponse
    {
        $services = ProjectService::with('children')
            ->parents()
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'data' => [
                'services' => ProjectServiceResource::collection($services),
            ]
        ]);
    }
}
