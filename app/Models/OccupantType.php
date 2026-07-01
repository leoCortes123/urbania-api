<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\OccupantTypeFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OccupantType extends Model
{
    /** @use HasFactory<OccupantTypeFactory> */
    use HasFactory, HasUuids;

    protected $table = 'occupant_types';

    protected $fillable = [
        'code',
        'name',
        'description',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];
}
