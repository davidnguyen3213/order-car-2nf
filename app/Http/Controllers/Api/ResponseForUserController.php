<?php

namespace App\Http\Controllers\API;

use App\Helpers\PushNotification;
use App\Helpers\TransFormatApi;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Http\Requests\CompanyApproveRequest;
use App\Http\Requests\CompanyRequestHistoryRequest;
use App\Http\Requests\CountUnapprovedRequestRequest;
use App\Http\Requests\CountUnreadResponseRequest;
use App\Http\Requests\HistoryByUserRequest;
use App\Http\Requests\ListResponseOfCompanyRequest;
use App\Http\Requests\ReadResponseRequest;
use App\Http\Requests\RegistryRequestPickupUserRequest;
use App\Http\Requests\RequestCompanyOrderRequest;
use App\Repository\Eloquent\CompanyRepository;
use App\Repository\Eloquent\FCMTokenRepository;
use App\Repository\Eloquent\RequestUserRepository;
use App\Repository\Eloquent\ResponseForUserRepository;
use App\Repository\Eloquent\UserRepository;
use Illuminate\Support\Facades\DB;


class ResponseForUserController extends BaseController
{

    protected $responseForUserRepository;
    protected $requestUserRepository;
    protected $companyRepository;
    protected $userRepository;
    protected $useFCMTokenRepository;

    public function __construct(
        ResponseForUserRepository $responseForUserRepository,
        RequestUserRepository $requestUserRepository,
        CompanyRepository $companyRepository,
        UserRepository $userRepository,
        FCMTokenRepository $useFCMTokenRepository
    )
    {
        $this->responseForUserRepository = $responseForUserRepository;
        $this->requestUserRepository = $requestUserRepository;
        $this->companyRepository = $companyRepository;
        $this->userRepository = $userRepository;
        $this->useFCMTokenRepository = $useFCMTokenRepository;
    }

    /**
     * Get list history for user api
     *
     * @param HistoryByUserRequest $request
     * @return \Illuminate\Http\Response
     */
    public function getHistoryForUser(HistoryByUserRequest $request)
    {
        try {
            $currentPage = (int)isset($request['page']) ? (int)$request['page'] : 1;

            if (!$currentPage || !is_numeric($currentPage) || $currentPage < 1) {
                $currentPage = 1;
            }

            $pagination = [
                'limit' => config('constants.NUMBER_PERPAGE'),
                'offset' => ($currentPage - 1) * config('constants.NUMBER_PERPAGE')
            ];


            $listRequest = $this->responseForUserRepository->listHistoryByUser($request['user_id'], $pagination);

            return $this->sendResponse($listRequest, __('Successfully.'));
        } catch (\Exception $ex) {
            return $this->sendError(__($ex->getMessage()));
        }
    }

    /**
     * Get list response of company
     *
     * @param ListResponseOfCompanyRequest $request
     * @return \Illuminate\Http\Response
     */
    public function getListResponseOfCompany(ListResponseOfCompanyRequest $request)
    {
        try {
            $listResponse = $this->responseForUserRepository->getListResponseByRequestId($request);
            return $this->sendResponse($listResponse, __('Successfully.'));
        } catch (\Exception $ex) {
            return $this->sendError(__($ex->getMessage()));
        }
    }

