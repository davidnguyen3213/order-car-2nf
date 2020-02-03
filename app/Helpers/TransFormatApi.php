<?php

namespace App\Helpers;


class TransFormatApi
{

    const IS_RESPONDED = 1;
    const IS_NOT_RESPONDED = 0;

    /**
     * Format api by field
     *
     * @param $collectionListRequestUser
     * @return array
     */
    public static function formatApiListRequestUser($collectionListRequestUser)
    {
        if (empty($collectionListRequestUser)) {
            return [];
        }

        $result = [];
        $userDisable = \Config::get('constants.USER_LOGIN.STATUS_DISABLE');
        $userAdmin = \Config::get('constants.TYPE_USER.WEB_ADMIN');
        foreach ($collectionListRequestUser as $key => $itemRequestUser) {
            $itemUser = $itemRequestUser->users;
            // User not exits
            if (empty($itemUser)) {
                continue;
            }
            // User disable OR User is Admin
            if ($itemUser->status == $userDisable || $itemUser->type == $userAdmin) {
                continue;
            }

            $itemRequestUser->unsetRelation('users');
            //GET company a pickup request user
            $itemResponseForUsers = $itemRequestUser->responseForUsers;
            $itemRequestUser->unsetRelation('responseForUsers');

            // check if company canceled request
            if (count($itemResponseForUsers) > 0
                && isset($itemResponseForUsers[0]->is_deleted)
                && $itemResponseForUsers[0]->is_deleted == \Config::get('constants.RESPONSE_FOR_USERS.DELETED')
            ) {
                continue;
            }

            $result[$key] = $itemRequestUser->toArray();

            if (!empty($itemUser)) {
                $result[$key]['user_name'] = $itemUser->name;
                $result[$key]['user_phone'] = $itemUser->phone;
                $result[$key]['time_request'] = $itemRequestUser->created_time_request;
            }
            $result[$key]['time_pickup'] = count($itemResponseForUsers) ? $itemResponseForUsers[0]->time_pickup : null;
            $result[$key]['user_accept_time'] = count($itemResponseForUsers) ? $itemResponseForUsers[0]->user_accept_time : null;
        }

        return array_values($result);
    }

    /**
     * Get list device token a company
     * @param $itemCompany
     * @param $requestID
     * @return array
     */
    public static function formatDataPushForCompany($itemCompany, $requestID = '')
    {
        if (empty($itemCompany)) {
            return ['android' => [], 'ios' => []];
        }

        //GET company a pickup request user
        $itemResponseForUsers = $itemCompany->companyResponseUser;
        $isResponded = false;
        // check if company canceled request
        if (count($itemResponseForUsers) > 0 && !empty($requestID)) {
            foreach ($itemResponseForUsers as $itemResponseForUser) {
                if ($itemResponseForUser->request_id != $requestID
                    || $itemResponseForUser->is_deleted == \Config::get('constants.RESPONSE_FOR_USERS.DELETED')) {
                    return ['android' => [], 'ios' => []];
                }
                $isResponded = true;
            }
        }

        $tempTokens = self::_getFcmTokens($itemCompany->companyFcmTokens);
        $tempTokens['is_responded'] = $isResponded ? self::IS_RESPONDED : self::IS_NOT_RESPONDED;
        return $tempTokens;
    }

    /**
     * Get FCM token
     *
     * @param $collectionFcmTokens
     * @return array
     */
    private static function _getFcmTokens($collectionFcmTokens)
    {
        if ($collectionFcmTokens->isEmpty()) {
            return ['android' => [], 'ios' => []];
        }

        $platforms = \Config::get('constants.TYPE_PLATFORM');
        $deviceTokensAndroid = [];
        $deviceTokensIOS = [];
        foreach ($collectionFcmTokens as $keyFcm => $fcmToken) {
            $tmpPlatform = strtolower($fcmToken->platform);
            if ($tmpPlatform == $platforms[0]) {
                //Android
                $deviceTokensAndroid[] = $fcmToken->device_token;
            } elseif ($tmpPlatform == $platforms[1]) {
                //IOS
                $deviceTokensIOS[] = $fcmToken->device_token;
            }
        }

        return ['android' => $deviceTokensAndroid, 'ios' => $deviceTokensIOS];
    }

    /**
     * Get device token of company
     *
     * @param $collectionCompanies
     * @return array
     */
    public static function formatDataDeviceToken($collectionCompanies)
    {
        return self::_getFcmTokens($collectionCompanies);
    }

}