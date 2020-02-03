<?php

namespace App\Repository\Eloquent;

use App;
use App\Repository\Contracts\ResponseForUserInterface as ResponseForUserInterface;

class ResponseForUserRepository extends BaseRepository implements ResponseForUserInterface
{

    const COMPANY = 'companies';
    const REQUEST_USER = 'request_users';
    const RESPONSE_FOR_USER = 'response_for_users';
    const USER = 'users';

    protected function model()
    {
        return \App\ResponseForUser::class;
    }

    protected function getRules()
    {
        return \App\ResponseForUser::rules;
    }

    /**
     * Get history request for company
     *
     * @param $userID
     * @param $pagination
     * @return mixed
     */
    public function listHistoryByUser($userID, $pagination)
    {
        $result = \DB::table(self::REQUEST_USER)
            ->join(self::RESPONSE_FOR_USER, self::REQUEST_USER . '.id', '=', self::RESPONSE_FOR_USER . '.request_id')
            ->leftjoin(self::COMPANY, self::COMPANY . '.id', '=', self::RESPONSE_FOR_USER . '.company_id')
            ->select(self::REQUEST_USER . '.*',
                self::RESPONSE_FOR_USER . '.company_id',
                self::RESPONSE_FOR_USER . '.user_accept_time',
                self::RESPONSE_FOR_USER . '.time_pickup',
                self::RESPONSE_FOR_USER . '.status',
                self::RESPONSE_FOR_USER . '.is_approved',
                self::COMPANY . '.name as name_company',
                self::COMPANY . '.address as address_company',
                self::COMPANY . '.phone as phone_company',
                self::COMPANY . '.email as email_company',
                self::COMPANY . '.base_price as base_price',
                self::COMPANY . '.person_charged as person_charged',
                self::COMPANY . '.company_pr as company_pr'
            );

        $result = $result->where(self::REQUEST_USER . '.user_id', '=', $userID)
            ->where(self::REQUEST_USER . '.is_expired', '=', \Config::get('constants.REQUEST_USERS.EXPIRED'))
            ->where(self::REQUEST_USER . '.is_history_deleted', '=', \Config::get('constants.REQUEST_USERS.IS_HISTORY_ACTIVE'))
            ->where(self::RESPONSE_FOR_USER . '.is_deleted', '=', \Config::get('constants.RESPONSE_FOR_USERS.NOT_DELETED'))
            ->where(self::RESPONSE_FOR_USER . '.status', '=', \Config::get('constants.RESPONSE_FOR_USERS.STATUS_ACCEPTED'))
            ->orderBy(self::REQUEST_USER . '.created_time_request', 'desc');

        if (isset($pagination['offset'])) {
            $result = $result->offset((int)$pagination['offset']);
        }
        if (isset($pagination['limit']) && $pagination['limit']) {
            $result = $result->limit((int)$pagination['limit']);
        }
        $result = $result->get();

        return $result->toArray();
    }

    /**
     * Get list response of company by request id
     *
     * @param array $request [user_id, request_id]
     * @return mixed
     */
    public function getListResponseByRequestId($request)
    {
        $result = \DB::table(self::RESPONSE_FOR_USER)
            ->join(self::REQUEST_USER, self::REQUEST_USER . '.id', '=', self::RESPONSE_FOR_USER . '.request_id')
            ->leftjoin(self::COMPANY, self::COMPANY . '.id', '=', self::RESPONSE_FOR_USER . '.company_id')
            ->select(
                self::RESPONSE_FOR_USER . '.company_id',
                self::RESPONSE_FOR_USER . '.time_pickup',
                self::REQUEST_USER . '.created_time_request',
                self::REQUEST_USER . '.appointment_time',
                self::COMPANY . '.name as name_company',
                self::COMPANY . '.address as address_company',
                self::COMPANY . '.phone as phone_company',
                self::COMPANY . '.base_price as base_price',
                self::COMPANY . '.company_pr as company_pr'
            );

        $result = $result->where(self::REQUEST_USER . '.user_id', '=', $request['user_id'])
            ->where(self::REQUEST_USER . '.is_expired', '=', \Config::get('constants.REQUEST_USERS.UNEXPIRED'))
            ->where(self::RESPONSE_FOR_USER . '.request_id', '=', $request['request_id'])
            ->where(self::RESPONSE_FOR_USER . '.is_deleted', '=', \Config::get('constants.RESPONSE_FOR_USERS.NOT_DELETED'));

        $result = $result->get();

        return $result->toArray();
    }