    /**
     * Request company order
     *
     * @param RequestCompanyOrderRequest $request
     * @return \Illuminate\Http\Response
     */
    public function requestCompanyOrder(RequestCompanyOrderRequest $request)
    {
        try {
            DB::beginTransaction();
            $requestUser = $this->requestUserRepository->firstWhere([
                'id' => $request['request_id'],
                'user_id' => $request['user_id']
            ]);
            // check request valid
            if (empty($requestUser)) {
                return $this->sendResponse([], __('依頼は不正です。'), true);
            }
            // check request expired or not
            if ($requestUser['is_expired'] == \Config::get('constants.REQUEST_USERS.EXPIRED')) {
                return $this->sendResponse([], __('締切時間が経過いたしました。'), true);
            }

            $company = $this->companyRepository->firstWhere([
                'id' => $request['company_id'],
                'status_login' => \Config::get('constants.COMPANY_LOGIN.STATUS_ENABLE')
            ]);
            // check company exist
            if (empty($company)) {
                return $this->sendResponse([], __('会社は存在しません。'), true);
            }

            // check response valid
            $response = $this->responseForUserRepository->firstWhere([
                'request_id' => $request['request_id'],
                'company_id' => $request['company_id'],
                'is_deleted' => \Config::get('constants.RESPONSE_FOR_USERS.NOT_DELETED'),
                'status' => \Config::get('constants.RESPONSE_FOR_USERS.STATUS_UNACCEPTED'),
            ]);

            if (empty($response)) {
                return $this->sendResponse([], __('申し込みができません。'), true);
            }
            // update to table response_for_user
            $this->responseForUserRepository->update([
                'user_accept_time' => round(microtime(true) * 1000),
                'status' => \Config::get('constants.RESPONSE_FOR_USERS.STATUS_ACCEPTED')
            ], $response['id']);
            // update to table request_user
            $this->requestUserRepository->update([
                'is_expired' => \Config::get('constants.REQUEST_USERS.EXPIRED')
            ], $request['request_id']);
            DB::commit();

            // Get all companies around from address enable notification
            $listCompany = $this->companyRepository->listCompanyFollowAddress(escape_like($requestUser['address_from']), $requestUser['id']);
            if ($listCompany->isNotEmpty()) {
                // Push notification for all company
                foreach ($listCompany as $company) {
                    $deviceTokens = TransFormatApi::formatDataPushForCompany($company);

                    $arrayMsg = mergeArrayNotify(\Config::get('notification.TEMP_MSG_PUSH_NOTIFY.USER_ACCEPT_COMPANY'));
                    $arrayMsg['request_id'] = $requestUser['id'];
                    $arrayMsg['count_unread_request'] = $this->requestUserRepository->countUnreadByAddress($company->corresponding_area, $company->id);
                    $arrayMsg['count_unapproved'] = $this->responseForUserRepository->countUnapprovedRequest($company->id);
                    $arrayMsg['is_accepted'] = \Config::get('constants.RESPONSE_FOR_USERS.STATUS_UNACCEPTED');

                    if ($company->id == $request['company_id']) {
                        $arrayMsg['company_id'] = $request['company_id'];
                        $arrayMsg['is_accepted'] = \Config::get('constants.RESPONSE_FOR_USERS.STATUS_ACCEPTED');
                    }

                    PushNotification::sendNotification($arrayMsg, $deviceTokens['android'], $deviceTokens['ios']);
                }
            }

            $collectionFcmTokenUsers = $this->useFCMTokenRepository->getDeviceToken(
                $request['user_id'],
                \Config::get('constants.TYPE_NOTIFY.USER'),
                $request['device_token']
            );

            //Push notification to other devices of user
            if ($collectionFcmTokenUsers->isNotEmpty()) {
                $arrayMsgUser = mergeArrayNotify(\Config::get('notification.TEMP_MSG_PUSH_NOTIFY.USER_ACCEPT_COMPANY'));
                $arrayMsgUser['request_id'] = $request['request_id'];
                $arrayMsgUser['company_id'] = $request['company_id'];
                $arrayMsgUser['type_app'] = \Config::get('constants.TYPE_NOTIFY.USER');
                $deviceTokensUser = TransFormatApi::formatDataDeviceToken($collectionFcmTokenUsers);

                PushNotification::sendNotification($arrayMsgUser, $deviceTokensUser['android'], $deviceTokensUser['ios']);
            }

            return $this->sendResponse([], __('Successfully.'));
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->sendError(__($ex->getMessage()));
        }
    }

