<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class teacher extends Model
{
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
    protected $primaryKey = 'teacher_id';


    function getTeacher()
    {
        $getTeacher = DB::select('select * from teachers');
        return $getTeacher;
    }
}
