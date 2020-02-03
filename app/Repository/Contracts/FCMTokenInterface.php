<?php

namespace App\Repository\Contracts;

use App\Repository;

interface FCMTokenInterface extends RepositoryInterface
{
    public function getDeviceToken($ucID, $typeUserOrCompany);
    public function getDeviceTokenForAdminPush($push_type);
}