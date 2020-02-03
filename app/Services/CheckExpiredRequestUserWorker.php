<?php

namespace App\Services;


use App\Helpers\TransFormatApi;
use App\Repository\Eloquent\CompanyRepository;
use App\Repository\Eloquent\RequestUserRepository;
use App\Repository\Eloquent\ResponseForUserRepository;

class CheckExpiredRequestUserWorker extends PushNotificationWorker
{

    const IS_RESPONDED = 1;
    const IS_NOT_RESPONDED = 0;

    protected $requestUserRepository;
    protected $responseForUserRepository;
    protected $companyRepository;

    function __construct(
        RequestUserRepository $requestUserRepository,
        ResponseForUserRepository $responseForUserRepository,
        CompanyRepository $companyRepository
    )
    {
        $this->requestUserRepository = $requestUserRepository;
        $this->responseForUserRepository = $responseForUserRepository;
        $this->companyRepository = $companyRepository;
    }

    /**
     * Call run request user expired
     */
    public function run()
    {
        $collectionRequestUser = $this->_processRequestUserExpired();
    }

    /**
     * Process check request user expires
     */
    private function _processRequestUserExpired()
    {
        $collectionRequestUserExpired = $this->requestUserRepository->collectionRequestUserExpired();

        // Update request user expired
        $collectionRequestUserID = $collectionRequestUserExpired->map(function ($itemRequestUser) {
            return $itemRequestUser->id;
        });
        if ($collectionRequestUserID->isNotEmpty()) {
            $this->_updateRequestUser($collectionRequestUserID->toArray());
        }

        $collectionFCMTokens = $this->_formatDataRequestUser($collectionRequestUserExpired);

        if (isset($collectionFCMTokens['collectionRequestUserExpiredFormat'])) {
            //process request user expired with company unpick
            self::_formatDataRequestUserByCompanyUnpick($collectionFCMTokens['collectionRequestUserExpiredFormat']);
        }

        // Push notification to User
        if (isset($collectionFCMTokens['collectionFCMTokensUsers'])) {
            self::_requestUserExpiredTwentyMinute($collectionFCMTokens['collectionFCMTokensUsers']);
        }

        // Push notification to Company
        if (isset($collectionFCMTokens['collectionFCMTokensCompanies'])) {
            self::_requestUserExpiredFiveMinute($collectionFCMTokens['collectionFCMTokensCompanies']);
        }

    }

    /**
     * Update request user expired
     *
     * @param $listRequestUserID
     */
    private function _updateRequestUser($listRequestUserID)
    {
        if (empty($listRequestUserID)) {
            return;
        }
        // update multiple rows by ID
        $this->requestUserRepository->updateMultipleRowsByID($listRequestUserID, ['is_expired' => \Config::get('constants.REQUEST_USERS.EXPIRED')]);
    }

    /**
     * Format data request user expired Raw
     *
     * @param $collectionRequestUserExpired
     * @return array
     */
    private function _formatDataRequestUser($collectionRequestUserExpired)
    {
        if ($collectionRequestUserExpired->isEmpty()) {
            return [];
        }

        $collectionFCMTokensUsers = [];
        $collectionFCMTokensCompanies = [];
        foreach ($collectionRequestUserExpired as $collection => $itemRequest) {
            $itemRequest->listCompanyFollowAddress = $this->companyRepository->listCompanyFollowAddress($itemRequest->address_from, $itemRequest->id);
            $collectionResponseForUsers = $itemRequest->activeResponseUser;

            $itemRequest->isStatusOrApproved = false;
            if ($collectionResponseForUsers->isNotEmpty()) {
                foreach ($collectionResponseForUsers as $keyResponseForUsers => $itemResponseForUsers) {
                    if (($itemResponseForUsers->status == \Config::get('constants.RESPONSE_FOR_USERS.STATUS_ACCEPTED'))
                        || ($itemResponseForUsers->is_approved == \Config::get('constants.RESPONSE_FOR_USERS.APPROVED')))
                    {
                        $itemRequest->isStatusOrApproved = true;
                        break;
                    }
                }
            }

            if ($itemRequest->isStatusOrApproved == true) {
                continue;
            }

            $collectionFCMTokensCompanies = array_merge($collectionFCMTokensCompanies, self::_collectionFCMTokensCompanies($itemRequest));

            $collectionFCMTokensUsers = array_merge($collectionFCMTokensUsers, self::_collectionFCMTokensUsers($itemRequest));
        }

        $resultData = [
            'collectionFCMTokensUsers' => collect($collectionFCMTokensUsers),
            'collectionFCMTokensCompanies' => collect($collectionFCMTokensCompanies),
            'collectionRequestUserExpiredFormat' => $collectionRequestUserExpired
        ];

        return $resultData;
    }

