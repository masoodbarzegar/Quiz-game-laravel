<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
    ];

    /**
     * Get the questions created by this user.
     */
    public function createdQuestions()
    {
        return $this->hasMany(Question::class, 'created_by');
    }

    /**
     * Get the questions approved by this user.
     */
    public function approvedQuestions()
    {
        return $this->hasMany(Question::class, 'approved_by');
    }

    /**
     * Get the questions rejected by this user.
     */
    public function rejectedQuestions()
    {
        return $this->hasMany(Question::class, 'rejected_by');
    }

    /**
     * Check if user has any of the given roles
     *
     * @param array $roles Array of roles to check
     * @return bool
     */
    public function hasAnyRole(array $roles): bool
    {
        return in_array($this->role, $roles);
    }

    /**
     * Check if user has a specific role
     *
     * @param string|array $roles Single role or array of roles to check
     * @return bool
     */
    public function hasRole($roles): bool
    {
        if (!is_array($roles)) {
            $roles = [$roles];
        }
        return in_array($this->role, $roles);
    }

    /**
     * Check if user is a manager
     */
    public function isManager(): bool
    {
        return $this->role === 'manager';
    }

    /**
     * Check if user is a corrector
     */
    public function isCorrector(): bool
    {
        return $this->role === 'corrector';
    }

    /**
     * Check if user is a general admin
     */
    public function isGeneral(): bool
    {
        return $this->role === 'general';
    }

    /**
     * Route notifications for the mail channel.
     *
     * @return string
     */
    public function routeNotificationForMail()
    {
        return $this->email;
    }
}
