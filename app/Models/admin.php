<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;

class admin extends  Authenticatable implements JWTSubject
{
    use Notifiable;
    protected $table = 'admins';
    protected $fillable = [
        'admin_id',
        'username',
        'email',
        'password',
        'name',
        'permission',
        'last_login',
        'gender_id',
        'avatar',
        'birthday'
    ];
    public $timestamps = false;
    protected $primaryKey = 'admin_id';

    //
    function getAdmin()
    {
        $getAllAdmin = DB::select('select * from admins');
        return $getAllAdmin;
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
