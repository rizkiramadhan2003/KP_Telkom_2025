<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FalloutReport extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function hdDaman(): BelongsTo
    {
        return $this->belongsTo(HdDaman::class);
    }

    public function orderType(): BelongsTo
    {
        return $this->belongsTo(OrderType::class);
    }

    public function falloutStatus(): BelongsTo
    {
        return $this->belongsTo(FalloutStatus::class);
    }
}