    /**
     * Company agree request user
     *
     * @param RegistryRequestPickupUserRequest $request
     * @return \Illuminate\Http\Response
     */
    public function registryRequestPickupUser(RegistryRequestPickupUserRequest $request)
    {
        try {
            $input = $request->only('request_id', 'company_id', 'time_pickup', 'device_token');

            $resUser = $this->requestUserRepository->requestUser($input['request_id']);
            if (empty($resUser)) {
                return $this->sendResponse([], __('申込時間が超過しました。'), true);
            }

            // Request is exist
            // Check Company responses request to user
            if (empty($resUser->first_time_requested)) {
                $requestUserUpdate = $this->requestUserRepository->update(['first_time_requested' => round(microtime(true) * 1000)], $resUser->id);
                $firstTimeRequestedUpdate = !empty($requestUserUpdate->first_time_requested) ? $requestUserUpdate->first_time_requested : null;
            }

            $firstTimeRequested = isset($firstTimeRequestedUpdate) ? $firstTimeRequestedUpdate : $resUser->first_time_requested;
            $responseRequestUser = $this->responseForUserRepository->updateOrCreate([
                'request_id' => $input['request_id'],
                'company_id' => $input['company_id']
            ], [
                'request_id' => $input['request_id'],
                'company_id' => $input['company_id'],
                'time_pickup' => $input['time_pickup']
            ]);

            // If true then created otherwise maybe updated
            $wasCreated = $responseRequestUser->wasRecentlyCreated;

            if(!$wasCreated){
                return $this->sendResponse([], __('Successfully.'));
            }

            // Push notification to user request
            // Change pickup time
            // Company agree request user
            $collectionFcmTokenUsers = $this->useFCMTokenRepository->getDeviceToken($resUser->user_id, \Config::get('constants.TYPE_NOTIFY.USER'));
            if ($collectionFcmTokenUsers->isNotEmpty()) {
                $unreadCountOfUser = $this->responseForUserRepository->countUnreadResponseOfUser($resUser['user_id']);

                $arrayMsg = mergeArrayNotify(\Config::get('notification.TEMP_MSG_PUSH_NOTIFY.COMPANY_AGREE_REQUEST_USER'));
                $arrayMsg['request_id'] = $responseRequestUser->request_id;
                $arrayMsg['company_id'] = $responseRequestUser->company_id;
                $arrayMsg['time_pickup'] = $input['time_pickup'];
                $arrayMsg['first_time_requested'] = $firstTimeRequested;
                $arrayMsg['count_unread_response'] = $unreadCountOfUser;
                $deviceTokens = TransFormatApi::formatDataDeviceToken($collectionFcmTokenUsers);

                PushNotification::sendNotification($arrayMsg, $deviceTokens['android'], $deviceTokens['ios']);
            }
            // Push notification for company and except company pickup
            $company = $this->companyRepository->firstWhere([
                'id' => $input['company_id'],
                'status_login' => \Config::get('constants.COMPANY_LOGIN.STATUS_ENABLE')
            ]);
            //
            if (!empty($company) && isset($company['corresponding_area'])) {
                $collectionFcmTokenCompanies = $this->useFCMTokenRepository->getDeviceToken($input['company_id'], \Config::get('constants.TYPE_NOTIFY.COMPANY'), $input['device_token']);
                if ($collectionFcmTokenCompanies->isNotEmpty()) {
                    $arrayMsg = mergeArrayNotify(\Config::get('notification.TEMP_MSG_PUSH_NOTIFY.COMPANY_AGREE_REQUEST_USER'));
                    $arrayMsg['request_id'] = $responseRequestUser->request_id;
                    $arrayMsg['company_id'] = $responseRequestUser->company_id;
                    $arrayMsg['time_pickup'] = $input['time_pickup'];
                    $arrayMsg['first_time_requested'] = $firstTimeRequested;
                    $arrayMsg['type_app'] = \Config::get('constants.TYPE_NOTIFY.COMPANY');
                    $deviceTokens = TransFormatApi::formatDataDeviceToken($collectionFcmTokenCompanies);

                    PushNotification::sendNotification($arrayMsg, $deviceTokens['android'], $deviceTokens['ios']);
                }
            }

            return $this->sendResponse([], __('Successfully.'));
        } catch (\Exception $ex) {
            return $this->sendError(__($ex->getMessage()));
        }
    }

    /**
     * Get history request of company
     * @param CompanyRequestHistoryRequest $request
     * @return \Illuminate\Http\Response
     */
    public function companyRequestHistory(CompanyRequestHistoryRequest $request)
    {
        try {
            $company = $this->companyRepository->firstWhere([
                'id' => $request['company_id'],
                'status_login' => \Config::get('constants.COMPANY_LOGIN.STATUS_ENABLE')
            ]);
            // check company exist
            if (empty($company)) {
                return $this->sendResponse([], __('会社は存在しません。'), true);
            }

            $currentPage = (int)isset($request['page']) ? (int)$request['page'] : 1;

            if (!$currentPage || !is_numeric($currentPage) || $currentPage < 1) {
                $currentPage = 1;
            }

            $pagination = [
                'limit' => config('constants.NUMBER_PERPAGE'),
                'offset' => ($currentPage - 1) * config('constants.NUMBER_PERPAGE')
            ];

            // get history
            $response = $this->responseForUserRepository->getCompanyRequestHistory($request['company_id'], $pagination);

            return $this->sendResponse($response, __('Successfully.'));
        } catch (\Exception $ex) {
            return $this->sendError(__($ex->getMessage()));
        }
    }

