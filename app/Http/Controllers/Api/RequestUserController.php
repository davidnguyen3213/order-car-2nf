<?php

namespace App\Http\Controllers\API;

use App\Helpers\PushNotification;
use App\Helpers\TransFormatApi;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Http\Requests\AddRequestCompany;
use App\Http\Requests\CancelRequestByCompanyRequest;
use App\Http\Requests\CancelRequestByUserRequest;
use App\Http\Requests\DeleteHistoryByUserRequest;
use App\Http\Requests\DeleteSuggestedAddressToRequest;
use App\Http\Requests\DeleteSuggestedNoteRequest;
use App\Http\Requests\ExpireRequestByCompanyRequest;
use App\Http\Requests\ListUserRequest;
use App\Http\Requests\FrequencyUserRequest;
use App\Repository\Eloquent\CompanyRepository;
use App\Repository\Eloquent\FCMTokenRepository;
use App\Repository\Eloquent\RequestUserRepository;
use App\Repository\Eloquent\ResponseForUserRepository;

class RequestUserController extends BaseController
{

    protected $requestUserRepository;
    protected $responseForUserRepository;
    protected $useFCMTokenRepository;
    protected $companyRepository;
    const FREQUENCY_REQUEST_USER_NOTE = 'address_note';
    const FREQUENCY_REQUEST_USER_ADDRESS_TO = 'address_to';
    const IS_DELETED_NOTE = 'is_deleted_note';
    const IS_DELETED_ADDRESS = 'is_deleted_address';

    public function __construct(
        RequestUserRepository $requestUserRepository,
        ResponseForUserRepository $responseForUserRepository,
        FCMTokenRepository $useFCMTokenRepository,
        CompanyRepository $companyRepository
    )
    {
        $this->requestUserRepository = $requestUserRepository;
        $this->responseForUserRepository = $responseForUserRepository;
        $this->useFCMTokenRepository = $useFCMTokenRepository;
        $this->companyRepository = $companyRepository;
    }

    /**
     * Cancel Request By User api
     *
     * @param CancelRequestByUserRequest $request
     * @return \Illuminate\Http\Response
     */
    public function cancelRequestByUser(CancelRequestByUserRequest $request)
    {
        try {
            $data = $request->all();
            $requestUser = $this->requestUserRepository->firstWhere(['id' => $data['request_id']]);

            if (empty($requestUser) || $requestUser['is_expired'] == \Config::get('constants.REQUEST_USERS.CANCELED')) {
                return $this->sendResponse([], __('依頼はキャンセルされました。'), true);
            }

            $listResponse = $this->responseForUserRepository->firstWhere([
                'request_id' => $data['request_id'],
                'status' => \Config::get('constants.RESPONSE_FOR_USERS.STATUS_ACCEPTED'),
            ]);

            if (!empty($listResponse)) {
                return $this->sendResponse([], __('登録された依頼はキャンセルできません。'), true);
            }

            if(isset($data['is_expired']) && $data['is_expired'] == \Config::get('constants.REQUEST_USERS.EXPIRED')){
                $this->requestUserRepository->update([
                    'is_expired' => \Config::get('constants.REQUEST_USERS.EXPIRED'),
                ], $data['request_id']);
            }
            else{
                $this->requestUserRepository->update([
                    'is_expired' => \Config::get('constants.REQUEST_USERS.EXPIRED'),
                    'is_cancel' => \Config::get('constants.REQUEST_USERS.CANCELED'),
                ], $data['request_id']);
            }

            $tempMessageCompany = isset($data['is_expired']) && $data['is_expired'] == \Config::get('constants.REQUEST_USERS.EXPIRED') ?
                mergeArrayNotify(\Config::get('notification.TEMP_MSG_PUSH_NOTIFY.REQUEST_USER_EXPIRED_TO_COMPANY_AGREE'))
                : mergeArrayNotify(\Config::get('notification.TEMP_MSG_PUSH_NOTIFY.USER_CANCEL_REQUEST'));

            // Get all companies around from address
            $listCompany = $this->companyRepository->listCompanyFollowAddress(escape_like($requestUser['address_from']), $data['request_id']);
            if ($listCompany->isNotEmpty()) {
                // Push notification for all company
                $arrayMsg = $tempMessageCompany;
                $arrayMsg['request_id'] = $data['request_id'];
                $arrayMsg['type_app'] = \Config::get('constants.TYPE_NOTIFY.COMPANY');

                foreach ($listCompany as $company) {
                    $countUnreadRequest = $this->requestUserRepository->countUnreadByAddress($company->corresponding_area, $company->id);
                    $deviceTokens = TransFormatApi::formatDataPushForCompany($company, $data['request_id']);
                    $arrayMsg['count_unread_request'] = $countUnreadRequest;
                    $arrayMsg['count_unapproved'] = $this->responseForUserRepository->countUnapprovedRequest($company->id);
                    $arrayMsg['is_responded'] = isset($deviceTokens['is_responded']) ? $deviceTokens['is_responded'] : '';
                    PushNotification::sendNotification($arrayMsg, $deviceTokens['android'], $deviceTokens['ios']);
                }
            }

            $collectionFcmTokenUsers = $this->useFCMTokenRepository->getDeviceToken(
                $data['user_id'],
                \Config::get('constants.TYPE_NOTIFY.USER'),
                $data['device_token']
            );

            $tempMessage = isset($data['is_expired']) && $data['is_expired'] == \Config::get('constants.REQUEST_USERS.EXPIRED') ?
                mergeArrayNotify(\Config::get('notification.TEMP_MSG_PUSH_NOTIFY.REQUEST_USER_EXPIRED_TO_USER'))
                : mergeArrayNotify(\Config::get('notification.TEMP_MSG_PUSH_NOTIFY.USER_CANCEL_REQUEST'));

            //Push notification to other devices of user
            if ($collectionFcmTokenUsers->isNotEmpty()) {
                $unreadCountOfUser = $this->responseForUserRepository->countUnreadResponseOfUser($data['user_id']);
                $arrayMsgUser = $tempMessage;

                $arrayMsgUser['request_id'] = $data['request_id'];
                $arrayMsgUser['type_app'] = \Config::get('constants.TYPE_NOTIFY.USER');
                $arrayMsgUser['count_unread_response'] = $unreadCountOfUser;
                $deviceTokensUser = TransFormatApi::formatDataDeviceToken($collectionFcmTokenUsers);

                PushNotification::sendNotification($arrayMsgUser, $deviceTokensUser['android'], $deviceTokensUser['ios']);
            }

            return $this->sendResponse([], __('Successfully.'));
        } catch (\Exception $ex) {
            return $this->sendError(__($ex->getMessage()));
        }
    }

