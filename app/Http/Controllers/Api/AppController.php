<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\API\BaseController as BaseController;
use App\Http\Requests\CheckAppVersionRequest;
use stdClass;

class AppController extends BaseController
{

    public function __construct()
    {

    }

    /**
     * Check app version
     * @param CheckAppVersionRequest $request
     * @return \Illuminate\Http\Response
     */
    public function checkAppVersion(CheckAppVersionRequest $request) {
        try {
            $object = new StdClass;
            if ($request['type'] == \Config::get('constants.TYPE_NOTIFY.USER')) {
                switch ($request['platform']) {
                    case \Config::get('constants.TYPE_PLATFORM.0'):
                        $object->version = (int)\Config::get('constants.APP.ANDROID_USER_APP_VERSION');
                        $object->url = \Config::get('constants.APP.ANDROID_USER_APP_DOWNLOAD_URL');
                        break;
                    case \Config::get('constants.TYPE_PLATFORM.1'):
                        $object->version = (int)\Config::get('constants.APP.IOS_USER_APP_VERSION');
                        $object->url = \Config::get('constants.APP.IOS_USER_APP_DOWNLOAD_URL');
                        break;
                }
            }

            if ($request['type'] == \Config::get('constants.TYPE_NOTIFY.COMPANY')) {
                switch ($request['platform']) {
                    case \Config::get('constants.TYPE_PLATFORM.0'):
                        $object->version = (int)\Config::get('constants.APP.ANDROID_COMPANY_APP_VERSION');
                        $object->url = \Config::get('constants.APP.ANDROID_COMPANY_APP_DOWNLOAD_URL');
                        break;
                    case \Config::get('constants.TYPE_PLATFORM.1'):
                        $object->version = (int)\Config::get('constants.APP.IOS_COMPANY_APP_VERSION');
                        $object->url = \Config::get('constants.APP.IOS_COMPANY_APP_DOWNLOAD_URL');
                        break;
                }
            }

            return $this->sendResponse($object, __('新しいバージョンがストアにリリースされています。アプリを更新してください。'));
        } catch (\Exception $ex) {
            return $this->sendError(__($ex->getMessage()));
        }
    }

}
