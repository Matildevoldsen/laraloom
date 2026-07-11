<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\LoginRequest;
use App\Http\Resources\Api\V1\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthTokenController extends Controller
{
    public function store(LoginRequest $request): JsonResponse
    {
        $user = $request->authenticate();
        $token = $user->createToken($request->string('device_name'), ['mobile'])->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => UserResource::make($user),
        ]);
    }

    public function show(Request $request): UserResource
    {
        $user = $request->user();
        abort_unless($user instanceof User, 401);

        return UserResource::make($user->loadCount(['followers', 'following', 'posts', 'projects']));
    }

    public function destroy(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user instanceof User, 401);
        $user->currentAccessToken()->delete();

        return response()->json(status: 204);
    }
}
