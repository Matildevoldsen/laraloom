<?php

namespace App\Actions;

use App\ContentRequestStatus;
use App\Models\ContentRequest;
use App\Models\User;

class UpdateContentRequestStatusAction
{
    public function execute(
        ContentRequest $contentRequest,
        ContentRequestStatus $status,
        User $administrator,
        ?string $resolutionNotes,
    ): ContentRequest {
        $contentRequest->update([
            'status' => $status,
            'resolution_notes' => $resolutionNotes,
            'status_updated_by' => $administrator->getKey(),
            'resolved_at' => in_array($status, [
                ContentRequestStatus::Resolved,
                ContentRequestStatus::Rejected,
            ], true) ? now() : null,
        ]);

        return $contentRequest->refresh();
    }
}
