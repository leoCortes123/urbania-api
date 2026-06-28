<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\TowerFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tower extends Model
{
    /** @use HasFactory<TowerFactory> */
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'towers';

    public $incrementing = false;

    protected $keyType = 'string';

    /** @var list<string> */
    protected $fillable = [
        'id',
        'condominium_id',
        'name',
        'code',
        'floor_count',
        'has_elevator',
        'description',
        'sort_order',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'floor_count' => 'integer',
            'has_elevator' => 'boolean',
            'sort_order' => 'integer',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Condominium, $this>
     */
    public function condominium(): BelongsTo
    {
        return $this->belongsTo(Condominium::class, 'condominium_id');
    }

    /**
     * @return HasMany<Property, $this>
     */
    public function properties(): HasMany
    {
        return $this->hasMany(Property::class, 'tower_id');
    }
}
