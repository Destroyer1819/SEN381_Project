<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RequestAssignment extends Model
{
    const UPDATED_AT = null;
    protected $fillable =
    [
        'request_id','assigned_to','offered_by','state','responded_at'
    ];
    
    
    protected function casts(): array
    {
        return ['responded_at' => 'datetime'];
    }
    public function request(): BelongsTo
    {
        return $this->belongsTo(ServiceRequest::class, 'request_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function offeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'offered_by');
    }
}
