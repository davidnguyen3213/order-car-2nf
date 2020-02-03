<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class Company extends Authenticatable
{
    use Notifiable, HasApiTokens;
    protected $table = 'companies';

    protected $fillable = [
        'name', 'address', 'phone', 'phone_to_login', 'email', 'password', 'raw_pass', 'company_pr', 'base_price', 'person_charged', 'status_notify', 'status_login', 'corresponding_area', 'is_deleted'
    ];

    protected $hidden = [
        'password', 'is_deleted'
    ];

    /**
     * Scope a query to only include active.
     *
     * @param $query
     * @return mixed
     */
    public function scopeActive($query)
    {
        return $query->where('status_login', \Config::get('constants.COMPANY_LOGIN.STATUS_ENABLE'))
            ->where('is_deleted', \Config::get('constants.COMPANY_DELETED.ACTIVE'));
    }

    /**
     * Scope a query to only include enable.
     *
     * @param $query
     * @return mixed
     */
    public function scopeEnable($query)
    {
        return $query->where('status_login', \Config::get('constants.COMPANY_LOGIN.STATUS_ENABLE'))
            ->where('is_deleted', \Config::get('constants.COMPANY_DELETED.ACTIVE'));
    }

    /**
     * Define relationship table fcm_tokens
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function companyFcmTokens()
    {
        return $this->hasMany('App\FCMToken', 'uc_id')->where('type', \Config::get('constants.TYPE_NOTIFY.COMPANY'));
    }

    /**
     * Define relationship table response_for_users
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function companyResponseUser()
    {
        return $this->hasMany('App\ResponseForUser', 'company_id');
    }

    /**
     * Define relationship table fcm_tokens
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function correspondingArea()
    {
        return $this->hasMany('App\CorrespondingArea', 'company_id')->where('type', \Config::get('constants.TYPE_AREA.COMPANY'));
    }
}
