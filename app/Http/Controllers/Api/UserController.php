<?php

namespace App\Http\Controllers\API;

use App\Helpers\PushNotification;
use App\Helpers\TransFormatApi;
use App\Helpers\SendSMS;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Http\Requests\CheckPhoneUserRequest;
use App\Http\Requests\RegisterUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Requests\UserInfoRequest;
use App\Http\Requests\GenerateOTPRequest;
use App\Http\Requests\AccuracyOTPRequest;
use App\Http\Requests\ForgotPasswordRequest;
use App\Repository\Eloquent\FCMTokenRepository;
use App\Repository\Eloquent\UserRepository;
use App\Repository\Eloquent\SmsOtpRepository;
use Illuminate\Support\Facades\Auth;


class UserController extends BaseController
{

    protected $userRepository;
    protected $useFCMTokenRepository;
    protected $smsOtp;

    public function __construct(
        UserRepository $userRepository,
        FCMTokenRepository $useFCMTokenRepository,
        SmsOtpRepository $smsOtp
    )
    {
        $this->userRepository = $userRepository;
        $this->useFCMTokenRepository = $useFCMTokenRepository;
        $this->smsOtp = $smsOtp;
    }

    /**
     * Register api
     *
     * @param RegisterUserRequest $request
     * @return \Illuminate\Http\Response
     */
    public function register(RegisterUserRequest $request)
    {
        try {
            $users = $this->userRepository->findWhereAll([
                ['phone', request('phone')],
                ['type', \Config::get('constants.TYPE_USER.OTHER')],
                ['is_deleted', \Config::get('constants.USER_DELETED.ACTIVE')]
            ]);

            $resultUser = findUserORCompanyLogin($users, request('password'));

            if (!empty($resultUser)) {
                return $this->sendResponse(["phone" => ["指定のphoneは既に使用されています。"]], __('The given data was invalid.'), true, true);
            }
            
            $data = $request->all();
            $data['raw_pass'] = $data['password'];
            $data['password'] = bcrypt($data['raw_pass']);
            $data['type'] = \Config::get('constants.TYPE_USER.OTHER');

            $result = $this->userRepository->create($data);
            if ($result) {
                $fields['mobilenumber'] = str_replace("-","", $data['phone']);
                $fields['smstext'] = "運転代行Yobooアプリのアカウント登録を完了しました。";
                SendSMS::sendSMS($fields);
            }
            return $this->sendResponse([], __('User register successfully.'));
        } catch (\Exception $ex) {
            return $this->sendError(__($ex->getMessage()));
        }
    }

    /**
     * @return \Illuminate\Http\Response
     */
    public function login()
    {
        $users = $this->userRepository->findWhereAll([
            ['phone', request('phone')],
            ['type', \Config::get('constants.TYPE_USER.OTHER')],
            ['is_deleted', \Config::get('constants.USER_DELETED.ACTIVE')]
        ]);

        $resultUser = findUserORCompanyLogin($users, request('password'));

        if (empty($resultUser)) {
            return $this->sendResponse([], __('電話番号またはパスワードが違います。'), true, true);
        }

        if ($resultUser['status'] != \Config::get('constants.USER_LOGIN.STATUS_ENABLE')) {
            return $this->sendResponse([], __('ご利用のアカウントではログインできません。こちらからお問い合わせください。'), true);
        }
        $success = $resultUser;
        $success['token'] = 'Bearer ' . $resultUser->createToken('Laravel Password Grant Client')->accessToken;

        return $this->sendResponse($success, __('User login successfully.'));
    }

    /**
     * @param UpdateUserRequest $request
     * @return \Illuminate\Http\Response
     */
    public function updateInfo(UpdateUserRequest $request)
    {
        try {
            $input = $request->all();

            $user = $this->userRepository->firstWhere([
                'id' => $input['user_id'],
                'type' => \Config::get('constants.TYPE_USER.OTHER'),
                'status' => \Config::get('constants.USER_LOGIN.STATUS_ENABLE'),
            ]);
            // invalid user
            if (empty($user)) {
                return $this->sendResponse([], __('ユーザー情報が更新できません。'), true);
            }
            // if nothing change
            if ($user['name'] == $input['name'] && $user['raw_pass'] == $input['password']) {
                return $this->sendResponse([], __('You did not update anything'));
            }

            $this->userRepository->update([
                'name' => $input['name'],
                'raw_pass' => $input['password'],
                'password' => bcrypt($input['password'])
            ], $input['user_id']);

            //Push notification to user
            $collectionFcmTokenUsers = $this->useFCMTokenRepository->getDeviceToken(
                $input['user_id'],
                \Config::get('constants.TYPE_NOTIFY.USER'),
                $input['device_token']
            );

            if ($collectionFcmTokenUsers->isNotEmpty()) {
                $arrayMsg = mergeArrayNotify(\Config::get('notification.TEMP_MSG_PUSH_NOTIFY.USER_UPDATE'));
                $deviceTokens = TransFormatApi::formatDataDeviceToken($collectionFcmTokenUsers);

                PushNotification::sendNotification($arrayMsg, $deviceTokens['android'], $deviceTokens['ios']);

                // Delete device token by user_id
                foreach ($collectionFcmTokenUsers as $item) {
                    $this->useFCMTokenRepository->delete($item->id);
                }
            }

            return $this->sendResponse([], __('User updated successfully.'));
        } catch (\Exception $ex) {
            return $this->sendError(__($ex->getMessage()));
        }
    }

