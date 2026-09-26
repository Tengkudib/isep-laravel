<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Chapter extends Model
{
    protected $table = 'chapters';

    protected $guarded = [];

    public $timestamps = false;

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class, 'language_id');
    }

    public function contents(): HasMany
    {
        return $this->hasMany(LearningContent::class, 'chapter_id');
    }

    public function exercises(): HasMany
    {
        return $this->hasMany(Exercise::class, 'chapter_id');
    }

    public function quizzes(): HasMany
    {
        return $this->hasMany(Quiz::class, 'chapter_id');
    }
}
