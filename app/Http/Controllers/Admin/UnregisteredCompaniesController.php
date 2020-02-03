<?php
namespace App\Http\Controllers\Admin;

use App\Repository\Eloquent\UnregisteredCompanyRepository;
use App\Repository\Eloquent\CallCountRepository;
use App\Repository\Eloquent\CorrespondingAreaRepository;
use App\Repository\Eloquent\FavouriteRepository;
use App\Http\Controllers\Admin\AdminBaseController;
use Illuminate\Http\Request;
use App\Http\Requests\UnregisteredCompanyFormRequest;
use App\Http\Requests\FavouriteRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Validator;

/**
 * @property UnregisteredCompaniesController userRepository
 */
class UnregisteredCompaniesController extends AdminBaseController
{
    protected $unregisteredCompanyRepository;
    protected $callCountRepository;
    protected $correspondingAreaRepository;
    protected $favouriteRepository;

    /**
     * UnregisteredCompaniesController constructor.
     * @param UnregisteredCompanyRepository $unregisteredCompanyRepository
     */
    public function __construct(
        UnregisteredCompanyRepository $unregisteredCompanyRepository,
        CallCountRepository $callCountRepository,
        CorrespondingAreaRepository $correspondingAreaRepository,
        FavouriteRepository $favouriteRepository
    )
    {
        parent::__construct();

        $this->unregisteredCompanyRepository = $unregisteredCompanyRepository;
        $this->callCountRepository = $callCountRepository;
        $this->correspondingAreaRepository = $correspondingAreaRepository;
        $this->favouriteRepository = $favouriteRepository;
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
        $total = $this->unregisteredCompanyRepository->countSearchWithUnregisteredCompany($searchData);

        // Total pagination
        $totalPage = ceil($total/$numberPerPage);

        if ( $currentPage > $totalPage ) {
            $currentPage = $totalPage;
        }

        $offset = ($currentPage-1)*$numberPerPage;
        $orderBy = [];
        $order = isset($searchData['order']) ? $searchData['order'] : 'unregistered_companies.created_at';
        $sort = isset($searchData['sort']) ? $searchData['sort'] : 'desc';
        $orderBy = [
            [$order, $sort]
        ];

        $unregisteredCompanyList = $this->unregisteredCompanyRepository->searchWithUnregisteredCompany($searchData,$numberPerPage,$offset,$orderBy);

        $this->viewData['unregisteredCompanyData'] = [
            'unregisteredCompanyList' => $unregisteredCompanyList,
            'searchValue' => [
                'unregistered_company_start_date' => isset($searchData['unregistered_company_start_date']) ? $searchData['unregistered_company_start_date'] : null,
                'unregistered_company_end_date' => isset($searchData['unregistered_company_end_date']) ? $searchData['unregistered_company_end_date'] : null,
                'unregistered_company_name' => isset($searchData['unregistered_company_name']) ? $searchData['unregistered_company_name'] : null,
                'unregistered_company_phone' => isset($searchData['unregistered_company_phone']) ? $searchData['unregistered_company_phone'] : null,
                'unregistered_company_corresponding_area' => isset($searchData['unregistered_company_corresponding_area']) ? $searchData['unregistered_company_corresponding_area'] : null,
                'sort' => $sort,
                'order' => $order
            ],
            "page" => [
                "total" => $total,
                "totalPage" => $totalPage,
                "currentPage" => $currentPage,
            ]
        ];

        return view('admin.unregisteredCompany.index', $this->viewData);
    }

    /**
     * Delete user
     *
     * @param Request $request
     * @param $userId
     */
    public function delete(Request $request, $unregistered_company_id)
    {
        try {
            DB::beginTransaction();

            if ( !$unregistered_company_id || !is_numeric($unregistered_company_id) ) {
                return redirect()->route('unregisteredCompany.index')->withError('この会社は存在しません。');
            }

            //check permission
            $condition = [
                ['id', "=", $unregistered_company_id]
            ];

            $unregisteredCompany = $this->unregisteredCompanyRepository->firstWhere($condition);

            if ( !$unregisteredCompany ) {
                return redirect()->route('error');
            }

            $this->unregisteredCompanyRepository->delete($unregisteredCompany->id);

            $condition = [
                ['unregistered_company_id', "=", $unregistered_company_id]
            ];
            $favourite = $this->favouriteRepository->firstWhere($condition);

            if ($favourite) {
                $this->favouriteRepository->deleteWhere($condition);
            }

            DB::commit();
            return redirect()->route('unregisteredCompany.index')->withSuccess('1件のレコードを削除しました。');
        } catch (\Exception $ex) {
            DB::rollBack();
            return redirect()->route('unregisteredCompany.index')->withError($ex->getMessage());
        }
    }

