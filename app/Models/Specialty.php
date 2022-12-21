<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Specialty extends Model
{
    use HasFactory;

    protected $fillable = ["specialtyName"];
    protected $hidden = ['created_at', 'updated_at'];

    //Specialty is Foreign key in table "experts"
    public function expert()
    {
        return $this->hasMany(Expert::class);
    }

    //Specialty has Foreign key from table "X"
}
