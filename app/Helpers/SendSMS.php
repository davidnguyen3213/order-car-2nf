<?php

namespace App\Helpers;


class SendSMS
{
    const URL_SEND_SMS = 'https://www.sms-console.jp/api/';
    const STATUS_SEND_SMS_SUCCESSFULLY = 200;

    // Change the above three variables as per your app.
    public function __construct()
    {
        exit('Init function is not allowed');
    }

    /**
     * Send SMS
     * @param array $fields
     * @return bool
     */
    public static function sendSMS($fields = [])
    {
        if (empty($fields) || !isset($fields['mobilenumber']) || !isset($fields['smstext'])) {
            return false;
        }
        $fields['username'] = \Config::get('constants.SMS_CONFIG.USERNAME');
        $fields['password'] = \Config::get('constants.SMS_CONFIG.PASSWORD');
        $result = self::_useCurl($fields);
        if ($result == self::STATUS_SEND_SMS_SUCCESSFULLY) {
            return true;
        }

        return false;
    }

    /**
     * Create Curl
     *
     * @param $headers
     * @param null $fields
     * @return mixed
     */
    private static function _useCurl($fields = null, $headers = [])
    {
        // Open connection
        $ch = curl_init();
        // Set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_URL, self::URL_SEND_SMS);
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