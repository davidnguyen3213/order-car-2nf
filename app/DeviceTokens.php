<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\NotifyCation;
class DeviceTokens extends Model
{
    protected $table = "device_tokens";
    protected $fillable = [
        "platform",'device_token','type','uc_id'
    ];

    public function notifyCation(){
        return $this->belongsToMany(NotifyCation::class, "device_notify", "device_id", "notify_id");
    }
}
