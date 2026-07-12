<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Contracts\View\View;

class UserController extends Controller
{
    public function __invoke(): View
    {
        $users = User::query()
            ->orderByDesc('is_verified')
            ->orderByDesc('created_at')
            ->paginate(30);

        return view('admin.users.index', compact('users'));
    }
}
