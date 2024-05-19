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
        'grade_id',
        'class_name',
        'teacher_id'
    ];
    public $timestamps = false;
    protected $primaryKey = 'class_id';

    public function students()
    {
        return $this->hasMany(Student::class, 'class_id', 'class_id');
    }
    function getClasses()
    {
        $getClasses = DB::select('select * from classes');
        return $getClasses;
    }
    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function studentNotifications()
    {
        return $this->hasMany(student_notifications::class, 'class_id', 'class_id');
    }

}
