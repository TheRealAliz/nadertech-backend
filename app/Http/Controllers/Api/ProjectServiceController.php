<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProjectRequests\ProjectServiceResource;
use App\Models\ProjectService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProjectServiceController extends Controller
{
    public function index(): ProjectServiceResource|AnonymousResourceCollection
    {
        $services = ProjectService::query()
            ->orderBy('parent_id')
            ->orderBy('sort_order')
            ->get();

        return ProjectServiceResource::collection($services);
    }

    public function show(ProjectService $projectService): ProjectServiceResource
    {
        $projectService->load('children');

        return new ProjectServiceResource($projectService);
    }

    public function tree(): ProjectServiceResource|AnonymousResourceCollection
    {
        $services = ProjectService::with('children')
            ->parents()
            ->orderBy('sort_order')
            ->get();

        return ProjectServiceResource::collection($services);
    }
}
