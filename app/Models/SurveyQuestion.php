<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SurveyQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'question',
        'description',
        'data',
        'survey_id'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function survey(): BelongsTo
    {
        return $this->belongsTo(Survey::class);
    }

}
