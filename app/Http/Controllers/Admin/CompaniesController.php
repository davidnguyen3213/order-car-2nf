<?php
namespace App\Http\Controllers\Admin;

use App\Repository\Eloquent\CompanyRepository;
use App\Repository\Eloquent\CorrespondingAreaRepository;
use App\Repository\Eloquent\FCMTokenRepository;
use App\Repository\Eloquent\ResponseForUserRepository;
use App\Http\Controllers\Admin\AdminBaseController;
use Illuminate\Http\Request;
use App\Http\Requests\CompaniesFormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Facades\Hash;
use App\Helpers\PushNotification;
use App\Helpers\TransFormatApi;
use Illuminate\Support\Facades\DB;
use Validator;

/**
 * @property CompanyRepository companyRepository
 */
class CompaniesController extends AdminBaseController
{
    protected $companyRepository;
    protected $correspondingAreaRepository;
    protected $useFCMTokenRepository;
    protected $responseForUserRepository;

    /**
     * CompanyRepository constructor.
     * @param CompanyRepository $companyRepository
     * @param FCMTokenRepository $useFCMTokenRepository
     */
    public function __construct(
        CompanyRepository $companyRepository,
        CorrespondingAreaRepository $correspondingAreaRepository,
        FCMTokenRepository $useFCMTokenRepository,
        ResponseForUserRepository $responseForUserRepository
    )
    {
        parent::__construct();

        $this->companyRepository = $companyRepository;
        $this->correspondingAreaRepository = $correspondingAreaRepository;
        $this->useFCMTokenRepository = $useFCMTokenRepository;
        $this->responseForUserRepository = $responseForUserRepository;
    }

    public function index(Request $request)
    {
        $searchData = $request->all();
        $currentPage =  isset($searchData['page']) ? $searchData['page'] : 1;

        if ( !$currentPage || !is_numeric($currentPage) || $currentPage < 1 ) {
            $currentPage = 1;
        }

        // Record counts in a page.
        $numberPerPage = config('constants.NUMBER_PERPAGE');

        if (!is_array($searchData)) {
            $searchData = [];
        }

        // Total record of .
        $total = $this->companyRepository->countSearchWithCompany($searchData);

        // Total pagination
        $totalPage = ceil($total/$numberPerPage);

        if ( $currentPage > $totalPage ) {
            $currentPage = $totalPage;
        }

        $offset = ($currentPage-1)*$numberPerPage;
        $orderBy = [];
        $order = isset($searchData['order']) ? $searchData['order'] : 'created_at';
        $sort = isset($searchData['sort']) ? $searchData['sort'] : 'desc';
        $orderBy = [$order,$sort];

        $userList = $this->companyRepository->searchWithCompany($searchData,$numberPerPage,$offset,$orderBy);

        $this->viewData['companyData'] = [
            'companyList' => $userList,
            'searchValue' => [
                'company_start_date' => isset($searchData['company_start_date']) ? $searchData['company_start_date'] : null,
                'company_end_date' => isset($searchData['company_end_date']) ? $searchData['company_end_date'] : null,
                'company_status_login' => isset($searchData['company_status_login']) ? $searchData['company_status_login'] : null,
                'company_name' => isset($searchData['company_name']) ? $searchData['company_name'] : null,
                'company_phone' => isset($searchData['company_phone']) ? $searchData['company_phone'] : null,
                'company_corresponding_area' => isset($searchData['company_corresponding_area']) ? $searchData['company_corresponding_area'] : null,
                'sort' => $sort,
                'order' => $order
            ],
            "page" => [
                "total" => $total,
                "totalPage" => $totalPage,
                "currentPage" => $currentPage,
            ]
        ];

        return view('admin.company.index', $this->viewData);
    }

