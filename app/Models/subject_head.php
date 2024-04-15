<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;

class subject_head extends  Authenticatable implements JWTSubject
{
    protected $table = 'subject_head';
    protected $fillable = [
        'subject_head_id',
        'name',
        'username',
        'gender_id',
        'password',
        'email',
        'permission',
        'avatar',
        'birthday',
        'last_login',
        'subject_id'

    ];
    public $timestamps = false;
    protected $primaryKey = 'subject_head_id';


    function getTBM()
    {
        $getAllTBM = DB::select('select * from subject_head');
        return $getAllTBM;
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
