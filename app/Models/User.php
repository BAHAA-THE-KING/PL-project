<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use PhpParser\Node\Expr\FuncCall;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'phone',
        'password',
        'image'
    ];
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'remember_token',
        'password',
        'money',
        'created_at',
        'updated_at'
    ];
    public function expert()
    {
        return $this->hasMany(Expert::class);
    }

    public function favorite()
    {
        return $this->hasMany(Favorite::class);
    }
    //Expert is Foreign key in table "times"
    public function times()
    {
        return $this->hasMany(Time::class);
    }
    public function lovedExperts()
    {
        return $this->hasMany(Favorite::class, 'expert_id');
    }
    public function setPasswordAttribute($password)
    {
        $this->attributes['password'] = bcrypt($password);
    }
    public function scopeFilter($query, array $data)
    {
        $query->when($data['phone'] ?? false, fn ($query)
        => $query->where('phone', $data['phone']));

        $query->when($data['password'] ?? false, fn ($query)
        => $query->where('password', bcrypt($data['password'])));
    }
}
