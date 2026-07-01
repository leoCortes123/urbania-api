<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SurveyResponse extends Model
{
    use HasUuids;

    protected $table = 'survey_responses';

    protected $fillable = [
        'id',
        'survey_id',
        'contact_id',
        'option_id',
    ];

    /** @return BelongsTo<Survey, $this> */
    public function survey(): BelongsTo
    {
        return $this->belongsTo(Survey::class);
    }

    /** @return BelongsTo<Contact, $this> */
    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    /** @return BelongsTo<SurveyOption, $this> */
    public function option(): BelongsTo
    {
        return $this->belongsTo(SurveyOption::class, 'option_id');
    }
}
