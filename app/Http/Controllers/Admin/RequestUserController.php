<?php
namespace App\Http\Controllers\Admin;
use App\Helpers\PushNotification;
use App\Helpers\TransFormatApi;
use App\Repository\Eloquent\RequestUserRepository;
use App\Repository\Eloquent\ResponseForUserRepository;
use App\Repository\Eloquent\FCMTokenRepository;
use App\Http\Controllers\Admin\AdminBaseController;
use Illuminate\Http\Request;
use App\Http\Requests\UserFormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\DB;
use Validator;

/**
 * @property RequestUserRepository requestUserRepository
 */
class RequestUserController extends AdminBaseController
{
    protected $requestUserRepository;
    protected $responseForUserRepository;
    protected $useFCMTokenRepository;

    /**
     * UserController constructor.
     * @param RequestUserRepository $userRepository
     */
    public function __construct(
        RequestUserRepository $requestUserRepository,
        ResponseForUserRepository $responseForUserRepository,
        FCMTokenRepository $useFCMTokenRepository
    )
    {
        parent::__construct();

        $this->requestUserRepository = $requestUserRepository;
        $this->responseForUserRepository = $responseForUserRepository;
        $this->useFCMTokenRepository = $useFCMTokenRepository;
    }

    public function index(Request $request)
    {
        $listRequest = $this->requestUserRepository->listRequestUserSearchAddress('Thành phố Hà Nội', 18);;
        dd($listRequest);
        $searchData = $request->all();
        $currentPage = isset($searchData['page']) ? $searchData['page'] : 1;
        $boolSearch = isset($searchData['request_bool_search']) ? $searchData['request_bool_search'] : 0;

        // Refresh in first Or default
        if (!$boolSearch) {
            $searchData['request_user_start_date'] = null;
            $searchData['request_user_end_date'] = null;
            $searchData['request_user_name'] = null;
            $searchData['request_company_name'] = null;
            $searchData['request_user_has_status'] = null;
            $searchData['order'] = 'request_users_created_at';
            $searchData['sort'] = 'desc';
        }

        if (!$currentPage || !is_numeric($currentPage) || $currentPage < 1) {
            $currentPage = 1;
        }

        // Record counts in a page.
        $numberPerPage = config('constants.NUMBER_PERPAGE');

        if (!is_array($searchData)) {
            $searchData = [];
        }

        // Total record of .
        $total = $this->requestUserRepository->countSearchWithRequestUser($searchData);

       //  Total pagination
        $totalPage = ceil($total / $numberPerPage);

        if ($currentPage > $totalPage) {
            $currentPage = $totalPage;
        }

        $offset = ($currentPage - 1) * $numberPerPage;
        $orderBy = [];
        $order = isset($searchData['order']) ? $searchData['order'] : 'request_users_created_at';
        $sort = isset($searchData['sort']) ? $searchData['sort'] : 'desc';
        $orderBy = [$order, $sort];

        $userList = $this->requestUserRepository->searchWithRequestUser($searchData, $numberPerPage, $offset, $orderBy);

        $this->viewData['requestUserData'] = [
            'requestUserList' => $userList,
            'searchValue' => [
                'request_user_start_date' => isset($searchData['request_user_start_date']) ? $searchData['request_user_start_date'] : null,
                'request_user_end_date' => isset($searchData['request_user_end_date']) ? $searchData['request_user_end_date'] : null,
                'request_user_name' => isset($searchData['request_user_name']) ? $searchData['request_user_name'] : null,
                'request_company_name' => isset($searchData['request_company_name']) ? $searchData['request_company_name'] : null,
                'request_user_has_status' => isset($searchData['request_user_has_status']) ? $searchData['request_user_has_status'] : null,
                'sort' => $sort,
                'order' => $order
            ],
            "page" => [
                "total" => $total,
                "totalPage" => $totalPage,
                "currentPage" => $currentPage
            ],
            "boolSearch" => $boolSearch
        ];

        return view('admin.requestuser.index', $this->viewData);
    }

