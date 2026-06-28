<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\PropertyDocumentFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PropertyDocument extends Model
{
    /** @use HasFactory<PropertyDocumentFactory> */
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'property_documents';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'id',
        'property_id',
        'property_document_type_id',
        'name',
        'file_url',
        'file_size_bytes',
        'mime_type',
        'notes',
        'uploaded_by_user_id',
        'created_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'file_size_bytes' => 'integer',
            'created_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Property, $this>
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class, 'property_id');
    }

    /**
     * @return BelongsTo<PropertyDocumentType, $this>
     */
    public function documentType(): BelongsTo
    {
        return $this->belongsTo(PropertyDocumentType::class, 'property_document_type_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id');
    }
}
