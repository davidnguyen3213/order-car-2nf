<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Repository\Eloquent\FCMTokenRepository;
use App\Helpers\PushNotification;
use App\Helpers\TransFormatApi;
use Illuminate\Support\Facades\Log;

class ScheduleRemoveNotRegisteredDeviceToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'schedule:check-not-registered-device-id';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Admin check not registered device token';

    protected $FCMTokenRepository;

    /**
     * ScheduleRemoveNotRegisteredDeviceToken constructor.
     * @param FCMTokenRepository $FCMTokenRepository
     */
    public function __construct(FCMTokenRepository $FCMTokenRepository)
    {
        parent::__construct();
        $this->FCMTokenRepository = $FCMTokenRepository;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {

            //log::channel("debug")->info("START CHECK: ". time());
            // 0: all, 1:company, 2:user
            $push_type = 0;
            $data_deviceTokens = $this->FCMTokenRepository->getDeviceTokenForAdminPush($push_type);

            foreach ($data_deviceTokens->chunk(10000) as  $key => $data_device) {
                $deleteDeviceTokenAndroid = [];
                $deleteDeviceTokenIos = [];

                // send notify
                $deviceTokensUser = TransFormatApi::formatDataDeviceToken($data_device);
                $arrayMsgUser = [
                    'title' => "代行アプリ通知",
                    'messageBody' => "CHECK DEVICE",
                    'type_app' => $push_type,
                    'status_code' => "APP000"
                ];
                $result = PushNotification::sendNotification($arrayMsgUser, $deviceTokensUser['android'], $deviceTokensUser['ios'], true);

                if (!empty($deviceTokensUser['android']) && isset($result['resultAndroid']) && !empty($result['resultAndroid'])) {
                    $resultA = [];
                    if (isset($result['resultAndroid']->results)) {
                        $resultA = $result['resultAndroid']->results;
                    }

                    if (!empty($resultA)) {
                        foreach ($deviceTokensUser['android'] as $keyA => $deviceA) {
                            if (isset($resultA[$keyA]->error)) {
                                if ($resultA[$keyA]->error == "NotRegistered") {
                                    $deleteDeviceTokenAndroid[] = $deviceA;
                                }
                            }
                        }
                    }

                    if (!empty($deleteDeviceTokenAndroid)) {
                        $this->FCMTokenRepository->deleteByDeviceToken($deleteDeviceTokenAndroid);
                    }
                }

                if (!empty($deviceTokensUser['ios']) && isset($result['resultIOS']) && !empty($result['resultIOS'])) {
                    $resultI = [];
                    if (isset($result['resultIOS']->results)) {
                        $resultI = $result['resultIOS']->results;
                    }

                    if (!empty($resultI)) {
                        foreach ($deviceTokensUser['ios'] as $keyI => $deviceI) {
                            if (isset($resultI[$keyI]->error)) {
                                if ($resultI[$keyI]->error == "NotRegistered") {
                                    $deleteDeviceTokenIos[] = $deviceI;
                                }
                            }
                        }
                    }

                    if (!empty($deleteDeviceTokenIos)) {
                        $this->FCMTokenRepository->deleteByDeviceToken($deleteDeviceTokenIos);
                    }
                }

            }
            //log::channel("debug")->info("END CHECK: ". time());

        } catch(\Exception $e) {
            $this->info($e);
        }
    }
}
