<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Survey extends Model
{
    use HasUuids;

    protected $table = 'surveys';

    protected $fillable = [
        'id',
        'condominium_id',
        'pregunta',
        'tipo',
        'cierra_el',
        'activa',
    ];

    protected $casts = [
        'cierra_el' => 'datetime',
        'activa' => 'boolean',
    ];

    /** @return BelongsTo<Condominium, $this> */
    public function condominium(): BelongsTo
    {
        return $this->belongsTo(Condominium::class);
    }

    /** @return HasMany<SurveyOption, $this> */
    public function options(): HasMany
    {
        return $this->hasMany(SurveyOption::class);
    }

    /** @return HasMany<SurveyResponse, $this> */
    public function responses(): HasMany
    {
        return $this->hasMany(SurveyResponse::class);
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
