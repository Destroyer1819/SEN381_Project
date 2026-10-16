<?php

namespace App\Notifications;

use App\Events\RequestStatusChanged;
use App\Models\ServiceRequest;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RequestStatusChangedMail extends Notification
{
    public function __construct(public RequestStatusChanged $event) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $from = ServiceRequest::STATUS_LABELS[$this->event->fromStatus];
        $to = ServiceRequest::STATUS_LABELS[$this->event->toStatus];

        return (new MailMessage)
            ->subject("{$this->event->reference} is now {$to}")
            ->line("Your request {$this->event->reference} has moved from {$from} to {$to}.")
            ->action('View your request', route('requests.show', $this->event->requestId))
            ->line('You are receiving this because you submitted this request on CivicConnect.');
    }
}
