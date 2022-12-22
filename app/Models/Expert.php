<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expert extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'specialty_id',
        'price',
        'description',
        'address',
        'specialization'
    ];

    protected $hidden = ['created_at', 'updated_at'];

    //Expert is Foreign key in table "times"
    public function time()
    {
        return $this->hasMany(Time::class);
    }

    //Expert has Foreign key from table "Specialteis" and "users"
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function specialty()
    {
        return $this->belongsTo(specialty::class);
    }
}
