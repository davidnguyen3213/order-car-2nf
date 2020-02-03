<?php

namespace App\Repository\Eloquent;

use App;
use App\Repository\Contracts\RequestUserInterface as RequestUserInterface;
use App\RequestUser;
use App\ResponseForUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RequestUserRepository extends BaseRepository implements RequestUserInterface
{

    // 依頼
    const REQUEST_USERS_REQUEST = 1;
    // 返答中
    const REQUEST_USERS_REPLY = 2;
    // 配車
    const REQUEST_USERS_APROVEL = 3;
    // キャンセル
    const REQUEST_USERS_CANCEL = 4;
    // 時間経過
    const REQUEST_USERS_EXPIRED = 5;

    const COMPANY_READ_REQUEST = 'company_read_request';
    const REQUEST_USER = 'request_users';

    protected function model()
    {
        return RequestUser::class;
    }

    protected function getRules()
    {
        return RequestUser::rules;
    }

    /**
     * Get request by requestID
     *
     * @param $requestID
     * @return mixed
     */
    public function requestUser($requestID)
    {
        $requestUser = RequestUser::where('id', $requestID)
            ->unexpired();

        return $requestUser->first();
    }

    /**
     * Get list request follow address
     *
     * @param $address
     * @param $companyID
     * @return mixed
     */
    public function listRequestUserSearchAddress($address, $companyID)
    {
        $listRequestUser = RequestUser::searchAddress(escape_like($address))
            ->with('users')
            ->unexpired()
            ->with(['responseForUsers' => function ($query) use ($companyID) {
                $query->where('is_approved', \Config::get('constants.RESPONSE_FOR_USERS.UNAPPROVED'))
                    ->where('company_id', $companyID);
            }])
            ->orderBy('created_time_request', 'desc');

        return $listRequestUser->get([
            'id',
            'user_id',
            'address_from',
            'address_to',
            'latitude',
            'longitude',
            'address_note',
            'created_time_request',
            'first_time_requested',
            'appointment_time',
            'created_at',
            'updated_at'
        ]);
    }

    /**
     * Count UnRequest By Address
     *
     * @param $address
     * @param $companyID
     * @return mixed
     */
    public function countUnreadByAddress($address, $companyID)
    {
        $listRequestUser = RequestUser::searchAddress(escape_like($address))
            ->leftJoin(DB::raw('(
                SELECT COUNT(*) as is_read, request_id, company_id
                FROM ' . self::COMPANY_READ_REQUEST . '
                WHERE company_id = ' . $companyID . '
                GROUP by request_id, company_id
                ) rq'), function ($join) {
                $join->on(self::REQUEST_USER . '.id', '=', 'rq.request_id');
            })
            ->unexpired()
            ->where('rq.is_read', '=', null);

        return $listRequestUser->count();
    }

    /**
     * Get frequency user
     *
     * @param $userID
     * @param $columnGroupBy
     * @return mixed
     */
    public function listFrequencyRequestUser($userID, $columnGroupBy, $columnCheckDeleted)
    {
        $requestUser = new RequestUser;
        $nameTable = $requestUser->getTable();

        $frequencyUser = RequestUser::where('user_id', $userID);
        if ($columnGroupBy) {
            if (Schema::hasColumn($nameTable, $columnGroupBy)) {
                $frequencyUser->groupBy($columnGroupBy)
                    ->select($columnGroupBy . " as frequency", \DB::raw('count(*) as count'), \DB::raw('MAX(created_at) as created_at'))
                    ->where($columnCheckDeleted, \Config::get('constants.REQUEST_USERS.IS_ACTIVE_FREQUENCY'))
                    ->orderBy('created_at', 'DESC')
                    ->orderBy('count', 'DESC');
            }
        }

        return $frequencyUser->get();
    }

    /**
     * Get list unexpired request
     *
     * @param $userId
     * @return mixed
     */
    public function listUnexpiredRequestOfUser($userId)
    {
        $listRequestUser = RequestUser::where('user_id', $userId)
            ->unexpired()
            ->orderBy('created_time_request', 'DESC');

        return $listRequestUser->get();
    }

    public function searchWithRequestUser(array $searchCondition = [], int $limit = 0, int $offset = 0, array $orderBy = [])
    {
        $requestUserModel = DB::table('view_request_user')
            ->join("users", "view_request_user.request_users_user_id", '=', "users.id")
            ->leftjoin("companies", "view_request_user.response_for_users_company_id", '=', "companies.id")
            ->join(DB::raw('(SELECT request_users_id, MAX(response_for_users_status) as response_for_users_status  FROM `view_request_user` GROUP BY request_users_id)
               vru'),
                function($join)
                {
                    $join->on('view_request_user.request_users_id', '=', 'vru.request_users_id');
                    $join->on('view_request_user.response_for_users_status', '=', 'vru.response_for_users_status');
                })
            ->where('users.is_deleted', '=', \Config::get('constants.USER_DELETED.ACTIVE'));

        // table request user
        $requestUserStartDate = isset($searchCondition["request_user_start_date"]) ? $searchCondition["request_user_start_date"] : null;
        $requestUserEndDate = isset($searchCondition["request_user_end_date"]) ? $searchCondition["request_user_end_date"] : null;
        $requestUserName = isset($searchCondition["request_user_name"]) ? $searchCondition["request_user_name"] : null;
        $requestCompanyName = isset($searchCondition["request_company_name"]) ? $searchCondition["request_company_name"] : null;
        $requestUserHasStatus = isset($searchCondition["request_user_has_status"]) ? $searchCondition["request_user_has_status"] : null;

        if ($requestUserStartDate != null) {
            $requestUserModel = $requestUserModel->where(DB::raw("(DATE_FORMAT(view_request_user.request_users_created_at, '%Y/%m/%d'))"), ">=", $requestUserStartDate);
        }

        if ($requestUserEndDate != null) {
            $requestUserModel = $requestUserModel->where(DB::raw("(DATE_FORMAT(view_request_user.request_users_created_at, '%Y/%m/%d'))"), "<=", $requestUserEndDate);
        }

        if ($requestUserName != null) {
            $requestUserModel = $requestUserModel->where('users.name', "like", "%{$requestUserName}%");
        }

        if ($requestCompanyName != null) {
            $requestUserModel = $requestUserModel->whereNotNull('view_request_user.response_for_users_request_id')
                ->where('companies.name', "like", "%{$requestCompanyName}%")
                ->where('view_request_user.response_for_users_status', '=', 1);
        }

        if ($requestUserHasStatus != null) {
            if ($requestUserHasStatus == RequestUserRepository::REQUEST_USERS_REQUEST) {
                $requestUserModel = $requestUserModel->whereNull('view_request_user.response_for_users_id')
                    ->where('view_request_user.request_users_is_expired', '=', 0);
            }

            if ($requestUserHasStatus == RequestUserRepository::REQUEST_USERS_REPLY) {
                $requestUserModel = $requestUserModel->whereNotNull('view_request_user.response_for_users_request_id')
                    ->where('view_request_user.request_users_is_expired', '=', 0)
                    ->where('view_request_user.response_for_users_status', '=', 0);
            }

            if ($requestUserHasStatus == RequestUserRepository::REQUEST_USERS_APROVEL) {
                $requestUserModel = $requestUserModel->whereNotNull('view_request_user.response_for_users_request_id')
                    ->where('view_request_user.response_for_users_status', '=', 1);
            }
            if ($requestUserHasStatus == RequestUserRepository::REQUEST_USERS_EXPIRED) {
                $requestUserModel = $requestUserModel->where(function ($query) {
                    $query->whereNotNull('view_request_user.response_for_users_request_id')
                        ->where('view_request_user.response_for_users_status', '=', 0)
                        ->where('view_request_user.request_users_is_expired', '=', 1)
                        ->where('view_request_user.request_users_is_cancel', '=', 0)
                        ->orWhereNull('view_request_user.response_for_users_request_id')
                        ->where('view_request_user.request_users_is_expired', '=', 1)
                        ->where('view_request_user.request_users_is_cancel', '=', 0);
                });
            }
            if ($requestUserHasStatus == RequestUserRepository::REQUEST_USERS_CANCEL) {
                $requestUserModel = $requestUserModel->where(function ($query) {
                    $query->whereNotNull('view_request_user.response_for_users_request_id')
                        ->where('view_request_user.response_for_users_status', '=', 0)
                        ->where('view_request_user.request_users_is_expired', '=', 1)
                        ->where('view_request_user.request_users_is_cancel', '=', 1)
                        ->orWhereNull('view_request_user.response_for_users_request_id')
                        ->where('view_request_user.request_users_is_expired', '=', 1)
                        ->where('view_request_user.request_users_is_cancel', '=', 1);
                });
            }
        }

        if ($offset) {
            $requestUserModel = $requestUserModel->offset($offset);
        }

        if ($limit) {
            $requestUserModel = $requestUserModel->limit($limit);
        }

        if ($orderBy) {
            $requestUserModel->orderBy($orderBy[0], $orderBy[1]);
        }

        $results = $requestUserModel
            ->select(['view_request_user.*', 'users.name as request_user_user_name',
                "companies.name as request_user_company_name"
                ])
            ->groupBy('view_request_user.request_users_id')
            ->get();

        foreach ($results as &$result) {
            $query = ResponseForUser::query();
            $result->count_response = $query->where(["request_id" => $result->request_users_id])->count();

            $companyName = $result->request_user_company_name;
            $result->request_user_company_name = "";
            $result->request_user_has_status_name = "";

            if ($result->response_for_users_id == null && $result->request_users_is_expired == 0) {
                $result->request_user_has_status_name = RequestUserRepository::REQUEST_USERS_REQUEST;
            }

            if ($result->response_for_users_request_id != null && $result->request_users_is_expired == 0 && $result->response_for_users_status == 0) {
                $result->request_user_has_status_name = RequestUserRepository::REQUEST_USERS_REPLY;
            }

            if ($result->response_for_users_request_id != null && $result->response_for_users_status == 1) {
                $result->request_user_has_status_name = RequestUserRepository::REQUEST_USERS_APROVEL;
                $result->request_user_company_name = $companyName;
            }

            if (($result->response_for_users_request_id != null && $result->response_for_users_status == 0 && $result->request_users_is_expired == 1 && $result->request_users_is_cancel == 0)
                || ($result->response_for_users_request_id == null && $result->request_users_is_expired == 1 && $result->request_users_is_cancel == 0)) {
                $result->request_user_has_status_name = RequestUserRepository::REQUEST_USERS_EXPIRED;
            }

            if (($result->response_for_users_request_id != null && $result->response_for_users_status == 0 && $result->request_users_is_expired == 1 && $result->request_users_is_cancel == 1)
                || ($result->response_for_users_request_id == null && $result->request_users_is_expired == 1 && $result->request_users_is_expired == 1 && $result->request_users_is_cancel == 1)
            ) {
                $result->request_user_has_status_name = RequestUserRepository::REQUEST_USERS_CANCEL;
            }
        }

        if ($results && count($results) > 0) {
            return $results;
        } else {
            return array();
        }
    }

    public function countSearchWithRequestUser(array $searchCondition = [])
    {
        $requestUserModel = DB::table('view_request_user')
            ->join("users", "view_request_user.request_users_user_id", '=', "users.id")
            ->leftjoin("companies", "view_request_user.response_for_users_company_id", '=', "companies.id")
            ->where('users.is_deleted', '=', config('constants.USER_DELETED.ACTIVE'));

        // table request user
        $requestUserStartDate = isset($searchCondition["request_user_start_date"]) ? $searchCondition["request_user_start_date"] : null;
        $requestUserEndDate = isset($searchCondition["request_user_end_date"]) ? $searchCondition["request_user_end_date"] : null;
        $requestUserName = isset($searchCondition["request_user_name"]) ? $searchCondition["request_user_name"] : null;
        $requestCompanyName = isset($searchCondition["request_company_name"]) ? $searchCondition["request_company_name"] : null;
        $requestUserHasStatus = isset($searchCondition["request_user_has_status"]) ? $searchCondition["request_user_has_status"] : null;

        if ($requestUserStartDate != null) {
            $requestUserModel = $requestUserModel->where(DB::raw("(DATE_FORMAT(view_request_user.request_users_created_at, '%Y/%m/%d'))"), ">=", $requestUserStartDate);
        }

        if ($requestUserEndDate != null) {
            $requestUserModel = $requestUserModel->where(DB::raw("(DATE_FORMAT(view_request_user.request_users_created_at, '%Y/%m/%d'))"), "<=", $requestUserEndDate);
        }

        if ($requestUserName != null) {
            $requestUserModel = $requestUserModel->where('users.name', "like", "%{$requestUserName}%");
        }

        if ($requestCompanyName != null) {
            $requestUserModel = $requestUserModel->whereNotNull('view_request_user.response_for_users_request_id')
                ->where('companies.name', "like", "%{$requestCompanyName}%")
                ->where('view_request_user.response_for_users_status', '=', 1);
        }

        if ($requestUserHasStatus != null) {
            if ($requestUserHasStatus == RequestUserRepository::REQUEST_USERS_REQUEST) {
                $requestUserModel = $requestUserModel->whereNull('view_request_user.response_for_users_id')
                    ->where('view_request_user.request_users_is_expired', '=', 0);
            }

            if ($requestUserHasStatus == RequestUserRepository::REQUEST_USERS_REPLY) {
                $requestUserModel = $requestUserModel->whereNotNull('view_request_user.response_for_users_request_id')
                    ->where('view_request_user.request_users_is_expired', '=', 0)
                    ->where('view_request_user.response_for_users_status', '=', 0);
            }

            if ($requestUserHasStatus == RequestUserRepository::REQUEST_USERS_APROVEL) {
                $requestUserModel = $requestUserModel->whereNotNull('view_request_user.response_for_users_request_id')
                    ->where('view_request_user.response_for_users_status', '=', 1);
            }

            if ($requestUserHasStatus == RequestUserRepository::REQUEST_USERS_EXPIRED) {
                $requestUserModel = $requestUserModel->where(function ($query) {
                    $query->whereNotNull('view_request_user.response_for_users_request_id')
                        ->where('view_request_user.response_for_users_status', '=', 0)
                        ->where('view_request_user.request_users_is_expired', '=', 1)
                        ->where('view_request_user.request_users_is_cancel', '=', 0)
                        ->orWhereNull('view_request_user.response_for_users_request_id')
                        ->where('view_request_user.request_users_is_expired', '=', 1)
                        ->where('view_request_user.request_users_is_cancel', '=', 0);
                });
            }

            if ($requestUserHasStatus == RequestUserRepository::REQUEST_USERS_CANCEL) {
                $requestUserModel = $requestUserModel->where(function ($query) {
                    $query->whereNotNull('view_request_user.response_for_users_request_id')
                        ->where('view_request_user.response_for_users_status', '=', 0)
                        ->where('view_request_user.request_users_is_expired', '=', 1)
                        ->where('view_request_user.request_users_is_cancel', '=', 1)
                        ->orWhereNull('view_request_user.response_for_users_request_id')
                        ->where('view_request_user.request_users_is_expired', '=', 1)
                        ->where('view_request_user.request_users_is_cancel', '=', 1);
                });
            }
        }

        $requestUserModel = $requestUserModel->groupBy('view_request_user.request_users_id');

        $result = $requestUserModel->getCountForPagination();
        return $result;
    }

    /**
     * Get device token of request user expired
     *
     * @return mixed
     */
    public function collectionRequestUserExpired()
    {
        $listRequestUserExpired = RequestUser::expired()
            ->active()
            ->with(['activeUser' => function ($queryUser) {
                $queryUser->where(function ($queryUserFCM) {
                    $queryUserFCM->with('userFcmTokens');
                });
            }])
            ->with(['responseForUsers' => function ($queryResponseUSer) {
                $queryResponseUSer->where(function ($queryResponseForUserOfCompany) {
                    $queryResponseForUserOfCompany->with(['responseForUserOfCompany' => function ($queryResponseCompany) {
                        $queryResponseCompany->where(function ($queryCompany) {
                            $queryCompany->with('companyFcmTokens');
                        });
                    }]);
                });
            }]);

        return $listRequestUserExpired->get([
            'id',
            'user_id',
            'created_time_request',
            'first_time_requested',
            'address_from'
        ]);
    }

}
