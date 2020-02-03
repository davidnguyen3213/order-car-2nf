<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController as BaseController;
use App\Http\Requests\FCMTokenRequest;
use App\Repository\Eloquent\FCMTokenRepository;
use App\Repository\Eloquent\DeviceTokenRepository;


class FCMTokenController extends BaseController
{

    protected $useFCMTokenRepository;
    protected $deviceTokenRepository;

    public function __construct(
        FCMTokenRepository $useFCMTokenRepository,
        DeviceTokenRepository $deviceTokenRepository
    )
    {
        $this->useFCMTokenRepository = $useFCMTokenRepository;
        $this->deviceTokenRepository = $deviceTokenRepository;
    }

    /**
     * Api register device_token user or company
     *
     * @param FCMTokenRequest $request
     * @return \Illuminate\Http\Response
     */
    public function registryNotify(FCMTokenRequest $request)
    {
        try {
            $data = $request->only('uc_id', 'device_token', 'platform', 'type');

            $getDevice = $this->useFCMTokenRepository->firstWhere([
                ['type', $data['type']],
                ['platform', $data['platform']],
                ['device_token', $data['device_token']],
                ['uc_id', $data['uc_id']]
            ]);

            if (empty($getDevice)) {
                $this->useFCMTokenRepository->create($data);
            }
            //check device_token table
            $getDeviceToken = $this->deviceTokenRepository->firstWhere([
                ['type', $data['type']],
                ['device_token', $data['device_token']],
                ['platform', $data['platform']],
                ['uc_id', $data['uc_id']]
            ]);

            if (empty($getDeviceToken)) {
                $this->deviceTokenRepository->create($data);
            }
            return $this->sendResponse([], __('Successfully.'));
        } catch (\Exception $ex) {
            return $this->sendError(__($ex->getMessage()));
        }
    }

    /**
     * Api logout delete device_token
     *
     * @param FCMTokenRequest $request
     * @return \Illuminate\Http\Response
     */
    public function logoutDevice(FCMTokenRequest $request)
    {
        try {
            $data = $request->only('uc_id', 'device_token', 'platform', 'type');

            $getDevice = $this->useFCMTokenRepository->firstWhere([
                ['type', $data['type']],
                ['platform', $data['platform']],
                ['device_token', $data['device_token']],
                ['uc_id', $data['uc_id']]
            ]);

            if (!empty($getDevice)) {
                //Delete
                $this->useFCMTokenRepository->delete($getDevice->id);
            }

            return $this->sendResponse([], __('Successfully.'));
        } catch (\Exception $ex) {
            return $this->sendError(__($ex->getMessage()));
        }
    }


}