    /**
     * Add Request Company api
     *
     * @param AddRequestCompany $request
     * @return \Illuminate\Http\Response
     */
    public function addRequestCompany(AddRequestCompany $request)
    {
        try {
            $data = $request->all();
            // count all companies around from address
            $listCompany = $this->companyRepository->listCompanyNearByAddress(escape_like($data['address_from']));
            $countCompanies = $listCompany->count();
            // check if have no company around from address
            if ($countCompanies == 0) {
                return $this->sendResponse([], __('Error.'), true);
            }

            // get current time in milliseconds
            $data['created_time_request'] = round(microtime(true) * 1000);

            // create request
            $createRequest = $this->requestUserRepository->create($data);
            // Get all companies around from address enable notification
            if ($listCompany->isNotEmpty()) {
                // Push notification for all company
                $arrayMsg = mergeArrayNotify(\Config::get('notification.TEMP_MSG_PUSH_NOTIFY.USER_TO_ALL_COMPANY'));
                $arrayMsg['request_id'] = $createRequest->id;

                $data1 = [];
                foreach ($listCompany as $company) {
                    $countUnreadRequest = $this->requestUserRepository->countUnreadByAddress($company->corresponding_area, $company->id);
                    $deviceTokens = TransFormatApi::formatDataPushForCompany($company);
                    $arrayMsg['count_unread_request'] = $countUnreadRequest;
                    $arrayMsg['count_unapproved'] = $this->responseForUserRepository->countUnapprovedRequest($company->id);
                    PushNotification::sendNotification($arrayMsg, $deviceTokens['android'], $deviceTokens['ios']);
                    //$data1[] = [
                    //    $arrayMsg,
                    //    $deviceTokens
                    //];
                }
                //$this->sendNotification($data1);
            }

            $collectionFcmTokenUsers = $this->useFCMTokenRepository->getDeviceToken(
                $data['user_id'],
                \Config::get('constants.TYPE_NOTIFY.USER'),
                $data['device_token']
            );
           
            //Push notification to other devices of user
            if ($collectionFcmTokenUsers->isNotEmpty()) {
                $arrayMsgUser = mergeArrayNotify(\Config::get('notification.TEMP_MSG_PUSH_NOTIFY.USER_TO_ALL_COMPANY'));
                $arrayMsgUser['request_id'] = $createRequest->id;
                $arrayMsgUser['type_app'] = \Config::get('constants.TYPE_NOTIFY.USER');
                $deviceTokensUser = TransFormatApi::formatDataDeviceToken($collectionFcmTokenUsers);
                //$this->sendNotification([[$arrayMsgUser, $deviceTokensUser]]);
                PushNotification::sendNotification($arrayMsgUser, $deviceTokensUser['android'], $deviceTokensUser['ios']);
            }

            return $this->sendResponse([], __('Successfully.'));
        } catch (\Exception $ex) {
            return $this->sendError(__($ex->getMessage()));
        }
    }

