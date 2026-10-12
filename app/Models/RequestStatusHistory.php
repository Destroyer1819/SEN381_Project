<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RequestStatusHistory extends Model
{
    protected $table = 'request_status_history';

    public $timestamps = false;

    protected $fillable = ['request_id', 'old_status', 'new_status', 'changed_by'];

    protected function casts(): array
    {
        return ['changed_at' => 'datetime'];
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(ServiceRequest::class, 'request_id');
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
