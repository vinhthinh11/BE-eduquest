<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Tymon\JWTAuth\Contracts\JWTSubject;
use DB;

class notifications extends Model
{
    protected $table = "notifications";

    protected $fillable = [
        'notification_id',
        'username',
        'name',
        'notification_title',
        'notification_content',
        'time_sent'
    ];
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
    
}
