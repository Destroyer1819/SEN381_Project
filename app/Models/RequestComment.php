<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RequestComment extends Model
{
    const UPDATED_AT = null;

    protected $touches = ['request'];

    protected $fillable =
        [
            'request_id', 'author_id', 'comment', 'is_resolution',
        ];

    protected function casts(): array
    {
        return ['is_resolution' => 'boolean'];
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(ServiceRequest::class, 'request_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
