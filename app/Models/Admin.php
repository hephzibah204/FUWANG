<?php

namespace App\Models;

use App\Traits\Loggable;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use PragmaRX\Google2FALaravel\Support\Traits\TwoFactorAuthenticatable;

class Admin extends Authenticatable
{
    use Notifiable, HasRoles, HasFactory, TwoFactorAuthenticatable, Loggable;

    protected $fillable = [
        'username',
        'fullname',
        'email',
        'phone',
        'avatar',
        'password',
        'is_super_admin',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'google2fa_secret',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_super_admin' => 'boolean',
        ];
    }

    /**
     * Check if the admin has a specific permission.
     * Super admins always have all permissions.
     */
    public function hasPermission(string $permission): bool
    {
        if ($this->is_super_admin) {
            return true;
        }

        try {
            return $this->hasPermissionTo($permission);
        } catch (PermissionDoesNotExist) {
            return false;
        }
    }
}
