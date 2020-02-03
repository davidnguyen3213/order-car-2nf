<?php

namespace App\Helpers;


class PushNotification
{
    const URL_PUSH_NOTIFY = 'https://fcm.googleapis.com/fcm/send';

    // Change the above three variables as per your app.
    public function __construct()
    {
        exit('Init function is not allowed');
    }

    /**
     * Functionality to send notification.
     *
     * @param $arrayMsg
     * @param $listTokensAndroid
     * @param $listTokensIOS
     * @return mixed
     */
    public static function sendNotification($arrayMsg = [], $listTokensAndroid = [], $listTokensIOS = [], $dryRun = false)
    {
        $headers = array(
            'Authorization: key=' . \Config::get('constants.FCM_SERVER_KEY'),
            'Content-Type: application/json'
        );

        $fields = array(
            'content_available' => true,
            'mutable_content' => true,
            'dry_run' => $dryRun
        );

        //Push notification Android
        $responseAndroid = [];
        if (count($listTokensAndroid)) {
            $arrayMsgAndroid = renameKeyArray($arrayMsg, true);
            $fieldsAndroid = [
                'registration_ids' => $listTokensAndroid,
                'data' => $arrayMsgAndroid
            ];

            $responseAndroid = self::_useCurl($headers, json_encode(array_merge($fields, $fieldsAndroid)));
        }

        //Push notification IOS
        $responseIOS = [];
        if (count($listTokensIOS)) {
            $arrayMsgIOS = renameKeyArray($arrayMsg);
            if (isset($arrayMsgIOS['count_unread_response'])) {
                $arrayMsgIOS['badge'] = $arrayMsgIOS['count_unread_response'];
            }

            if (isset($arrayMsgIOS['count_unread_request']) || isset($arrayMsgIOS['count_unapproved'])) {
                $arrayMsgIOS['badge'] = (isset($arrayMsgIOS['count_unread_request']) ? $arrayMsgIOS['count_unread_request'] : 0)
                    + (isset($arrayMsgIOS['count_unapproved']) ? $arrayMsgIOS['count_unapproved'] : 0);
            }

            $fieldsIOS = [
                'registration_ids' => $listTokensIOS,
                'notification' => $arrayMsgIOS
            ];

            $responseIOS = self::_useCurl($headers, json_encode(array_merge($fields, $fieldsIOS)));
        }

        return ['resultAndroid' => $responseAndroid, 'resultIOS' => $responseIOS];
    }

    /**
     * Create Curl
     *
     * @param $headers
     * @param null $fields
     * @return mixed
     */
    private static function _useCurl($headers, $fields = null)
    {
        // Open connection
        $ch = curl_init();
        // Set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_URL, self::URL_PUSH_NOTIFY);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Disabling SSL Certificate support temporarily
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        if ($fields) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        }

        // Execute post
        $result = curl_exec($ch);

        if ($result === FALSE) {
            die('Curl failed: ' . curl_error($ch));
        }

        $result = json_decode($result);
        // Close connection
        curl_close($ch);

        return $result;
    }
}