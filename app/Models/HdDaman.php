<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class HdDaman extends Model
{
    use HasFactory;

    protected $table = 'hd_damans';
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}