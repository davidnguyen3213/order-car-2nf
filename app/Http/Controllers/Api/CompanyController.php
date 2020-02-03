<?php

namespace App\Http\Controllers\API;

use App\Helpers\TransFormatApi;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Http\Requests\CompanyInfoRequest;
use App\Http\Requests\ListRequestUserForCompanyRequest;
use App\Http\Requests\OnOffPushNotifyRequest;
use App\Http\Requests\RegisterCompanyRequest;
use App\Repository\Eloquent\CompanyRepository;
use App\Repository\Eloquent\RequestUserRepository;
use Illuminate\Support\Facades\Mail;


class CompanyController extends BaseController
{

    protected $companyRepository;
    protected $requestUserRepository;

    public function __construct(
        CompanyRepository $companyRepository,
        RequestUserRepository $requestUserRepository
    )
    {
        $this->companyRepository = $companyRepository;
        $this->requestUserRepository = $requestUserRepository;
    }

    /**
     * Company info api
     *
     * @param CompanyInfoRequest $request
     * @return \Illuminate\Http\Response
     */
    public function companyInfo(CompanyInfoRequest $request)
    {
        try {
            $input = $request->only('company_id');

            $user = $this->companyRepository->find($input['company_id']);

            return $this->sendResponse($user->toArray(), __('Successfully.'));
        } catch (\Exception $ex) {
            return $this->sendError(__($ex->getMessage()));
        }
    }

    /**
     * Loin company api
     *
     * @return \Illuminate\Http\Response
     */
    public function loginCompany()
    {
        $phone_to_login = str_replace('-', '', trim(request('phone')));
        $companies = $this->companyRepository->findWhereAll([
            ['phone_to_login', $phone_to_login],
            ['is_deleted', \Config::get('constants.COMPANY_DELETED.ACTIVE')]
        ]);

        $resultCompany = findUserORCompanyLogin($companies, request('password'));

        if (empty($resultCompany)) {
            return $this->sendResponse((object)[], __('電話番号またはパスワードが違います。'), true, true);
        }

        if ($resultCompany['status_login'] != \Config::get('constants.COMPANY_LOGIN.STATUS_ENABLE')) {
            return $this->sendResponse((object)[], __('ご利用のアカウントではログインできません。こちらからお問い合わせください。'), true);
        }

        $success = $resultCompany;
        $success['token'] = 'Bearer ' . $resultCompany->createToken('Laravel Password Grant Client')->accessToken;

        return $this->sendResponse($success, __('User login successfully.'));
    }

    /**
     * On - Off push notification in company
     *
     * @param OnOffPushNotifyRequest $request
     * @return \Illuminate\Http\Response
     */
    public function onOffPushNotify(OnOffPushNotifyRequest $request)
    {
        try {
            $input = $request->only('company_id', 'status_notify');
            $company = $this->companyRepository->find($input['company_id']);

            if (!empty($company)) {
                $statusNotify = \Config::get('constants.NOTIFY.ON') == $input['status_notify']
                    ? \Config::get('constants.NOTIFY.ON')
                    : \Config::get('constants.NOTIFY.OFF');

                $this->companyRepository->update(['status_notify' => $statusNotify], $company['id']);
                return $this->sendResponse([], __('Successfully.'));
            }

            return $this->sendResponse([], __('代行会社は不存在です。'), true);
        } catch (\Exception $ex) {
            return $this->sendError(__($ex->getMessage()));
        }
    }

    /**
     * List request user for company
     *
     * @param ListRequestUserForCompanyRequest $request
     * @return \Illuminate\Http\Response
     */
    public function listRequestUserForCompany(ListRequestUserForCompanyRequest $request)
    {
        try {
            $input = $request->only('company_id');

            $company = $this->companyRepository->firstWhere(
                ['id' => $input['company_id']],
                ['corresponding_area', 'id']
            );

            if (empty($company)) {
                return $this->sendResponse([], __('代行会社は不存在です。'), true);
            }

            $result = [];
            if ($company->corresponding_area) {
                $response = $this->requestUserRepository->listRequestUserSearchAddress($company->corresponding_area, $company->id);
                $result = TransFormatApi::formatApiListRequestUser($response);
            }

            return $this->sendResponse($result, __('Successfully.'));
        } catch (\Exception $ex) {
            return $this->sendError(__($ex->getMessage()));
        }
    }

    /**
     * Register company
     *
     * @param RegisterCompanyRequest $request
     * @return \Illuminate\Http\Response
     */
    public function registerCompany(RegisterCompanyRequest $request)
    {
        try {
            $data = $request->all();

            $data['phone_to_login'] = str_replace('-', '', trim($data['phone']));
            $keyRandomString = generateRandomString();
            $keyRandomString = $this->companyRepository->checkRegisterPhonePassword($data['phone_to_login'], $keyRandomString);

            $data['raw_pass'] = $keyRandomString;
            $data['password'] = bcrypt($data['raw_pass']);
            // Send mail confirm
            if ($this->companyRepository->create($data)) {
                $dataMail = [
                    'company' => $data['name'],
                    'person_charged' => $data['person_charged'],
                    'address' => $data['address'],
                    'phone' => $data['phone'],
                    'app_name' => \Config::get('app.name'),
                    'admin_email' => \Config::get('mail.admin_email'),
                ];

                // send mail to admin
                Mail::send('mail.registerCompany', $dataMail, function ($message) use ($request) {
                    $message->to(\Config::get('mail.username'))->subject('Register Confirm');
                });
            }

            return $this->sendResponse([], __('Company register successfully.'));
        } catch (\Exception $ex) {
            return $this->sendError(__($ex->getMessage()));
        }
    }
}
