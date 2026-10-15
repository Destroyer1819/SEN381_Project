<?php

namespace App\Listeners;

use App\Events\RequestStatusChanged;
use App\Notifications\RequestStatusChangedMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Throwable;

// D-011: runs on the queue worker, so a mail failure can never undo a status change.
class NotifyRequesterOfStatusChange implements ShouldQueue
{
    public int $tries = 3;

    public function backoff(): array
    {
        return [60, 300]; // retry after 1 minute, then 5 minutes
    }

    public function handle(RequestStatusChanged $event): void
    {
        // Sent to the address in the event (13.6.4), so no user lookup is needed
        Notification::route('mail', $event->requesterEmail)
            ->notify(new RequestStatusChangedMail($event));
    }

    public function failed(RequestStatusChanged $event, Throwable $e): void
    {
        Log::warning('Status email failed', ['request_id' => $event->requestId, 'error' => $e->getMessage()]);
    }
}
