<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class student_notifications extends Model
{
    protected $table = "student_notifications";

    protected $fillable = [
        'notification_id',
        'class_id'
    ];
}
