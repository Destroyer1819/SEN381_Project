<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Support\Facades\DB;
use PhpParser\Node\Expr\FuncCall;

class ServiceRequest extends Model
{
    use Prunable;
    public const Retention_Years = 5;

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

    public function prunable(): Builder
    {
        return static::where('updated_at', "<", now()->subYears(self::Retention_Years));
    }

    Protected function pruning(): void 
    {
        $this->history()->delete();
        $this->assignments()->delete();
        $this->comments()->delete();
    }

    //added for a atomicity. either everything succeeds, or everything fails.
    public function prune()
    {
    return DB::transaction(function()
        {
        $this->pruning();
        return $this->delete();
        });
    }
}
