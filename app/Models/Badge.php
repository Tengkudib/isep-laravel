<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Badge extends Model
{
    protected $table = 'badges';

    protected $guarded = [];

    public $timestamps = false;
}
