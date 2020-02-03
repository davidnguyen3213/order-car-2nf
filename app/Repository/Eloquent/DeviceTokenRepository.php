<?php

namespace App\Repository\Eloquent;

use App;
use App\Repository\Contracts\FavouriteInterface as FavouriteInterface;
use Exception;
use Illuminate\Support\Facades\DB;

class DeviceTokenRepository extends BaseRepository implements FavouriteInterface
{

    protected function model()
    {
        return \App\DeviceTokens::class;
    }

    protected function getRules()
    {
        return \App\DeviceTokens::rules;
    }
    
    public function insertNotify(string $device_id, int $notify_id)
    {
        $pivotTable = DB::table('device_notify');
        try{
            $pivotTable->insert([
                'device_id' => $device_id,
                'notify_id' => $notify_id,
            ]);
            return "success";
        } 
        catch(\Exception $e) {
            return "notify had read";
        }
    }
    public function checkDeviceTokenUnique(array $ids_notify = [], $device_id =''){
        $pivotTable = DB::table('device_notify');
        if($device_id != ""){
            if ($ids_notify == []) {
                return 'true';
            }
            else{
                $check = $pivotTable->where("device_id", $device_id)
                                    ->whereIn('notify_id', $ids_notify)
                                    ->first();
                if($check != null){ return 'false'; }
                else { return 'true'; }
            }

        }
        else return false;
    }
    public function getDeviceId(string $device_token, int $uc_id, int $type){
        $model = \App\DeviceTokens::query();
        $result = $model->where([['device_token',$device_token], ['uc_id',$uc_id], ['type', $type]])->first()->id;
        return $result;
    }
}
