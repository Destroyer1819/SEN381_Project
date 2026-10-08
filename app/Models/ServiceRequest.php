<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceRequest extends Model
{
    protected $table = 'service_request';

    protected $guarded = [];

    protected $casts = [
        'version' => 'integer',
        'resolved_at' => 'datetime',
    ];

    public function requestor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requestor_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function history(): HasMany
    {
        return $this->hasMany(RequestStatusHistory::class, 'request_id')->orderBy('changed_at')->orderBy('id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(RequestComment::class, 'request_id')->orderBy('created_at')->orderBy('id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(RequestAssignment::class, 'request_id')->orderBy('created_at')->orderBy('id');
    }

    public function isClosedOrResolved(): bool
    {
        return in_array($this->status, ['resolved', 'closed'], true);
    }

    public function isOverdue(): bool
    {
        return ! $this->isClosedOrResolved()
            && $this->created_at->lt(now()->subDays(config('civicconnect.overdue_days')));
    }
}
