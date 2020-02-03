<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RequestUser extends Model
{
    protected $fillable = [
        'user_id', 'address_from', 'address_to', 'latitude', 'longitude', 'address_note', 'created_time_request', 'is_expired', 'is_history_deleted', 'is_deleted_note', 'is_deleted_address', 'first_time_requested', 'is_cancel','appointment_time'
    ];

    /**
     * Define relationship table response_for_users
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function responseForUsers()
    {
        return $this->hasMany('App\ResponseForUser', 'request_id', 'id');
    }

    /**
     * Define relationship table CompanyReadRequest
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function companyReadRequest()
    {
        return $this->hasMany('App\CompanyReadRequest', 'request_id', 'id');
    }

    /**
     * Define relationship table users
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function users()
    {
        return $this->belongsto('App\User', 'user_id', 'id');
    }

    /**
     *
     * Scope a query to only include active search.
     * @param $query
     * @param $search
     * @return mixed
     */
    public function scopeSearchAddress($query, $search)
    {
        if (empty($search)) return $query->whereNotNull('address_from')
            ->where('address_from', '!=', '');

        return $query->whereNotNull('address_from')
            ->where('address_from', '!=', '')
            ->where(function ($query1) use ($search) {
                $arraySearch = explode(\Config::get('constants.DELIMITER'), $search);
                if (!empty($arraySearch)) {
                    $hasWhereRaw = false;
                    foreach ($arraySearch as $key => $area) {
                        if (strlen(trim($area)) == 0) {
                            continue;
                        }
                        if (strlen(trim(str_replace(' ', '', $area))) == 0) {
                            continue;
                        }
                        if (!$hasWhereRaw) {
                            $hasWhereRaw = true;
                            $query1->whereRaw("address_from LIKE '" . trim($area) . "%'")
                                ->orWhereRaw("'" . trim($area) . "' LIKE CONCAT(address_from, '%')");
                        } else {
                            $query1->orWhereRaw("address_from LIKE '" . trim($area) . "%'")
                                ->orWhereRaw("'" . trim($area) . "' LIKE CONCAT(address_from, '%')");
                        }
                    }
                } else {
                    $query1->whereRaw("address_from LIKE '" . $search . "%'")
                        ->orWhereRaw("'" . $search . "' LIKE CONCAT(address_from, '%')");
                }
            });
    }

    /**
     * Scope a query to only include active.
     *
     * @param $query
     * @return mixed
     */
    public function scopeActive($query)
    {
        return $query->where('is_expired', \Config::get('constants.REQUEST_USERS.UNEXPIRED'));
    }

    /**
     * Scope a query to only include active.
     *
     * @param $query
     * @return mixed
     */
    public function scopeUnexpired($query)
    {
        $minuteUserWait = subMinuteForRequest(\Config::get('constants.REQUEST_MINUTE_EXISTED.USER_WAIT'));
        $minuteCompanyWait = subMinuteForRequest(\Config::get('constants.REQUEST_MINUTE_EXISTED.COMPANY_WAIT'));
        return $query->where(function ($query1) use ($minuteUserWait, $minuteCompanyWait) {
            $query1->where(function ($query11) use ($minuteUserWait) {
                $query11->where('created_time_request', '>=', $minuteUserWait)
                    ->whereNull('first_time_requested');
            });
            $query1->orwhere(function ($query2) use ($minuteCompanyWait) {
                $query2->where(function ($query12) use ($minuteCompanyWait) {
                    $query12->where('first_time_requested', '>=', $minuteCompanyWait)
                        ->whereNotNull('first_time_requested');
                });
            });
        })
            ->active();
    }

    /**
     * Scope a query to only include active.
     *
     * @param $query
     * @return mixed
     */
    public function scopeExpired($query)
    {
        $minuteUserWait = subMinuteForRequest(\Config::get('constants.REQUEST_MINUTE_EXISTED.USER_WAIT'));
        $minuteCompanyWait = subMinuteForRequest(\Config::get('constants.REQUEST_MINUTE_EXISTED.COMPANY_WAIT'));

        return $query->where(function ($query1) use ($minuteUserWait, $minuteCompanyWait) {
            $query1->where(function ($query11) use ($minuteUserWait) {
                $query11->where('created_time_request', '<', $minuteUserWait)
                    ->whereNull('first_time_requested');
            });
            $query1->orwhere(function ($query2) use ($minuteCompanyWait) {
                $query2->where(function ($query12) use ($minuteCompanyWait) {
                    $query12->where('first_time_requested', '<', $minuteCompanyWait)
                        ->whereNotNull('first_time_requested');
                });
            });
        });
    }

    /**
     * Scope a query to only include active.
     *
     * @return mixed
     */
    public function activeUser()
    {
        return $this->users()->where('status', \Config::get('constants.USER_LOGIN.STATUS_ENABLE'))
            ->where('type', \Config::get('constants.TYPE_USER.OTHER'))
            ->where('is_deleted', \Config::get('constants.USER_DELETED.ACTIVE'));
    }

    /**
     * Scope a query to only include active.
     *
     * @return mixed
     */
    public function activeResponseUser()
    {
        return $this->responseForUsers()->where('is_deleted', \Config::get('constants.RESPONSE_FOR_USERS.NOT_DELETED'));
    }
}
