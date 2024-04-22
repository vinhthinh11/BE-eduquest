<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;


class student_notifications extends Model
{
    protected $table = "student_notifications";

    protected $fillable = [
        'ID',
        'notification_id',
        'class_id'
    ];
    public $timestamps = false;
}
