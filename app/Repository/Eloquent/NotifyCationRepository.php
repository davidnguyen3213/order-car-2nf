<?php

namespace App\Repository\Eloquent;

use App;
use App\Repository\Contracts\NotifyCationInterface as NotifyCationInterface;
use Illuminate\Support\Facades\DB;
class NotifyCationRepository extends BaseRepository implements NotifyCationInterface
{

    protected function model()
    {
        return \App\NotifyCation::class;
    }

    protected function getRules()
    {
        return \App\NotifyCation::rules;
    }
    public function getListNotify(int $limit = 0, int $offset = 0, array $orderBy = []){
        $model = \App\NotifyCation::query();
        return $model->skip($offset)->take($limit)->orderBy($orderBy[0], $orderBy[1])->get();
    }

    public function countListNotify(){
        $model = \App\NotifyCation::query();
        return $model->count();
    }

    public function getLastRecord()
    {
        $model = \App\NotifyCation::query();
        return $model->orderBy('created_at', 'desc')->first();
    }
    public function getIdNotifyUnique( $title="", $mesg = ""){
        $model = \App\NotifyCation::query()->select('id');
        $check_title = $model->where('title', $title);
        if ($check_title != null) {
            $check_mesg = $check_title->where('message', $mesg);
            if ($check_mesg != null) {
                return $check_mesg->get()->toArray();
            } else return [];
        } else return [];
    }
    public function getListNotifyForApp(int $device_id = 0, int $type = 1)
    {
        $list_type = [$type, config('constants.TYPE_NOTIFY.All')];
        $pivot = App\NotifyCation::query()->select('title', 'message', 'id as notify_id')
            ->whereIn('type', $list_type)
            ->whereRaw("notify_cations.id NOT IN (SELECT pivot.notify_id FROM device_notify pivot WHERE pivot.device_id = $device_id)")
            ->whereRaw("notify_cations.created_at >= (SELECT device.created_at FROM device_tokens device WHERE device.id = $device_id)");
        $list_not_read = $pivot->orderBy('notify_cations.created_at', 'asc')->get()->toArray();
        return $list_not_read;
    }
}
