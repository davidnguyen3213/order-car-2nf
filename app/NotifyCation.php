<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NotifyCation extends Model
{
    protected $table = "notify_cations";
    protected $fillable = [
        'type', 'title', 'message'
    ];
    public function deviceToken()
    {
        return $this->belongsToMany(DeviceTokens::class, "device_notify", "notify_id", "device_id");
    }
}
