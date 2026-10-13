<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    
    public $timestamps = false;

    protected $fillable = [
        'code', 'name', 'is_active',
        
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function serviceRequests(): HasMany
    {
        return $this->hasMany(ServiceRequest::class);
    }
}
