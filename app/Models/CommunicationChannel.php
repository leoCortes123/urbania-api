<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommunicationChannel extends Model
{
    use HasUuids;

    protected $table = 'communication_channels';

    protected $fillable = [
        'id',
        'condominium_id',
        'canal',
        'provider',
        'config',
        'activo',
    ];

    protected $casts = [
        'config' => 'array',
        'activo' => 'boolean',
    ];

    /** @return BelongsTo<Condominium, $this> */
    public function condominium(): BelongsTo
    {
        return $this->belongsTo(Condominium::class);
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeForCondominium($query, string $condominiumId)
    {
        return $query->where('condominium_id', $condominiumId);
    }
}
