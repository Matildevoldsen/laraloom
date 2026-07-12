<?php

namespace App\Http\Controllers;

use App\Models\PostAttachment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

class PostAttachmentController extends Controller
{
    public function __invoke(PostAttachment $attachment): RedirectResponse
    {
        abort_unless($attachment->post->published_at?->isPast(), 404);

        return redirect()->away(Storage::disk($attachment->disk)->temporaryUrl(
            $attachment->path,
            now()->addMinutes(15),
            [
                'ResponseContentType' => $attachment->mime_type,
                'ResponseContentDisposition' => 'inline; filename="'.addslashes($attachment->original_name).'"',
            ],
        ));
    }
}
