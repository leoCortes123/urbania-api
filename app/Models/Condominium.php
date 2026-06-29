<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\CondominiumFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Condominium extends Model
{
    /** @use HasFactory<CondominiumFactory> */
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'condominiums';

    public $incrementing = false;

    protected $keyType = 'string';

    /** @var list<string> */
    protected $fillable = [
        'id',
        'name',
        'address',
        'city',
        'department',
        'country',
        'nit',
        'phone',
        'email',
        'legal_representative',
        'total_coefficient',
        'logo_url',
        'is_active',
        'organization_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'total_coefficient' => 'decimal:6',
            'is_active' => 'boolean',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * @return HasMany<Tower, $this>
     */
    public function towers(): HasMany
    {
        return $this->hasMany(Tower::class, 'condominium_id');
    }

    /**
     * @return HasMany<Property, $this>
     */
    public function properties(): HasMany
    {
        return $this->hasMany(Property::class, 'condominium_id');
    }
}
