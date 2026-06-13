<?php

namespace App\Models;

use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Notifications\Notifiable;
use MongoDB\Laravel\Auth\User as Authenticatable;

class User extends Authenticatable implements CanResetPasswordContract
{
    use Notifiable, CanResetPassword;

    protected $connection = 'mongodb';
    protected $collection = 'users';

    protected $fillable = [
        'name',
        'email',
        'nim',
        'phone',
        'gender',
        'profile_photo',
        'password',
        'role',
        'api_token',
        'google_id',
        'auth_provider',
        'otp',
        'otp_code',
        'otp_purpose',
        'otp_expires_at',
        'email_verified',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'api_token',
        'otp',
        'otp_code',
    ];

    protected $casts = [
        'email_verified' => 'boolean',
        'is_active' => 'boolean',
        'otp_expires_at' => 'datetime',
    ];
}
