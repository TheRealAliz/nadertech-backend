<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class AdminRoleController extends Controller
{
    public function sync(Request $request, Admin $admin): JsonResponse
    {
        $validated = $request->validate([
            'roles' => ['required', 'array'],
            'roles.*' => ['exists:roles,name'],
        ]);

        $admin->syncRoles($validated['roles']);

        return response()->json([
            'message' => 'Roles updated successfully.',
        ]);
    }

    public function show(Admin $admin): JsonResponse
    {
        return response()->json([
            'roles' => $admin->roles()->pluck('name'),
        ]);
    }
}