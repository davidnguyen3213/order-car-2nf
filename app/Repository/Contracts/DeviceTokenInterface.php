<?php

namespace App\Repository\Contracts;

use App\Repository;

interface DeviceTokenInterface extends RepositoryInterface
{
    public function readNotify($device_id,$notify_id);
    public function insertNotify($device_id, $notify_id);
    public function checkDeviceTokenUnique($array_notify_id, $device_id);
    public function getDeviceId(string $device_token, int $uc_id, int $type);
}
