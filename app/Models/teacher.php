<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Contracts\JWTSubject;

class teacher extends  Authenticatable implements JWTSubject
{
    use Notifiable;
    protected $table = 'teachers';
    protected $fillable = [
        'teacher_id',
        'name',
        'username',
        'gender_id',
        'password',
        'email',
        'permission',
        'avatar',
        'birthday',
        'last_login'
    ];
    public $timestamps = false;
    public function questions()
    {
        return $this->hasMany(Questions::class, 'teacher_id');
    }
    protected $primaryKey = 'teacher_id';


    function getTeacher()
    {
        $getTeacher = DB::select('select * from teachers');
        return $getTeacher;
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
}
