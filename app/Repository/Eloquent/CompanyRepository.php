<?php

namespace App\Repository\Eloquent;

use App;
use App\Repository\Contracts\CompanyInterface as CompanyInterface;
use Illuminate\Support\Facades\DB;

class CompanyRepository extends BaseRepository implements CompanyInterface
{

    protected function model()
    {
        return \App\Company::class;
    }

    protected function getRules()
    {
        return \App\Company::rules;
    }

    /**
     * Get list company nearby address
     *
     * @param $address
     * @return mixed
     */
    public function listCompanyNearByAddress($address)
    {
        $listCompany = \App\Company::leftjoin('corresponding_area', 'companies.id', '=', 'corresponding_area.company_id')
            ->where('corresponding_area.type', '=', \Config::get('constants.TYPE_AREA.COMPANY'))
            ->whereNotNull('companies.corresponding_area')
            ->where('companies.corresponding_area', '!=', '')
            ->where(function ($query1) use ($address) {
                $query1->whereRaw("corresponding_area.corresponding_area LIKE '" . $address . "%'")
                    ->orWhereRaw("'" . $address . "' LIKE CONCAT(corresponding_area.corresponding_area, '%')");
            })
            ->active()
            ->with('companyFcmTokens')
            ->with('companyResponseUser')
            ->groupBy('companies.id');

        return $listCompany->get(['companies.*']);
    }

    /**
     * Get list company follow address
     *
     * @param $address
     * @param $requestID
     * @return mixed
     */
    public function listCompanyFollowAddress($address, $requestID)
    {
        $listCompany = \App\Company::leftjoin('corresponding_area', 'companies.id', '=', 'corresponding_area.company_id')
            ->where('corresponding_area.type', '=', \Config::get('constants.TYPE_AREA.COMPANY'))
            ->whereNotNull('companies.corresponding_area')
            ->where('companies.corresponding_area', '!=', '')
            ->where(function ($query1) use ($address) {
                $query1->whereRaw("corresponding_area.corresponding_area LIKE '" . $address . "%'")
                    ->orWhereRaw("'" . $address . "' LIKE CONCAT(corresponding_area.corresponding_area, '%')");
            })
            ->active()
            ->with('companyFcmTokens')
            ->with(['companyResponseUser' => function ($query) use ($requestID) {
                $query->where('request_id', $requestID);
            }])
            ->groupBy('companies.id');

        return $listCompany->get(['companies.*']);
    }

    /**
     * Get count companies follow address
     *
     * @param $address
     * @return mixed
     */
    public function countCompanyFollowAddress($address)
    {
        $companies = \App\Company::leftjoin('corresponding_area', 'companies.id', '=', 'corresponding_area.company_id')
            ->where('corresponding_area.type', '=', \Config::get('constants.TYPE_AREA.COMPANY'))
            ->whereNotNull('companies.corresponding_area')
            ->where('companies.corresponding_area', '!=', '')
            ->where(function ($query1) use ($address) {
                $query1->whereRaw("corresponding_area.corresponding_area LIKE '" . $address . "%'")
                    ->orWhereRaw("'" . $address . "' LIKE CONCAT(corresponding_area.corresponding_area, '%')");
            })
            ->enable()
            ->groupBy('companies.id');

        return $companies->count();
    }

    /**
     * Random key phone and password
     *
     * @param $phone
     * @param string $keyRandomString
     * @return string
     */
    public function checkRegisterPhonePassword($phone, $keyRandomString = '')
    {
        $checkCompany = \App\Company::where('phone_to_login', $phone)
            ->where('raw_pass', $keyRandomString)
            ->where('is_deleted', \Config::get('constants.php.COMPANY_DELETED.ACTIVE'))
            ->get();

        if ($checkCompany->isEmpty()) {
            return $keyRandomString;
        } else {
            $randomString = generateRandomString();
            $this->checkRegisterPhonePassword($phone, $randomString);
        }
    }

    /**
     * @param array $searchCondition
     * @param int $limit
     * @param int $offset
     * @param array $orderBy
     * @return array
     */
    public function searchWithCompany(array $searchCondition = [], int $limit = 0, int $offset = 0, array $orderBy = [])
    {
        //reset model
        $this->makeModel();

        $this->searchWithCompanyCondition($searchCondition);

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
    private function searchWithCompanyCondition(array $searchCondition = [])
    {
        $companyTableName = $this->model->getTable();

        $companyStartDate = isset($searchCondition["company_start_date"]) ? $searchCondition["company_start_date"] : null;
        $companyEndDate = isset($searchCondition["company_end_date"]) ? $searchCondition["company_end_date"] : null;
        $companyStatusLogin = isset($searchCondition["company_status_login"]) ? $searchCondition["company_status_login"] : null;
        $companyName = isset($searchCondition["company_name"]) ? $searchCondition["company_name"] : null;
        $companyPhone = isset($searchCondition["company_phone"]) ? $searchCondition["company_phone"] : null;
        $companyCorrespondingArea = isset($searchCondition["company_corresponding_area"]) ? $searchCondition["company_corresponding_area"] : null;

        if ( $companyStartDate != null ) {
            $this->model = $this->model->where(DB::raw("(DATE_FORMAT({$companyTableName}.created_at,'%Y/%m/%d'))"), ">=", $companyStartDate);
        }

        if ( $companyEndDate != null ) {
            $this->model = $this->model->where(DB::raw("(DATE_FORMAT({$companyTableName}.created_at,'%Y/%m/%d'))"), "<=", $companyEndDate);
        }

        if ( $companyStatusLogin != null ) {
            $this->model = $this->model->where("{$companyTableName}.status_login", "=", $companyStatusLogin);
        }

        if ( $companyName != null ) {
            $this->model = $this->model->where("{$companyTableName}.name", "like", "%{$companyName}%");
        }

        if ($companyPhone != null) {
            $companyPhone = str_replace("-","",$companyPhone);
            $this->model = $this->model->where(DB::raw("REPLACE({$companyTableName}.phone, '-', '')"), 'LIKE', '%' . $companyPhone . '%');
        }

        if ( $companyCorrespondingArea != null ) {
            $this->model = $this->model->where("{$companyTableName}.corresponding_area", "like", "%{$companyCorrespondingArea}%");
        }

        $this->model = $this->model->where("{$companyTableName}.is_deleted", "=", config('constants.COMPANY_DELETED.ACTIVE'));
    }

    /**
     * @param array $searchCondition
     */
    public function countSearchWithCompany(array $searchCondition = [])
    {
        //reset model
        $this->makeModel();

        $this->searchWithCompanyCondition($searchCondition);
        return $this->model->count();
    }

    /**
     * Get list company by ids
     *
     * @param array $ids
     * @return mixed
     */
    public function listCompanyByIds($ids)
    {
        $listCompany = \App\Company::whereIn('id', $ids)
            ->active()
            ->with('companyFcmTokens');

        return $listCompany->get();
    }
}
