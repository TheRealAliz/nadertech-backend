<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginWithPasswordRequest;
use App\Http\Resources\Admin\Auth\AdminResource;
use App\Models\Admin;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(LoginWithPasswordRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $admin = Admin::where('username', $validated['login'])->first();

        if (!$admin || !Hash::check($request->password, $admin->password)) {
            throw ValidationException::withMessages([
                'login' => ['اطلاعات ورود نادرست است.'],
            ]);
        }

        $token = $admin->createToken('api_token')->plainTextToken;

        return response()->json([
            'message' => 'ورود با موفقیت انجام شد.',
            'data' => [
                'access_token' => $token,
                'token_type' => 'Bearer',
                'admin' => new AdminResource($admin),
            ],
        ]);
    }
}
