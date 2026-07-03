<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invitation extends Model
{
    use HasUuids;

    protected $table = 'invitations';

    protected $fillable = [
        'token',
        'inviter_user_id',
        'property_id',
        'invitee_email',
        'invitee_name',
        'occupant_type_id',
        'status',
        'expires_at',
        'accepted_at',
        'accepted_by_user_id',
        'revoked_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'accepted_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    /** @return BelongsTo<User, $this> */
    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inviter_user_id');
    }

    /** @return BelongsTo<\App\Models\Property, $this> */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /** @return BelongsTo<OccupantType, $this> */
    public function occupantType(): BelongsTo
    {
        return $this->belongsTo(OccupantType::class);
    }
}