    /**
     * Company approve request
     *
     * @param CompanyApproveRequest $request
     * @return \Illuminate\Http\Response
     */
    public function companyApprove(CompanyApproveRequest $request)
    {
        try {
            $input = $request->only('company_id', 'response_id', 'time_pickup', 'device_token');

            // check company exist
            $company = $this->companyRepository->firstWhere([
                'id' => $input['company_id'],
                'status_login' => \Config::get('constants.COMPANY_LOGIN.STATUS_ENABLE')
            ]);
            if (empty($company)) {
                return $this->sendResponse([], __('会社は存在しません。'), true);
            }

            // check response valid
            $response = $this->responseForUserRepository->firstWhere([
                'id' => $input['response_id'],
                'is_deleted' => \Config::get('constants.RESPONSE_FOR_USERS.NOT_DELETED'),
                'status' => \Config::get('constants.RESPONSE_FOR_USERS.STATUS_ACCEPTED'),
            ]);

            if (empty($response)) {
                return $this->sendResponse([], __('申し込みができません。'), true);
            }

            if ($response['is_approved'] == \Config::get('constants.RESPONSE_FOR_USERS.APPROVED')) {
                return $this->sendResponse([], __('依頼が承認されました。'), true);
            }

            // check request valid
            $requestUser = $this->requestUserRepository->firstWhere([
                'id' => $response['request_id'],
                'is_expired' => \Config::get('constants.REQUEST_USERS.EXPIRED')
            ]);

            if (empty($requestUser)) {
                return $this->sendResponse([], __('依頼は不正です。'), true);
            }

            // check user
            $user = $this->userRepository->firstWhere([
                'id' => $requestUser['user_id'],
                'type' => \Config::get('constants.TYPE_USER.OTHER'),
                'status' => \Config::get('constants.USER_LOGIN.STATUS_ENABLE'),
            ]);
            if (empty($user)) {
                return $this->sendResponse([], __('ユーザーが無効になりました。'), true);
            }

            // update to table response_for_user
            $this->responseForUserRepository->update([
                'time_pickup' => $input['time_pickup'],
                'is_approved' => \Config::get('constants.RESPONSE_FOR_USERS.APPROVED')
            ], $response['id']);

            //Push notification to user
            $collectionFcmTokenUsers = $this->useFCMTokenRepository->getDeviceToken($requestUser['user_id'], \Config::get('constants.TYPE_NOTIFY.USER'));
            if ($collectionFcmTokenUsers->isNotEmpty()) {
                $arrayMsg = mergeArrayNotify(\Config::get('notification.TEMP_MSG_PUSH_NOTIFY.COMPANY_APPROVE_USER'));
                $arrayMsg['request_id'] = $response['request_id'];
                $arrayMsg['company_id'] = $input['company_id'];
                $arrayMsg['time_pickup'] = $input['time_pickup'];
                $deviceTokens = TransFormatApi::formatDataDeviceToken($collectionFcmTokenUsers);

                PushNotification::sendNotification($arrayMsg, $deviceTokens['android'], $deviceTokens['ios']);
            }

            // Push notification for company and except company approval request
            $collectionFcmTokenCompanies = $this->useFCMTokenRepository->getDeviceToken($input['company_id'], \Config::get('constants.TYPE_NOTIFY.COMPANY'), $input['device_token']);
            if ($collectionFcmTokenCompanies->isNotEmpty()) {
                $countUnapproved = $this->responseForUserRepository->countUnapprovedRequest($input['company_id']);
                $countUnreadRequest = $this->requestUserRepository->countUnreadByAddress($company['corresponding_area'], $company['id']);

                $arrayMsg = mergeArrayNotify(\Config::get('notification.TEMP_MSG_PUSH_NOTIFY.COMPANY_APPROVE_USER'));
                $arrayMsg['request_id'] = $response['request_id'];
                $arrayMsg['company_id'] = $input['company_id'];
                $arrayMsg['time_pickup'] = $input['time_pickup'];
                $arrayMsg['type_app'] = \Config::get('constants.TYPE_NOTIFY.COMPANY');
                $arrayMsg['count_unapproved'] = $countUnapproved;
                $arrayMsg['count_unread_request'] = $countUnreadRequest;

                $deviceTokens = TransFormatApi::formatDataDeviceToken($collectionFcmTokenCompanies);

                PushNotification::sendNotification($arrayMsg, $deviceTokens['android'], $deviceTokens['ios']);
            }

            return $this->sendResponse([], __('Successfully.'));
        } catch (\Exception $ex) {
            return $this->sendError(__($ex->getMessage()));
        }
    }

