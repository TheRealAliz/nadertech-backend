<?php

namespace App\Http\Controllers\Api;

use App\Enums\ProjectRequestStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProjectRequest\StoreProjectRequest;
use App\Models\ProjectRequest;
use Illuminate\Http\JsonResponse;

class ProjectRequestController extends Controller
{
    public function store(StoreProjectRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $projectRequest = ProjectRequest::create([
            'project_service_id' => $validated['service_id'],
            'user_id' => $request->user()?->id ?? null,
            'name' => $validated['name'],
            'mobile' => $validated['mobile'],
            'email' => $validated['email'],
            'description' => $validated['description'],
            'status' => ProjectRequestStatus::Pending->value,
        ]);

        return response()->json([
            'message' => 'درخواست با موفقیت ثبت شد.',
            'data' => [
                'project_request' => $projectRequest
            ]
        ]);
    }
}