    /**
     * Delete company
     *
     * @param Request $request
     * @param $userId
     */
    public function delete(Request $request, $companies_id)
    {
        try {
            DB::beginTransaction();

            if ( !$companies_id || !is_numeric($companies_id) ) {
                return redirect()->route('company.index')->withError('この会社は存在しません。');
            }

            //check permission
            $condition = [
                ['id', "=", $companies_id],
                ['is_deleted', "=", config('constants.COMPANY_DELETED.ACTIVE')],
            ];

            $company = $this->companyRepository->firstWhere($condition);

            if ( !$company ) {
                return redirect()->route('error');
            }

            $updateData = [
                'is_deleted' => config('constants.COMPANY_DELETED.DELETED'),
            ];
            $this->companyRepository->update($updateData, $company->id);

            $listResponseForUsers = $this->responseForUserRepository->findByField('company_id', $company->id);
            foreach ($listResponseForUsers as $responseForUser) {
                $condition = [
                    'is_deleted' => config('constants.RESPONSE_FOR_USERS.DELETED'),
                ];
                $this->responseForUserRepository->update($condition, $responseForUser->id);
            }

            //Push notification to company
            $collectionFcmTokenCompanies = $this->useFCMTokenRepository->getDeviceToken(
                $company->id,
                config('constants.TYPE_NOTIFY.COMPANY')
            );

            if ($collectionFcmTokenCompanies->isNotEmpty()) {
                $arrayMsg = mergeArrayNotify(config('notification.TEMP_MSG_PUSH_NOTIFY.COMPANY_UPDATE'));
                $deviceTokens = TransFormatApi::formatDataDeviceToken($collectionFcmTokenCompanies);

                PushNotification::sendNotification($arrayMsg, $deviceTokens['android'], $deviceTokens['ios']);

                // Delete device token by company_id
                foreach ($collectionFcmTokenCompanies as $item) {
                    $this->useFCMTokenRepository->delete($item->id);
                }
            }

            DB::commit();
            return redirect()->route('company.index')->withSuccess('1件のレコードを削除しました。');
        } catch (\Exception $ex) {
            DB::rollBack();
            return redirect()->route('company.index')->withError($ex->getMessage());
        }
    }