    private function sendNotification($data) {
        $tmp = storage_path('tmp');
        $filename = $tmp . '/'. time();
        if (!file_exists($tmp)) {
            mkdir($tmp);
        }
        file_put_contents($filename, json_encode($data));
        if (strtoupper(substr(php_uname(), 0, 7)) === 'WINDOWS') {
            $command = 'cd "' . base_path() . '" && start /B php artisan notification:send "' . $filename . '"';
        } else {
            $command = 'cd "' . base_path() . '" && php artisan notification:send "' . $filename . '" > /dev/null 2>&1 &';
        }
        exec($command);
    }

    /**
     * List requests of user
     *
     * @param ListUserRequest $request
     * @return \Illuminate\Http\Response
     */
    public function listUserRequest(ListUserRequest $request)
    {
        try {
            $listRequest = $this->requestUserRepository->listUnexpiredRequestOfUser($request['user_id']);
            if (!empty($listRequest)) {
                foreach ($listRequest as $key => $requestUser) {
                    $listRequest[$key]['number_response'] = $this->responseForUserRepository->countWhere([
                        'request_id' => $requestUser->id,
                        'is_deleted' => \Config::get('constants.RESPONSE_FOR_USERS.NOT_DELETED')
                    ]);
                }
            }
            return $this->sendResponse($listRequest, __('Successfully.'));
        } catch (\Exception $ex) {
            return $this->sendError(__($ex->getMessage()));
        }
    }

    /**
     * Api Delete history by User
     *
     * @param DeleteHistoryByUserRequest $request
     * @return \Illuminate\Http\Response
     */
    public function deleteHistory(DeleteHistoryByUserRequest $request)
    {
        try {
            $requestUser = $this->requestUserRepository->firstWhere([
                'id' => $request['request_id'],
                'user_id' => $request['user_id']
            ]);

            if (empty($requestUser)) {
                return $this->sendResponse([], __('削除できません。'), true);
            }

            $responseRequest = $this->responseForUserRepository->firstWhere([
                ['request_id', $request['request_id']],
                ['status', \Config::get('constants.RESPONSE_FOR_USERS.STATUS_ACCEPTED')]
            ]);

            if (empty($responseRequest)) {
                return $this->sendResponse([], __('削除できません。'), true);
            }

            $this->requestUserRepository->update([
                'is_history_deleted' => \Config::get('constants.REQUEST_USERS.IS_HISTORY_DELETED'),
            ], $requestUser->id);

            $collectionFcmTokenUsers = $this->useFCMTokenRepository->getDeviceToken(
                $request['user_id'],
                \Config::get('constants.TYPE_NOTIFY.USER'),
                $request['device_token']
            );

            //Push notification to other devices of user
            if ($collectionFcmTokenUsers->isNotEmpty()) {
                $arrayMsgUser = mergeArrayNotify(\Config::get('notification.TEMP_MSG_PUSH_NOTIFY.USER_DELETE_HISTORY'));
                $arrayMsgUser['request_id'] = $request['request_id'];
                $deviceTokensUser = TransFormatApi::formatDataDeviceToken($collectionFcmTokenUsers);

                PushNotification::sendNotification($arrayMsgUser, $deviceTokensUser['android'], $deviceTokensUser['ios']);
            }
            return $this->sendResponse([], __('Successfully.'));
        } catch (\Exception $ex) {
            return $this->sendError(__($ex->getMessage()));
        }
    }