    /**
     * @param UserInfoRequest $request
     * @return \Illuminate\Http\Response
     */
    public function userInfo(UserInfoRequest $request)
    {
        try {
            $input = $request->only('user_id');

            $user = $this->userRepository->find($input['user_id']);

            return $this->sendResponse($user->toArray(), __('Successfully.'));
        } catch (\Exception $ex) {
            return $this->sendError(__($ex->getMessage()));
        }
    }

    /**
     * Check phone User
     *
     * @param CheckPhoneUserRequest $request
     * @return \Illuminate\Http\Response
     */
    public function checkPhoneUser(CheckPhoneUserRequest $request)
    {
        try {
            $input = $request->only('phone');

            $user = $this->userRepository->firstWhere([
                'phone' => $input['phone'],
                'is_deleted' => \Config::get('constants.USER_DELETED.ACTIVE')
            ]);

            if (empty($user)) {
                return $this->sendResponse([], __('Successfully.'));
            }

            return $this->sendResponse([], __('Error.'), true);
        } catch (\Exception $ex) {
            return $this->sendError(__($ex->getMessage()));
        }
    }

    /**
     * Generate OTP
     * @param GenerateOTPRequest $request
     * @return \Illuminate\Http\Response
     */
    public function generateOtp(GenerateOTPRequest $request) {
        try {
            $otp = mt_rand(100000, 999999);
            $fields['mobilenumber'] = str_replace("-","", $request['phone']);
            $fields['smstext'] = "暗証番号は「" . $otp . "」です。";
            $this->smsOtp->updateOrCreate(['phone' => $request['phone']], ['otp_code' => $otp]);
            $result = SendSMS::sendSMS($fields);
            if ($result) {
                return $this->sendResponse([], __('Successfully.'));
            }
            return $this->sendResponse([], __('Error.'), true);
        } catch (\Exception $ex) {
            return $this->sendError(__($ex->getMessage()));
        }
    }

    /**
     * Accuracy OTP
     * @param AccuracyOTPRequest $request
     * @return \Illuminate\Http\Response
     */
    public function accuracyOtp(AccuracyOTPRequest $request) {
        try {
            $otp = $this->smsOtp->firstWhere([
                ['phone', '=', $request['phone']],
                ['otp_code', '=', $request['otp_code']],
                ['updated_at', '>=', date('Y-m-d H:i:s', time() - 300)],
            ]);

            if (!empty($otp)) {
                return $this->sendResponse([], __('Successfully.'));
            }

            return $this->sendResponse([], __('OTP code is incorrect.'), true);
        } catch (\Exception $ex) {
            return $this->sendError(__($ex->getMessage()));
        }
    }

    /**
     * Send Password to user by SMS
     * @param ForgotPasswordRequest $request
     * @return \Illuminate\Http\Response
     */
    public function forgotPassword(ForgotPasswordRequest $request) {
        try {
            $user = $this->userRepository->firstWhere([
                'phone' => $request['phone'],
                'type'=> \Config::get('constants.TYPE_USER.OTHER'),
                'is_deleted' => \Config::get('constants.USER_DELETED.ACTIVE'),
                'status' => \Config::get('constants.USER_LOGIN.STATUS_ENABLE'),
            ]);

            if (empty($user)) {
                return $this->sendResponse([], __('Error.'), true);
            }

            $fields['mobilenumber'] = str_replace("-","", $request['phone']);
            $fields['smstext'] = "お問合せいただいたパスワードは「" . $user['raw_pass'] . "」です。心当たりのない場合、当メールは破棄して下さい。";
            $result = SendSMS::sendSMS($fields);

            if ($result) {
                return $this->sendResponse([], __('Successfully.'));
            }
            return $this->sendResponse([], __('Error.'), true);
        } catch (\Exception $ex) {
            return $this->sendError(__($ex->getMessage()));
        }
    }
}