    /**
     * @param $itemRequest
     * @param array $collectionFCMTokensCompanies
     * @return array
     */
    private function _collectionFCMTokensCompanies(&$itemRequest, $collectionFCMTokensCompanies = [])
    {
        $collectionResponseForUsers = $itemRequest->responseForUsers;
        if ($collectionResponseForUsers->isNotEmpty()) {
            $companyIDByCompanyResponse = [];
            foreach ($collectionResponseForUsers as $keyResponseForUsers => $itemResponseForUsers) {
                $collectionCompany = $itemResponseForUsers->responseForUserOfCompany;
                $companyIDByCompanyResponse[] = $collectionCompany->id;

                // Check not empty collectionCompany
                if (empty($collectionCompany)) {
                    continue;
                }
                $collectionFCMTokensCompany = $collectionCompany->companyFcmTokens;
                // Check not empty collectionFCMTokensCompany
                if ($collectionFCMTokensCompany->isEmpty()) {
                    continue;
                }

                if (trim($collectionCompany->corresponding_area) == '') {
                    continue;
                }

                // check company canceled request
                if ($itemResponseForUsers->is_deleted == \Config::get('constants.RESPONSE_FOR_USERS.DELETED')) {
                    continue;
                }

                $countUnreadRequest = $this->requestUserRepository->countUnreadByAddress($collectionCompany->corresponding_area, $collectionCompany->id);
                $countUnApproved = $this->responseForUserRepository->countUnapprovedRequest($collectionCompany->id);

                foreach ($collectionFCMTokensCompany as $itemFCMTokensCompany) {
                    $itemFCMTokensCompany->request_id = $itemRequest->id;
                    $itemFCMTokensCompany->company_id = $collectionCompany->id;
                    $itemFCMTokensCompany->user_id = null;
                    $itemFCMTokensCompany->isStatusOrApproved = $itemRequest->isStatusOrApproved;

                    $collectCompanies['collectionFCMTokensCompanies'] = $itemFCMTokensCompany;
                    $collectCompanies['countUnreadRequest'] = $countUnreadRequest;
                    $collectCompanies['countUnApproved'] = $countUnApproved;
                    $collectCompanies['is_responded'] = self::IS_RESPONDED;

                    $collectionFCMTokensCompanies[] = $collectCompanies;
                }
            }
            $itemRequest->listCompanyIDByCompanyResponse = $companyIDByCompanyResponse;
        }

        return $collectionFCMTokensCompanies;
    }

    /**
     * @param $itemRequest
     * @param array $collectionFCMTokensUsers
     * @return array
     */
    private function _collectionFCMTokensUsers(&$itemRequest, $collectionFCMTokensUsers = [])
    {
        $collectionUser = $itemRequest->activeUser;
        if (!empty($collectionUser)) {
            $collectionFCMTokensUser = $collectionUser->userFcmTokens;
            // Check not empty collectionFCMTokensUser
            if (empty($collectionFCMTokensUser)) {
                return $collectionFCMTokensUsers;
            }

            //count_unread_response
            $countUnreadResponse = $this->responseForUserRepository->countUnreadResponseOfUser($collectionUser->id);

            foreach ($collectionFCMTokensUser as $itemFCMTokens) {
                $itemFCMTokens->request_id = $itemRequest->id;
                $itemFCMTokens->company_id = null;
                $itemFCMTokens->user_id = $itemFCMTokens->uc_id;
                $itemFCMTokens->isStatusOrApproved = $itemRequest->isStatusOrApproved;

                $collectionUsers['collectionFCMTokensUsers'] = $itemFCMTokens;
                $collectionUsers['countUnreadResponse'] = $countUnreadResponse;

                $collectionFCMTokensUsers[] = $collectionUsers;
            }
        }

        return $collectionFCMTokensUsers;
    }