    /**
     * Company cancel request user
     *
     * @param CancelRequestByCompanyRequest $request
     * @return \Illuminate\Http\Response
     */
    public function cancelRequestByCompany(CancelRequestByCompanyRequest $request)
    {
        try {
            $requestUser = $this->requestUserRepository->requestUser($request['request_id']);
            if (!empty($requestUser)) {
                $responseForUser = $this->responseForUserRepository->firstWhere([
                    ['request_id', $request['request_id']],
                    ['company_id', $request['company_id']]
                ]);

                if (!empty($responseForUser)) {
                    if ($responseForUser->status == \Config::get('constants.RESPONSE_FOR_USERS.STATUS_ACCEPTED') || $responseForUser->is_approved == \Config::get('constants.RESPONSE_FOR_USERS.APPROVED')) {
                        return $this->sendResponse([], __('削除できません。'), true);
                    }

                    $this->responseForUserRepository->delete($responseForUser->id);

                    // Push notification for company
                    $collectionFcmTokenCompanies = $this->useFCMTokenRepository->getDeviceToken($request['company_id'], \Config::get('constants.TYPE_NOTIFY.COMPANY'), $request['device_token']);
                    if ($collectionFcmTokenCompanies->isNotEmpty()) {
                        $arrayMsg = mergeArrayNotify(\Config::get('notification.TEMP_MSG_PUSH_NOTIFY.COMPANY_CANCEL_REQUEST'));
                        $arrayMsg['request_id'] = $request['request_id'];
                        $arrayMsg['company_id'] = $request['company_id'];
                        $arrayMsg['type_app'] = \Config::get('constants.TYPE_NOTIFY.COMPANY');
                        $deviceTokens = TransFormatApi::formatDataDeviceToken($collectionFcmTokenCompanies);

                        PushNotification::sendNotification($arrayMsg, $deviceTokens['android'], $deviceTokens['ios']);
                    }

                    // Push notification for user
                    $collectionFcmTokenUser = $this->useFCMTokenRepository->getDeviceToken($requestUser->user_id, \Config::get('constants.TYPE_NOTIFY.USER'));
                    if ($collectionFcmTokenUser->isNotEmpty()) {
                        $countUnreadResponseOfUser = $this->responseForUserRepository->countUnreadResponseOfUser($requestUser->user_id);
                        $arrayMsg = mergeArrayNotify(\Config::get('notification.TEMP_MSG_PUSH_NOTIFY.COMPANY_CANCEL_REQUEST'));
                        $arrayMsg['request_id'] = $request['request_id'];
                        $arrayMsg['company_id'] = $request['company_id'];
                        $arrayMsg['type_app'] = \Config::get('constants.TYPE_NOTIFY.USER');
                        $arrayMsg['count_unread_response'] = $countUnreadResponseOfUser;
                        $deviceTokens = TransFormatApi::formatDataDeviceToken($collectionFcmTokenUser);

                        PushNotification::sendNotification($arrayMsg, $deviceTokens['android'], $deviceTokens['ios']);
                    }
                }

                return $this->sendResponse([], __('Successfully.'));
            }

            return $this->sendResponse([], __('削除できません。'), true);
        } catch (\Exception $ex) {
            return $this->sendError(__($ex->getMessage()));
        }
    }

    /**
     * Expire Request By Company api
     *
     * @param ExpireRequestByCompanyRequest $request
     * @return \Illuminate\Http\Response
     */
    public function expireRequestByCompany(ExpireRequestByCompanyRequest $request)
    {
        try {
            $data = $request->all();
            $requestUser = $this->requestUserRepository->firstWhere(['id' => $data['request_id']]);

            if (empty($requestUser) || $requestUser['is_expired'] == \Config::get('constants.REQUEST_USERS.EXPIRED')) {
                return $this->sendResponse([], __('依頼はキャンセルされました。'), true);
            }

            $now = round(microtime(true) * 1000);
            $userWaitTime = \Config::get('constants.REQUEST_MINUTE_EXISTED.USER_WAIT') * 60 * 1000;
            $companyWaitTime = \Config::get('constants.REQUEST_MINUTE_EXISTED.COMPANY_WAIT') * 60 * 1000;
            if (
                (empty($requestUser['first_time_requested']) && ($now - $requestUser['created_time_request'] <= $userWaitTime))
                || (!empty($requestUser['first_time_requested']) && ($now - $requestUser['created_time_request'] <= $companyWaitTime))
            ) {
                return $this->sendResponse([], __('依頼はキャンセルされました。'), true);
            }

            $this->requestUserRepository->update([
                'is_expired' => \Config::get('constants.REQUEST_USERS.EXPIRED')
            ], $data['request_id']);

            // Get all companies around from address
            $listCompany = $this->companyRepository->listCompanyFollowAddress(escape_like($requestUser['address_from']), $data['request_id']);
            if ($listCompany->isNotEmpty()) {
                // Push notification for all company
                $arrayMsg = mergeArrayNotify(\Config::get('notification.TEMP_MSG_PUSH_NOTIFY.REQUEST_USER_EXPIRED_TO_COMPANY_AGREE'));;
                $arrayMsg['request_id'] = $data['request_id'];
                $arrayMsg['type_app'] = \Config::get('constants.TYPE_NOTIFY.COMPANY');

                foreach ($listCompany as $company) {
                    $countUnreadRequest = $this->requestUserRepository->countUnreadByAddress($company->corresponding_area, $company->id);
                    $deviceTokens = TransFormatApi::formatDataPushForCompany($company, $data['request_id']);
                    $arrayMsg['count_unread_request'] = $countUnreadRequest;
                    $arrayMsg['count_unapproved'] = $this->responseForUserRepository->countUnapprovedRequest($company->id);
                    $arrayMsg['is_responded'] = isset($deviceTokens['is_responded']) ? $deviceTokens['is_responded'] : '';
                    PushNotification::sendNotification($arrayMsg, $deviceTokens['android'], $deviceTokens['ios']);
                }
            }

            $collectionFcmTokenUsers = $this->useFCMTokenRepository->getDeviceToken(
                $requestUser['user_id'],
                \Config::get('constants.TYPE_NOTIFY.USER')
            );
            //Push notification to other devices of user
            if ($collectionFcmTokenUsers->isNotEmpty()) {
                $unreadCountOfUser = $this->responseForUserRepository->countUnreadResponseOfUser($data['user_id']);
                $arrayMsgUser = mergeArrayNotify(\Config::get('notification.TEMP_MSG_PUSH_NOTIFY.REQUEST_USER_EXPIRED_TO_USER'));

                $arrayMsgUser['request_id'] = $data['request_id'];
                $arrayMsgUser['type_app'] = \Config::get('constants.TYPE_NOTIFY.USER');
                $arrayMsgUser['count_unread_response'] = $unreadCountOfUser;
                $deviceTokensUser = TransFormatApi::formatDataDeviceToken($collectionFcmTokenUsers);

                PushNotification::sendNotification($arrayMsgUser, $deviceTokensUser['android'], $deviceTokensUser['ios']);
            }

            return $this->sendResponse([], __('Successfully.'));
        } catch (\Exception $ex) {
            return $this->sendError(__($ex->getMessage()));
        }
    }

