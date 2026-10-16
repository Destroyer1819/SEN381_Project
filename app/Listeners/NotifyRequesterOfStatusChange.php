<?php

namespace App\Listeners;

use App\Events\RequestStatusChanged;
use App\Notifications\RequestStatusChangedMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Throwable;

// D-011: runs on the queue worker, so a mail failure can never undo a status change.
class NotifyRequesterOfStatusChange implements ShouldQueue
{
    use InteractsWithQueue;

    public int $tries = 3;

    public function backoff(): array
    {
        return [60, 300]; // retry after 1 minute, then 5 minutes
    }

    public function handle(RequestStatusChanged $event): void
    {
        try {
            // Sent to the address in the event (13.6.4), so no user lookup is needed
            Notification::route('mail', $event->requesterEmail)
                ->notify(new RequestStatusChangedMail($event));
        } catch (Throwable $e) {
            // On a real queue, let it fail so the worker retries (tries and backoff above).
            if (! $this->runsInsideTheRequest()) {
                throw $e;
            }

            // On the sync queue (staging on Render, PED 13.7.2) the email is sent during the user's request,
            // after the status change is saved. There is no retry, and an error page would wrongly suggest
            // the change failed, so log it instead.
            Log::error('Status email could not be sent', ['request_id' => $event->requestId, 'error' => $e->getMessage()]);
        }
    }

    public function failed(RequestStatusChanged $event, Throwable $e): void
    {
        Log::warning('Status email failed', ['request_id' => $event->requestId, 'error' => $e->getMessage()]);
    }

    private function runsInsideTheRequest(): bool
    {
        return $this->job === null || $this->job->getConnectionName() === 'sync';
    }
}
