<?php
namespace App\Http\Controllers\Admin;

use App\Repository\Eloquent\UserRepository;
use App\Repository\Eloquent\FCMTokenRepository;
use App\Repository\Eloquent\RequestUserRepository;
use App\Http\Controllers\Admin\AdminBaseController;
use Illuminate\Http\Request;
use App\Http\Requests\UserFormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Facades\Hash;
use App\Helpers\PushNotification;
use App\Helpers\TransFormatApi;
use Illuminate\Support\Facades\DB;
use Validator;

/**
 * @property UserRepository userRepository
 */
class UserController extends AdminBaseController
{
    protected $userRepository;
    protected $useFCMTokenRepository;
    protected $requestUserRepository;

    /**
     * UserController constructor.
     * @param UserRepository $userRepository
     */
    public function __construct(
        UserRepository $userRepository,
        FCMTokenRepository $useFCMTokenRepository,
        RequestUserRepository $requestUserRepository
    )
    {
        parent::__construct();

        $this->userRepository = $userRepository;
        $this->useFCMTokenRepository = $useFCMTokenRepository;
        $this->requestUserRepository = $requestUserRepository;
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
        $total = $this->userRepository->countSearchWithUser($searchData);

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

        $userList = $this->userRepository->searchWithUser($searchData,$numberPerPage,$offset,$orderBy);

        $this->viewData['userData'] = [
            'userList' => $userList,
            'searchValue' => [
                'user_start_date' => isset($searchData['user_start_date']) ? $searchData['user_start_date'] : null,
                'user_end_date' => isset($searchData['user_end_date']) ? $searchData['user_end_date'] : null,
                'user_status' => isset($searchData['user_status']) ? $searchData['user_status'] : null,
                'user_name' => isset($searchData['user_name']) ? $searchData['user_name'] : null,
                'user_phone' => isset($searchData['user_phone']) ? $searchData['user_phone'] : null,
                'sort' => $sort,
                'order' => $order
            ],
            "page" => [
                "total" => $total,
                "totalPage" => $totalPage,
                "currentPage" => $currentPage,
            ]
        ];

        return view('admin.user.index', $this->viewData);
    }

    /**
     * Delete user
     *
     * @param Request $request
     * @param $userId
     */
    public function delete(Request $request, $user_id)
    {
        try {
            DB::beginTransaction();

            if ( !$user_id || !is_numeric($user_id) ) {
                return redirect()->route('user.index')->withError('このユーザーは存在しません');
            }

            //check permission
            $condition = [
                ['id', "=", $user_id],
                ['type', "=", config('constants.TYPE_USER.OTHER')],
                ['is_deleted', "=", config('constants.USER_DELETED.ACTIVE')]
            ];

            $user = $this->userRepository->firstWhere($condition);

            if ( !$user ) {
                return redirect()->route('error');
            }

            $updateData = [
                'is_deleted' => config('constants.USER_DELETED.DELETED'),
            ];
            $this->userRepository->update($updateData, $user->id);

            $listRequestUser = $this->requestUserRepository->findByField('user_id', $user->id);
            foreach ($listRequestUser as $requestUser) {
                $condition = [
                    'is_expired' => config('constants.REQUEST_USERS.EXPIRED'),
                ];
                $this->requestUserRepository->update($condition, $requestUser->id);
            }

            //Push notification to user
            $collectionFcmTokenUsers = $this->useFCMTokenRepository->getDeviceToken(
                $user->id,
                config('constants.TYPE_NOTIFY.USER')
            );

            if ($collectionFcmTokenUsers->isNotEmpty()) {
                $arrayMsg = mergeArrayNotify(config('notification.TEMP_MSG_PUSH_NOTIFY.USER_UPDATE'));
                $deviceTokens = TransFormatApi::formatDataDeviceToken($collectionFcmTokenUsers);

                PushNotification::sendNotification($arrayMsg, $deviceTokens['android'], $deviceTokens['ios']);

                // Delete device token by user_id
                foreach ($collectionFcmTokenUsers as $item) {
                    $this->useFCMTokenRepository->delete($item->id);
                }
            }

            DB::commit();
            return redirect()->route('user.index')->withSuccess('1件のレコードを削除しました');
        } catch (\Exception $ex) {
            DB::rollBack();
            return redirect()->route('user.index')->withError($ex->getMessage());
        }
    }

