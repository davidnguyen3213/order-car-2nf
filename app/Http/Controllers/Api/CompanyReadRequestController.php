<?php

namespace App\Http\Controllers\API;

use App\Helpers\PushNotification;
use App\Helpers\TransFormatApi;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Http\Requests\CompanyReadRequestRequest;
use App\Http\Requests\CountUnreadRequestRequest;
use App\Repository\Eloquent\CompanyReadRequestRepository;
use App\Repository\Eloquent\CompanyRepository;
use App\Repository\Eloquent\FCMTokenRepository;
use App\Repository\Eloquent\RequestUserRepository;
use App\Repository\Eloquent\ResponseForUserRepository;


class CompanyReadRequestController extends BaseController
{

    protected $companyReadRequestRepository;
    protected $companyRepository;
    protected $requestUserRepository;
    protected $responseForUserRepository;
    protected $useFCMTokenRepository;

    public function __construct(
        CompanyReadRequestRepository $companyReadRequestRepository,
        CompanyRepository $companyRepository,
        RequestUserRepository $requestUserRepository,
        ResponseForUserRepository $responseForUserRepository,
        FCMTokenRepository $useFCMTokenRepository
    )
    {
        $this->companyReadRequestRepository = $companyReadRequestRepository;
        $this->companyRepository = $companyRepository;
        $this->requestUserRepository = $requestUserRepository;
        $this->responseForUserRepository = $responseForUserRepository;
        $this->useFCMTokenRepository = $useFCMTokenRepository;
    }

    /**
     * Read request api
     *
     * @param CompanyReadRequestRequest $request
     * @return \Illuminate\Http\Response
     */
    public function readRequest(CompanyReadRequestRequest $request)
    {
        try {
            $input = $request->all();
            $readRequest = $this->companyReadRequestRepository->firstWhere([
                'request_id' => $input['request_id'],
                'company_id' => $input['company_id']
            ]);

            if (empty($readRequest)) {
                // Create read
                $this->companyReadRequestRepository->create([
                    'request_id' => $input['request_id'],
                    'company_id' => $input['company_id']
                ]);

                // Push notification for company and except company read request
                $company = $this->companyRepository->firstWhere([
                    'id' => $input['company_id'],
                    'status_login' => \Config::get('constants.COMPANY_LOGIN.STATUS_ENABLE')
                ]);

                if (!empty($company) && isset($company['corresponding_area'])) {
                    $collectionFcmTokenCompanies = $this->useFCMTokenRepository->getDeviceToken($input['company_id'], \Config::get('constants.TYPE_NOTIFY.COMPANY'), $input['device_token']);
                    if ($collectionFcmTokenCompanies->isNotEmpty()) {
                        $countUnreadRequest = $this->requestUserRepository->countUnreadByAddress($company['corresponding_area'], $company['id']);

                        $arrayMsg = mergeArrayNotify(\Config::get('notification.TEMP_MSG_PUSH_NOTIFY.SYNC_COMPANY_READ_REQUEST'));
                        $arrayMsg['request_id'] = $input['request_id'];
                        $arrayMsg['company_id'] = $input['company_id'];
                        $arrayMsg['count_unread_request'] = $countUnreadRequest;
                        $arrayMsg['count_unapproved'] = $this->responseForUserRepository->countUnapprovedRequest($company['id']);
                        $deviceTokens = TransFormatApi::formatDataDeviceToken($collectionFcmTokenCompanies);

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
     * Count unread request api
     *
     * @param CountUnreadRequestRequest $request
     * @return \Illuminate\Http\Response
     */
    public function countUnreadRequest(CountUnreadRequestRequest $request)
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

            $countUnreadRequest = 0;
            if ($company['corresponding_area']) {
                $countUnreadRequest = $this->requestUserRepository->countUnreadByAddress($company['corresponding_area'], $company['id']);
            }

            return $this->sendResponse(['count' => $countUnreadRequest], __('Successfully.'));
        } catch (\Exception $ex) {
            return $this->sendError(__($ex->getMessage()));
        }
    }
}