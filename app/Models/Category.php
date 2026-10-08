<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $table = 'categories';
    public $timestamps = false;
    protected $fillable = ['id', 'code', 'name', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];
}
