<?php

namespace App\Services;


use App\Helpers\PushNotification;

class PushNotificationWorker
{

    /**
     * Common push notification
     *
     * @param array $arrayMsg
     * @param array $deviceTokenAndroids
     * @param array $deviceTokenIOS
     * @return mixed|null
     */
    protected static function pushNotification($arrayMsg = [], $deviceTokenAndroids = [], $deviceTokenIOS = [])
    {
        if (empty($arrayMsg) || (empty($deviceTokenAndroids) && empty($deviceTokenIOS))) {
            return null;
        }
        $resultPush = PushNotification::sendNotification($arrayMsg, $deviceTokenAndroids, $deviceTokenIOS);

        return $resultPush;
    }
}