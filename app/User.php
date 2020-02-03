<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'phone', 'password', 'raw_pass', 'status', 'type', 'is_deleted'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'type', 'is_deleted'
    ];

    /**
     * Define relationship table request_users
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function requestUsers()
    {
        return $this->hasMany('App\RequestUser', 'user_id');
    }

    /**
     * Define relationship table fcm_tokens
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function userFcmTokens()
    {
        return $this->hasMany('App\FCMToken', 'uc_id')->where('type', \Config::get('constants.TYPE_NOTIFY.USER'));
    }
}
