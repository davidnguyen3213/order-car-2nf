<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ResponseForUser extends Model
{
    protected $fillable = [
        'request_id', 'company_id', 'time_pickup', 'user_accept_time', 'status', 'is_approved', 'is_deleted', 'is_read'
    ];

    /**
     * Define relationship table request_users
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function requestUsers()
    {
        return $this->belongsto('App\RequestUser', 'request_id', 'id');
    }

    /**
     * Active table response_for_user with not deleted
     *
     * @param $query
     * @return mixed
     */
    public function scopeActive($query)
    {
        return $query->where('is_deleted', \Config::get('constants.RESPONSE_FOR_USERS.NOT_DELETED'));
    }

    /**
     * Unread record
     *
     * @param $query
     * @return mixed
     */
    public function scopeUnRead($query)
    {
        return $query->where('is_read', \Config::get('constants.RESPONSE_FOR_USERS.UNREAD'))
            ->active();
    }

    /**
     * Define relationship table companies
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function responseForUserOfCompany()
    {
        return $this->belongsto('App\Company', 'company_id', 'id')
            ->where('status_notify', \Config::get('constants.NOTIFY.ON'))
            ->where('status_login', \Config::get('constants.COMPANY_LOGIN.STATUS_ENABLE'))
            ->where('is_deleted', \Config::get('constants.COMPANY_DELETED.ACTIVE'));
    }
}
