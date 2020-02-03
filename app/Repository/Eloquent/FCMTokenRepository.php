<?php

namespace App\Repository\Eloquent;

use App;
use App\Repository\Contracts\FCMTokenInterface as FCMTokenInterface;

class FCMTokenRepository extends BaseRepository implements FCMTokenInterface
{

    protected function model()
    {
        return \App\FCMToken::class;
    }

    protected function getRules()
    {
        return \App\FCMToken::rules;
    }

    /**
     * get device token
     *
     * @param $ucID
     * @param $typeUserOrCompany
     * @param $exceptDeviceToken
     * @return mixed
     */
    public function getDeviceToken($ucID, $typeUserOrCompany, $exceptDeviceToken = '')
    {
        $deviceTokens = \App\FCMToken::where('uc_id', $ucID);
        if ($typeUserOrCompany == \Config::get('constants.TYPE_NOTIFY.USER')) {
            $deviceTokens = $deviceTokens->fcmTokenUsers();
        } else if ($typeUserOrCompany == \Config::get('constants.TYPE_NOTIFY.COMPANY')) {
            $deviceTokens = $deviceTokens->fcmTokenCompanies();
        }

        if ($exceptDeviceToken != '') {
            $deviceTokens = $deviceTokens->where('device_token', '!=', $exceptDeviceToken);
        }
        return $deviceTokens->get();
    }

    public function getDeviceTokenForAdminPush($push_type = 0)
    {
        $model = \App\FCMToken::query();
        if ($push_type != 0) {
            $results = $model->where('type', $push_type);
        } else {
            $results = $model;
        }
        return $results->get();
    }

    public function deleteByDeviceToken($deviceTokens = [])
    {
        $model = \App\FCMToken::query();

        $results = $model->whereIn('device_token', $deviceTokens);

        return $results->delete();
    }
}
