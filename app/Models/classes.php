<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class classes extends Model
{
    protected $table = 'classes';
    protected $fillable = [
        'class_id',
        'gende_id',
        'class_name',
        'teacher_id'
    ];
    public $timestamps = false;
    protected $primaryKey = 'class_id';


    function getClasses()
    {
        $getClasses = DB::select('select * from classes');
        return $getClasses;
    }
}
