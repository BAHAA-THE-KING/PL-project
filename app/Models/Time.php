<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class time extends Model
{
    use HasFactory;

    protected $fillable = ["expert_id", "day", "start", "end"];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
