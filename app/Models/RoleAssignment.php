<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RoleAssignment extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'role_assignments';

    public $incrementing = false;

    protected $keyType = 'string';

    /** @var list<string> */
    protected $fillable = [
        'id', 'user_id', 'role_id', 'scope_type', 'scope_id',
        'starts_at', 'ends_at', 'assigned_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }
}
