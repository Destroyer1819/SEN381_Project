<?php

namespace App\Events;

use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;

// PED 13.6.4: carries only what the listener needs, not the whole request.
class RequestStatusChanged implements ShouldDispatchAfterCommit
{
    use Dispatchable;

    public function __construct(
        public int $requestId,
        public string $reference,
        public string $fromStatus,
        public string $toStatus,
        public int $actorId,
        public string $occurredAt,
        public string $requesterEmail,
    ) {}
}
