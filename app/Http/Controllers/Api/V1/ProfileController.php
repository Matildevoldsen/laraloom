<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\UserResource;
use App\Models\User;

class ProfileController extends Controller
{
    public function __invoke(User $user): UserResource
    {
        abort_if($user->username === null, 404);

        return UserResource::make($user->loadCount(['followers', 'following', 'posts', 'projects']));
    }
}