    /**
     * Edit user.
     *
     * @param Request $request
     * @param $user_id
     */
    public function showEditForm(Request $request, $unregistered_company_id)
    {
        if ( !$unregistered_company_id || !is_numeric($unregistered_company_id) ) {
            return redirect()->route('unregisteredCompany.index')->withError('この会社は存在しません。');
        }

        $condition = [
            ['id', "=", $unregistered_company_id]
        ];

        $unregisteredCompany = $this->unregisteredCompanyRepository->firstWhere($condition);
        if ( !$unregisteredCompany ) {
            return redirect()->route('error');
        }

        $callCount = $this->callCountRepository->findByField(['unregistered_company_id' => $unregisteredCompany->id]);

        $unregisteredCompany->call_count_company = count($callCount);
        $this->viewData['unregisteredCompanyEditData'] = [
            'unregisteredCompany' => $unregisteredCompany,
        ];

        return $this->viewData;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(UnregisteredCompanyFormRequest $request)
    {
        $data = $request->only("unregistered_company_store_created_at", "unregistered_company_store_display_order", "unregistered_company_store_name",
            "unregistered_company_store_phone", "unregistered_company_store_address", "unregistered_company_store_corresponding_area", "unregistered_company_store_base_price",
            "unregistered_company_store_company_pr", "unregistered_company_store_id");

        $currentUnregisteredCompanyId = $data['unregistered_company_store_id'];
        //add new user
        if ( !$currentUnregisteredCompanyId ) {
            unset($data["unregistered_company_store_id"]);

            //add data to db
            $createData = [
                'display_order' => $data['unregistered_company_store_display_order'],
                'name' => $data['unregistered_company_store_name'],
                'phone' => $data['unregistered_company_store_phone'],
                'address' => $data['unregistered_company_store_address'],
                'corresponding_area' => $data['unregistered_company_store_corresponding_area'],
                'base_price' => $data['unregistered_company_store_base_price'] ? $data['unregistered_company_store_base_price'] : 0,
                'company_pr' => $data['unregistered_company_store_company_pr'],
            ];

            $createUnregisteredCompany = $this->unregisteredCompanyRepository->create($createData);
            if ($createUnregisteredCompany) {
                $areaArray = explode(\Config::get('constants.DELIMITER'), $data['unregistered_company_store_corresponding_area']);
                if (!empty($areaArray)) {
                    $dataArea = [];
                    foreach ($areaArray as $key => $area) {
                        if (strlen(trim($area)) == 0) {
                            continue;
                        }
                        if (strlen(trim(str_replace(' ', '', $area))) == 0) {
                            continue;
                        }
                        $dataArea[$key]['company_id'] = $createUnregisteredCompany->id;
                        $dataArea[$key]['corresponding_area'] = $area;
                        $dataArea[$key]['type'] = \Config::get('constants.TYPE_AREA.UNREGISTERED_COMPANY');
                        $dataArea[$key]['created_at'] = date('y-m-d H:i:s');
                        $dataArea[$key]['updated_at'] = date('y-m-d H:i:s');
                    }
                    //insert to corresponding area
                    if (!empty($dataArea)) {
                        $this->correspondingAreaRepository->insertMultipleRows($dataArea);
                    }
                }
            }
            return redirect()->route('unregisteredCompany.index')->withSuccess('登録しました。');
        }

        //check permission
        if (!$currentUnregisteredCompanyId || !is_numeric($currentUnregisteredCompanyId)) {
            return redirect()->route('unregisteredCompany.index')->withError('この会社は存在しません。');
        }

        $condition = [
            ['id', "=", $currentUnregisteredCompanyId]
        ];
        $unregisteredCompany = $this->unregisteredCompanyRepository->firstWhere($condition);

        if (!$unregisteredCompany) {
            return redirect()->route('unregisteredCompany.index')->withError('この会社は存在しません。');
        }

        $updateData = [
            'display_order' => $data['unregistered_company_store_display_order'],
            'name' => $data['unregistered_company_store_name'],
            'phone' => $data['unregistered_company_store_phone'],
            'address' => $data['unregistered_company_store_address'],
            'corresponding_area' => $data['unregistered_company_store_corresponding_area'],
            'base_price' => $data['unregistered_company_store_base_price'] ? $data['unregistered_company_store_base_price'] : 0,
            'company_pr' => $data['unregistered_company_store_company_pr'],
        ];

        $this->unregisteredCompanyRepository->update($updateData, $unregisteredCompany->id);
        //delete current record
        $this->correspondingAreaRepository->deleteWhere([
            ['company_id', '=', $unregisteredCompany->id],
            ['type', '=', \Config::get('constants.TYPE_AREA.UNREGISTERED_COMPANY')]
        ]);
        $areaArray = explode(\Config::get('constants.DELIMITER'), $data['unregistered_company_store_corresponding_area']);
        if (!empty($areaArray)) {
            $dataArea = [];
            foreach ($areaArray as $key => $area) {
                if (strlen(trim($area)) == 0) {
                    continue;
                }
                if (strlen(trim(str_replace(' ', '', $area))) == 0) {
                    continue;
                }
                $dataArea[$key]['company_id'] = $unregisteredCompany->id;
                $dataArea[$key]['corresponding_area'] = $area;
                $dataArea[$key]['type'] = \Config::get('constants.TYPE_AREA.UNREGISTERED_COMPANY');
                $dataArea[$key]['created_at'] = date('y-m-d H:i:s');
                $dataArea[$key]['updated_at'] = date('y-m-d H:i:s');
            }
            //insert to corresponding area
            if (!empty($dataArea)) {
                $this->correspondingAreaRepository->insertMultipleRows($dataArea);
            }
        }
        return redirect()->route('unregisteredCompany.index')->withSuccess('更新が完了しました。');
    }
}