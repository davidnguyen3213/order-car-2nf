<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FCMToken extends Model
{
    protected $table = 'fcm_tokens';

    protected $fillable = [
        'uc_id', 'company_id', 'platform', 'device_token', 'type', 'is_deleted'
    ];

    /**
     * Define relationship table users
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function users()
    {
        return $this->belongsto('App\User', 'uc_id', 'id')
            ->fcmTokenUsers();
    }

    /**
     * Define relationship table company
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function companies()
    {
        return $this->belongsto('App\Company', 'uc_id', 'id')
            ->fcmTokenCompanies();
    }

    /**
     * Get token Company
     *
     * @param $query
     * @return mixed
     */
    public function scopeFcmTokenCompanies($query)
    {
        return $query->where('type', \Config::get('constants.TYPE_NOTIFY.COMPANY'));
    }

    /**
     * Get token User
     *
     * @param $query
     * @return mixed
     */
    public function scopeFcmTokenUsers($query)
    {
        return $query->where('type', \Config::get('constants.TYPE_NOTIFY.USER'));
    }
}
