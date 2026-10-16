<?php

namespace App\Policies;

use App\Models\RequestAssignment;
use App\Models\User;

class RequestAssignmentPolicy
{
    // Only the person an offer went to can answer it, and only while it is waiting
    public function respond(User $user, RequestAssignment $assignment): bool
    {
        return $assignment->state === 'offered'
            && $assignment->assigned_to === $user->id;
    }
}
