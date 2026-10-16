<?php

namespace App\Policies;

use App\Models\ServiceRequest;
use App\Models\User;

class ServiceRequestPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ServiceRequest $serviceRequest): bool
    {
        return match ($user->role) {
            'management' => true,
            'staff' => $serviceRequest->assigned_to === null || $serviceRequest->assigned_to === $user->id,
            default => $serviceRequest->requestor_id === $user->id,
        };
    }

    public function comment(User $user, ServiceRequest $serviceRequest): bool
    {
        return $this->view($user, $serviceRequest);
    }

    public function take(User $user, ServiceRequest $serviceRequest): bool
    {
        return $user->role === 'staff'
        && $serviceRequest->status === 'open'
        && $serviceRequest->assigned_to === null
        && ! $serviceRequest->pendingOffer()->exists(); // Take is blocked while an offer is pending (D-018)
    }

    public function offer(User $user, ServiceRequest $serviceRequest): bool
    {
        return in_array($user->role, ['staff', 'management'], true)
        && $serviceRequest->status === 'open'
        && $serviceRequest->assigned_to === null
        && ! $serviceRequest->pendingOffer()->exists();
    }

    public function changeStatus(User $user, ServiceRequest $serviceRequest): bool
    {
        return $user->role === 'staff' && $serviceRequest->assigned_to === $user->id;
    }
}
