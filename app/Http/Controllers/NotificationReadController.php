<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class NotificationReadController extends Controller
{
    public function __invoke(Request $request, string $notification): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user instanceof User, 401);
        $databaseNotification = $user->notifications()->findOrFail($notification);
        $url = $databaseNotification->data['url'] ?? null;
        abort_unless(is_string($url) && Str::startsWith($url, '/'), 404);

        $databaseNotification->markAsRead();

        return redirect()->to($url);
    }
}
