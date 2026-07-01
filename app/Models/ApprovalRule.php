<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApprovalRule extends Model
{
    use HasUuids;

    protected $table = 'approval_rules';

    public $incrementing = false;

    protected $keyType = 'string';

    /** @var list<string> */
    protected $fillable = [
        'id', 'resource', 'action', 'organization_id',
        'threshold', 'approver_role_id', 'requires_second_approval',
    ];

    protected function casts(): array
    {
        return [
            'threshold' => 'decimal:2',
            'requires_second_approval' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Role, $this>
     */
    public function approverRole(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'approver_role_id');
    }
}