    /**
     * show info company Edit.
     *
     * @param Request $request
     * @param $user_id
     */
    public function showEditForm(Request $request, $companies_id)
    {
        if ( !$companies_id || !is_numeric($companies_id) ) {
            return redirect()->route('company.index')->withError('この会社は存在しません。');
        }

        $condition = [
            ['id', "=", $companies_id],
            ['is_deleted', "=", config('constants.COMPANY_DELETED.ACTIVE')]
        ];

        $company = $this->companyRepository->firstWhere($condition);
        if ( !$company ) {
            return redirect()->route('error');
        }

        $this->viewData['companyEditData'] = [
            'company' => $company,
        ];

        return $this->viewData;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(CompaniesFormRequest $request)
    {
        $data = $request->only("company_store_created_at", "company_store_status_login", "company_store_name", "company_store_person_charged",
            "company_store_email", "company_store_password", "company_store_phone", "company_store_address", "company_store_corresponding_area",
            "company_store_base_price", "company_store_company_pr", "company_store_id");

        $currentCompanyId = $data['company_store_id'];
        //add company new
        if ( !$currentCompanyId ) {
            unset($data["company_store_id"]);

            //add data to db
            $createData = [
                'status_login' => $data['company_store_status_login'],
                'name' => $data['company_store_name'],
                'person_charged' => $data['company_store_person_charged'],
                'email' => $data['company_store_email'],
                'raw_pass' => $data['company_store_password'],
                'password' => bcrypt($data['company_store_password']),
                'phone' => $data['company_store_phone'],
                'phone_to_login' => str_replace('-', '', trim($data['company_store_phone'])),
                'address' => $data['company_store_address'],
                'corresponding_area' => $data['company_store_corresponding_area'],
                'base_price' => $data['company_store_base_price'] ? $data['company_store_base_price'] : 0,
                'company_pr' => $data['company_store_company_pr'],
            ];

            $createCompany = $this->companyRepository->create($createData);
            if ($createCompany) {
                $areaArray = explode(\Config::get('constants.DELIMITER'), $data['company_store_corresponding_area']);
                if (!empty($areaArray)) {
                    $dataArea = [];
                    foreach ($areaArray as $key => $area) {
                        if (strlen(trim($area)) == 0) {
                            continue;
                        }
                        if (strlen(trim(str_replace(' ', '', $area))) == 0) {
                            continue;
                        }
                        $dataArea[$key]['company_id'] = $createCompany->id;
                        $dataArea[$key]['corresponding_area'] = $area;
                        $dataArea[$key]['type'] = \Config::get('constants.TYPE_AREA.COMPANY');
                        $dataArea[$key]['created_at'] = date('y-m-d H:i:s');
                        $dataArea[$key]['updated_at'] = date('y-m-d H:i:s');
                    }
                    //insert to corresponding area
                    if (!empty($dataArea)) {
                        $this->correspondingAreaRepository->insertMultipleRows($dataArea);
                    }
                }
            }
            return redirect()->route('company.index')->withSuccess('登録しました。');
        }

        //check permission
        if (!$currentCompanyId || !is_numeric($currentCompanyId)) {
            return redirect()->route('error');
        }

        $condition = [
            ['id', "=", $currentCompanyId],
            ['is_deleted', "=", config('constants.COMPANY_DELETED.ACTIVE')]
        ];
        $company = $this->companyRepository->firstWhere($condition);

        if (!$company) {
            return redirect()->route('company.index')->withError('この会社は存在しません。');
        }

        // if company changes the password then removing her fcm-token
        if (($data['company_store_password'] != null && !Hash::check($data['company_store_password'], $company->password)) ||
            $data['company_store_status_login'] == config('constants.COMPANY_LOGIN.STATUS_DISABLE') ||
            $data['company_store_phone'] != $company->phone) {
            //Push notification to company
            $collectionFcmTokenCompanies = $this->useFCMTokenRepository->getDeviceToken(
                $company->id,
                config('constants.TYPE_NOTIFY.COMPANY')
            );

            if ($collectionFcmTokenCompanies->isNotEmpty()) {
                $arrayMsg = mergeArrayNotify(config('notification.TEMP_MSG_PUSH_NOTIFY.COMPANY_UPDATE'));
                $deviceTokens = TransFormatApi::formatDataDeviceToken($collectionFcmTokenCompanies);

                PushNotification::sendNotification($arrayMsg, $deviceTokens['android'], $deviceTokens['ios']);

                // Delete device token by company_id
                foreach ($collectionFcmTokenCompanies as $item) {
                    $this->useFCMTokenRepository->delete($item->id);
                }
            }
        }

        // update data to db
        $updateData = [
            'status_login' => $data['company_store_status_login'],
            'name' => $data['company_store_name'],
            'person_charged' => $data['company_store_person_charged'],
            'email' => $data['company_store_email'],
            'phone' => $data['company_store_phone'],
            'phone_to_login' => str_replace('-', '', trim($data['company_store_phone'])),
            'address' => $data['company_store_address'],
            'corresponding_area' => $data['company_store_corresponding_area'],
            'base_price' => $data['company_store_base_price'] ? $data['company_store_base_price'] : 0,
            'company_pr' => $data['company_store_company_pr'],
        ];

        // change the user's password
        if ($data['company_store_password'] && !Hash::check($data['company_store_password'], $company->password)) {
            $updateData['raw_pass'] = $data['company_store_password'];
            $updateData['password'] = bcrypt($data['company_store_password']);
        }

        $this->companyRepository->update($updateData, $company->id);

        //delete current record
        $this->correspondingAreaRepository->deleteWhere([
            ['company_id', '=', $company->id],
            ['type', '=', \Config::get('constants.TYPE_AREA.COMPANY')]
        ]);
        $areaArray = explode(\Config::get('constants.DELIMITER'), $data['company_store_corresponding_area']);
        if (!empty($areaArray)) {
            $dataArea = [];
            foreach ($areaArray as $key => $area) {
                if (strlen(trim($area)) == 0) {
                    continue;
                }
                if (strlen(trim(str_replace(' ', '', $area))) == 0) {
                    continue;
                }
                $dataArea[$key]['company_id'] = $company->id;
                $dataArea[$key]['corresponding_area'] = $area;
                $dataArea[$key]['type'] = \Config::get('constants.TYPE_AREA.COMPANY');
                $dataArea[$key]['created_at'] = date('y-m-d H:i:s');
                $dataArea[$key]['updated_at'] = date('y-m-d H:i:s');
            }
            //insert to corresponding area
            if (!empty($dataArea)) {
                $this->correspondingAreaRepository->insertMultipleRows($dataArea);
            }
        }
        return redirect()->route('company.index')->withSuccess('更新が完了しました。');
    }
}