    /**
     * Get list note of request user
     *
     * @param FrequencyUserRequest $request
     * @return \Illuminate\Http\Response
     */
    public function getFrequencyUserByNote(FrequencyUserRequest $request)
    {
        try {
            $frequencyRequestUsers = $this->requestUserRepository->listFrequencyRequestUser($request['user_id'], self::FREQUENCY_REQUEST_USER_NOTE, self::IS_DELETED_NOTE);

            return $this->sendResponse($frequencyRequestUsers, __('Successfully.'));
        } catch (\Exception $ex) {
            return $this->sendError(__($ex->getMessage()));
        }
    }

    /**
     * Get list address to of request user
     *
     * @param FrequencyUserRequest $request
     * @return \Illuminate\Http\Response
     */
    public function getFrequencyUserByAddressTo(FrequencyUserRequest $request)
    {
        try {
            $frequencyRequestUsers = $this->requestUserRepository->listFrequencyRequestUser($request['user_id'], self::FREQUENCY_REQUEST_USER_ADDRESS_TO, self::IS_DELETED_ADDRESS);

            return $this->sendResponse($frequencyRequestUsers, __('Successfully.'));
        } catch (\Exception $ex) {
            return $this->sendError(__($ex->getMessage()));
        }
    }

    /**
     * Delete suggested note
     * @param DeleteSuggestedNoteRequest $request
     * @return \Illuminate\Http\Response
     */
    public function deleteSuggestedNote(DeleteSuggestedNoteRequest $request)
    {
        try {
            $this->requestUserRepository->updateMultipleRows(
                [
                    'user_id' => $request['user_id'],
                    'address_note' => $request['note']
                ],
                [
                    'is_deleted_note' => \Config::get('constants.REQUEST_USERS.IS_DELETED_FREQUENCY')
                ]
            );
            return $this->sendResponse([], __('Successfully.'));
        } catch (\Exception $ex) {
            return $this->sendError(__($ex->getMessage()));
        }
    }

    /**
     * Delete suggested address to
     * @param DeleteSuggestedAddressToRequest $request
     * @return \Illuminate\Http\Response
     */
    public function deleteSuggestedAddressTo(DeleteSuggestedAddressToRequest $request)
    {
        try {
            $this->requestUserRepository->updateMultipleRows(
                [
                    'user_id' => $request['user_id'],
                    'address_to' => $request['address']
                ],
                [
                    'is_deleted_address' => \Config::get('constants.REQUEST_USERS.IS_DELETED_FREQUENCY')
                ]
            );
            return $this->sendResponse([], __('Successfully.'));
        } catch (\Exception $ex) {
            return $this->sendError(__($ex->getMessage()));
        }
    }
}
