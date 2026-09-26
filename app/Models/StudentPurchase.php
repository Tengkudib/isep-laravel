<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentPurchase extends Model
{
    protected $table = 'student_purchases';

    protected $guarded = [];

    public $timestamps = false;

    public function item(): BelongsTo
    {
        return $this->belongsTo(ShopItem::class, 'item_id');
    }
}
