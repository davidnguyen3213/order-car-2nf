<?php

namespace App\Repository\Contracts;

use App\Repository;

interface UserInterface extends RepositoryInterface
{
    /**
     * @param $searchCondition
     * @param int $limit
     * @param int $offset
     * @param string $order
     * @return mixed
     */
    public function searchWithUser (array  $searchCondition = [],int $limit=0,int $offset=0,array $orderBy = []);

    public function countSearchWithUser (array  $searchCondition = []);
}