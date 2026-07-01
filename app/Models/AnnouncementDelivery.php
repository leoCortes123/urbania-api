<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnnouncementDelivery extends Model
{
    use HasUuids;

    protected $table = 'announcement_deliveries';

    protected $fillable = [
        'id',
        'announcement_id',
        'contact_id',
        'canal',
        'estado',
        'external_id',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    /** @return BelongsTo<Announcement, $this> */
    public function announcement(): BelongsTo
    {
        return $this->belongsTo(Announcement::class);
    }

    /** @return BelongsTo<Contact, $this> */
    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }
}
