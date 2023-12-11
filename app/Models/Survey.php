<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Survey extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'image',
        'status',
        'description' ,
        'expire_date'  ,
        'questions',
    ];


    public function questions(): HasMany
    {
        return $this->hasMany(SurveyQuestion::class);
    }
}