    /**
     * Read response
     *
     * @param ReadResponseRequest $request
     * @return \Illuminate\Http\Response
     */
    public function readResponse(ReadResponseRequest $request)
    {
        try {
            $requestUser = $this->requestUserRepository->firstWhere([
                'id' => $request['request_id'],
            ]);

            // check request valid
            if (empty($requestUser)) {
                return $this->sendResponse([], __('依頼は不正です。'), true);
            }

            // get number of response by request id
            $countResponse = $this->responseForUserRepository->countWhere([
                'request_id' => $request['request_id'],
                'is_read' => \Config::get('constants.RESPONSE_FOR_USERS.UNREAD')
            ]);

            if ($countResponse > 0) {
                // update to table response_for_user
                $this->responseForUserRepository->updateMultipleRows([
                    'request_id' => $request['request_id'],
                ], [
                    'is_read' => \Config::get('constants.RESPONSE_FOR_USERS.READ')
                ]);

                // Push notification for user and except user read response
                $user = $this->userRepository->firstWhere([
                    'id' => $requestUser['user_id'],
                    'type' => \Config::get('constants.TYPE_USER.OTHER'),
                    'status' => \Config::get('constants.USER_LOGIN.STATUS_ENABLE'),
                ]);
                if (!empty($user)) {
                    $collectionFcmTokenUser = $this->useFCMTokenRepository->getDeviceToken($requestUser['user_id'], \Config::get('constants.TYPE_NOTIFY.USER'), $request['device_token']);
                    if ($collectionFcmTokenUser->isNotEmpty()) {
                        // count all response of user
                        $countUnreadResponseOfUser = $this->responseForUserRepository->countUnreadResponseOfUser($requestUser['user_id']);

                        $arrayMsg = mergeArrayNotify(\Config::get('notification.TEMP_MSG_PUSH_NOTIFY.SYNC_USER_READ_RESPONSE'));
                        $arrayMsg['request_id'] = $request['request_id'];
                        $arrayMsg['count_unread_response'] = $countUnreadResponseOfUser;
                        $deviceTokens = TransFormatApi::formatDataDeviceToken($collectionFcmTokenUser);

                        PushNotification::sendNotification($arrayMsg, $deviceTokens['android'], $deviceTokens['ios']);
                    }
                }
            }

            return $this->sendResponse([], __('Successfully.'));
        } catch (\Exception $ex) {
            return $this->sendError(__($ex->getMessage()));
        }
    }


    /**
     * Count Unread response
     *
     * @param CountUnreadResponseRequest $request
     * @return \Illuminate\Http\Response
     */
    public function countUnreadResponse(CountUnreadResponseRequest $request)
    {
        try {
            // check user
            $user = $this->userRepository->firstWhere([
                'id' => $request['user_id'],
                'type' => \Config::get('constants.TYPE_USER.OTHER'),
                'status' => \Config::get('constants.USER_LOGIN.STATUS_ENABLE'),
            ]);
            if (empty($user)) {
                return $this->sendResponse([], __('ユーザーが無効になりました。'), true);
            }

            // count all response of user
            $countUnreadResponseOfUser = $this->responseForUserRepository->countUnreadResponseOfUser($request['user_id']);

            return $this->sendResponse(['count' => $countUnreadResponseOfUser], __('Successfully.'));
        } catch (\Exception $ex) {
            return $this->sendError(__($ex->getMessage()));
        }
    }

    /**
     * Get count of unapproved request of company
     * @param CountUnapprovedRequestRequest $request
     * @return \Illuminate\Http\Response
     */
    public function countUnapprovedRequest(CountUnapprovedRequestRequest $request)
    {
        try {
            $company = $this->companyRepository->firstWhere([
                'id' => $request['company_id'],
                'status_login' => \Config::get('constants.COMPANY_LOGIN.STATUS_ENABLE')
            ]);
            // check company exist
            if (empty($company)) {
                return $this->sendResponse([], __('会社は存在しません。'), true);
            }

            // get count
            $count = $this->responseForUserRepository->countUnapprovedRequest($request['company_id']);

            return $this->sendResponse(['count' => $count], __('Successfully.'));
        } catch (\Exception $ex) {
            return $this->sendError(__($ex->getMessage()));
        }
    }
}
