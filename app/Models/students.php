<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\User as Authenticatable;

class students extends Model
{
    protected $table = 'students';
    protected $fillable = [
        'student_id',
        'name',
        'username',
        'gender_id',
        'password',
        'email',
        'permission',
        'avatar',
        'birthday',

    ];
    public $timestamps = false;
    protected $primaryKey = 'student_id';


    function getHS()
    {
        $getAllHS = DB::select('select * from students');
        return $getAllHS;
    }

    
}
