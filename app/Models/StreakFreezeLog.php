<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StreakFreezeLog extends Model
{
    protected $table = 'streak_freeze_log';

    protected $guarded = [];

    public $timestamps = false;
}
