<?php

namespace App\Repository\Eloquent;

use App;
use App\Repository\Contracts\UnregisteredCompanyInterface as UnregisteredCompanyInterface;
use DB;

class UnregisteredCompanyRepository extends BaseRepository implements UnregisteredCompanyInterface
{
    const UNREGISTERED_COMPANIES = 'unregistered_companies';
    const FAVOURITES = 'favourites';

    protected function model()
    {
        return \App\UnregisteredCompany::class;
    }

    protected function getRules()
    {
        return \App\UnregisteredCompany::rules;
    }

    /**
     * Get list company follow address
     *
     * @param $address
     * @param $userId
     * @return mixed
     */
    public function listUnregisteredCompanyCall($address, $userId)
    {
        $listCompany = \App\UnregisteredCompany::leftjoin('corresponding_area', 'unregistered_companies.id', '=', 'corresponding_area.company_id')
            ->leftJoin(DB::raw('(
                SELECT user_id, COUNT(*) as is_favourite, unregistered_company_id
                FROM ' . self::FAVOURITES . '
                WHERE user_id = ' . $userId . '
                GROUP by user_id, unregistered_company_id
                ) fv'), function($join)
                    {
                        $join->on('fv.unregistered_company_id', '=', self::UNREGISTERED_COMPANIES . '.id');
                    })
            ->where('corresponding_area.type', '=', \Config::get('constants.TYPE_AREA.UNREGISTERED_COMPANY'))
            ->whereNotNull('unregistered_companies.corresponding_area')
            ->where('unregistered_companies.corresponding_area', '!=', '')
            ->where(function ($query1) use ($address) {
                $query1->whereRaw("corresponding_area.corresponding_area LIKE '" . $address . "%'")
                    ->orWhereRaw("'" . $address . "' LIKE CONCAT(corresponding_area.corresponding_area, '%')");
            })
            ->groupBy('unregistered_companies.id')
            ->orderBy('fv.is_favourite', 'DESC')
            ->orderBy(DB::raw('ISNULL(unregistered_companies.display_order), display_order'), 'asc')
            ->orderBy('unregistered_companies.updated_at', 'DESC');

        return $listCompany->select(
            self::UNREGISTERED_COMPANIES . '.id as company_id',
            self::UNREGISTERED_COMPANIES . '.name as name_company',
            self::UNREGISTERED_COMPANIES . '.address as address_company',
            self::UNREGISTERED_COMPANIES . '.phone as phone_company',
            self::UNREGISTERED_COMPANIES . '.display_order as display_order',
            self::UNREGISTERED_COMPANIES . '.base_price as base_price',
            self::UNREGISTERED_COMPANIES . '.corresponding_area as corresponding_area',
            self::UNREGISTERED_COMPANIES . '.company_pr as company_pr',
            'fv.is_favourite'
        )->get();
    }

    /**
     * @param array $searchCondition
     * @param int $limit
     * @param int $offset
     * @param array $orderBy
     * @return array
     */
    public function searchWithUnregisteredCompany(array $searchCondition = [], int $limit = 0, int $offset = 0, array $orderBy = [])
    {
        //reset model
        $this->makeModel();

        $this->_searchWithUnregisteredCompanyCondition($searchCondition);

        $unregisteredCompanyStartDate = isset($searchCondition["unregistered_company_start_date"]) ? $searchCondition["unregistered_company_start_date"] : null;
        $unregisteredCompanyEndDate = isset($searchCondition["unregistered_company_end_date"]) ? $searchCondition["unregistered_company_end_date"] : null;

        if ( $offset ) {
            $this->model = $this->model->offset($offset);
        }

        if ( $limit ) {
            $this->model = $this->model->limit($limit);
        }

        $this->orderBy([DB::raw('ISNULL(display_order), display_order'), 'asc']);
        $this->orderBy($orderBy);

        $results = $this->model->get();

        if ( $results && count($results) > 0 ) {
            foreach ($results as $result) {
                $totalCallsByCompany = $this->totalCallsByCompany($result->id, $unregisteredCompanyStartDate, $unregisteredCompanyEndDate);
                $result->call_count_company = $totalCallsByCompany;
            }
            return $results;
        } else {
            return array();
        }
    }

    public function totalCallsByCompany($companyId, $startTime = null, $endTime = null) {
        $query = DB::table('call_counts')
                ->where("unregistered_company_id", "=", $companyId);

        if ($startTime != null) {
            $query = $query->where(DB::raw("(DATE_FORMAT(created_at,'%Y/%m/%d'))"), ">=", $startTime);
        }

        if ($endTime != null) {
            $query = $query->where(DB::raw("(DATE_FORMAT(created_at,'%Y/%m/%d'))"), "<=", $endTime);
        }

        return $query->count();
    }

    /**
     * @param array $searchCondition
     */
    private function _searchWithUnregisteredCompanyCondition(array $searchCondition = [])
    {
        $unregisteredCompanyTableName = $this->model->getTable();

        $unregisteredCompanyStartDate = isset($searchCondition["unregistered_company_start_date"]) ? $searchCondition["unregistered_company_start_date"] : null;
        $unregisteredCompanyEndDate = isset($searchCondition["unregistered_company_end_date"]) ? $searchCondition["unregistered_company_end_date"] : null;
        $unregisteredCompanyName = isset($searchCondition["unregistered_company_name"]) ? $searchCondition["unregistered_company_name"] : null;
        $unregisteredCompanyPhone = isset($searchCondition["unregistered_company_phone"]) ? $searchCondition["unregistered_company_phone"] : null;
        $unregisteredCompanyCorrespondingArea = isset($searchCondition["unregistered_company_corresponding_area"]) ? $searchCondition["unregistered_company_corresponding_area"] : null;

        if ( $unregisteredCompanyStartDate != null ) {
            $this->model = $this->model->where(DB::raw("(DATE_FORMAT({$unregisteredCompanyTableName}.created_at,'%Y/%m/%d'))"), ">=", $unregisteredCompanyStartDate);
        }

        if ( $unregisteredCompanyEndDate != null ) {
            $this->model = $this->model->where(DB::raw("(DATE_FORMAT({$unregisteredCompanyTableName}.created_at,'%Y/%m/%d'))"), "<=", $unregisteredCompanyEndDate);
        }

        if ( $unregisteredCompanyName != null ) {
            $this->model = $this->model->where("{$unregisteredCompanyTableName}.name", "like", "%{$unregisteredCompanyName}%");
        }

        if ( $unregisteredCompanyPhone != null ) {
            $unregisteredCompanyPhone = str_replace("-","",$unregisteredCompanyPhone);
            $this->model = $this->model->where(DB::raw("REPLACE({$unregisteredCompanyTableName}.phone, '-', '')"), 'LIKE', '%' . $unregisteredCompanyPhone . '%');
        }

        if ($unregisteredCompanyCorrespondingArea != null) {
            $this->model = $this->model->where("{$unregisteredCompanyTableName}.corresponding_area", "like", "%{$unregisteredCompanyCorrespondingArea}%");
        }
    }

    /**
     * @param array $searchCondition
     */
    public function countSearchWithUnregisteredCompany(array $searchCondition = [])
    {
        //reset model
        $this->makeModel();

        $this->_searchWithUnregisteredCompanyCondition($searchCondition);
        return $this->model->count();
    }
}
