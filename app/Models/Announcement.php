<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Announcement extends Model
{
    use HasUuids;
    use SoftDeletes;

    protected $table = 'announcements';

    protected $fillable = [
        'id',
        'condominium_id',
        'autor_user_id',
        'titulo',
        'cuerpo',
        'segmento',
        'target_id',
        'estado',
        'programado_para',
        'fijado',
        'canales',
    ];

    protected $casts = [
        'canales' => 'array',
        'fijado' => 'boolean',
        'programado_para' => 'datetime',
    ];

    /** @return BelongsTo<Condominium, $this> */
    public function condominium(): BelongsTo
    {
        return $this->belongsTo(Condominium::class);
    }

    /** @return BelongsTo<User, $this> */
    public function autor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'autor_user_id');
    }

    /** @return HasMany<AnnouncementDelivery, $this> */
    public function deliveries(): HasMany
    {
        return $this->hasMany(AnnouncementDelivery::class);
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
