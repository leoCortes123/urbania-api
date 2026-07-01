<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MessageTemplate extends Model
{
    use HasUuids;
    use SoftDeletes;

    protected $table = 'message_templates';

    protected $fillable = [
        'id',
        'condominium_id',
        'nombre',
        'tipo',
        'cuerpo',
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
