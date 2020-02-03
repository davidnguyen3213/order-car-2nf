<?php

namespace App\Repository\Eloquent;

use App;
use App\Repository\Contracts\UserInterface as UserInterface;
use Illuminate\Support\Facades\DB;

class UserRepository extends BaseRepository implements UserInterface
{

    protected function model()
    {
        return \App\User::class;
    }

    protected function getRules()
    {
        return \App\User::rules;
    }

    /**
     * @param array $searchCondition
     * @param int $limit
     * @param int $offset
     * @param array $orderBy
     * @return array
     */
    public function searchWithUser(array $searchCondition = [], int $limit = 0, int $offset = 0, array $orderBy = [])
    {
        //reset model
        $this->makeModel();

        $this->searchWithUserCondition($searchCondition);

        if ( $offset ) {
            $this->model = $this->model->offset($offset);
        }

        if ( $limit ) {
            $this->model = $this->model->limit($limit);
        }

        $this->orderBy($orderBy);
        $result = $this->model->get();

        if ( $result && count($result) > 0 ) {
            return $result;
        } else {
            return array();
        }
    }

    /**
     * @param array $searchCondition
     */
    private function searchWithUserCondition(array $searchCondition = [])
    {
        $userTableName = $this->model->getTable();

        $userStartDate = isset($searchCondition["user_start_date"]) ? $searchCondition["user_start_date"] : null;
        $userEndDate = isset($searchCondition["user_end_date"]) ? $searchCondition["user_end_date"] : null;
        $userStatus = isset($searchCondition["user_status"]) ? $searchCondition["user_status"] : null;
        $userName = isset($searchCondition["user_name"]) ? $searchCondition["user_name"] : null;
        $userPhone = isset($searchCondition["user_phone"]) ? $searchCondition["user_phone"] : null;

        if ( $userStartDate != null ) {
            $this->model = $this->model->where(DB::raw("(DATE_FORMAT({$userTableName}.created_at,'%Y/%m/%d'))"), ">=", $userStartDate);
        }

        if ( $userEndDate != null ) {
            $this->model = $this->model->where(DB::raw("(DATE_FORMAT({$userTableName}.created_at,'%Y/%m/%d'))"), "<=", $userEndDate);
        }

        if ( $userStatus != null ) {
            $this->model = $this->model->where("{$userTableName}.status", "=", $userStatus);
        }

        if ( $userName != null ) {
            $this->model = $this->model->where("{$userTableName}.name", "like", "%{$userName}%");
        }

        if ($userPhone != null) {
            $userPhone = str_replace("-","",$userPhone);
            $this->model = $this->model->where(DB::raw("REPLACE({$userTableName}.phone, '-', '')"), 'LIKE', '%' . $userPhone . '%');
        }

        $this->model = $this->model->where("{$userTableName}.type", "=", config('constants.TYPE_USER.OTHER'))
            ->where("{$userTableName}.is_deleted", "=", config('constants.USER_DELETED.ACTIVE'));
    }

    /**
     * @param array $searchCondition
     */
    public function countSearchWithUser(array $searchCondition = [])
    {
        //reset model
        $this->makeModel();

        $this->searchWithUserCondition($searchCondition);
        return $this->model->count();
    }
}
