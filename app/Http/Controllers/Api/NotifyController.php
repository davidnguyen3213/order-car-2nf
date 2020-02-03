<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Repository\Eloquent\NotifyCationRepository;
use App\Repository\Eloquent\DeviceTokenRepository;
use Validator;

class NotifyController extends BaseController
{
    protected $notifyCationRepository;
    protected $deviceTokenRepository;

    public function __construct(
        NotifyCationRepository $notifyCationRepository,
        DeviceTokenRepository $deviceTokenRepository
    ){
        $this->notifyCationRepository = $notifyCationRepository;
        $this->deviceTokenRepository = $deviceTokenRepository;
    }
    public function getListNotify(Request $request){
        try{
            $rules = [
                'device_token' => 'required',
                'uc_id' => 'required',
                'type'=> 'required'
            ];
            $validtion = Validator::make($request->all(), $rules);
            if ($validtion->fails()) {
                if ($validtion->errors()->has("device_token")) {
                    return $this->sendResponse([], __($validtion->errors()->first('device_token')), 1);
                }
                if ($validtion->errors()->has("uc_id")) {
                    return $this->sendResponse([], __($validtion->errors()->first('uc_id')), 1);
                }
                if ($validtion->errors()->has("type")) {
                    return $this->sendResponse([], __($validtion->errors()->first('type')), 1);
                }
            }
            $device_id = $this->deviceTokenRepository->getDeviceId($request->device_token, $request->uc_id, $request->type);
            $listNotify = $this->notifyCationRepository->getListNotifyForApp($device_id, $request->type );
            return $this->sendResponse($listNotify, __('Successfully.'));
        } catch (\Exception $ex) {
            return $this->sendError(__($ex->getMessage()));
        }
    }
    public function readNotify(Request $request){
        try{
            $rules = [
                'device_token' => 'required',
                'notify_id' => 'required',
                'uc_id' => 'required',
            ];
            $validtion = Validator::make($request->all(), $rules);
            if($validtion->fails()){
                if($validtion->errors()->has("notify_id")){
                    return $this->sendResponse([], __($validtion->errors()->first('notify_id')),1);
                }
                if ($validtion->errors()->has("device_token")) {
                    return $this->sendResponse([], __($validtion->errors()->first('device_token')),1);
                }
                if ($validtion->errors()->has("uc_id")) {
                    return $this->sendResponse([], __($validtion->errors()->first('uc_id')), 1);
                }
            }
            $notify_id = $request->notify_id;
            $device_token = $request->device_token;
            $uc_id = $request->uc_id;
            //check exits device & notify
            $device_id = $this->deviceTokenRepository->firstWhere([['device_token', $device_token],['uc_id', $uc_id]])->id;
            if( !empty($device_id)){
                $insert = $this->deviceTokenRepository->insertNotify($device_id, $notify_id);
                if ($insert == "success") {
                    return $this->sendResponse([], __('Successfully.'));
                } else {
                    return $this->sendResponse([], __('Account is not valid'), 1);
                }
            }
            else{
                return $this->sendResponse([], __("Not found notify or device"));
            }
        } catch (\Exception $ex) {
            return $this->sendError(__($ex->getMessage()));
        }
    }
}