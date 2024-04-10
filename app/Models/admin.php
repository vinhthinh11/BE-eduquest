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

    public function update_avatar($avatar, $username)
    {
        $admin = admin::where('username', $username)->first();

        if ($admin) {
            $admin->avatar = $avatar;
            $admin->save();
            return true;
        }

        return false;
    }

    // public function update_profile($username, $name, $email, $password, $gender, $birthday)
    // {
    //     $password = password_hash($password, PASSWORD_BCRYPT);

    //     $result = DB::table($this->table)
    //         ->where('username', $username)
    //         ->update([
    //             'email' => $email,
    //             'password' => $password,
    //             'name' => $name,
    //             'gender_id' => $gender,
    //             'birthday' => $birthday
    //         ]);

    //     return $result;
    // }

    // public function update_last_login($adminId)
    // {
    //     $result = DB::table($this->table)
    //         ->where('admin_id', $adminId)
    //         ->update(['last_login' => now()]);

    //     return $result;
    // }
}
