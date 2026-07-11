<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Fortify\CreateNewUser;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\RegisterRequest;
use App\Http\Resources\Api\V1\UserResource;
use Illuminate\Http\JsonResponse;

class RegistrationController extends Controller
{
    public function __invoke(RegisterRequest $request, CreateNewUser $createNewUser): JsonResponse
    {
        $user = $createNewUser->create($request->validated());
        $token = $user->createToken($request->string('device_name'), ['mobile'])->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => UserResource::make($user),
        ], 201);
    }
}