    /**
     * Get list response of company (was accepted by user) by company id
     *
     * @param int $companyId
     * @param array $pagination
     * @return mixed
     */
    public function getCompanyRequestHistory($companyId, $pagination)
    {
        $result = \DB::table(self::REQUEST_USER)
            ->join(self::RESPONSE_FOR_USER, self::RESPONSE_FOR_USER . '.request_id', '=', self::REQUEST_USER . '.id')
            ->leftjoin(self::USER, self::USER . '.id', '=', self::REQUEST_USER . '.user_id')
            ->select(
                self::RESPONSE_FOR_USER . '.id',
                self::USER . '.name as user_name',
                self::USER . '.phone as user_phone',
                self::REQUEST_USER . '.user_id',
                self::REQUEST_USER . '.created_time_request',
                self::REQUEST_USER . '.appointment_time',
                self::REQUEST_USER . '.address_from',
                self::REQUEST_USER . '.address_to',
                self::REQUEST_USER . '.latitude',
                self::REQUEST_USER . '.longitude',
                self::REQUEST_USER . '.address_note',
                self::RESPONSE_FOR_USER . '.time_pickup',
                self::RESPONSE_FOR_USER . '.user_accept_time',
                self::RESPONSE_FOR_USER . '.is_approved'
            );

        $result = $result->where(self::RESPONSE_FOR_USER . '.company_id', '=', $companyId)
            ->where(self::RESPONSE_FOR_USER . '.status', '=', \Config::get('constants.RESPONSE_FOR_USERS.STATUS_ACCEPTED'))
            ->where(self::REQUEST_USER . '.is_expired', '=', \Config::get('constants.REQUEST_USERS.EXPIRED'))
            ->orderBy(self::REQUEST_USER . '.created_time_request', 'desc');

        if (isset($pagination['offset'])) {
            $result = $result->offset((int)$pagination['offset']);
        }
        if (isset($pagination['limit']) && $pagination['limit']) {
            $result = $result->limit((int)$pagination['limit']);
        }
        $result = $result->get();

        return $result->toArray();
    }

    /**
     * Get count unread response of user
     *
     * @param int $userId
     * @return mixed
     */
    public function countUnreadResponseOfUser($userId) {
        $result = \DB::table(self::RESPONSE_FOR_USER)
            ->join(self::REQUEST_USER, self::RESPONSE_FOR_USER . '.request_id', '=', self::REQUEST_USER . '.id')
            ->where(self::REQUEST_USER . '.user_id', '=', $userId)
            ->where(self::REQUEST_USER . '.is_expired', '=', \Config::get('constants.REQUEST_USERS.UNEXPIRED'))
            ->where(self::RESPONSE_FOR_USER . '.is_deleted', '=', \Config::get('constants.RESPONSE_FOR_USERS.NOT_DELETED'))
            ->where(self::RESPONSE_FOR_USER . '.is_read', '=', \Config::get('constants.RESPONSE_FOR_USERS.UNREAD'));
       return $result->count();
    }

    /**
     * Get count unapproved request of company
     *
     * @param int $userId
     * @return mixed
     */
    public function countUnapprovedRequest($companyId)
    {
        $result = \DB::table(self::RESPONSE_FOR_USER)
            ->join(self::REQUEST_USER, self::RESPONSE_FOR_USER . '.request_id', '=', self::REQUEST_USER . '.id')
            ->where(self::RESPONSE_FOR_USER . '.company_id', '=', $companyId)
            ->where(self::REQUEST_USER . '.is_expired', '=', \Config::get('constants.REQUEST_USERS.EXPIRED'))
            ->where(self::RESPONSE_FOR_USER . '.is_deleted', '=', \Config::get('constants.RESPONSE_FOR_USERS.NOT_DELETED'))
            ->where(self::RESPONSE_FOR_USER . '.is_approved', '=', \Config::get('constants.RESPONSE_FOR_USERS.UNAPPROVED'))
            ->where(self::RESPONSE_FOR_USER . '.status', '=', \Config::get('constants.RESPONSE_FOR_USERS.STATUS_ACCEPTED'));
        return $result->count();
    }
    /**
     * Get list of company
     *
     * @param int $requestId
     * @return mixed
     */
    public function getListResponseCompany($requestId)
    {
        $result = \DB::table(self::RESPONSE_FOR_USER)->where('request_id', $requestId)
                    ->join(self::COMPANY, self::COMPANY.".id","=", self::RESPONSE_FOR_USER.".company_id")
                    ->select(self::COMPANY.".name", self::RESPONSE_FOR_USER.".time_pickup")
                    ->get();
        return $result;
    }
}
