<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;


class ServiceRequest extends Model
{
    protected $fillable = 
    [
        'reference_number','requestor_id','category_id',
        'location','area','description',
        
    ];

    protected function casts():array
    {
        return ['resolved_at'=>'datetime'];
    }
    public function requestor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requestor_id');
    }
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
    public function comments(): HasMany
    {
        return $this->hasMany(RequestComment::class, 'request_id');
    }

    public function history(): HasMany
    {
        return $this->hasMany(RequestStatusHistory::class, 'request_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(RequestAssignment::class, 'request_id');
    }  

    public function scopeVisibleTo(Builder $query, User $user): void
    {
        if ($user->role === 'requestor') 
        {
            $query->where('requestor_id', $user->id);
        } 
        elseif ($user->role === 'staff') 
        {
            $query->where(fn (Builder $q) => $q
                ->whereNull('assigned_to')
                ->orWhere('assigned_to', $user->id));
        }
    }
}
