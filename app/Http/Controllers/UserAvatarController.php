<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

class UserAvatarController extends Controller
{
    public function __invoke(User $user): RedirectResponse
    {
        abort_if(blank($user->avatar_disk) || blank($user->avatar_path), 404);

        return redirect()->away(
            Storage::disk($user->avatar_disk)->temporaryUrl(
                $user->avatar_path,
                now()->addMinutes(10),
            ),
        );
    }
}