    /**
     * export file csv of detailed logs.
     *
     * @param Request $request
     * @return mixed
     */
    Public function exportCsv(Request $request) {
        set_time_limit(0);

        $data = $request->all();

        if(!is_array($data)) {
            $data = [];
        }
        $total = $this->requestUserRepository->countSearchWithRequestUser($data);

        if($total>100000) {
            return redirect()->route('requestuser.index')->withError('一致するレコード数が10万件を超えています。検索条件を加えて再度お試しください。');
        }

        $response = new StreamedResponse(function() use ($data){

            echo "\xEF\xBB\xBF";

            // Open output stream
            $handle = fopen('php://output', 'w');

            // Add CSV headers
            $columns = array('依頼日時', 'ユーザー名', '申込ステータス', '返答数', '代行会社名', 'お迎え先', '目印', 'おかえり先');
            fputcsv($handle, $columns);

            $page = 1;
            $limit = 1000;
            while (1) {
                set_time_limit(0);
                $offset = ($page-1)*$limit;

                $detailRequestUsers = $this->requestUserRepository->searchWithRequestUser($data, $limit, $offset, []);
                $page++;
                if(!$detailRequestUsers) {
                    break;
                }

                foreach ( $detailRequestUsers as $detailRequestUser ) {

                    $listRequestUserHasStatus = [
                        '1' => '依頼',
                        '2' => '返答中',
                        '3' => '配車',
                        '4' => 'キャンセル',
                        '5' => '時間経過'
                    ];
                    if(array_key_exists($detailRequestUser->request_user_has_status_name, $listRequestUserHasStatus)) {
                        $generalRequestUserHasStatus = $listRequestUserHasStatus[$detailRequestUser->request_user_has_status_name];
                    } else {
                        $generalRequestUserHasStatus = "";
                    }

                    fputcsv($handle, [
                        $detailRequestUser->request_users_created_at,
                        $detailRequestUser->request_user_user_name,
                        $generalRequestUserHasStatus,
                        $detailRequestUser->count_response,
                        $detailRequestUser->request_user_company_name,
                        $detailRequestUser->request_users_address_from,
                        $detailRequestUser->request_users_address_note,
                        $detailRequestUser->request_users_address_to
                    ]);
                }
            }
            // Close the output stream
            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=daikou_irai_'.date("Ymd").'.csv',
        ]);

        return $response;
    }

    /**
     * @param Request $request
     * @param $request_user_id
     * @return mixed
     */
    public function delete(Request $request) {
        $data = $request->all()['array_delete'];
        try {
            DB::beginTransaction();
            foreach ($data as $request_user_id) {
                if (!$request_user_id || !is_numeric($request_user_id)) {
                    return $this->sendError("errors");
                }

                //check permission
                $condition = [
                    ['id', "=", $request_user_id]
                ];

                $requestUser = $this->requestUserRepository->firstWhere($condition);

                if (!$requestUser) {
                    return $this->sendError("errors");
                }

                $this->requestUserRepository->delete($request_user_id);

                $condition = [
                    ['request_id', "=", $request_user_id]
                ];

                $responseForUser = $this->responseForUserRepository->firstWhere($condition);

                $responseRequest = [];
                if ($responseForUser) {
                    $responseRequest = $this->responseForUserRepository->firstWhere([
                        ['request_id', $request_user_id],
                        ['status', \Config::get('constants.RESPONSE_FOR_USERS.STATUS_ACCEPTED')],
                        ['is_deleted', \Config::get('constants.RESPONSE_FOR_USERS.NOT_DELETED')]
                    ]);
                    $this->responseForUserRepository->deleteWhere($condition);
                }

                if (!empty($responseRequest)) {
                    $collectionFcmTokenUsers = $this->useFCMTokenRepository->getDeviceToken(
                        $requestUser['user_id'],
                        \Config::get('constants.TYPE_NOTIFY.USER')
                    );

                    //Push notification to other devices of user
                    if ($collectionFcmTokenUsers->isNotEmpty()) {
                        $arrayMsgUser = mergeArrayNotify(\Config::get('notification.TEMP_MSG_PUSH_NOTIFY.USER_DELETE_HISTORY'));
                        $arrayMsgUser['request_id'] = $request_user_id;
                        $deviceTokensUser = TransFormatApi::formatDataDeviceToken($collectionFcmTokenUsers);

                        PushNotification::sendNotification($arrayMsgUser, $deviceTokensUser['android'], $deviceTokensUser['ios']);
                    }
                }
            }
            DB::commit();
            return $this->sendResponse([], __('Successfully.'));
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->sendError(__($ex->getMessage()));
        }
    }
    /**
     * @param Request $request
     * @param $request_id
     * @return mixed
     */
    public function getListResponseCompany(Request $request){
        $request_id = $request->request_id;
        $results = $this->responseForUserRepository->getListResponseCompany($request_id);
        return response()->json($results);
    }
}