    /**
     * Edit user.
     *
     * @param Request $request
     * @param $user_id
     */
    public function showEditForm(Request $request, $user_id)
    {
        if ( !$user_id || !is_numeric($user_id) ) {
            return redirect()->route('error');
        }

        $condition = [
            ['id', "=", $user_id],
            ['type', "=", config('constants.TYPE_USER.OTHER')],
            ['is_deleted', "=", config('constants.USER_DELETED.ACTIVE')]
        ];

        $user = $this->userRepository->firstWhere($condition);
        if ( !$user ) {
            return redirect()->route('error');
        }

        $this->viewData['userEditData'] = [
            'user' => $user,
        ];

        return $this->viewData;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(UserFormRequest $request)
    {
        $data = $request->only("user_store_created_at", "user_store_status", "user_store_name", "user_store_password", "user_store_phone", "user_store_id");

        $currentUserId = $data['user_store_id'];
        //add new user
        if ( !$currentUserId ) {
            unset($data["user_store_id"]);

            //add data to db
            $createData = [
                'raw_pass' => $data['user_store_password'],
                'password' => bcrypt($data['user_store_password']),
                'type' => config('constants.TYPE_USER.OTHER'),
                'name' => $data['user_store_name'],
                'status' => $data['user_store_status'],
                'phone' => $data['user_store_phone'],
            ];

            $createUser = $this->userRepository->create($createData);

            return redirect()->route('user.index')->withSuccess('登録しました。');
        }

        //check permission
        if (!$currentUserId || !is_numeric($currentUserId)) {
            return redirect()->route('error');
        }

        $condition = [
            ['id', "=", $currentUserId],
            ['type', "=", config('constants.TYPE_USER.OTHER')],
            ['is_deleted', "=", config('constants.USER_DELETED.ACTIVE')]
        ];
        $user = $this->userRepository->firstWhere($condition);

        if (!$user) {
            return redirect()->route('error');
        }

        // if user changes the password then removing her fcm-token
        if (($data['user_store_password'] != null && !Hash::check($data['user_store_password'], $user->password)) ||
            $data['user_store_status'] == config('constants.USER_LOGIN.STATUS_DISABLE') ||
            $data['user_store_phone'] != $user->phone) {
            //Push notification to user
            $collectionFcmTokenUsers = $this->useFCMTokenRepository->getDeviceToken(
                $user->id,
                config('constants.TYPE_NOTIFY.USER')
            );

            if ($collectionFcmTokenUsers->isNotEmpty()) {
                $arrayMsg = mergeArrayNotify(config('notification.TEMP_MSG_PUSH_NOTIFY.USER_UPDATE'));
                $deviceTokens = TransFormatApi::formatDataDeviceToken($collectionFcmTokenUsers);

                PushNotification::sendNotification($arrayMsg, $deviceTokens['android'], $deviceTokens['ios']);

                // Delete device token by user_id
                foreach ($collectionFcmTokenUsers as $item) {
                    $this->useFCMTokenRepository->delete($item->id);
                }
            }
        }

        $updateData = [
            'name' => $data['user_store_name'],
            'status' => $data['user_store_status'],
            'phone' => $data['user_store_phone'],
        ];

        // change the user's password
        if ($data['user_store_password'] && !Hash::check($data['user_store_password'], $user->password)) {
            $updateData['raw_pass'] = $data['user_store_password'];
            $updateData['password'] = bcrypt($data['user_store_password']);
        }

        $this->userRepository->update($updateData, $user->id);

        return redirect()->route('user.index')->withSuccess('更新が完了しました。');
    }
}