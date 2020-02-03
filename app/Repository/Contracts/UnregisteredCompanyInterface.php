<?php

namespace App\Repository\Contracts;

use App\Repository;

interface UnregisteredCompanyInterface extends RepositoryInterface
{
    public function listUnregisteredCompanyCall($address, $userId);

    /**
     * @param $searchCondition
     * @param int $limit
     * @param int $offset
     * @param string $order
     * @return mixed
     */
    public function searchWithUnregisteredCompany (array  $searchCondition = [],int $limit=0,int $offset=0,array $orderBy = []);

    public function countSearchWithUnregisteredCompany (array  $searchCondition = []);
}