    /**
     * Process request user expired with company unpick
     *
     * @param $collectionRequestUserExpiredFormat
     * @return array
     */
    private function _formatDataRequestUserByCompanyUnpick($collectionRequestUserExpiredFormat)
    {
        if ($collectionRequestUserExpiredFormat->isEmpty()) {
            return [];
        }

        $tempMessage = mergeArrayNotify(\Config::get('notification.TEMP_MSG_PUSH_NOTIFY.REQUEST_USER_EXPIRED_TO_COMPANY_AGREE'));
        foreach ($collectionRequestUserExpiredFormat as $collection => $itemRequest) {
            if ($itemRequest->isStatusOrApproved == true) {
                continue;
            }

            // Get all companies around from address
            $listCompany = $itemRequest->listCompanyFollowAddress;
            $listCompanyIDByCompanyResponse = isset($itemRequest->listCompanyIDByCompanyResponse) ? $itemRequest->listCompanyIDByCompanyResponse : [];
            if ($listCompany->isNotEmpty()) {
                // Push notification for all company response unpick
                $arrayMsg = $tempMessage;
                $arrayMsg['request_id'] = $itemRequest->id;

                foreach ($listCompany as $company) {
                    // Check company pick request user expired and add push notify
                    if (in_array($company->id, $listCompanyIDByCompanyResponse)) {
                        continue;
                    }
                    $countUnreadRequest = $this->requestUserRepository->countUnreadByAddress($company->corresponding_area, $company->id);
                    $deviceTokens = TransFormatApi::formatDataPushForCompany($company, $itemRequest->id);
                    $arrayMsg['count_unread_request'] = $countUnreadRequest;
                    $arrayMsg['count_unapproved'] = $this->responseForUserRepository->countUnapprovedRequest($company->id);
                    $arrayMsg['company_id'] = $company->id;
                    $arrayMsg['isStatusOrApproved'] = false;
                    $arrayMsg['is_responded'] = self::IS_NOT_RESPONDED;

                    $this->pushNotification($arrayMsg, $deviceTokens['android'], $deviceTokens['ios']);
                }
            }
        }
    }

    /**
     * Push notification for user
     *
     * @param $collectionFCMTokensUsers
     */
    private function _requestUserExpiredTwentyMinute($collectionFCMTokensUsers)
    {
        if ($collectionFCMTokensUsers->isEmpty()) {
            return;
        }
        foreach ($collectionFCMTokensUsers as $itemFcm) {
            $arrayMsgUser = mergeArrayNotify(\Config::get('notification.TEMP_MSG_PUSH_NOTIFY.REQUEST_USER_EXPIRED_TO_USER'));
            $arrayMsgUser['count_unread_response'] = $itemFcm['countUnreadResponse'];

            $deviceTokens = TransFormatApi::formatDataDeviceToken(collect([$itemFcm['collectionFCMTokensUsers']]));

            $this->pushNotification($arrayMsgUser, $deviceTokens['android'], $deviceTokens['ios']);
        }
    }

    /**
     * Push notification for company
     *
     * @param $collectionFCMTokensCompanies
     */
    private function _requestUserExpiredFiveMinute($collectionFCMTokensCompanies)
    {
        if ($collectionFCMTokensCompanies->isEmpty()) {
            return;
        }
        foreach ($collectionFCMTokensCompanies as $itemFcm) {
            $arrayMsg = mergeArrayNotify(\Config::get('notification.TEMP_MSG_PUSH_NOTIFY.REQUEST_USER_EXPIRED_TO_COMPANY_AGREE'));
            $arrayMsg['count_unread_request'] = $itemFcm['countUnreadRequest'];
            $arrayMsg['count_unapproved'] = $itemFcm['countUnApproved'];
            $arrayMsg['is_responded'] = isset($itemFcm['is_responded']) ? $itemFcm['is_responded'] : self::IS_NOT_RESPONDED;

            $deviceTokens = TransFormatApi::formatDataDeviceToken(collect([$itemFcm['collectionFCMTokensCompanies']]));

            $this->pushNotification($arrayMsg, $deviceTokens['android'], $deviceTokens['ios']);
        }
    }
}
