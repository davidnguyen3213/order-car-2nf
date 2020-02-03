<?php

namespace App\Repository\Contracts;

use App\Repository;

interface NotifyCationInterface extends RepositoryInterface
{
    public function getListNotify(int $limit, int $offset, array $orderBy);
    public function countListNotify();
    public function getLastRecord();
    public function getIdNotifyUnique($title, $mesg);
    public function getListNotifyForApp(int $device_id, int $type);
}
