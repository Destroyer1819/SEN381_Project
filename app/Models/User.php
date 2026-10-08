<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use HasFactory;

    protected $table = 'users';

    protected $fillable = ['name', 'email', 'password', 'role'];

    protected $hidden = ['password'];

    protected function casts(): array
    {
        return ['password' => 'hashed'];
    }

    // The agreed schema has no remember_token column.
    public function getRememberTokenName(): string
    {
        return '';
    }

    public function isStaff(): bool
    {
        return $this->role === 'staff';
    }

    public function isManagement(): bool
    {
        return $this->role === 'management';
    }

    public function isRequestor(): bool
    {
        return $this->role === 'requestor';
    }

    public function isStaffOrManagement(): bool
    {
        return $this->isStaff() || $this->isManagement();
    }
}
