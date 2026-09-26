<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * Pengguna iSEP (pelajar, pensyarah, admin). Jadual `users` sedia ada:
 * log masuk guna `username` (nama penuh huruf besar), bukan emel.
 */
class User extends Authenticatable
{
    protected $table = 'users';

    protected $guarded = [];

    protected $hidden = ['password'];

    // created_at & updated_at diurus terus oleh MySQL
    public $timestamps = false;

    // Jadual users tiada lajur remember_token
    protected $rememberTokenName = '';

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class, 'student_id');
    }

    public function progress(): HasMany
    {
        return $this->hasMany(StudentProgress::class, 'student_id');
    }

    public function isRole(string ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }
}
