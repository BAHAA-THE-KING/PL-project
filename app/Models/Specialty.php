<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Specialty extends Model
{
    use HasFactory;

    protected $fillable = ["specialtyName"];

    //Specialty is Foreign key in table "experts"
    public function expert()
    {
        return $this->hasMany(Expert::class);
    }
    
    //Specialty has Foreign key from table "X"
}
