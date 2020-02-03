<?php

if (!function_exists('escape_like')) {
    /**
     * @param $string
     * @return mixed
     */
    function escape_like($string)
    {
        $search = array('_', '\'');
        $replace = array('\_', '');
        return str_replace($search, $replace, $string);
    }
}

if (!function_exists('subMinuteForRequest')) {
    /**
     * @param int $minute
     * @return float|int
     */
    function subMinuteForRequest(int $minute = 0)
    {
        return (int)\Carbon\Carbon::now()->subMinute($minute)->timestamp * 1000;
    }
}

if (!function_exists('generateRandomString')) {
    /**
     * @param int $length
     * @return bool|string
     */
    function generateRandomString($length = 8)
    {
        return substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $length);
    }
}

if (!function_exists('renameKeyArray')) {
    /**
     * @param array $arrayRename
     * @param bool $isAndroid
     * @return array
     */
    function renameKeyArray($arrayRename = [], $isAndroid = false)
    {
        if (isset($arrayRename['messageBody'])) {
            if ($isAndroid) {
                $arrayRename['message'] = $arrayRename['messageBody'];
            } else {
                $arrayRename['body'] = $arrayRename['messageBody'];
                $arrayRename['sound'] = 'notisound.caf';
            }
            unset($arrayRename['messageBody']);
        }

        return $arrayRename;
    }
}

if (!function_exists('mergeArrayNotify')) {
    /**
     * @param array $arrayNotify
     * @return array
     */
    function mergeArrayNotify($arrayNotify = [])
    {
        return array_merge($arrayNotify, \Config::get('notification.TEMP_MSG_PUSH_NOTIFY.MESSAGE'));
    }
}

if (!function_exists('findUserORCompanyLogin')) {
    /**
     * @param $listCollectionUserOrCompany
     * @param $passwordCheck
     * @return null || Model User || Model Company
     */
    function findUserORCompanyLogin($listCollectionUserOrCompany, $passwordCheck)
    {
        if (count($listCollectionUserOrCompany) == 0) {
            return null;
        }

        foreach ($listCollectionUserOrCompany as $itemCollection) {
            if (\Illuminate\Support\Facades\Hash::check($passwordCheck, $itemCollection->password)) {
                return $itemCollection;
            }
        }

        return null;
    }
}
if (!function_exists('checkStringTaxi')) {
    function checkStringTaxi(string $text){
        $checkText = mb_substr($text, 0, 4);
        if($checkText == 'タクシー'){
            return mb_substr($text,4);
        }
        else return $text;
    }
}
