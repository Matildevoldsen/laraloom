<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\DeleteUserAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\DeleteAccountRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class DeleteAccountController extends Controller
{
    public function __invoke(DeleteAccountRequest $request, DeleteUserAction $deleteUser): JsonResponse
    {
        $user = $request->user();
        abort_unless($user instanceof User, 401);
        $deleteUser->execute($user);

        return response()->json(status: 204);
    }
}
