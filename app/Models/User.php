<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasUuids, Notifiable, SoftDeletes;

    protected $authPasswordName = 'password_hash';

    /** @var list<string> */
    protected $fillable = [
        'id',
        'email',
        'name',
        'phone',
        'unit',
        'avatar_url',
        'password_hash',
        'email_verified_at',
        'mfa_secret',
        'mfa_enabled',
        'mfa_backup_codes',
        'failed_login_attempts',
        'locked_until',
        'last_login_at',
        'last_login_ip',
        'password_changed_at',
        'must_change_password',
        'role',
        'status',
    ];

    /** @var list<string> */
    protected $hidden = [
        'password_hash',
        'mfa_secret',
        'mfa_backup_codes',
        'remember_token',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'locked_until' => 'datetime',
            'last_login_at' => 'datetime',
            'password_changed_at' => 'datetime',
            'mfa_enabled' => 'boolean',
            'must_change_password' => 'boolean',
            'mfa_backup_codes' => 'array',
            'failed_login_attempts' => 'integer',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * @return HasMany<RefreshToken, $this>
     */
    public function refreshTokens(): HasMany
    {
        return $this->hasMany(RefreshToken::class, 'user_id');
    }

    /**
     * @return array<int, string>
     */
    public function uniqueIds(): array
    {
        return ['id'];
    }
}
