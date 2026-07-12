<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Actions\UpdateUserVerificationAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateUserVerificationRequest;
use App\Http\Resources\Api\V1\UserResource;
use App\Models\User;

class UserVerificationController extends Controller
{
    public function __invoke(
        UpdateUserVerificationRequest $request,
        User $user,
        UpdateUserVerificationAction $updateVerification,
    ): UserResource {
        $administrator = $request->user();
        abort_unless($administrator instanceof User, 401);

        return UserResource::make($updateVerification->execute(
            $administrator,
            $user,
            $request->boolean('is_verified'),
        ));
    }
}
