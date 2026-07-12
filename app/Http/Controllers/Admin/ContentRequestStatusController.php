<?php

namespace App\Http\Controllers\Admin;

use App\Actions\UpdateContentRequestStatusAction;
use App\ContentRequestStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateContentRequestStatusRequest;
use App\Models\ContentRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

class ContentRequestStatusController extends Controller
{
    public function __invoke(
        UpdateContentRequestStatusRequest $request,
        ContentRequest $contentRequest,
        UpdateContentRequestStatusAction $updateStatus,
    ): RedirectResponse {
        $administrator = $request->user();
        abort_unless($administrator instanceof User, 401);
        $status = ContentRequestStatus::from($request->string('status')->toString());
        $updateStatus->execute(
            $contentRequest,
            $status,
            $administrator,
            $request->string('resolution_notes')->trim()->toString() ?: null,
        );

        return back()->with('status', "Request {$contentRequest->reference()} marked {$status->value}.");
    }
}
