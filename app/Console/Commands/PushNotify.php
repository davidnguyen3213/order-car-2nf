<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Repository\Eloquent\NotifyCationRepository;
use App\Repository\Eloquent\FCMTokenRepository;
use App\Helpers\PushNotification;
use App\Helpers\TransFormatApi;

class PushNotify extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pushNotify:send {push_type=0}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'admin push notify for app';

    protected $notifyCationRepository;
    protected $FCMTokenRepository;
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(NotifyCationRepository $notifyCationRepository, FCMTokenRepository $FCMTokenRepository)
    {
        parent::__construct();
        $this->FCMTokenRepository = $FCMTokenRepository;
        $this->notifyCationRepository = $notifyCationRepository;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // 0: all, 1:company, 2:user
        $push_type = $this->argument('push_type');
        $data_notify = $this->notifyCationRepository->getLastRecord();
        $data_deviceTokens = $this->FCMTokenRepository->getDeviceTokenForAdminPush($push_type);
        try{         
            foreach ($data_deviceTokens->chunk(10000) as  $key => $data_device) {
                // send notify
                $deviceTokensUser = TransFormatApi::formatDataDeviceToken($data_device);
                $arrayMsgUser = [
                    'title' => $data_notify['title'],
                    'messageBody' => $data_notify['message'],
                    'type_app' => $push_type,
                    'notify_id' => $data_notify['id'],
                    'status_code' => config('constants.STATUS_CODE_NOTIFY.ADMIN_PUSH_NOTIFY')
                ];
                PushNotification::sendNotification($arrayMsgUser, $deviceTokensUser['android'], $deviceTokensUser['ios']);
            }
        }
        catch(\Exception $e){
            $this->info($e);
        }
    }
}
