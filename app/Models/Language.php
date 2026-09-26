<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Language extends Model
{
    protected $table = 'languages';

    protected $guarded = [];

    public $timestamps = false;

    public function chapters(): HasMany
    {
        return $this->hasMany(Chapter::class, 'language_id');
    }
}
